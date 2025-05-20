<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListReserva extends ListController
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Reservas";
        $data["menu"] = "Agencia";
        $data["icon"] = "fas fa-book-bookmark";
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsReserva();
        $this->createViewsPasajero();
    }

    protected function createViewsReserva(string $viewName = "ListReserva")
    {
        $this->addView($viewName, "Reserva", "Reservas", "fas fa-book-bookmark");

        $this->addOrderBy($viewName, ["id"], "id", 2);
        $this->addOrderBy($viewName, ["name"], "name");

        $this->addSearchFields($viewName, ["id", "name"]);
        $this->addFilterAutocomplete($viewName, 'paquete', 'Paquete', 'paquete_id', 'ta_paquetes_de_viajes', 'id', 'name');
        $this->addFilterAutocomplete($viewName, 'codcliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'codpago', 'Forma de pago', 'forma_pago', 'formaspago', 'codpago', 'descripcion');

        $this->addFilterSelectWhere($viewName, 'status', [
            ['label' => 'Todos', 'where' => []],
            [
                'label' => 'Pendiente',
                'where' =>
                [new DataBaseWhere('status', 1),]
            ],
            [
                'label' => 'Confirmada',
                'where' => [new DataBaseWhere('status', 2),]
            ],
            [
                'label' => 'Cancelada',
                'where' => [new DataBaseWhere('status', 3),]
            ],

        ]);
    }

    protected function createViewsPasajero(string $viewName = "ListPasajero")
    {
        $this->addView($viewName, "Pasajero", "Pasajeros", "fa-solid fa-people-group");
    }
}
