<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 23/02/2019
 * Time: 19.38
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\MapProdukToSatuanStandar_M;
use App\Model\Master\SatuanStandar_M;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  MapProdukToSatuanController extends Controller
{
	public function getMapSatuan (Request $request)
	{
		$data = DB::table('mapproduktosatuanstandard_m as mp')
			->join('produk_m as prd','prd.id','=','mp.produkfk')
			->join('satuanstandard_m as sa','sa.id','=','mp.satuanasalfk')
			->join('satuanstandard_m as st','st.id','=','mp.satuantujuanfk')
			->leftjoin('satuanstandard_m as ss','ss.id','=','prd.satuanstandardfk')
			->select('mp.*','sa.satuanstandard as satuanasal','st.satuanstandard as satuantujuan',
				'prd.namaproduk','ss.id as satuanprodukfk','ss.satuanstandard as satuanproduk')
			->where('mp.statusenabled',true)
			->get();
		$result['data'] = $data;
		$result['status'] = 200;
		$result['as'] = "ramdanegie";

		return response()->json($result,$result['status']);
	}
	public function saveMapSatuan (Request $request)
	{
		DB::beginTransaction();
		try{
			$idMax = MapProdukToSatuanStandar_M::max('id') + 1;
			if($request['idMap'] == null){
				$log = new MapProdukToSatuanStandar_M();
				$log->id = $idMax;
				$log->statusenabled = true;
			}else{
				$log = MapProdukToSatuanStandar_M::where('id',$request['idMap'])->first();
			}
			$log->produkfk= $request['produk']['kdProduk'];
			$log->satuanasalfk= $request['kdSatuanAsal'];
			$log->satuantujuanfk= $request['kdSatuanTujuan'];
			$log->hasilkonversi= $request['hasilKonversi'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Mapping";
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
	public function deleteMapping (Request $request)
	{
		DB::beginTransaction();
		try{

			MapProdukToSatuanStandar_M::where('id',$request['idMap'])->update(
				[ 'statusenabled' => false ]
			);

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Mapping";
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
