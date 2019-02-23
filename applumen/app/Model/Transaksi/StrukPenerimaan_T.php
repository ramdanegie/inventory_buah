<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 22/02/2019
 * Time: 23.43
 */


namespace App\Model\Transaksi;

use Illuminate\Database\Eloquent\Model;

class StrukPenerimaan_T extends Model
{
	protected $table = 'strukpenerimaan_t';
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
		$table = 'strukpenerimaan_t';
		$param['table_from']= $table;
		$param['select']= array($table.'.*');
		$param['label'] = array();

		$param['where'][0]['fieldname']= $table.'.statusenabled';
		$param['where'][0]['operand']= '=';
		$param['where'][0]['is']= true;

		return $param;
	}
}