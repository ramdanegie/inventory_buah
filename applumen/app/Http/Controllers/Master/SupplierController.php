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
use App\Model\Master\Supplier_M;
use App\Model\Master\Toko_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  SupplierController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('supplier_m as tk')
			->leftJoin('alamat_m as alm','alm.id','=','tk.alamatfk')
			->select('tk.*','alm.alamat','alm.kota','alm.kabupaten','alm.kecamatan')
			->where('tk.statusenabled', true)
			->orderBy('tk.namasupplier')
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

			$idMax = Supplier_M::max('id') + 1;
			if ($request['idSupplier'] == null) {
				$log = new Supplier_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = Supplier_M::where('id', $request['idSupplier'])->first();
			}
			$log->namasupplier = $request['namaSupplier'];
			$log->alamatfk = $request['kdAlamat'];
			$log->nohp = $request['noHp'];
			$log->notlp = $request['noTlp'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Supplier";
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
			Supplier_M::where('id', $request['idSupplier'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Supplier";
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