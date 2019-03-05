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
use App\Model\Transaksi\Struk_T;
use App\Model\Transaksi\StrukPenerimaan_T;
use App\Model\Transaksi\Transaksi_T;
use App\Traits\GenerateCode;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  PenjualanController extends Controller
{
	use GenerateCode;
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
	public function savePenjualan(Request $request){
		$maxNoTransaksi = $this->getNewCode( 'notransaksi', 12, 'ST'.date('ym'));
		if ($maxNoTransaksi == ''){
			DB::rollBack();
			$result = array(
				"status" => 400,
				"message"  => 'Gagal mengumpukan data, Coba lagi.',
				"as" => 'ramdanegie',
			);
			return response()->json($result,$result['status']);
		}

		DB::beginTransaction();
		try {
			$req = $request['penjualan'];
			if ($req['noRec'] == null) {
				$SP = new Struk_T();
				$norecSP = $this->generateUid();
				$noTransaksi = $maxNoTransaksi;
				$SP->norec = $norecSP;
				$SP->statusenabled = true;
			} else {
				$SP = Struk_T::where('norec', $req['noRec'])->first();
				Transaksi_T::where('strukfk', $req['noRec'])->delete();
				$noTransaksi = $SP->notransaksi;
				foreach ($request['detail'] as $item) {
					//region Penambahan Stok

					$stokProduk = StokProduk_T::where('strukpenerimaanfk',$item['strukpenerimaanfk'])
						->where('produkfk',$item['kdProduk'])
						->first();

					$jmlStok = (float) $stokProduk->qty + (float)$item['konversi'];
					StokProduk_T::where('strukpenerimaanfk',$item['strukpenerimaanfk'])
						->where('produkfk',$item['kdProduk'])
						->update([
							'qty' => $jmlStok
						]);
					//endregion
				}
			}
			$SP->notransaksi = $noTransaksi;
			$SP->customerfk = $req['kdCustomer'];
			$SP->tokofk = $req['kdToko'];
			$SP->jenistransaksifk = 2;
			$SP->pegawaifk = $req['kdPegawai'];
			$SP->tgltransaksi = date('Y-m-d H:i:s', strtotime($req['tglTransaksi']));
			$SP->save();
			$norecStruk = $SP->norec;
			$noTrans = $SP->notransaksi;
			foreach ($request['detail'] as $item){
				$detail = new Transaksi_T();
				$detail->norec = $this->generateUid();
				$detail->strukfk  =$norecStruk;
				$detail->notransaksi = $noTrans;
				$detail->produkfk = $item['kdProduk'];
				$detail->penerimaanfk = $item['strukpenerimaanfk'];
				$detail->qty = $item['qtyProduk'];
				$detail->hargajual = $item['hargaJual'];
				$detail->hargadiskon = $item['hargaDiskon'];
				$detail->tgltransaksi = date('Y-m-d H:i:s');
				$detail->nilaikonversi = $item['konversi'];
				$detail->satuanjualfk = $item['kdSatuan'];

				$detail->save();

				//region Pengurangan Stok

				$stokProduk = StokProduk_T::where('strukpenerimaanfk',$item['strukpenerimaanfk'])
					->where('produkfk',$item['kdProduk'])
					->first();

				$jmlStok = (float) $stokProduk->qty - (float)$item['konversi'];
				StokProduk_T::where('strukpenerimaanfk',$item['strukpenerimaanfk'])
					->where('produkfk',$item['kdProduk'])
					->update([
						'qty' => $jmlStok
					]);
				//endregion
			}

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Simpan Penjualan";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"data" => $SP,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Simpan Penjualan Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function getDaftarPenjualan(Request $request)
	{
		$data = DB::table('struk_t as sp')
			->LEFTJOIN('jenistransaksi_m as jt', 'jt.id', '=', 'sp.jenistransaksifk')
			->LEFTJOIN('toko_m as tk', 'tk.id', '=', 'sp.tokofk')
			->LEFTJOIN('customer_m as cus', 'cus.id', '=', 'sp.customerfk')
			->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
			->select('sp.norec','sp.tgltransaksi', 'sp.notransaksi','jt.id as jenistransaksifk', 'jt.jenistransaksi', 'sp.tokofk', 'tk.namatoko',
				'sp.customerfk', 'cus.namacustomer','cus.notlp','cus.nohp','sp.pegawaifk', 'pg.namalengkap')
			->where('sp.statusenabled',true)
			->orderBy('sp.tgltransaksi','desc');

		if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
			$data = $data->where('sp.tgltransaksi', '>=', $request['tglAwal']);
		}
		if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
			$tgl = $request['tglAkhir'];
			$data = $data->where('sp.tgltransaksi', '<=', $tgl);
		}
		if (isset($request['notransaksi']) && $request['notransaksi'] != "" && $request['notransaksi'] != "undefined") {
			$data = $data->where('sp.notransaksi', 'ilike', '%' . $request['notransaksi']);
		}

		if (isset($request['namacustomer']) && $request['namacustomer'] != "" && $request['namacustomer'] != "undefined") {
			$data = $data->where('cus.namacustomer', 'ilike', '%' . $request['namacustomer'] . '%');
		}
		if (isset($request['kdpegawai']) && $request['kdpegawai'] != "" && $request['kdpegawai'] != "undefined") {
			$data = $data->where('pg.id', '=', $request['kdpegawai'] );
		}
		if (isset($request['kdtoko']) && $request['kdtoko'] != "" && $request['kdtoko'] != "undefined") {
			$data = $data->where('tk.id', '=', $request['kdtoko'] );
		}
		if (isset($request['norec']) && $request['norec'] != "" && $request['norec'] != "undefined") {
			$data = $data->where('sp.norec', '=', $request['norec']);
		}
		$data = $data->get();
		$resData = [];
		foreach ($data as $item){
			$norec = $item->norec;
			$details = DB::select(DB::raw("
					select tt.norec as norec_detail,tt.produkfk,
					pr.namaproduk,ss.id as satuanfk,ss.satuanstandard,
					tt.qty,tt.hargajual, COALESCE(tt.hargadiskon ,0) as hargadiskon,
					tt.nilaikonversi,tt.satuanjualfk, sss.satuanstandard as satuanjual,
					tt.penerimaanfk,(tt.hargajual *tt.qty) -  COALESCE(tt.hargadiskon ,0)as total
					from transaksi_t as tt
					join struk_t as s on s.norec= tt.strukfk
					join produk_m as pr on pr.id= tt.produkfk
					left join satuanstandard_m as ss on ss.id= pr.satuanstandardfk
						left join satuanstandard_m as sss on sss.id= tt.satuanjualfk
					where tt.strukfk = '$norec'
			"));
			$qty = 0;
			$total = 0;
			foreach ($details as $items){
				$total = $total + (float) $items->total;
				$qty = $qty + (float) $items->qty;
			}

			$resData [] = array(
				'norec' => $item->norec,
				'tgltransaksi' =>date('Y-m-d H:i', strtotime($item->tgltransaksi)),
				'notransaksi' => $item->notransaksi,
				'jenistransaksifk' => $item->jenistransaksifk,
				'jenistransaksi' => $item->jenistransaksi,
				'tokofk' => $item->tokofk,
				'namatoko' => $item->namatoko,
				'customerfk' => $item->customerfk,
				'namacustomer' => $item->namacustomer,
				'pegawaifk' => $item->pegawaifk,
				'namalengkap' => $item->namalengkap,
				'telpon' => $item->notlp.'-'.$item->nohp,
				'qtyproduk' => $qty,
				'totalall' => $total,
				'details' => $details
			);
		}
		$result = array(
			'data' => $resData,
			'as' => 'ramdanegie'
		);
		return response()->json($result);
	}
	public function hapusPenjualan(Request $request){

		DB::beginTransaction();
		try {
			$trans = Transaksi_T::where('strukfk', $request['noRec'])
				->get();

			foreach ($trans as $item){
				//region Penambahan Stok

				$stokProduk = StokProduk_T::where('strukpenerimaanfk',$item->penerimaanfk)
					->where('produkfk',$item->produkfk)
					->first();

				$jmlStok = (float) $stokProduk->qty + (float)$item->qty;
//				return $jmlStok;
				StokProduk_T::where('strukpenerimaanfk',$item['strukpenerimaanfk'])
					->where('produkfk',$item->produkfk)
					->update([
						'qty' => $jmlStok
					]);
				//endregion
			}


			 Struk_T::where('norec', $request['noRec'])->update(
				 [ 'statusenabled' => false]
			 );
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Hapus Penjualan";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Hapus Penjualan Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function getStokByNoterima(Request $request)
	{
		$results = DB::select(DB::raw("select spt.norec as norec_terima,spt.nopenerimaan, sp.produkfk,spt.tgltransaksi, sp.hargajual,
				sum(sp.qty) as qtyproduk
				from stokproduk_t as sp
				INNER JOIN strukpenerimaan_t as spt on spt.norec=sp.strukpenerimaanfk
				where sp.produkfk =:produkId
				and spt.norec= :norecTerima
				and sp.qty > 0
				GROUP  by spt.norec,sp.produkfk,spt.tgltransaksi, sp.hargajual
				order by spt.tgltransaksi desc;
				"),
			array(
				'produkId' => $request['produkfk'],
				'norecTerima' => $request['norecTerima'],
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