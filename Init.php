<?php

namespace FacturaScripts\Plugins\TravelAgency;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Plugins\TravelAgency\Model\HabitacionesPaquete;
use FacturaScripts\Plugins\TravelAgency\Model\Itinerario;
use FacturaScripts\Plugins\TravelAgency\Model\Paquete;
use FacturaScripts\Plugins\TravelAgency\Model\Pasajero;
use FacturaScripts\Plugins\TravelAgency\Model\Reserva;
use FacturaScripts\Core\Controller\ApiRoot;
use FacturaScripts\Core\Kernel;

class Init extends InitClass
{
    public function init(): void
    {
        // se ejecuta cada vez que carga FacturaScripts (si este plugin está activado).
        $this->loadExtension(new Extension\Controller\EditCliente());
        $this->loadExtension(new Extension\Model\ReservaPasajero());
        Kernel::addRoute('/api/3/sync-paquetes', 'ApiControllerPaquete', -1);
        ApiRoot::addCustomResource('sync-paquetes');
        Kernel::addRoute('/api/3/sync-reserva', 'ApiControllerReserva', -1);
        ApiRoot::addCustomResource('sync-reserva');
    }

    public function update(): void
    {
        // se ejecuta cada vez que se instala o actualiza el plugin.
        new Paquete();
        new Reserva();
        new Pasajero();
        new Itinerario();

        new HabitacionesPaquete();
    }
    public function uninstall(): void
    {
        // se ejecuta cada vez que se instala o actualiza el plugin.
    }
}
