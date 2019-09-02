<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 11/03/2019
 * Time: 18.46
 */

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Model\Master\JenisTransaksi_M;
use App\Model\Master\Pegawai_M;
use App\Model\Standar\LoginUser_S;
use App\Model\Standar\KelompokUser_S;
use App\Model\Transaksi\StokProduk_T;
use App\Model\Transaksi\Struk_T;
use App\Model\Transaksi\StrukPembayaran_T;
use App\Model\Transaksi\StrukPembayaranDetail_T;
use App\Model\Transaksi\StrukPenerimaan_T;
use App\Traits\GenerateCode;

use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use App\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use test\Mockery\SimpleTrait;

class  StrukPembayaranController extends Controller
{
//	use Core;
	use GenerateCode;

	public function getCombo(Request $request)
	{
		$tipePembayaran = DB::table('tipepembayaran_m')
			->select('*')
			->where('statusenabled', true)
			->orderBy('tipepembayaran')
			->get();


		$result['code'] = 200;
		$result['data'] = array(
			'tipepembayaran' => $tipePembayaran,

		);
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}

    public function getPenerimaanKasir(Request $request)
    {
        $data = DB::table('strukpembayaran_t as sp')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->select('sp.*', 'pg.id as pegawaiid', 'pg.namalengkap')
            ->where('sp.statusenabled','true');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglpembayaran', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('sp.tglpembayaran', '<=', $tgl);
        }
        if (isset($request['noPembayaran']) && $request['noPembayaran'] != "" && $request['noPembayaran'] != "undefined") {
            $data = $data->where('sp.nopembayaran', 'ilike', $request['noPembayaran']);
        }
        if (isset($request['kdPegawai']) && $request['kdPegawai'] != "" && $request['kdPegawai'] != "undefined") {
            $data = $data->where('pg.id', '=', $request['kdPegawai']);
        }

