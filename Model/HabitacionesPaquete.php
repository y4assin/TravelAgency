<?php

namespace FacturaScripts\Plugins\TravelAgency\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Session;

class HabitacionesPaquete extends ModelClass
{
    use ModelTrait;

    /** @var int */
    public $cantidad_plazas;
    public $cantidad_disp;
    public $cantidad_reservada;

    /** @var string */
    public $creation_date;

    /** @var int */
    public $id;

    /** @var string */
    public $last_nick;

    /** @var string */
    public $last_update;

    /** @var string */
    public $name;

    /** @var string */
    public $nick;

    /** @var int */
    public $paquete_id;

    /** @var float */
    public $precio;

    /** @var int */
    public $tipo_habitacion;

    public function clear()
    {
        parent::clear();
        $this->cantidad_plazas = 10;
        $this->cantidad_reservada = 0;
        $this->precio = 1000;
        $this->tipo_habitacion = 1;
        $this->name = "Tipo habitación para paquete NRO {$this->paquete_id}";
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "ta_habitaciones_paquete";
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

        $this->cantidad_disp = $this->cantidad_plazas - $this->cantidad_reservada;
        $this->name = Tools::noHtml($this->name);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, 'ListPaquete?activetab=' . $list);
    }
}
