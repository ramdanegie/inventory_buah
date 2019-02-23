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
use App\Model\Master\JenisProduk_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  JenisProdukController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('jenisproduk_m as djp')
			->leftJoin('kelompokproduk_m as jp','jp.id','=','djp.kelompokprodukfk')
			->select('djp.*','jp.kelompokproduk')
			->where('djp.statusenabled', true)
			->orderBy('djp.jenisproduk')
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

			$idMax = JenisProduk_M::max('id') + 1;
			if ($request['idJenisProduk'] == null) {
				$log = new JenisProduk_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = JenisProduk_M::where('id', $request['idJenisProduk'])->first();
			}
			$log->jenisproduk = $request['jenisProduk'];
			$log->kelompokprodukfk = $request['kdKelompokProduk'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Jenis Produk";
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
			JenisProduk_M::where('id', $request['idJenisProduk'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Jenis Produk";
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