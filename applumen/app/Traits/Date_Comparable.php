<?php
namespace App\Traits;

use DB;

Trait Date_Comparable
{
    //for source
    protected $sourceTable = "StrukHistori_T";
    protected  $params = "NoHistori";

    protected function cekAvailable(){
        $data = DB::select("select * from ".$this->sourceTable." where GETDATE() BETWEEN TglAwal
              AND ISNULL(TglAkhir, GETDATE())");
        $results = array();
        foreach ($data as $value){
            array_push($results, $value->{$this->params});
        }
        return $results;
    }
}