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
            ->leftJoin('tipepembayaran_m as tp', 'tp.id', '=', 'spd.tipepembayaranfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->select('tp.tipepembayaran',
                        DB::raw('sum(spd.subtotalbayar) as totalpenerimaan'))
            ->where('sp.statusenabled','true')
            ->where('pg.id','=', $request['kdPegawai'])
            ->groupBy('tp.tipepembayaran');

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
            ->leftJoin('transaksi_t as tr', 'tr.strukfk', '=', 'str.norec')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->leftJoin('customer_m as cs', 'cs.id', '=', 'str.customerfk')
            ->leftJoin('tipepembayaran_m as tp', 'tp.id', '=', 'spd.tipepembayaranfk')
            ->select('cs.id as csid', 'cs.namacustomer', 'tr.notransaksi', 'spd.subtotalbayar')
            ->where('sp.statusenabled','true')
            ->where('pg.id','=', $request['kdPegawai'])
            ->where('tp.id','=', $request['tpId']);

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
}
