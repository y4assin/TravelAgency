<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Plugins\TravelAgency\Model\HabitacionesPaquete;

class ListPaquete extends ListController
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Paquetes";
        $data["menu"] = "Agencia";
        $data["icon"] = "fas fa-box";
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsPaquete();
        $this->createViewsHabitacionesPaquete();
        $this->createViewsListItinerario();
    }

    protected function createViewsPaquete(string $viewName = "ListPaquete")
    {
        $this->addView($viewName, "Join\Paquete", "Paquetes", "fas fa-box");

        $this->addOrderBy($viewName, ["id"], "id", 2);
        $this->addOrderBy($viewName, ["name"], "name");

        $this->addSearchFields($viewName, ["id", "name", "description"]);
        $this->addFilterPeriod($viewName, 'fecha_salida', 'Fecha Salida', 'fecha_salida');
        $this->addFilterPeriod($viewName, 'fecha_regreso', 'Fecha Regreso', 'fecha_regreso');

        $ciudades = $this->codeModel->all('ciudades', 'idciudad', 'ciudad');
        $this->addFilterSelect($viewName, 'idciudad', 'Destinos', 'destino_id', $ciudades);
        $this->addFilterSelect($viewName, 'idciudad1', 'Origen', 'origen_id', $ciudades);
        $this->addFilterSelectWhere($viewName, 'status', [
            ['label' => 'Todos', 'where' => []],
            [
                'label' => 'Disponibles',
                'where' =>
                [new DataBaseWhere('status', 1),]
            ],
            [
                'label' => 'Agotados',
                'where' => [new DataBaseWhere('status', 2),]
            ],
            [
                'label' => 'Cancelados',
                'where' => [new DataBaseWhere('status', 3),]
            ],

        ]);
    }

    protected function createViewsHabitacionesPaquete(string $viewName = "ListHabitacionesPaquete")
    {
        $this->addView($viewName, "HabitacionesPaquete", "Habitaciones Por Paquete", "fa-solid fa-people-roof");

        $this->addOrderBy($viewName, ["id"], "id", 2);
        $this->addOrderBy($viewName, ["name"], "name");

        $this->addSearchFields($viewName, ["id", "name"]);
        $this->addFilterAutocomplete($viewName, 'paquete', 'Paquete', 'paquete_id', 'ta_paquetes_de_viajes', 'id', 'name');

        $this->addFilterSelectWhere($viewName, 'tipo_habitacion', [
            ['label' => 'Todos los tipos', 'where' => []],
            [
                'label' => 'Individual',
                'where' =>
                [new DataBaseWhere('tipo_habitacion', 1),]
            ],
            [
                'label' => 'Doble',
                'where' => [new DataBaseWhere('tipo_habitacion', 2),]
            ],
            [
                'label' => 'Triple',
                'where' => [new DataBaseWhere('tipo_habitacion', 3),]
            ],
            [
                'label' => 'Cuádruple',
                'where' => [new DataBaseWhere('tipo_habitacion', 4),]
            ],

        ]);
        // Añadir filtro: total mayor o igual
        $this->addFilterNumber($viewName, 'cantidad_disponible', 'Cantidad Disponible', 'cantidad_disp', '>=');
    }

    protected function createViewsListItinerario(string $viewName = "ListItinerario")
    {
        $this->addView($viewName, "Itinerario", "Itinerarios", "fa-solid fa-list-check");

        $this->addOrderBy($viewName, ["id"], "id", 2);
        $this->addOrderBy($viewName, ["name"], "name");

        $this->addSearchFields($viewName, ["id", "name", "description"]);
        $this->addFilterAutocomplete($viewName, 'paquete', 'Paquete', 'paquete_id', 'ta_paquetes_de_viajes', 'id', 'name');
    }
}
