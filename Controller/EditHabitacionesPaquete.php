<?php

namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditHabitacionesPaquete extends EditController
{
    public function getModelClassName(): string
    {
        return "HabitacionesPaquete";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "HabitacionesPaquete";
        $data["icon"] = "fa-solid fa-people-roof";
        return $data;
    }

}
