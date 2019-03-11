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
				$SP = new Struk_T();
				$norecSP = $this->generateUid();
				$SP->norec = $norecSP;
				$SP->statusenabled = true;
				$SP->nopembayaran = $maxNoTransaksi;
				$SP->totalbayar = $request['kdCustomer'];
				$SP->pegawaifk = $request['kdToko'];
				$SP->jenistransaksifk = 2;
//				$SP->pegawaifk = $req['kdPegawai'];
//				$SP->tgltransaksi = date('Y-m-d H:i:s', strtotime($req['tglTransaksi']));
//			return date('Y-m-d H:i:s', strtotime($req['tglTransaksi']));
				$SP->save();
				$norecStruk = $SP->norec;
				$noTrans = $SP->notransaksi;


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
}