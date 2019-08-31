<?php
/**
 * Created by IntelliJ IDEA.
 * User: SitepuMan
 * Date: 3/19/2019
 * Time: 10:32 PM
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
use App\Model\Transaksi\Verifikasi_T;
use App\Traits\GenerateCode;

use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use App\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

class PenerimaanKasirController extends Controller{
    use GenerateCode;
    public function getCombo(Request $request)
    {
        $tipePembayaran = DB::table('pegawai_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('namalengkap')
            ->get();
        $result ['code'] = 200;
        $result ['data'] = array(
            'pegawai' => $tipePembayaran
        );
        $result['as'] = "SitepuMan";

        return response()->json($result);
    }
    public function getPenerimaanKasir1(Request $request)
    {
        $data = DB::table('struk_t as str')
            ->leftJoin('transaksi_t as tr', 'tr.strukfk', '=', 'str.norec')
            ->leftJoin('strukpembayaran_t as sp', 'sp.norec', '=', 'str.strukpembayaranfk')
            ->leftJoin('strukpembayarandetail_t as spd', 'spd.strukpembayaranfk', '=', 'sp.norec')
            ->leftJoin('customer_m as cs', 'cs.id', '=', 'str.customerfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->leftJoin('tipepembayaran_m as tp', 'tp.id', '=', 'sp.tipepembayaranfk')
            ->select('sp.nopembayaran', 'sp.tglpembayaran','cs.id as customerid', 'cs.namacustomer',
                'tp.tipepembayaran', 'pg.namalengkap as namapegawai', 'sp.totalbayar')
            ->where('sp.statusenabled','true')
            ->where('sp.pegawaifk','=', $request['kdPegawai']);

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

        $data=$data->get();
        $result = array(
            'data' => $data,
            'as' => 'SitepuMan'
        );
        return response()->json($result);
    }

    public function getPenerimaanKasir(Request $request)
    {
        $data = DB::table('strukpembayarandetail_t as spd')
            ->leftJoin('strukpembayaran_t as sp', 'sp.norec', '=', 'spd.strukpembayaranfk')
            ->leftJoin('verifikasi_t as vr', 'vr.norec', '=', 'sp.verifikasifk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->select(DB::raw('sum(spd.subtotalbayar) as totalpenerimaan'))
            ->where('sp.statusenabled','true')
            ->where('sp.verifikasifk', '=', null)
            ->where('pg.id','=', $request['kdPegawai']);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglpembayaran', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('sp.tglpembayaran', '<=', $tgl);
        }

        $data=$data->get();

        $data2 = DB::table('strukpembayarandetail_t as spd')
            ->leftJoin('strukpembayaran_t as sp', 'sp.norec', '=', 'spd.strukpembayaranfk')
            ->leftJoin('struk_t as str', 'str.strukpembayaranfk', '=', 'sp.norec')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->leftJoin('customer_m as cs', 'cs.id', '=', 'str.customerfk')
            ->leftJoin('verifikasi_t as vr', 'vr.norec', '=', 'sp.verifikasifk')
            ->leftJoin('tipepembayaran_m as tp', 'tp.id', '=', 'spd.tipepembayaranfk')
            ->select('sp.norec as norecSP', 'cs.id as csid', 'cs.namacustomer',
                'spd.subtotalbayar', 'tp.tipepembayaran', 'sp.tglpembayaran', 'sp.nopembayaran')
            ->where('sp.statusenabled','true')
            ->where('sp.verifikasifk', '=', null)
            ->where('pg.id','=', $request['kdPegawai']);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data2 = $data2->where('sp.tglpembayaran', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data2 = $data2->where('sp.tglpembayaran', '<=', $tgl);
        }

        $data2=$data2->get();

        $result = array(
            'data' => $data,
            'data2' => $data2,
            'as' => 'SitepuMan'
        );
        return response()->json($result);
    }
    public function saveSetoran(Request $request){
        $maxNoTransaksi = $this->getNewCode( 'noverifikasi', 12, 'SR'.date('ym'));
        if ($maxNoTransaksi == ''){
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => 'Gagal mengumpukan data, Coba lagi.',
                "as" => 'SitepuMan',
            );
            return response()->json($result,$result['status']);
        }

        DB::beginTransaction();
        try {
            if ($request['norec'] != null) {
                $SP = new Verifikasi_T();
                $norecV = $this->generateUid();
                $SP->norec = $norecV;
                $SP->statusenabled = true;
                $SP->noverifikasi = $maxNoTransaksi;
                $SP->pegawaifk = $request['pegawaifk'];
                $SP->tglverifikasi = date('Y-m-d H:i:s');
                $SP->save();
                $norecVerif = $SP->norec;
                foreach ($request['detail'] as $item){
                    Struk_T::where('norec',$item['norecStruk'])->update(
                        [
                            'strukpembayaranfk' => $norecVerif
                        ]
                    );
                }
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
                "as" => 'SitepuMan',
            );
        } else {
            $transMessage = "Simpan Pembayaran Gagal";
            DB::rollBack();
            $result = array(
                "message" => $transMessage,
                "status" => 500,
                "as" => 'SitepuMan',
            );
        }
        return response()->json($result,$result['status']);
    }
    public function saveClosing(Request $request){
        $maxNoVerifikasi = $this->getNewCode( 'noclosing', 12, 'CL'.date('ym'));
        if ($maxNoVerifikasi == ''){
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => 'Gagal mengumpukan data, Coba lagi.',
                "as" => 'SitepuMan',
            );
            return response()->json($result,$result['status']);
        }

        DB::beginTransaction();
//        try {
//            if ($request['norecVR'] != null) {
                $VR = new Verifikasi_T();
                $norecVR = $this->generateUid();
                $VR->norec = $norecVR;
                $VR->statusenabled = true;
                $VR->noverifikasi = $maxNoVerifikasi;
                $VR->pegawaifk = $request['pegawaifk'];
                $VR->totalclosing = $request['totalpenerimaan'];
                $VR->jenistransaksifk = 5;
                $VR->tglverifikasi = date('Y-m-d H:i:s');
                $VR->save();
                $norecVerif = $VR->norec;
                foreach ($request['detail'] as $item){
                    StrukPembayaran_T::where('norec',$item['norecSP'])->update(
                        [
                            'verifikasifk' => $norecVerif
                        ]
                    );
                }
//            }

            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Pembayaran";
            DB::commit();
            $result = array(
                "message" => $transMessage,
                "status" => 200,
//                "data" => $VR,
                "as" => 'SitepuMan',
            );
        } else {
            $transMessage = "Simpan Pembayaran Gagal";
            DB::rollBack();
            $result = array(
                "message" => $transMessage,
                "status" => 500,
                "as" => 'SitepuMan',
            );
        }
        return response()->json($result,$result['status']);
    }
}
