<?php
/**
 * Created by IntelliJ IDEA.
 * User: SitepuMan
 * Date: 3/11/2019
 * Time: 8:09 PM
 */
namespace App\Model\Transaksi;

use Illuminate\Database\Eloquent\Model;

class StrukPembayaranDetail_T extends Model
{
    protected $table = 'strukpembayarandetail_t';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'norec';
    public static function queryTable($request){
        $table = 'strukpembayarandetail_t';
        $param['table_from']= $table;
        $param['select']= array($table.'.*');
        $param['label'] = array();

        $param['where'][0]['fieldname']= $table.'.statusenabled';
        $param['where'][0]['operand']= '=';
        $param['where'][0]['is']= true;

        return $param;
    }
}