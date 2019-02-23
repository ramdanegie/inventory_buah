<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 16/02/2019
 * Time: 15.44
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  KelompokUserController extends Controller
{
	/*
	 * https://github.com/lcobucci/jwt/blob/3.2/README.md
	 */
	use Core;
	use JsonResponse;

	public function getAll (Request $request)
	{
		// ini_set('memory_limit', '128M');
		$kelUser = KelompokUser_S::where('statusenabled',true)
			->select('id','kelompokuser');
		$kelUser = $kelUser->get();
		// $kelUser = DB::table('produk_m')
		// 	->select('*')
		// 	->get();
		if ($kelUser->count() > 0) {
			$result['code'] = 200;
			$result['data'] = $kelUser;
			$result['as'] = "ramdanegie";
		} else {
			$result['code'] = 500;
			$result['status'] = false;
			$result['as'] = "ramdanegie";
		}
		return response()->json($result);
	}
	public function saveKelompokUser (Request $request)
	{
		DB::beginTransaction();
		try{
			$idMax = KelompokUser_S::max('id') + 1;
			if($request['idKelompokUser'] == null){
				$log = new KelompokUser_S();
				$log->id = $idMax;
				$log->statusenabled = true;
			}else{
				$log = KelompokUser_S::where('id',$request['idKelompokUser'])->first();
			}
			$log->kelompokuser= $request['kelompokUser'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Kelompok User";
			DB::commit();
			$result = array(
				'status' => 200,
				'message' => $transMessage,
				'as' => 'ramdanegie',
			);
		} else {
			$transMessage = "Terjadi Kesalahan saat menyimpan data";
			DB::rollBack();
			$result = array(
				'status' => 500,
				'message'  => $transMessage,
				'as' => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function deleteKelompokUser (Request $request)
	{
		DB::beginTransaction();
		try{

			 KelompokUser_S::where('id',$request['idKelompokUser'])->update(
			 	[ 'statusenabled' => false ]
			 );

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Kelompok User";
			DB::commit();
			$result = array(
				'status' => 200,
				'message' => $transMessage,
				'as' => 'ramdanegie',
			);
		} else {
			$transMessage = "Terjadi Kesalahan saat menghapus data";
			DB::rollBack();
			$result = array(
				'status' => 500,
				'message'  => $transMessage,
				'as' => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
}