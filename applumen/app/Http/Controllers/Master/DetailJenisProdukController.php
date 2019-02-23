<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 09.26
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\Alamat_M;
use App\Model\Master\Customer_M;
use App\Model\Master\DetailJenisProduk_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  DetailJenisProdukController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('detailjenisproduk_m as djp')
			->leftJoin('jenisproduk_m as jp','jp.id','=','djp.jenisprodukfk')
			->select('djp.*','jp.jenisproduk')
			->where('djp.statusenabled', true)
			->orderBy('djp.detailjenisproduk')
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

			$idMax = DetailJenisProduk_M::max('id') + 1;
			if ($request['idDetailJenisProduk'] == null) {
				$log = new DetailJenisProduk_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = DetailJenisProduk_M::where('id', $request['idDetailJenisProduk'])->first();
			}
			$log->detailjenisproduk = $request['detailJenisProduk'];
			$log->jenisprodukfk = $request['kdJenisProduk'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Detail Jenis Produk";
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
			DetailJenisProduk_M::where('id', $request['idDetailJenisProduk'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Detail Jenis Produk";
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