<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\Calculator;
use FacturaScripts\Core\Model\Cliente;
use FacturaScripts\Core\Template\ApiController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\FacturaCliente;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\TravelAgency\Model\Paquete;
use FacturaScripts\Plugins\TravelAgency\Model\ReservaPasajero;
use FacturaScripts\Plugins\TravelAgency\Model\Pasajero;
use FacturaScripts\Plugins\TravelAgency\Model\Reserva;
use FacturaScripts\Dinamic\Model\ReciboCliente;
use FacturaScripts\Core\Model\AttachedFile;
use FacturaScripts\Core\Model\AttachedFileRelation;

class ApiControllerReserva extends ApiController
{
    protected function runResource(): void
    {
        $logPath = __DIR__ . '/../sync-reserva.log';
        Tools::log()->notice('Inicio sync-reserva');
        file_put_contents($logPath, "\n\n--- NUEVA EJECUCIÓN: " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);

        try {
            $post = $this->request->request->all();
            // Copia del post para log
            $logPost = $post;

            foreach ($logPost as $key => $value) {
                // Si es un array (como cliente o pasajeros), procesamos internamente
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        if (is_array($subValue)) {
                            foreach ($subValue as $campo => $contenido) {
                                if (strpos($campo, 'file_') === 0 && is_string($contenido)) {
                                    $logPost[$key][$subKey][$campo] = '[base64: OK]';
                                }
                            }
                        } elseif (strpos($subKey, 'file_') === 0 && is_string($subValue)) {
                            $logPost[$key][$subKey] = '[base64: OK]';
                        }
                    }
                } elseif (strpos($key, 'file_') === 0 && is_string($value)) {
                    $logPost[$key] = '[base64: OK]';
                }
            }

            file_put_contents($logPath, "POST recibido: " . json_encode($logPost) . "\n", FILE_APPEND);


            // 1. Cliente
            $cliente = $this->obtenerOCrearCliente($post['cliente'], $logPath);

            // 2. Factura
            $factura = $this->crearFacturaReserva($cliente, $post['reserva'], $logPath);

            // 3. Recibo
            $this->crearReciboFactura($factura, $logPath);

            // 4. Reserva
            $reserva = $this->crearReserva($factura, $post['reserva'], $logPath);

            // 5. Archivos adjuntos (cliente y pasajeros)
            $this->guardarImagenesBase64($post, $reserva->id);

            // 6. Pasajeros
            $this->guardarPasajeros($post, $reserva->id, $cliente, $logPath);

            // 7. Actualizar plazas
            $this->actualizarPlazas($post['reserva']['paquete_id'], $reserva->num_pasajeros);

