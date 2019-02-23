<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 09.26
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\DetailJenisProduk_M;
use App\Model\Master\JenisKelamin_M;
use App\Model\Master\JenisProduk_M;
use App\Model\Master\JenisTransaksi_M;
use App\Model\Master\SatuanStandar_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  SatuanStandarController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('satuanstandard_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('satuanstandard')
			->get();

		$result['code'] = 200;
		$result['data'] = $data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}

	public function save(Request $request)
	{
		DB::beginTransaction();
		try {

			$idMax = SatuanStandar_M::max('id') + 1;
			if ($request['idSatuanStandar'] == null) {
				$log = new SatuanStandar_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = SatuanStandar_M::where('id', $request['idSatuanStandar'])->first();
			}
			$log->satuanstandard = $request['satuanStandar'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Satuan";
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

	public function delete(Request $request)
	{
		DB::beginTransaction();
		try {
			SatuanStandar_M::where('id', $request['idSatuanStandar'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Satuan";
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