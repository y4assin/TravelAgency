<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditPasajero extends EditController
{
    public function getModelClassName(): string
    {
        return "Pasajero";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Pasajero";
        $data["icon"] = "fa-solid fa-people-group";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewReservasDelPasajero();
    }

    protected function createViewReservasDelPasajero(string $viewName = "ListReservaPasajero")
    {
        $this->addListView($viewName, "ReservaPasajero", "Reservas del pasajero", "fa-solid fa-users-rectangle");
    }

    protected function loadData($viewName, $view)
    {
        $code = $this->request->query->get('code', '');
        switch ($viewName) {

            case 'ListReservaPasajero':
                $where = [new DataBaseWhere('pasajero_id', $code)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
