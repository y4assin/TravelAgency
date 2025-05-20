<?php

namespace FacturaScripts\Plugins\TravelAgency\Extension\Controller;

use Closure;

class EditCliente
{
    public function createViews(): Closure
    {
        return function () {
            // createViews() se ejecuta una vez realizado el createViews() del controlador.
            $this->setSettings('EditDireccionContacto', 'active', false);
            $this->setSettings('EditCuentaBancoCliente', 'active', false);
        };
    }

    public function execAfterAction(): Closure
    {
        return function ($action) {
        
            // execAfterAction() se ejecuta tras el execAfterAction() del controlador.
        };
    }

    public function execPreviousAction(): Closure
    {
        return function ($action) {
            // execPreviousAction() se ejecuta después del execPreviousAction() del controlador.
            // Si devolvemos false detenemos la ejecución del controlador.
        };
    }

    public function loadData(): Closure
    {
        return function ($viewName, $view) {
            // loadData() se ejecuta tras el loadData() del controlador. Recibe los parámetros $viewName y $view.

        };
    }
}
