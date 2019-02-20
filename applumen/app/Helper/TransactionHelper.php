<?php

namespace App\Helper;


class TransactionHelper
{
    public function getCurrency($nominal, $kurs, $opt){
        if($opt == 0)
        //jika dollar ke rupiah (kurs = kurs beli)
        {
            return $nominal*$kurs;
        }
        else
        //jika rupiah ke mata uang lain (kurs = kurs jual)
        {
            return $nominal/$kurs;
        }
    }

}