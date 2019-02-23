<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 05.07
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\Alamat_M;
use App\Model\Master\Customer_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  CustomerController extends Controller
{
	use JsonResponse;

	public function get(Request $request)
	{
		$data = DB::table('customer_m')
			->leftJoin('alamat_m','alamat_m.id','=','customer_m.alamatfk')
			->select('customer_m.*','alamat_m.alamat')
			->where('customer_m.statusenabled', true)
			->orderBy('customer_m.namacustomer')
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
//			return 	$log = Customer_M::where('id', $request['idCustomer'])->first();

			$idMax = Customer_M::max('id') + 1;
			if ($request['idCustomer'] == null) {
				$log = new Customer_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			} else {
				$log = Customer_M::where('id', $request['idCustomer'])->first();
			}
			$log->namacustomer = $request['namaCustomer'];
			$log->alamatfk = $request['kdAlamat'];
			$log->notlp = $request['noTlp'];
			$log->nohp = $request['noHp'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Customer";
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
			Customer_M::where('id', $request['idCustomer'])->update(
				['statusenabled' => false]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Customer";
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