<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 19.40
 */
namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MapProdukToSatuanStandar_M extends Model
{
	protected $table = 'mapproduktosatuanstandard_m';
	public $timestamps = false;
	public $incrementing = false;
	protected $primaryKey = 'id';
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
	public static function queryTable($request)
	{
		$table = 'mapproduktosatuanstandard_m';
		$param['table_from'] = $table;
		$param['select'] = array($table . '.*');
		$param['label'] = array();

		$param['where'][0]['fieldname'] = $table . '.statusenabled';
		$param['where'][0]['operand'] = '=';
		$param['where'][0]['is'] = true;

		return $param;
	}
}
