<?php
/**
 * Created by IntelliJ IDEA.
 * User: SitepuMan
 * Date: 3/10/2019
 * Time: 2:33 PM
 */
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Model\Transaksi\SetoranDebitKredit_T;
use App\Model\Transaksi\Verifikasi_T;
use App\Traits\GenerateCode;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class SetoranDebitKreditController extends Controller
{
    use GenerateCode;
    public function getCombo(Request $request){
        $datapegawai = DB::table('pegawai_m')
            ->select('*')
            ->where ('statusenabled',true)
            ->orderBy('namalengkap')
            ->get();
        $result['code'] = 200;
        $result[''] = array(
            'status' => 200,
            'data' => $datapegawai,
            'as' => "{ng-SitepuMan}"
        );
        return response()->json($result);
    }
	public function getCombo2(Request $request){
		$ketSetor = DB::table('keterangansetor_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('keterangansetor')
			->get();
		$datapegawai = DB::table('pegawai_m')
			->select('*')
			->where ('statusenabled',true)
			->orderBy('namalengkap')
			->get();
		$result['code'] = 200;
		$result['data'] = array(
			'keterangansetor' => $ketSetor,
			'pegawai' =>$datapegawai,
		);
		return response()->json($result);
	}
	public function saveSetoranManual(Request $request)
	{
		$maxNoTransaksi = $this->getNewCode( 'nosetor', 12, 'S'.date('ym'));
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

			if ($request['norecSetoran'] == null) {
				$log = new SetoranDebitKredit_T();
				$log->norec = $this->generateUid();
				$log->statusenabled = 't';
				$idMax = $maxNoTransaksi;
			} else {
				$log = SetoranDebitKredit_T::where('norec', $request['norecSetoran'])->first();
				$idMax = $log->nosetor;
			}
			$log->nosetor =$idMax;
			$log->pegawaisetorfk = $request['penyetor'];
			$log->pegawaipenerimafk = $request['kdPegawaiPenerima'];
			$log->tgl =$request['tglSetor'];
			$log->ttldebitkredit = (float) $request['jumlahSetor'];
			$log->keteranganfk = $request['keterangan']['id'];
			$log->jenisdebitkredit = $request['keterangan']['jenisdebitkredit'];
			$log->asalsetorfk = $request['asalsetorfk'];
			$log->save();

			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Simpan Setoran";
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
	public function getDaftarSetoran(Request $request)
	{
		$data = \DB::table('setorandebitkredit_t as set')
			->LEFTJOIN('keterangansetor_m as ket', 'ket.id', '=', 'set.keteranganfk')
			->LEFTJOIN('pegawai_m as pg2', 'pg2.id', '=', 'set.pegawaipenerimafk')
			->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'set.pegawaisetorfk')
			->select('set.*','ket.keterangansetor','pg2.namalengkap as penerima','pg.namalengkap as penyetor')
			->where('set.statusenabled','t')
			->orderBy('set.tgl','desc');

		if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
			$data = $data->where('set.tgl', '>=', $request['tglAwal']);
		}
		if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
			$tgl = $request['tglAkhir'];
			$data = $data->where('set.tgl', '<=', $tgl);
		}
		if (isset($request['noSetor']) && $request['noSetor'] != "" && $request['noSetor'] != "undefined") {
			$data = $data->where('set.nosetor', 'ilike', '%' . $request['noSetor']);
		}
		if (isset($request['penyetor']) && $request['penyetor'] != "" && $request['penyetor'] != "undefined") {
			$data = $data->where('pg.id',  '=' , $request['penyetor']) ;
		}
		if (isset($request['penerima']) && $request['penerima'] != "" && $request['penerima'] != "undefined") {
			$data = $data->where('pg2.id',  '=' , $request['penerima']) ;
		}
		if (isset($request['keterangan']) && $request['keterangan'] != "" && $request['keterangan'] != "undefined") {
			$data = $data->where('ket.id',  '=' , $request['keterangan']) ;
		}
		if (isset($request['jenis']) && $request['jenis'] != "" && $request['jenis'] != "undefined") {
			$data = $data->where('set.jenisdenitkredit',  '=' , $request['jenis']) ;
		}
		if (isset($request['kdpegawai']) && $request['kdpegawai'] != "" && $request['kdpegawai'] != "undefined") {
			$data = $data->where('pg.id', '=', $request['kdpegawai'] );
		}
		if (isset($request['norec']) && $request['norec'] != "" && $request['norec'] != "undefined") {
			$data = $data->where('set.norec', '=', $request['norec']);
		}
		$data = $data->get();

		$result = array(
			'data' => $data,
			'as' => 'ramdanegie'
		);
		return response()->json($result);
	}
	public function hapusSetoran(Request $request)
	{

		DB::beginTransaction();
		try {

			if ($request['norecSetoran'] != null) {
				SetoranDebitKredit_T::where('norec',$request['norecSetoran'])->update([
					'statusenabled'=>'f'
				]);
			}
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Setoran";
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
				'message' => $transMessage,
				'as' => 'ramdanegie',
			);
		}
		return response()->json($result, $result['status']);
	}
    public function saveSetoranDariClosing(Request $request)
    {
        $maxNoTransaksi = $this->getNewCode( 'nosetor', 12, 'S'.date('ym'));
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

            foreach ($request['data']  as $item){
                $log = new SetoranDebitKredit_T();
                $log->norec = $this->generateUid();
                $log->statusenabled = 't';
                $log->nosetor =$maxNoTransaksi;
                $log->pegawaisetorfk = $item['pgid'];
                $log->pegawaipenerimafk = $request['penerimafk'];
                $log->tgl = date('Y-m-d H:i');
                $log->ttldebitkredit = (float) $item['totalclosing'];
                $log->keteranganfk = 1;//penerimaan
                $log->jenisdebitkredit = 'd';
                $log->asalsetorfk = $item['noclosing'];
                $log->save();
                $norec = $log->norec;

                Verifikasi_T::where('norec',$item['norec'])->update(
                  [
                      'setorandebitkreditfk'=> $norec
                  ]
                );
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Setoran";
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