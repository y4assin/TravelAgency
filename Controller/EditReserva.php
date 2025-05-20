<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Core\Lib\ExtendedController\DocFilesTrait;

class EditReserva extends EditController
{
    use DocFilesTrait;

    public function getModelClassName(): string
    {
        return "Reserva";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Reserva";
        $data["icon"] = "fas fa-book-bookmark";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewPasajeros();
        $this->createViewDocFiles();
    }

    protected function createViewPasajeros(string $viewName = "ListReservaPasajero")
    {
        $this->addListView($viewName, "ReservaPasajero", "Pasajeros de la reserva", "fa-solid fa-users-rectangle");
    }

    protected function loadData($viewName, $view)
    {
        $code = $this->request->query->get('code', '');
        switch ($viewName) {

            case 'ListReservaPasajero':
                $where = [new DataBaseWhere('reserva_id', $code)];
                $view->loadData('', $where);
                break;
            case 'docfiles':
                $this->loadDataDocFiles($view, $this->getModelClassName(), $this->getModel()->primaryColumnValue());
                break;
            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'add-file':
                return $this->addFileAction();

            case 'delete-file':
                return $this->deleteFileAction();

            case 'edit-file':
                return $this->editFileAction();
            case 'unlink-file':
                return $this->unlinkFileAction();

            default:
                return parent::execPreviousAction($action);
        }
    }
}
