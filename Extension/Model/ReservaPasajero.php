<?php

namespace FacturaScripts\Plugins\TravelAgency\Extension\Model;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\HabitacionesPaquete;
use FacturaScripts\Plugins\TravelAgency\Model\Reserva;

class ReservaPasajero
{
    public $habitacion_id;
    public $reserva_id;


    public function actualizarCantidadDisponibleHabitacion(): Closure
    {
        return function ($operacion = 1) {
            $habitacion = new HabitacionesPaquete();
            $habitacion->loadFromCode($this->habitacion_id);
            if ($habitacion !== null) {
                if ($operacion) {
                    $habitacion->cantidad_reservada++;
                    $habitacion->save();
                } else {
                    $habitacion->cantidad_reservada--;
                    $habitacion->save();
                }
            }
        };
    }

    public function actualizarCantidadPasajerosReserva(): Closure
    {
        return function ($operacion = 1) {
            $reserva = new Reserva();
            $reserva->loadFromCode($this->reserva_id);
            if ($reserva !== null) {
                if ($operacion) {
                    $reserva->num_pasajeros++;
                    $reserva->save();
                } else {
                    $reserva->num_pasajeros--;
                    $reserva->save();
                }
            }
        };
    }

    // ***************************************
    // ** Métodos disponibles para extender **
    // ***************************************

    public function clear(): Closure
    {
        return function () {
            // tu código aquí
            // se ejecuta cada vez que se instancia un objeto de este modelo. Asigna valores predeterminados.
        };
    }

    public function delete(): Closure
    {
        return function () {
            // tu código aquí
            // delete() se ejecuta una vez realizado el delete() del modelo,
            // cuando ya se ha eliminado el registro de la base de datos
        };
    }

    public function deleteBefore(): Closure
    {
        return function () {
            // tu código aquí
            // deleteBefore() se ejecuta antes de ejecutar el delete() del modelo.
            // Si devolvemos false, impedimos el delete().
            $this->actualizarCantidadDisponibleHabitacion(0);
            $this->actualizarCantidadPasajerosReserva(0);
        };
    }

    public function save(): Closure
    {
        return function () {
            // tu código aquí
            // save() se ejecuta una vez realizado el save() del modelo,
            // cuando ya se ha guardado el registro en la base de datos
            $this->actualizarCantidadPasajerosReserva();
        };
    }

    public function saveBefore(): Closure
    {
        return function () {
            // tu código aquí
            // saveBefore() se ejecuta antes de hacer el save() del modelo.
            // Si devolvemos false, impedimos el save().
        };
    }

    public function saveInsert(): Closure
    {
        return function () {
            // tu código aquí
            // saveInsert() se ejecuta una vez realizado el saveInsert() del modelo,
            // cuando ya se ha guardado el registro en la base de datos
        };
    }

    public function saveInsertBefore(): Closure
    {
        return function () {
            // tu código aquí
            // saveInsertBefore() se ejecuta antes de hacer el saveInsert() del modelo.
            // Si devolvemos false, impedimos el saveInsert().
        };
    }

    public function saveUpdate(): Closure
    {
        return function () {
            // tu código aquí
            // saveUpdate() se ejecuta una vez realizado el saveUpdate() del modelo,
            // cuando ya se ha guardado el registro en la base de datos
        };
    }

    public function saveUpdateBefore(): Closure
    {
        return function () {
          
            // saveUpdateBefore() se ejecuta antes de hacer el saveUpdate() del modelo.
            // Si devolvemos false, impedimos el saveUpdate().
        };
    }

    public function test(): Closure
    {
        return function () {
        
            // test se ejecuta justo después del método test del modelo.
            // Si devolvemos false, impedimos el save().
        };
    }

    public function testBefore(): Closure
    {
        return function () {
            // test se ejecuta justo antes del método test del modelo.
            // Si devolvemos false, impedimos el save() y el resto de test().
        };
    }
}
