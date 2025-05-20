<?php

namespace FacturaScripts\Plugins\TravelAgency\Model;

use DateTime;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Session;

class Paquete extends ModelClass
{
    use ModelTrait;

    /** @var string */
    public $creation_date;

    /** @var string */
    public $description;

    /** @var int */
    public $destino_id;

    /** @var int */
    public $duracion_dias;

    /** @var string */
    public $fecha_regreso;

    /** @var string */
    public $fecha_salida;

    /** @var string */
    public $hora_llegada;

    /** @var string */
    public $hora_llegada_vuelta;

    /** @var string */
    public $hora_salida;

    /** @var string */
    public $hora_vuelta;

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
    public $origen_id;

    /** @var int */
    public $plazas_disponibles;
    public $plazas_reservadas;

    /** @var float */
    public $precio;

    /** @var int */
    public $status;

    public function clear()
    {
        parent::clear();
        $this->duracion_dias = 10;
        $this->fecha_salida = (new DateTime())->modify('+3 days')->format(self::DATE_STYLE);
        $this->hora_salida = date("H:i");
        $this->fecha_regreso = (new DateTime($this->fecha_salida))->modify('+10 days')->format(self::DATE_STYLE);
        $this->hora_llegada = (new DateTime($this->hora_salida))->modify('+5 hours')->format('H:i');
        $this->hora_llegada_vuelta = (new DateTime())->modify('+2 hours')->format('H:i');
        $this->hora_vuelta = (new DateTime())->modify('+3 hours')->format('H:i');
        $this->plazas_disponibles = 0;
        $this->status = 1;
        $this->description = "descripción del paquete";
        $this->origen_id = 1;
        $this->destino_id = 2;
        $this->name = "paquete nro " . self::newCode();
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "ta_paquetes_de_viajes";
    }
    public function primaryDescription(): string
    {
        return ucwords($this->name);
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

        $this->description = Tools::noHtml($this->description);
        $this->name = Tools::noHtml($this->name);
        return parent::test();
    }
}
