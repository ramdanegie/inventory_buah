<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 20/02/2019
 * Time: 08.20
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Standar\LoginUser_S;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  MasterController extends Controller
{
	use Core;

	public function getAlamat (Request $request)
	{
		$data = DB::table('alamat_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('alamat')
			->get();

		$result['code'] = 200;
		$result['data'] = $data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
	public function saveAlamat (Request $request)
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
}