<?php
/**
 * Created by PhpStorm.
 * User: SitepuMan
 * Date: 06/02/2019
 * Time: 22.36
 */

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Produk_M extends Model
{
    protected $table = 'produk_m';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'namaproduk',
        'kdexternal',
        'detailjenisprodukfk',
        'satuanstandardfk',
        'statusenabled'
    ];
    public static function queryTable($request){
        $table = 'produk_m';
        $param['table_from']= $table;
        $param['select']= array($table.'.*');
        $param['label'] = array();

        $param['where'][0]['fieldname']= $table.'.statusenabled';
        $param['where'][0]['operand']= '=';
        $param['where'][0]['is']= true;

        return $param;
    }
}