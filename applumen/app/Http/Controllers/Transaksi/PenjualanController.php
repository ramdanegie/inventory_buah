<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 25/02/2019
 * Time: 02.18
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

class  PenjualanController extends Controller
{

	public function getStokProduk(Request $request)
	{
		$results = DB::select(DB::raw("select spt.norec as norec_terima,spt.nopenerimaan, sp.produkfk,spt.tgltransaksi, sp.hargajual,
				sum(sp.qty) as qtyproduk
				from stokproduk_t as sp
				INNER JOIN strukpenerimaan_t as spt on spt.norec=sp.strukpenerimaanfk
				where sp.produkfk =:produkId
				and sp.qty > 0
				GROUP  by spt.norec,sp.produkfk,spt.tgltransaksi, sp.hargajual
				order by spt.tgltransaksi desc;
				"),
			array(
				'produkId' => $request['produkfk'],
			)
		);
		$jmlstok =0;
		foreach ($results as $item){
			$jmlstok = $jmlstok+$item->qtyproduk;
		}
		$result= array(
			'code'=> 200,
			'jmlstok'=> $jmlstok,
			'detail' => $results,
			'message' => 'ramdanegoe',
		);
		return response()->json($result);;
	}

}