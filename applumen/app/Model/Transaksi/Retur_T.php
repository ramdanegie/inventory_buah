<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 07/03/2019
 * Time: 22.07
 */


namespace App\Model\Transaksi;

use Illuminate\Database\Eloquent\Model;

class Retur_T extends Model
{
	protected $table = 'retur_t';
	public $timestamps = false;
	public $incrementing = false;
	protected $primaryKey = 'norec';
//	protected $fillable = [
//		'id',
//		'kdprofile',
//		'agama',
//		'reportdisplay',
//		'kodeexternal',
//		'namaexternal',
//		'statusenabled',
//		'norec'
//	];
	public static function queryTable($request){
		$table = 'retur_t';
		$param['table_from']= $table;
		$param['select']= array($table.'.*');
		$param['label'] = array();

		$param['where'][0]['fieldname']= $table.'.statusenabled';
		$param['where'][0]['operand']= '=';
		$param['where'][0]['is']= true;

		return $param;
	}
}