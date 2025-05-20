<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\TravelAgency\Model\HabitacionesPaquete;
use FacturaScripts\Plugins\TravelAgency\Model\Join\Paquete;
use FacturaScripts\Plugins\TravelAgency\Model\Pasajero;
use FacturaScripts\Plugins\TravelAgency\Model\Reserva;

class EditReservaPasajero extends EditController
{
    public function getModelClassName(): string
    {
        return "ReservaPasajero";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Reservas de pasajeros";
        $data["icon"] = "fa-solid fa-users-rectangle";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
    }

    protected function loadData($viewName, $view)
    {
        $reserva_id = $this->request->query->get('reserva_id', '');
        $this->setHabitacionesPaquete($reserva_id);
        $this->searchPasajeroPersonalizado();

        parent::loadData($viewName, $view);
    }

    protected function setHabitacionesPaquete($reserva_id)
    {
        $viewName = $this->getMainViewName();

        $reserva = new Reserva();
        $reserva->loadFromCode($reserva_id);
        $paquete = new Paquete();
        $paquete->loadFromCode($reserva->paquete_id);

        $habitaciones = new HabitacionesPaquete();
        $where = [new DataBaseWhere('paquete_id', $paquete->id)];

        $habitaciones = $habitaciones->all($where);

        $column = $this->tab($viewName)->columnForName('Tipo');
        if ($column && $column->widget->getType() === 'select') {
            $customValues = [];

            foreach ($habitaciones as $h) {
                $customValues[] = [
                    'value' => $h->id,
                    'title' => "{$paquete->name} | {$h->name}",
                ];
            }

            $column->widget->setValuesFromArray($customValues);
        }
    }

    protected function searchPasajeroPersonalizado()
    {
        $viewName = $this->getMainViewName();
        $pasajeros = new Pasajero();
        $pasajeros = $pasajeros->all();

        $column = $this->views[$viewName]->columnForName('Pasajero');
        if ($column && $column->widget->getType() === 'select') {
            $customValues = [];

            foreach ($pasajeros as $p) {
                $customValues[] = [
                    'value' => $p->id,
                    'title' => "{$p->name} {$p->apellido} | {$p->num_pasaporte}",
                ];
            }

            $column->widget->setValuesFromArray($customValues);
        }
    }
}
