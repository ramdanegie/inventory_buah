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
			->join('strukpembayarandetail_t as dd','dd.strukpembayaranfk','=','ss.norec')
			->join('pegawai_m as pg','pg.id','=','ss.pegawaifk')
			->join('tipepembayaran_m as tt','tt.id','=','dd.tipepembayaranfk')
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
}