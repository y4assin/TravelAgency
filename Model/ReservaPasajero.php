<?php

namespace FacturaScripts\Plugins\TravelAgency\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;

class ReservaPasajero extends ModelClass
{
    use ModelTrait;
    /** @var int */
    public $id;

    /** @var int */
    public $reserva_id;

    /** @var int */
    public $pasajero_id;

    /** @var int */
    public $habitacion_id;

    /** @var string */
    public $creation_date;

    /** @var string */
    public $nick;

    /** @var string */
    public $last_nick;

    /** @var string */
    public $last_update;

    public function clear()
    {
        parent::clear();
    }
    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'ta_reservas_pasajeros';
    }

    public function test(): bool
    {
        if (empty($this->primaryColumnValue())) {
            $this->creation_date = Tools::dateTime();
            $this->last_nick = null;
            $this->last_update = null;
            $this->nick = Session::user()->nick;
        } else {
            $this->creation_date = $this->creationdate ?? Tools::dateTime();
            $this->last_nick = Session::user()->nick;
            $this->last_update = Tools::dateTime();
            $this->nick = $this->nick ?? Session::user()->nick;
        }
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, "EditReserva?code={$this->reserva_id}&activetab={$list}");
    }

    public function actualizarCantidadDisponibleHabitacion($operacion = 1)
    {

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
    }

    public function saveInsert(array $values = []): bool
    {
        $this->actualizarCantidadDisponibleHabitacion();

        return parent::saveInsert($values);
    }
}
