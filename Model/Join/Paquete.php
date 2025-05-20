<?php

namespace FacturaScripts\Plugins\TravelAgency\Model\Join;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Plugins\TravelAgency\Model\Paquete as JoinPaquete;

class Paquete extends JoinModel
{

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->setMasterModel(new JoinPaquete());
    }
    protected function getFields(): array
    {
        return [
            'id' => 'ta_paquetes_de_viajes.id',
            'name' => 'ta_paquetes_de_viajes.name',
            'description' => 'ta_paquetes_de_viajes.description',
            'status' => 'ta_paquetes_de_viajes.status',
            'creation_date' => 'ta_paquetes_de_viajes.creation_date',
            'nick' => 'ta_paquetes_de_viajes.nick',
            'last_nick' => 'ta_paquetes_de_viajes.last_nick',
            'last_update' => 'ta_paquetes_de_viajes.last_update',
            'origen_id' => 'ta_paquetes_de_viajes.origen_id',
            'destino_id' => 'ta_paquetes_de_viajes.destino_id',
            'duracion_dias' => 'ta_paquetes_de_viajes.duracion_dias',
            'fecha_salida' => 'ta_paquetes_de_viajes.fecha_salida',
            'fecha_regreso' => 'ta_paquetes_de_viajes.fecha_regreso',
            'hora_llegada' => 'ta_paquetes_de_viajes.hora_llegada',
            'hora_llegada_vuelta' => 'ta_paquetes_de_viajes.hora_llegada_vuelta',
            'hora_salida' => 'ta_paquetes_de_viajes.hora_salida',
            'hora_vuelta' => 'ta_paquetes_de_viajes.hora_vuelta',
            // Sumamos la cantidad disponible de todas las habitaciones del paquete
            'plazas_disponibles' => 'SUM(ta_habitaciones_paquete.cantidad_plazas)',
            'plazas_reservadas' => 'SUM(ta_habitaciones_paquete.cantidad_reservada)',
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'ta_paquetes_de_viajes LEFT JOIN ta_habitaciones_paquete ON ta_paquetes_de_viajes.id = ta_habitaciones_paquete.paquete_id';
    }

    protected function getTables(): array
    {
        return ['ta_paquetes_de_viajes', 'ta_habitaciones_paquete'];
    }

    protected function getGroupFields(): string
    {
        return 'ta_paquetes_de_viajes.id';
    }
}