        $data=$data->get();
        $resData = [];
        foreach ($data as $item){
            $norec=$item->norec;
            $details = DB::select(DB::raw("SELECT spd.*
                FROM strukpembayarandetail_t as spd
                LEFT JOIN strukpembayaran_t as sp on sp.norec = spd.strukpembayaranfk
                LEFT JOIN tipepembayaran_m as tp on tp.id = spd.tipepembayaranfk
				where spd.strukpembayaranfk = '$norec'
			"));
            $resData [] = array(
                'norec' => $item->norec,
                'nopembayaran' => $item->nopembayaran,
                'tlgpembayaran' => $item->tglpembayaran,
                'totalbayar' => $item->totalbayar,
                'details' => $details
            );
        }
        $result = array(
            'data' => $resData,
            'as' => 'SitepuMan'
        );
        return response()->json($result);
    }

	public function getTerbilangsss($number)
	{
		$data = $this->getTerbilang($number);
		return response()->json($data);
	}
	public function savePembayaran(Request $request){
		$maxNoTransaksi = $this->getNewCode( 'nopembayaran', 12, 'IP'.date('ym'));
		if ($maxNoTransaksi == ''){
			DB::rollBack();
			$result = array(
				"status" => 400,
				"message"  => 'Gagal mengumpukan data, Coba lagi.',
				"as" => 'ramdanegie',
			);
			return response()->json($result,$result['status']);
		}
		if(isset($request['isPenerimaanSupplier']) && $request['isPenerimaanSupplier'] ==true){
			$maxNoTransaksi = $this->getNewCode( 'nopembayaran', 12, 'IS'.date('ym'));
			if ($maxNoTransaksi == ''){
				DB::rollBack();
				$result = array(
					"status" => 400,
					"message"  => 'Gagal mengumpukan data, Coba lagi.',
					"as" => 'ramdanegie',
				);
				return response()->json($result,$result['status']);
			}
		}


		DB::beginTransaction();
		try {
			if ($request['norec_transaksi'] != null) {
				$SP = new StrukPembayaran_T();
				$norecSP = $this->generateUid();
				$SP->norec = $norecSP;
				$SP->statusenabled = true;
				$SP->nopembayaran = $maxNoTransaksi;
				$SP->totalbayar = $request['totalbayar'];
				$SP->pegawaifk = $request['pegawaifk'];
				$SP->tglpembayaran = date('Y-m-d H:i:s');
				$SP->save();
				$norecStruk = $SP->norec;
				foreach ($request['detail'] as $item){
					$det = new StrukPembayaranDetail_T();
					$det->norec = $this->generateUid();
					$det->statusenabled = true;
					$det->tipepembayaranfk = $item['tipepembayaranfk'];
					$det->subtotalbayar = $item['nominal'];
					$det->strukpembayaranfk = $norecStruk;
					$det->save();
				}
				if(isset($request['isPenerimaanSupplier']) && $request['isPenerimaanSupplier'] ==true) {
					StrukPenerimaan_T::where('norec',$request['norec_transaksi'])->update(
						[
							'strukpembayaranfk' => $norecStruk
						]
					);
				}
				Struk_T::where('norec',$request['norec_transaksi'])->update(
					[
						'strukpembayaranfk' => $norecStruk
					]
				);
			}

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			$transMessage = "Simpan Pembayaran";
			DB::commit();
			$result = array(
				"message" => $transMessage,
				"status" => 200,
				"data" => $SP,
				"as" => 'ramdanegie',
			);
		} else {
			$transMessage = "Simpan Pembayaran Gagal";
			DB::rollBack();
			$result = array(
				"message" => $transMessage,
				"status" => 500,
				"as" => 'ramdanegie',
			);
		}
		return response()->json($result,$result['status']);
	}
	public function getPembayaranByNoBayar(Request $request)
	{
		$data = DB::table('strukpembayaran_t as ss')
//			->join('strukpembayarandetail_t as dd','dd.strukpembayaranfk','=','ss.norec')
			->join('pegawai_m as pg','pg.id','=','ss.pegawaifk')
//			->join('tipepembayaran_m as tt','tt.id','=','dd.tipepembayaranfk')
			->join('struk_t as str','str.strukpembayaranfk','=','ss.norec')
			->join('transaksi_t as tr','tr.strukfk','=','str.norec')
			->leftjoin('produk_m as prd','prd.id','=','tr.produkfk')
			->select('ss.norec','ss.nopembayaran' ,'ss.totalbayar','ss.tglpembayaran' ,'pg.namalengkap','prd.namaproduk','tr.qty','tr.hargajual','tr.hargadiskon')
			->where('ss.statusenabled', true)
			->where('ss.nopembayaran',$request['nopembayaran'])
			->orderBy('prd.namaproduk')
			->get();


		$result['code'] = 200;
		$result['data'] =$data;
		$result['as'] = "ramdanegie";

		return response()->json($result);
	}
    public function batalPemabayaran(Request $request){

        DB::beginTransaction();
        try {
            StrukPembayaranDetail_T::where('strukpembayaranfk',$request['norecSP'])->delete();
            StrukPembayaran_T::where('norec',$request['norecSP'])->delete();
            $strukTerima = StrukPenerimaan_T::where('strukpembayaranfk',$request['norecSP'])->first();
            $strukPenjualan= Struk_T::where('strukpembayaranfk',$request['norecSP'])->first();
            if(!empty($strukTerima)){
                 StrukPenerimaan_T::where('norec',$strukTerima->norec )->update(
                    [
                        'strukpembayaranfk' => null
                    ]
                );
            }
            if(!empty($strukPenjualan)){
                Struk_T::where('norec',$strukPenjualan->norec )->update(
                    [
                        'strukpembayaranfk' => null
                    ]
                );
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Batal Pembayaran";
            DB::commit();
            $result = array(
                "message" => $transMessage,
                "status" => 200,
//                "data" => $SP,
                "as" => 'ramdanegie',
            );
        } else {
            $transMessage = "Batal Pembayaran Gagal";
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