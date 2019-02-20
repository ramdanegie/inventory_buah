<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 18/02/2019
 * Time: 20.51
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Standar\LoginUser_S;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  LoginUserController extends Controller
{
	/*
	 * https://github.com/lcobucci/jwt/blob/3.2/README.md
	 */
	use Core;
	use JsonResponse;

	public function getDaftarLoginUser (Request $request)
	{
		$data = DB::table('loginuser_s as log')
			->join('kelompokuser_s as kl','log.objectkelompokuserfk','=','kl.id')
			->join('pegawai_m as pg','log.objectpegawaifk','=','pg.id')
			->select('log.id','log.namauser','log.katasandi','log.objectkelompokuserfk','log.objectpegawaifk',
				'kl.kelompokuser','pg.namalengkap')
			->orderBy('pg.namalengkap')
			->get();

		$result['code'] = 200;
		$result['data'] = $data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
	public function saveLoginUser (Request $request)
	{
		DB::beginTransaction();
		try{
			$idMax = LoginUser_S::max('id') + 1;
			if($request['idUser'] == null){
				$log = new LoginUser_S();
				$log->id = $idMax;
				$log->statusenabled = true;
			}else{
				$log = LoginUser_S::where('id',$request['idUser'])->first();
			}
			if(isset($request['kataSandi'])){
				$log->katasandi= $request['kataSandi'];
			}
			$log->namauser= $request['namaUser'];
			$log->objectpegawaifk= $request['pegawai']['id'];
			$log->objectkelompokuserfk= $request['kdKelompokUser'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Sukses";
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
	public function deleteLoginUser (Request $request)
	{
		DB::beginTransaction();
		try{

			 LoginUser_S::where('id',$request['idUser'])->update(
			 	['statusenabled' =>  false]
			 );

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Sukses";
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