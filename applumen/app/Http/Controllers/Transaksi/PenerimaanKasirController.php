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

        $result['data'] = array(
            'datapegawai' => $tipePembayaran
        );
        $result['as'] = "SitepuMan";

        return response()->json($result);
    }
    public function getPenerimaanKasir(Request $request)
    {
        $data = DB::table('struk_t as str')
            ->leftJoin('transaksi_t as tr', 'tr.strukfk', '=', 'str.norec')
            ->leftJoin('strukpembayaran_t as sp', 'sp.norec', '=', 'str.strukpembayaranfk')
            ->leftJoin('strukpembayarandetail_t as spd', 'spd.strukpembayaranfk', '=', 'sp.norec')
            ->leftJoin('customer_m as cs', 'cs.id', '=', 'tr.custmerfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sp.pegawaifk')
            ->leftJoin('tipepembayaran_m as tp', 'tp.id', '=', 'sp.tipepembayaranfk')
            ->select('sp.nopembayaran', 'sp.tglpembayaran','cs.id as cuntomerid', 'cs.namacustomer',
                'tp.tipepembayaran', 'pg.namalengkap as namapegawai', 'sp.totalbayar')
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
        $result = array(
            'data' => $data,
            'as' => 'SitepuMan'
        );
        return response()->json($result);
    }
}
