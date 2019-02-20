<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 06/02/2019
 * Time: 22.36
 */

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    protected $table = 'agama_m';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'kdprofile',
        'agama',
        'reportdisplay',
        'kodeexternal',
        'namaexternal',
        'statusenabled',
        'norec'
    ];
    public static function queryTable($request){
        $table = 'agama_m';
        $param['table_from']= $table;
        $param['select']= array($table.'.*');
        $param['label'] = array();

        $param['where'][0]['fieldname']= $table.'.statusenabled';
        $param['where'][0]['operand']= '=';
        $param['where'][0]['is']= true;

        return $param;
    }
}