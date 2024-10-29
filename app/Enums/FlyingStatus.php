<?php

namespace App\Enums;

enum FlyingStatus: int
{
    case DEPARTING = 1;
    case IN_FLIGHT = 2;
    case ARRIVING = 3;

    public static function getNameByStatus($status)
    {
        if($status == self::DEPARTING->value){
            return "Departing";
        }

        if($status == self::IN_FLIGHT->value){
            return "In flight";
        }

        if($status == self::ARRIVING->value){
            return "Arriving";
        }
    }
}
