<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 08/03/2019
 * Time: 09.13
 */

namespace App\Model\Transaksi;

use Illuminate\Database\Eloquent\Model;

class ReturDetail_T extends Model
{
	protected $table = 'returdetail_t';
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
		$table = 'returdetail_t';
		$param['table_from']= $table;
		$param['select']= array($table.'.*');
		$param['label'] = array();

		$param['where'][0]['fieldname']= $table.'.statusenabled';
		$param['where'][0]['operand']= '=';
		$param['where'][0]['is']= true;

		return $param;
	}
}