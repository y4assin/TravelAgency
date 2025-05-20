<?php

namespace FacturaScripts\Plugins\TravelAgency\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Session;

class Pasajero extends ModelClass
{
    use ModelTrait;

    /** @var string */
    public $creation_date;

    /** @var string */
    public $fecha_nacimiento;

    /** @var int */
    public $id;

    /** @var string */
    public $last_nick;

    /** @var string */
    public $last_update;

    /** @var string */
    public $nacionalidad;

    /** @var string */
    public $name;
    /** @var string */
    public $apellido;

    /** @var string */
    public $nick;

    /** @var string */
    public $num_pasaporte;

    /** @var string */
    public $tratamiento;

    /** @var string */
    public $validez_pasaporte;

    public function clear()
    {
        parent::clear();
        $this->fecha_nacimiento = date(self::DATE_STYLE);
        $this->validez_pasaporte = date(self::DATE_STYLE);
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "ta_pasajeros";
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

        $this->nacionalidad = Tools::noHtml($this->nacionalidad);
        $this->name = Tools::noHtml($this->name);
        $this->apellido = Tools::noHtml($this->apellido);
        $this->tratamiento = Tools::noHtml($this->tratamiento);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, 'ListReserva?activetab=' . $list);
    }
}
