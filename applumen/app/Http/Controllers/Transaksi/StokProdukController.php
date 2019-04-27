<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 04/03/2019
 * Time: 20.53
 */

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Model\Master\JenisTransaksi_M;
use App\Model\Master\Pegawai_M;
use App\Model\Standar\LoginUser_S;
use App\Model\Standar\KelompokUser_S;
use App\Model\Transaksi\StokProduk_T;
use App\Model\Transaksi\StrukPenerimaan_T;
use App\Traits\GenerateCode;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  StokProdukController extends Controller
{
//	use Core;
	use GenerateCode;

	public function getCombo(Request $request)
	{

		$kelompokProduk = DB::table('kelompokproduk_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('kelompokproduk')
			->get();

		$jenisProduk = DB::table('jenisproduk_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('jenisproduk')
			->get();

		$detailJenisProduk = DB::table('detailjenisproduk_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('detailjenisproduk')
			->get();
		$kelompokProd =[];
		foreach ($kelompokProduk as $item) {
			$detail = [];
			foreach ($jenisProduk as $item2) {

				$detail2 = [];
				foreach ($detailJenisProduk as $item3) {
					if ($item2->id == $item3->jenisprodukfk) {
						$detail2[] = array(
							'id' => $item3->id,
							'detailjenisproduk' => $item3->detailjenisproduk,
						);
					}

				}
				if ($item->id == $item2->kelompokprodukfk) {
					$detail[] = array(
						'id' => $item2->id,
						'jenisproduk' => $item2->jenisproduk,
						'detailjenisproduk'=> $detail2
					);
				}
			}

			$kelompokProd[] = array(
				'id' => $item->id,
				'kelompokproduk' => $item->kelompokproduk,
				'jenisproduk' => $detail,
			);
		}
		$result['code'] = 200;
		$result['data'] = array(
			'kelompokproduk' => $kelompokProd,

		);
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
	public function getStokProduk(Request $request)
	{
		$data = DB::table('stokproduk_t as sp')
		->join('produk_m as prd','prd.id','=','sp.produkfk')
			->join('strukpenerimaan_t as spt','spt.norec','=','sp.strukpenerimaanfk')
			->jOIN ('satuanstandard_m AS ss','ss.id','=','prd.satuanstandardfk')
			->JOIN('satuanstandard_m AS ssd','ssd.id','=','sp.satuanterimafk')
			
			->leftJoin('detailjenisproduk_m as djp','djp.id','=','prd.detailjenisprodukfk')
			->leftJoin('jenisproduk_m as jp','jp.id','=','djp.jenisprodukfk')
			->leftJoin('kelompokproduk_m as kl','kl.id','=','jp.kelompokprodukfk')
			->leftJoin('toko_m as tk','tk.id','=','spt.tokofk')
			->select('sp.norec','sp.produkfk','prd.namaproduk',	'ssd.id AS satuanterimafk',	'ssd.satuanstandard as satuanterima',
				'ss.id as satuanfk','ss.satuanstandard' ,
				'sp.hargajual','spt.nopenerimaan','sp.nofaktur','spt.tgltransaksi','spt.tokofk','tk.namatoko',
				DB::raw("sum(sp.qty) as stok"))
			->groupBy('sp.norec','sp.produkfk','prd.namaproduk','ss.id','ss.satuanstandard',
				'sp.hargajual','spt.tgltransaksi','spt.nopenerimaan','sp.nofaktur','spt.tokofk','tk.namatoko','ssd.id','ssd.satuanstandard')
			->where('ss.statusenabled',true)
			->orderBy('spt.tgltransaksi','desc');

		if(isset($request['kdKelompokProduk'])  && $request['kdKelompokProduk']!=''
			    && $request['kdKelompokProduk']!= 'undefined' && $request['kdKelompokProduk']!= 'null'){
			$data= $data->where('kl.id','=',$request['kdKelompokProduk']);
		}
		if(isset($request['kdJenisProduk'])  && $request['kdJenisProduk']!=''  && $request['kdJenisProduk']!= 'undefined'
			&& $request['kdJenisProduk']!= 'null'){
			$data= $data->where('jp.id','=',$request['kdJenisProduk']);
		}
		if(isset($request['kdDetailJenis'])  && $request['kdDetailJenis']!=''  && $request['kdDetailJenis']!= 'undefined'
			&& $request['kdDetailJenis']!= 'null'){
			$data= $data->where('djp.id','=',$request['kdDetailJenis']);
		}
		if(isset($request['namaProduk'])  && $request['namaProduk']!=''  && $request['namaProduk']!= 'undefined'
			&& $request['namaProduk']!= 'null'){
			$data= $data->where('prd.namaproduk','ilike','%'.$request['namaProduk'].'%');
		}
		if(isset($request['row'])  && $request['row']!=''  && $request['row']!= 'undefined'&& $request['row']!= 'null'){
			$data= $data->take($request['row']);
		}
		$data = $data->get();
	

		$result= array(
			'code'=> 200,
			'data' => $data,
			'message' => 'ramdanegoe',
		);
		return response()->json($result);
	}
	public function updateHarga(Request $request){

		DB::beginTransaction();
		try {

			$SP = StokProduk_T::where('norec', $request['norec'])
					->where('produkfk', $request['produkfk'])->first();
			$SP->hargajual = $request['hargajual'];
			$SP->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Update Harga";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"data" => $SP,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Update Harga Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
}