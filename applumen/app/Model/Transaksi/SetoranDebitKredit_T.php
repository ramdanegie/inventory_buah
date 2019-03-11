<?php
/**
 * Created by IntelliJ IDEA.
 * User: SitepuMan
 * Date: 23/02/2019
 * Time: 00.23
 */


namespace App\Model\Transaksi;

use Illuminate\Database\Eloquent\Model;

class SetoranDebitKredit_T extends Model
{
    protected $table = 'setorandebitkredit_t';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'norec';
    public static function queryTable($request){
        $table = 'stokproduk_t';
        $param['table_from']= $table;
        $param['select']= array($table.'.*');
        $param['label'] = array();

        $param['where'][0]['fieldname']= $table.'.statusenabled';
        $param['where'][0]['operand']= '=';
        $param['where'][0]['is']= true;

        return $param;
    }
}