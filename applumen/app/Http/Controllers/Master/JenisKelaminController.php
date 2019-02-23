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
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  JenisKelaminController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('jeniskelamin_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('jeniskelamin')
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

			$idMax = JenisKelamin_M::max('id') + 1;
			if ($request['idJenisKelamin'] == null) {
				$log = new JenisKelamin_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = JenisKelamin_M::where('id', $request['idJenisKelamin'])->first();
			}
			$log->jeniskelamin = $request['jenisKelamin'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Jenis Kelamin";
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
			JenisKelamin_M::where('id', $request['idJenisKelamin'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Jenis Kelamin";
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