            // 8. Respuesta final
            $this->response->setContent(json_encode([
                'success' => true,
                'message' => 'Reserva y factura registradas correctamente',
                'reserva_id' => $reserva->id,
                'cliente' => $cliente->codcliente,
                'factura' => $factura->idfactura ?? 1,
            ]));
        } catch (\Throwable $e) {
            file_put_contents($logPath, "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString(), FILE_APPEND);
            $this->response->setContent(json_encode([
                'success' => false,
                'message' => 'Error al procesar la reserva.',
                'error' => $e->getMessage()
            ]));
        }
    }

    protected function obtenerOCrearCliente(array $clienteData, string $logPath): Cliente
    {
        $email = $clienteData['email'] ?? '';
        $cifnif = $clienteData['cifnif'] ?? '';
        $nombre = $clienteData['nombre'] ?? '';
        $apellido = $clienteData['apellido'] ?? '';
        $telefono = $clienteData['telefono'] ?? '';

        $clienteExistente = (new Cliente())->all([new DataBaseWhere('email', $email)]);
        if ($clienteExistente) {
            $cliente = new Cliente();
            $cliente->loadFromCode($clienteExistente[0]->codcliente);
            file_put_contents($logPath, "Cliente existente: " . $cliente->codcliente . "\n", FILE_APPEND);
        } else {
            $cliente = new Cliente();
            $cliente->cifnif = $cifnif;
            $cliente->nombre = $nombre . " " . $apellido;
            $cliente->email = $email;
            $cliente->telefono1 = $telefono;
            $cliente->tipoidfiscal = 'Pasaporte';
            $cliente->save();
            file_put_contents($logPath, "Cliente creado: " . $cliente->codcliente . "\n", FILE_APPEND);
        }

        return $cliente;
    }

    protected function crearFacturaReserva(Cliente $cliente, array $reservaData, string $logPath): FacturaCliente
    {
        $factura = new FacturaCliente();
        $factura->setSubject($cliente);
        $factura->codpago = 'TARJETA';
        $factura->fecha = date('d-m-Y');
        $factura->pagada = true;
        $factura->neto = $reservaData['total_pagado'];
        $factura->total = $reservaData['total_pagado'];
        $factura->numero2 = "Reserva online landing page ID: " . $reservaData['paquete_id'];
        $factura->save();

        // Línea
        $linea = $factura->getNewLine();
        $linea->descripcion = 'Reserva de Paquete ID ' . ($reservaData['paquete_id'] ?? '-');
        $linea->cantidad = 1;
        $linea->iva = 0;
        $linea->pvpunitario = $reservaData['total_pagado'];
        $lineas[] = $linea;
        Calculator::calculate($factura, $lineas, true);

        $factura->idestado = 11;
        $factura->save();

        file_put_contents($logPath, "Factura creada: " . $factura->idfactura . "\n", FILE_APPEND);

        return $factura;
    }

    protected function crearReciboFactura(FacturaCliente $factura, string $logPath): void
    {
        if (!empty($factura->getReceipts())) {
            return;
        }

        $receipt = new ReciboCliente();
        $receipt->idfactura = $factura->idfactura;
        $receipt->codigofactura = $factura->codigo;
        $receipt->codcliente = $factura->codcliente;
        $receipt->importe = $factura->total;
        $receipt->coddivisa = $factura->coddivisa;
        $receipt->fechapago = Tools::date();
        $receipt->codpago = $factura->codpago;
        $receipt->observaciones = 'Relacionado a reserva.';
        $receipt->pagado = true;
        $receipt->nick = 'admin';
        $receipt->save();

        file_put_contents($logPath, "Recibo generado: {$receipt->idfactura}\n", FILE_APPEND);
    }

    protected function crearReserva(FacturaCliente $factura, array $reservaData, string $logPath): Reserva
    {
        $reserva = new Reserva();
        $reserva->idfactura = $factura->idfactura;
        $reserva->codcliente = $factura->codcliente;
        $reserva->paquete_id = $reservaData['paquete_id'];
        $reserva->name = 'Reserva Online paquete: ' . $reservaData['paquete_id'];
        $reserva->fecha_reserva = date('Y-m-d');
        $reserva->forma_pago = $reservaData['forma_pago'] ?? 'TARJETA';
        $reserva->num_pasajeros = $reservaData['cantidad_pasajeros'] ?? 0;
        $reserva->total_pagado = $factura->total;
        $reserva->status = 2;
        $reserva->save();

        file_put_contents($logPath, "Reserva creada: " . $reserva->id . "\n", FILE_APPEND);

        return $reserva;
    }

    protected function guardarPasajeros(array $post, int $reservaId, $cliente, string $logPath): void
    {
        // Cliente como pasajero
        $pasajeroCliente = $this->buscarOCrearPasajero([
            'nombre' => $cliente->nombre,
            'apellido' => $cliente->apellido,
            'num_pasaporte' => $cliente->cifnif,
            'validez_pasaporte' => $post['cliente']['validez'] ?? '',
            'fecha_nacimiento' => $post['cliente']['fechaNacimiento'] ?? '',
            'nacionalidad' => $post['cliente']['nacionalidad'] ?? '',
            'tratamiento' => 'Sr.'
        ]);

        $reservaPasajero = new ReservaPasajero();
        $reservaPasajero->reserva_id = $reservaId;
        $reservaPasajero->pasajero_id = $pasajeroCliente->id;
        $reservaPasajero->habitacion_id = $post['reserva']['habitacion_id'];
        $reservaPasajero->save();

        file_put_contents($logPath, "Cliente registrado como pasajero: $pasajeroCliente->name\n", FILE_APPEND);

        // Adicionales
        foreach ($post['pasajeros'] ?? [] as $i => $p) {
            if (empty($p['nombre']) || empty($p['dni'])) {
                file_put_contents($logPath, "Pasajero $i incompleto.\n", FILE_APPEND);
                continue;
            }

            $pasajero = $this->buscarOCrearPasajero([
                'nombre' => $p['nombre'],
                'num_pasaporte' => $p['dni'],
                'validez_pasaporte' => $p['validez_pasaporte'] ?? '',
                'fecha_nacimiento' => $p['fechaNacimiento'] ?? '',
                'nacionalidad' => $p['nacionalidad'] ?? '',
                'tratamiento' => $p['tratamiento'] ?? '',
            ]);

            $reservaPasajero = new ReservaPasajero();
            $reservaPasajero->reserva_id = $reservaId;
            $reservaPasajero->pasajero_id = $pasajero->id;
            $reservaPasajero->habitacion_id = $post['reserva']['habitacion_id'];
            $reservaPasajero->save();
        }
    }

    protected function buscarOCrearPasajero(array $datos): Pasajero
    {
        $existente = (new Pasajero())->all([new DataBaseWhere('num_pasaporte', $datos['num_pasaporte'])]);

        if (!empty($existente)) {
            return $existente[0];
        }

        $pasajero = new Pasajero();
        $pasajero->name = $datos['nombre'];
        $pasajero->apellido = $datos['apellido'];
        $pasajero->num_pasaporte = $datos['num_pasaporte'];
        $pasajero->validez_pasaporte = $datos['validez_pasaporte'];
        $pasajero->fecha_nacimiento = $datos['fecha_nacimiento'];
        $pasajero->nacionalidad = $datos['nacionalidad'];
        $pasajero->tratamiento = $datos['tratamiento'];
        $pasajero->save();

        return $pasajero;
    }
    protected function actualizarPlazas(int $paqueteId, int $cantidad): void
    {
        $paquete = new Paquete();
        $paquete->loadFromCode($paqueteId);
        $paquete->plazas_reservadas += $cantidad;
        $paquete->save();
    }

    protected function guardarImagenesBase64(array $post, int $reservaId): void
    {
        $logPath = __DIR__ . '/../sync-reserva.log';
        file_put_contents($logPath, "Iniciando guardado de imágenes base64 para reserva: $reservaId\n", FILE_APPEND);

        $destFolder = FS_FOLDER . '/MyFiles';
        if (!file_exists($destFolder)) {
            mkdir($destFolder, 0777, true);
            file_put_contents($logPath, "Carpeta creada: $destFolder\n", FILE_APPEND);
        }

        $camposCliente = ['file_pasaporte', 'file_dni', 'file_foto'];

        // Guardar archivos del cliente
        foreach ($camposCliente as $campo) {
            if (!empty($post['cliente'][$campo])) {
                $this->guardarArchivoImagen(
                    $post['cliente'][$campo],
                    "{$campo}_cliente_{$reservaId}.jpg",
                    $reservaId,
                    "Imagen del comprador: {$campo}",
                    $logPath
                );
            } else {
                file_put_contents($logPath, "No se encontró base64 para cliente->{$campo}\n", FILE_APPEND);
            }
        }

        // Guardar archivos de pasajeros
        if (!empty($post['pasajeros']) && is_array($post['pasajeros'])) {
            foreach ($post['pasajeros'] as $i => $p) {
                foreach ($camposCliente as $tipo) {
                    if (!empty($p[$tipo])) {
                        $this->guardarArchivoImagen(
                            $p[$tipo],
                            "pasajero{$i}_{$tipo}_{$reservaId}.jpg",
                            $reservaId,
                            "Imagen pasajero $i: {$tipo}",
                            $logPath
                        );
                    } else {
                        file_put_contents($logPath, "No se encontró base64 para pasajero[$i]->{$tipo}\n", FILE_APPEND);
                    }
                }
            }
        }
    }

    protected function guardarArchivoImagen(string $base64Data, string $fileName, int $reservaId, string $observaciones = '', string $logPath = ''): void
    {
        $destFolder = FS_FOLDER . '/MyFiles';
        $filePath = $destFolder . '/' . $fileName;

        file_put_contents($logPath, "Intentando guardar imagen: $fileName\n", FILE_APPEND);

        $contenido = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Data));
        if (false === file_put_contents($filePath, $contenido)) {
            Tools::log()->error("Error al guardar imagen: $fileName");
            file_put_contents($logPath, "Error al escribir archivo en disco: $filePath\n", FILE_APPEND);
            return;
        }

        $file = new AttachedFile();
        $file->path = $fileName;
        $file->mimetype = 'image/jpeg';
        if (false === $file->save()) {
            Tools::log()->error("No se pudo guardar AttachedFile para: $fileName");
            file_put_contents($logPath, "Error al guardar AttachedFile: $fileName\n", FILE_APPEND);
            return;
        }

        $rel = new AttachedFileRelation();
        $rel->idfile = $file->idfile;
        $rel->model = 'Reserva';
        $rel->modelcode = $reservaId;
        $rel->modelid = $reservaId;
        $rel->nick = 'admin';
        $rel->observations = $observaciones;
        if (false === $rel->save()) {
            Tools::log()->error("No se pudo guardar AttachedFileRelation para: $fileName");
            file_put_contents($logPath, "Error al guardar relación para archivo: $fileName\n", FILE_APPEND);
        } else {
            file_put_contents($logPath, "Imagen $fileName guardada y relacionada con reserva $reservaId\n", FILE_APPEND);
        }
    }
}
