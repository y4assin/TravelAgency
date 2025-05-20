<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Core\Lib\ExtendedController\DocFilesTrait;
use FacturaScripts\Dinamic\Model\Paquete;
use FacturaScripts\Plugins\TravelAgency\Model\HabitacionesPaquete;

class EditPaquete extends EditController
{
    use DocFilesTrait;

    public function getModelClassName(): string
    {
        return "Paquete";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Paquete";
        $data["icon"] = "fas fa-box";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $column = $this->tab('EditPaquete')->columnForName('Plazas');
        if ($column && $column->widget->getType() === 'select') {
            $customValues = [
                ['value' => '1', 'title' => $this->getPlazasTotales()],

            ];
            $column->widget->setValuesFromArray($customValues);
        }

        $this->createViewsHabitacionesPaquete();
        $this->createViewsListItinerario();
        $this->createViewsReserva();
        $this->createViewDocFiles();
    }

    protected function createViewsHabitacionesPaquete(string $viewName = "ListHabitacionesPaquete")
    {
        $this->addListView($viewName, "HabitacionesPaquete", "Habitaciones del Paquete", "fa-solid fa-people-roof");
    }
    protected function createViewsListItinerario(string $viewName = "ListItinerario")
    {
        $this->addListView($viewName, "Itinerario", "Itinerario", "fa-solid fa-list-check");
    }
    protected function createViewsReserva(string $viewName = "ListReserva")
    {
        $this->addListView($viewName, "Reserva", "Reservas", "fas fa-book-bookmark");
    }

    protected function loadData($viewName, $view)
    {
        $code = $this->request->query->get('code', '');
        switch ($viewName) {
            case 'ListHabitacionesPaquete':
            case 'ListItinerario':
            case 'ListReserva':
                $where = [new DataBaseWhere('paquete_id', $code)];
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


    public function getPlazasTotales(): int
    {
        $id = $this->request->query->get('code', '');
        $habitaciones = new HabitacionesPaquete();
        $where = [new DataBaseWhere('paquete_id', $id)];

        $habitaciones = $habitaciones->all($where);

        $plazas = 0;
        if (!empty($habitaciones)) {
            foreach ($habitaciones as $h) {
                $plazas += $h->cantidad_disp;
            }
        }
        return $plazas;
    }
    public function getPlazasReservadas(): int
    {
        $id = $this->request->query->get('code', '');
        $habitaciones = new HabitacionesPaquete();
        $where = [new DataBaseWhere('paquete_id', $id)];

        $habitaciones = $habitaciones->all($where);

        $plazas = 0;
        if (!empty($habitaciones)) {
            foreach ($habitaciones as $h) {
                $plazas += $h->cantidad_disp;
            }
        }
        return $plazas;
    }
}
