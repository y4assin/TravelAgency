<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Model\Ciudad;
use FacturaScripts\Core\Template\ApiController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\AttachedFile;
use FacturaScripts\Core\Model\AttachedFileRelation;
use FacturaScripts\Core\Model\TotalModel;
use FacturaScripts\Dinamic\Model\HabitacionesPaquete;
use FacturaScripts\Plugins\TravelAgency\Model\Itinerario;
use FacturaScripts\Plugins\TravelAgency\Model\Paquete;

class ApiControllerPaquete extends ApiController
{
    protected function runResource(): void
    {
        $logPath = __DIR__ . '/../sync-paquete.log';
        $post = $this->request->request->all();
        $paqueteId = $post['paquete_id'] ?? false;

        if ($paqueteId) {
            file_put_contents($logPath, "\n\n--- NUEVA SYNC FULL PAQUETE ID: {$paqueteId}: " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);
            $paquete = new Paquete();
            if ($paquete->loadFromCode($paqueteId)) {
                $paquetesFull[] = $this->buildPaqueteFull($paquete);
            }
        } else {

            file_put_contents($logPath, "\n\n--- NUEVA SYNC FULL PAQUETES: " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);

            $paquetes = new Paquete();
            $paquetes = $paquetes->all([], ['id' => 'desc'], 0, 0);

            if (empty($paquetes)) {
                $this->response->setContent(json_encode([
                    'success' => false,
                    'message' => 'Sin paquetes cargados',
                ]));
                return;
            }
            file_put_contents($logPath, "\n\n--- paquetes: " . json_encode($paquetes) . " ---\n", FILE_APPEND);


            $paquetesFull = [];
            foreach ($paquetes as $paquete) {
                $paquetesFull[] = $this->buildPaqueteFull($paquete);
            }
        }
        $ciudades = new Ciudad();
        $ciudades = $ciudades->all([], [], 0, 0);
        $this->response->setContent(json_encode([
            'success' => true,
            'data' => [
                'paquetes' => $paquetesFull,
                'ciudades' => $ciudades,
            ],
        ]));
    }

    protected function buildPaqueteFull(Paquete $paquete): array
    {
        $imagenBase64 = $this->getPaqueteImageBase64($paquete->id);

        $where = [new DataBaseWhere('paquete_id', $paquete->id), new DataBaseWhere('cantidad_disp', 0, '>')];

        $habitacionesDelPaquete = (new HabitacionesPaquete())->all($where, ['tipo_habitacion' => 'desc']);

        $totales = TotalModel::all(
            'ta_habitaciones_paquete',
            $where,
            ['cantidad_disp' => 'SUM(cantidad_disp)'],
            'paquete_id'
        );

        $cantidad_disp = 0;
        if (!empty($totales)) {
            $cantidad_disp = (int) $totales[0]->totals['cantidad_disp'];
        }
$where = [new DataBaseWhere('paquete_id', $paquete->id)];
        $itinerarioPaquete = (new Itinerario())->all($where, ['id' => 'asc']);

        return [
            'id'                  => $paquete->id ?? null,
            'name'                => $paquete->name ?? '',
            'nick'                => $paquete->nick ?? '',
            'description'         => $paquete->description ?? '',
            'creation_date'       => $paquete->creation_date ?? '',
            'last_nick'           => $paquete->last_nick ?? '',
            'last_update'         => $paquete->last_update ?? '',

            'fecha_salida'        => $paquete->fecha_salida ?? '',
            'hora_salida'         => $paquete->hora_salida ?? '',
            'hora_llegada'        => $paquete->hora_llegada ?? '',
            'fecha_regreso'       => $paquete->fecha_regreso ?? '',
            'hora_vuelta'         => $paquete->hora_vuelta ?? '',
            'hora_llegada_vuelta' => $paquete->hora_llegada_vuelta ?? '',
            'duracion_dias'       => $paquete->duracion_dias ?? 0,

            'origen_id'           => $paquete->origen_id ?? null,
            'destino_id'          => $paquete->destino_id ?? null,

            'plazas_disponibles'  => $cantidad_disp,
            'status'              => $paquete->status ?? null,

            'imagen_url'          => $imagenBase64 ?? null,
            'habitacionespaquetes' => $habitacionesDelPaquete,
            'itinerarioPaquete'     => $itinerarioPaquete,
        ];
    }

    public function getPaqueteImageBase64(int $paqueteId): ?string
    {
        $relation = new AttachedFileRelation();
        $where = [
            new DataBaseWhere('model', 'Paquete'),
            new DataBaseWhere('modelcode', $paqueteId)
        ];

        $relacionado = $relation->all($where, [], 0, 1);

        if (!$relacionado) {
            return null;
        }

        $archivo = new AttachedFile();
        if (!$archivo->loadFromCode($relacionado[0]->idfile)) {
            return null;
        }

        if (strpos($archivo->mimetype, 'image/') !== 0) {
            return null;
        }

        $fullPath = FS_FOLDER . '/' . $archivo->path;
        if (!file_exists($fullPath)) {
            return null;
        }

        $mimetype = preg_replace('/^data:/', '', $archivo->mimetype);
        $mimetype = preg_replace('/;base64$/', '', $mimetype);

        $contenidoBase64 = base64_encode(file_get_contents($fullPath));
        return 'data:' . $mimetype . ';base64,' . $contenidoBase64;
    }
}
