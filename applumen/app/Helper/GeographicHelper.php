<?php

namespace App\Helper;

class GeographicHelper
{
    protected $R = 6371.01;
    public function getDegreesToRadians($degrees){
        return ($degrees * M_PI)/180;
    }

    public function getRadiansToDegrees($radians){
        return ($radians * 180)/M_PI;
    }

    //satuan M
    public function getDistanceFrom2Points($lat1,$lng1,$lat2,$lng2){
        return (acos(
                    (
                        sin($this->getDegreesToRadians($lat1)) * sin($this->getDegreesToRadians($lat2))
                    )
                    +
                    (
                        cos($this->getDegreesToRadians($lat1)) * cos($this->getDegreesToRadians($lat2)) * cos($this->getDegreesToRadians($lng1) - $this->getDegreesToRadians($lng2))
                    )
                )
                *
                $this->R)*1000;
    }


}
