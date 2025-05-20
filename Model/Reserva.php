<?php

namespace FacturaScripts\Plugins\TravelAgency\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Session;

class Reserva extends ModelClass
{
    use ModelTrait;

    /** @var string */
    public $creation_date;

    /** @var int */
    public $idfactura;
    public $codcliente;

    /** @var string */
    public $fecha_reserva;

    /** @var string */
    public $forma_pago;

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
    public $num_pasajeros;

    /** @var int */
    public $paquete_id;

    /** @var int */
    public $status;

    /** @var float */
    public $total_pagado;

    /** @var int */
    public $usuario_id;

    public function clear()
    {
        parent::clear();
        $this->fecha_reserva = date(self::DATE_STYLE);
        $this->num_pasajeros = 1;
        $this->status = 1;
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "ta_reservas";
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

        $this->forma_pago = Tools::noHtml($this->forma_pago);
        $this->name = Tools::noHtml($this->name);
        return parent::test();
    }
}
