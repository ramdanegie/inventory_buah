<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 05/03/2019
 * Time: 19.15
 */
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Traits\GenerateCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  LaporanController extends Controller
{
    public function getDaftarDetailTerimaBarang (Request $request)
    {
        $data = \DB::table('stokproduk_t as spr')
            ->leftJoin('strukpenerimaan_t as spn','spn.norec','=','spr.strukpenerimaanfk')
            ->LEFTJOIN ('produk_m as pr','pr.id','=','spr.produkfk')
            ->LEFTJOIN ('toko_m as tk','tk.id','=','spn.tokofk')
            ->LEFTJOIN ('pegawai_m as pg','pg.id','=','spn.pegawaifk')
            ->LEFTJOIN ('satuanstandard_m as ss','ss.id','=','spr.satuanterimafk')
            ->LEFTJOIN ('supplier_m as su','su.id','=','spn.supplierfk')
            ->select('spn.nopenerimaan', 'spn.nofaktur', 'pr.id', 'pr.namaproduk', 'spn.tgltransaksi as tglpenerimaan', 'pg.namalengkap',
            'tk.namatoko', 'spr.qtypenerimaan', 'ss.satuanstandard','spr.hargapenerimaan','spr.hargajual') ;

            if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
                $data = $data->where('spn.tgltransaksi', '>=', $request['tglAwal']);
            }
            if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
                $tgl = $request['tglAkhir'];
                $data = $data->where('spn.tgltransaksi', '<=', $tgl);
            }
            if(isset($request['noPenerimaan']) && $request['noPenerimaan'] != '' && $request['noPenerimaan'] != 'undefined' && $request['noPenerimaan']!='null')
            {
                $data = $data->where('spn.nopenerimaan','ilike','%'.$request['noPenerimaan'].'%');
            }
            if(isset($request['noFaktur']) && $request['noFaktur'] != '' && $request['noFaktur'] != 'undefined' && $request['noFaktur']!='null')
            {
                $data = $data->where('spn.nofaktur','ilike','%'.$request['noFaktur'].'%');
            }
            if(isset($request['kdProduk']) && $request['kdProduk'] != '' && $request['kdProduk'] != 'undefined' && $request['kdProduk']!='null')
            {
                $data = $data->where('pr.id','=',$request['kdProduk']);
            }
            if(isset($request['namaProduk']) && $request['namaProduk'] != '' && $request['namaProduk'] != 'undefined' && $request['namaProduk']!='null')
            {
                $data = $data->where('pr.namaproduk','ilike','%'.$request['namaProduk'].'%');
            }
            $data = $data->get();
            $result = array(
                'data' => $data,
                'as' => 'ramdanegie'
            );
            return response()->json($result);
    }

}