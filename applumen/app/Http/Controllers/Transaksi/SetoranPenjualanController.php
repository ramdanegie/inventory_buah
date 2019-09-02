<?php
/**
 * Created by IntelliJ IDEA.
 * User: SitepuMan
 * Date: 5/14/2019
 * Time: 4:43 PM
 */
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Model\Master\JenisTransaksi_M;
use App\Model\Standar\LoginUser_S;
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

class SetoranPenjualanController extends Controller{
    use GenerateCode;
    public function getDataClosing(Request $request)
    {
        $data = DB::table('verifikasi_t as vr')
            ->select('vr.norec', 'vr.tglverifikasi as tglclosing', 'vr.noverifikasi as noclosing',
                'pg.id as pgid', 'pg.namalengkap', 'vr.totalclosing','set.nosetor')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'vr.pegawaifk')
            ->leftJoin('setorandebitkredit_t as set', 'set.norec', '=', 'vr.setorandebitkreditfk')
            ->where('vr.statusenabled', true)
//            ->wherenull('vr.setorandebitkreditfk')
            ->where('vr.jenistransaksifk', '=', 5)
            ->orderBy('vr.tglverifikasi');
            if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
                $data = $data->where('vr.tglverifikasi', '>=', $request['tglAwal']);
            }
            if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('vr.tglverifikasi', '<=', $tgl);
            }
            if (isset($request['noclosing']) && $request['noclosing'] != "" && $request['noclosing'] != "undefined") {
                $data = $data->where('vr.noverifikasi', 'ilike', $request['noclosing']);
            }
            $data=$data->get();

        $result = array(
            'data' => $data,
            'as' => 'SitepuMan'
        );
        return response()->json($result);
    }
}