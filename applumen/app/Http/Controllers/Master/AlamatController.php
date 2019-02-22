<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 05.01
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\Alamat_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  AlamatController extends Controller
{
	use JsonResponse;

	public function getAlamat(Request $request)
	{
		$data = DB::table('alamat_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('alamat')
			->get();

		$result['code'] = 200;
		$result['data'] = $data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}

	public function saveAlamat(Request $request)
	{
		DB::beginTransaction();
		try {
			$idMax = Alamat_M::max('id') + 1;
			if ($request['idAlamat'] == null) {
				$log = new Alamat_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = Alamat_M::where('id', $request['idAlamat'])->first();
			}
			$log->alamat = $request['alamat'];
			$log->provinsi = $request['provinsi'];
			$log->kota = $request['kota'];
			$log->kabupaten = $request['kabupaten'];
			$log->kecamatan = $request['kecamatan'];
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
				'message' => $transMessage,
				'as' => 'ramdanegie',
			);
		}
		return response()->json($result, $result['status']);
	}
	public function deleteAlamat(Request $request)
	{
		DB::beginTransaction();
		try {
			Alamat_M::where('id', $request['idAlamat'])->update(
				['statusenabled' => false]
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
			$transMessage = "Terjadi Kesalahan saat menyimpan data";
			DB::rollBack();
			$result = array(
				'status' => 500,
				'message' => $transMessage,
				'as' => 'ramdanegie',
			);
		}
		return response()->json($result, $result['status']);
	}
}