<?php
namespace FacturaScripts\Plugins\TravelAgency\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditItinerario extends EditController
{
    public function getModelClassName(): string
    {
        return "Itinerario";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "Itinerario";
        $data["icon"] = "fas fa-search";
        return $data;
    }
}
