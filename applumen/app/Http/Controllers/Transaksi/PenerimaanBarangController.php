<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 21/02/2019
 * Time: 22.57
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

class  PenerimaanBarangController extends Controller
{
//	use Core;
	use GenerateCode;
	public function getListCombo (Request $request)
	{
		$toko = DB::table('toko_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('namatoko')
			->get();
		$pegawai = DB::table('pegawai_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('namalengkap')
			->get();
		$produk = DB::table('produk_m as prd')
			->select('prd.*','ss.satuanstandard','djp.detailjenisproduk')
			->leftjoin('satuanstandard_m as ss','ss.id','=','prd.satuanstandardfk')
			->leftjoin('detailjenisproduk_m as djp','djp.id','=','prd.detailjenisprodukfk')
			->where ('prd.statusenabled',true)
			->orderBy('namaproduk')
			->get();

		$satuan = DB::table('satuanstandard_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('satuanstandard')
			->get();
		$supplier = DB::table('supplier_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('namasupplier')
			->get();
		$result['code'] = 200;
		$result['data'] = array(
			'toko' => $toko,
			'pegawai' => $pegawai,
			'produk' => $produk,
			'satuan' => $satuan,
			'supplier' => $supplier,
		);
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
	public function savePenerimaan(Request $request){

		DB::beginTransaction();
		try {
			$req = $request['penerimaan'];
			$isAutoNoTerima =  $request['isAutoNoTerima'];
			$isAutoNoFaktur =  $request['isAutoNoFaktur'];
			if($isAutoNoFaktur == true){
				$maxNoFaktur = $this->getNewCode( 'nofaktur', 12, 'FS'.date('ym'));
				if ($maxNoFaktur == ''){
					DB::rollBack();
					$result = array(
						"status" => 400,
						"message"  => 'Gagal mengumpukan data, Coba lagi.',
						"as" => 'ramdanegie',
					);
					return response()->json($result,$result['status']);
				}else{
					$noFaktur = $maxNoFaktur;
				}
			}else{
				$noFaktur = $req['noFaktur'];
			}
			if($isAutoNoTerima == true){
				$maxNoTerima = $this->getNewCode( 'nopenerimaan', 12, 'FS'.date('ym'));
				if ($maxNoTerima == ''){
					DB::rollBack();
					$result = array(
						"status" => 400,
						"message"  => 'Gagal mengumpukan data, Coba lagi.',
						"as" => 'ramdanegie',
					);
					return response()->json($result,$result['status']);
				}else{
					$noTerima = $maxNoTerima;
				}

			}else{
				$noTerima = $req['noPenerimaan'];
			}


			if ($req['noRec'] == null) {
				$SP = new StrukPenerimaan_T();
				$norecSP = $this->generateUid();
				$SP->norec = $norecSP;
				$SP->statusenabled = true;
				$SP->nopenerimaan = $noTerima;
				$SP->nofaktur = $noFaktur;
			} else {
				$SP = StrukPenerimaan_T::where('norec', $req['noRec'])->first();
				StokProduk_T::where('strukpenerimaanfk',$req['noRec'])->delete();
//				$noTerima = $SP->nopenerimaan;
//				$noFaktur = $SP->nofaktur;
				//region PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//				$dataKembaliStok = DB::select(DB::raw("select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,
//                          sp.nostruk
//                                from strukpelayanandetail_t as spd
//                                INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
//                                where sp.norec=:norec"),
//					array(
//						'norec' => $request['struk']['nostruk'],
//					)
//				);
//
//				foreach ($dataKembaliStok as $item5) {
//					$TambahStok = (float)$item5->qtyproduk * (float)$item5->hasilkonversi;
//					$dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
//                                where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
//						array(
//							'ruanganfk' => $item5->objectruanganfk,
//							'produkfk' => $item5->objectprodukfk,
//						)
//					);
//
//					$saldoAwal = 0;
//					foreach ($dataSaldoAwal as $itemss) {
//						$saldoAwal = (float)$itemss->qty;
//					}
//
//					foreach ($req['details'] as $hit) {
//						if ($saldoAwal == $hit['jumlah'] || $saldoAwal >= $hit['jumlah']) {
//							if ($request['struk']['norecOrder'] != '') {
//								foreach ($req['details'] as $item) {
//									$dataOP = OrderPelayanan::where('noorderfk', $request['struk']['norecOrder'])
//										->where('objectprodukfk', $item['produkfk'])
//										->update([
//												'qtyterimalast' => (float)$item['jumlah']]
//										);
//
//								}
//
//							}
//
//
//
//							//END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//
//							//TODO: betulkan ubah penerimaan masih salah
//							//ubah penerimaan harusnya brg yg di terima hrs di keluarkan dulu
//							//tpi ini barang sudah terpakai, pengurang stok hanya delete spd dengan brang yg sudah kepake
//							$delSPD = StokProdukDetail::where('nostrukterimafk', $request['struk']['nostruk'])
//								->delete();
//							$delSPD = StrukPelayananDetail::where('nostrukfk', $request['struk']['nostruk'])
//								->delete();
//						}
//					}
//				}
				//endregion
			}
			$SP->jenistransaksi =1;// JenisTransaksi_M::where('jenistransaksi','PENERIMAAN')->first()->id;
			$SP->tgltransaksi = $req['tglPenerimaan'];
			$SP->tokofk = $req['kdToko'];
			if(isset($req['kdSupplier'])){
				$SP->supplierfk = $req['kdSupplier'];
			}
			$SP->pegawaifk = $req['kdPegawai'];
			$SP->save();

			foreach ($request['details'] as $item) {
//				$qtyJumlah = (float)$item['qtyProduk'] * 1 ;//konversi

				$SPD = new StokProduk_T();
				$SPD->norec = $this->generateUid();
				$SPD->statusenabled = true;
				$SPD->strukpenerimaanfk = $SP->norec;
				$SPD->produkfk = $item['kdProduk'];
				$SPD->qty =  $item['konversi'];
				$SPD->hargapenerimaan = $item['hargaSatuan'];
				$SPD->hargajual = $item['hargaJual'];
				$SPD->qtypenerimaan =  $item['qtyProduk'] ;
				$SPD->ppn = 0;
				$SPD->nofaktur = $noFaktur;
				$SPD->tokofk = $req['kdToko'];
				$SPD->satuanterimafk = $item['satuanterimafk'];
				$SPD->konversi = $item['konversi'];
//				$SPD->verifikasifk = $item['kdProduk'];
				$SPD->save();
			}
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Simpan Penerimaan";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"data" => $SP,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Simpan Penerimaan Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function getDaftarPenerimaanSuplier(Request $request)
	{
		$data = \DB::table('strukpenerimaan_t as sp')
			->LEFTJOIN('jenistransaksi_m as jt', 'jt.id', '=', 'sp.jenistransaksi')
			->LEFTJOIN('toko_m as tk', 'tk.id', '=', 'sp.tokofk')
			->LEFTJOIN('supplier_m as sup', 'sup.id', '=', 'sp.supplierfk')
//			->LEFTJOIN('satuanstandard_m as ss', 'ss.id', '=', 'sp.supplierfk')
			->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
			->select('sp.norec','sp.tgltransaksi', 'sp.nopenerimaan', 'sp.nofaktur','jt.id as jenistransaksifk', 'jt.jenistransaksi', 'sp.tokofk', 'tk.namatoko',
				'sp.supplierfk', 'sup.namasupplier','sp.pegawaifk', 'pg.namalengkap as namapenerima')
			->where('sp.statusenabled',true);

		if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
			$data = $data->where('sp.tgltransaksi', '>=', $request['tglAwal']);
		}
		if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
			$tgl = $request['tglAkhir'];
			$data = $data->where('sp.tgltransaksi', '<=', $tgl);
		}
		if (isset($request['nopenerimaan']) && $request['nopenerimaan'] != "" && $request['nopenerimaan'] != "undefined") {
			$data = $data->where('sp.nopenerimaan', 'ilike', '%' . $request['nopenerimaan']);
		}
		if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
			$data = $data->where('sp.nofaktur', 'ilike', '%' . $request['nofaktur'] . '%');
		}
		if (isset($request['namasupplier']) && $request['namasupplier'] != "" && $request['namasupplier'] != "undefined") {
			$data = $data->where('sp.namasupplier', 'ilike', '%' . $request['namasupplier'] . '%');
		}
		if (isset($request['kdpegawai']) && $request['kdpegawai'] != "" && $request['kdpegawai'] != "undefined") {
			$data = $data->where('pg.id', '=', $request['kdpegawai'] );
		}
		if (isset($request['norec']) && $request['norec'] != "" && $request['norec'] != "undefined") {
			$data = $data->where('sp.norec', '=', $request['norec']);
		}
		$data = $data->get();
		$resData = [];
		foreach ($data as $item){
			$norec = $item->norec;
			$details = DB::select(DB::raw("select spt.norec as norec_stok,spt.produkfk,
				pr.namaproduk,ss.id as satuanfk,ss.satuanstandard,
				spt.qty,spt.hargapenerimaan,spt.hargajual,spt.qtypenerimaan,
				spt.ppn,spt.strukpenerimaanfk,(spt.hargapenerimaan*spt.qtypenerimaan) as totalpenerimaan,
				 spt.satuanterimafk,sss.satuanstandard as satuanterima,spt.konversi
 				from stokproduk_t as spt
				join strukpenerimaan_t as sp on sp.norec= spt.strukpenerimaanfk
				join produk_m as pr on pr.id= spt.produkfk
				left join satuanstandard_m as ss on ss.id= pr.satuanstandardfk
				left join satuanstandard_m as sss on sss.id= spt.satuanterimafk
				where spt.strukpenerimaanfk = '$norec'
			"));
			$qty = 0;
			$totalterima = 0;
			foreach ($details as $items){
				$totalterima = $totalterima + (float) $items->totalpenerimaan;
				$qty = $qty + (float) $items->qtypenerimaan;
			}
			$resData [] = array(
				'norec' => $item->norec,
				'tgltransaksi' =>date('Y-m-d H:i', strtotime($item->tgltransaksi)),
				'nopenerimaan' => $item->nopenerimaan,
				'nofaktur' => $item->nofaktur,
				'jenistransaksifk' => $item->jenistransaksifk,
				'jenistransaksi' => $item->jenistransaksi,
				'tokofk' => $item->tokofk,
				'namatoko' => $item->namatoko,
				'supplierfk' => $item->supplierfk,
				'namasupplier' => $item->namasupplier,
				'pegawaifk' => $item->pegawaifk,
				'namapenerima' => $item->namapenerima,
				'qty' => $qty,
				'total' => $totalterima,
				'details' => $details
			);
		}
		$result = array(
			'data' => $resData,
			'as' => 'ramdanegie'
		);
		return response()->json($result);
	}
	public function hapusPenerimaan(Request $request){

		DB::beginTransaction();
		try {
			StokProduk_T::where('strukpenerimaanfk',$request['noRec'])->delete();
			StrukPenerimaan_T::where('norec', $request['noRec'])->delete();
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Hapus Penerimaan";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Hapus Penerimaan Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function getMapProdukToSatuan (Request $request)
	{
		$data = DB::table('mapproduktosatuanstandard_m as mm')
			->join('produk_m as pr','pr.id','=','mm.produkfk')
			->select('mm.id')
			->where ('statusenabled',true)
			->orderBy('namatoko')
			->get();

		$result['code'] = 200;
		$result['data'] = $data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
}