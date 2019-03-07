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

class  DashboardController extends Controller
{

	public function countData(Request $request)
	{
		$now = date('Y-m-d');
		$yesterday = Carbon::now()->subDay(1)->format('Y-m-d');

		$penjualan = DB::select(DB::raw("SELECT COALESCE(sum((tt.hargajual*tt.qty)-tt.hargadiskon),0) as jumlah from struk_t as s
			join transaksi_t as tt on tt.strukfk =s.norec
			where  to_char(s.tgltransaksi,'YYYY-MM-DD')='$now'
				and s.statusenabled =true	
			"));
		$penjualanKemarin = DB::select(DB::raw("SELECT COALESCE(sum((tt.hargajual*tt.qty)-tt.hargadiskon),0) as jumlah from struk_t as s
			join transaksi_t as tt on tt.strukfk =s.norec
			where to_char(s.tgltransaksi,'YYYY-MM-DD')='$yesterday'
				and s.statusenabled =true
			"));
		$todayPenjualan = 0;
		$yestPenjualan = 0;

		if(count($penjualan) > 0 ){
			$todayPenjualan = $penjualan[0]->jumlah;
		}
		if(count($penjualanKemarin) > 0 ){
			$yestPenjualan = $penjualanKemarin[0]->jumlah;
		}
//	return $yestPenjualan;
		if($todayPenjualan == 0 && $yestPenjualan == 0){
			$persenPenjualan = 0;
		}else if($todayPenjualan == 0 && $yestPenjualan != 0) {
			$todayPenjualan = 1;
			if($todayPenjualan > $yestPenjualan){
				$persenPenjualan = (($todayPenjualan - $yestPenjualan) / $todayPenjualan )* 100;
			}else{
				$persenPenjualan = ($todayPenjualan / $yestPenjualan )* 100;
			}
		}else{
			if($todayPenjualan > $yestPenjualan){
				$persenPenjualan = (($todayPenjualan - $yestPenjualan) / $todayPenjualan )* 100;
			}else{
				$persenPenjualan = ($todayPenjualan / $yestPenjualan )* 100;
			}
		}

		$persenPenjualan = number_format($persenPenjualan,0,',','.');

		$penerimaan = DB::select(DB::raw("SELECT COALESCE(sum(tt.hargapenerimaan*tt.qtypenerimaan),0) as jumlah 
			from strukpenerimaan_t as s
			join stokproduk_t as tt on tt.strukpenerimaanfk =s.norec
			where to_char(s.tgltransaksi,'YYYY-MM-DD')='$now'
				and s.statusenabled =true
			"));
		$penerimaanKemarin = DB::select(DB::raw("SELECT COALESCE(sum(tt.hargapenerimaan*tt.qtypenerimaan),0) as jumlah 
			from strukpenerimaan_t as s
			join stokproduk_t as tt on tt.strukpenerimaanfk =s.norec
			where to_char(s.tgltransaksi,'YYYY-MM-DD')='$yesterday'
			and s.statusenabled =true
			"));

		$todayTerima = 0;
		$yestTerima = 0;

		if(count($penerimaan) > 0 ){
			$todayTerima = $penerimaan[0]->jumlah;
		}
		if(count($penerimaanKemarin) > 0 ){
			$yestTerima = $penerimaanKemarin[0]->jumlah;
		}

		if($todayTerima == 0 && $yestTerima == 0){
			$persenTerima = 0;
		}else if($todayTerima == 0 && $yestTerima != 0) {
			$todayTerima = 1;
			if($todayTerima > $yestTerima){
				$persenTerima = (($todayTerima - $yestTerima) / $todayTerima )* 100;
			}else{
				$persenTerima = ($todayTerima / $yestTerima )* 100;
			}

		}else{
			if($todayTerima > $yestTerima){
				$persenTerima = (($todayTerima - $yestTerima) / $todayTerima )* 100;
			}else{
				$persenTerima = ($todayTerima / $yestTerima )* 100;
			}
		}
		$persenTerima = number_format($persenTerima,0,',','.');

		$pegawai = DB::select(DB::raw("select count(id) as jumlah from pegawai_m
			"));
		$uses = DB::select(DB::raw("select count(id) as jumlah from loginuser_s
			"));
		$result= array(
			'code'=> 200,
			'penjualan' => $todayPenjualan,
			'penjualanKemaren' => $yestPenjualan,
			'persenPenjualan' => $persenPenjualan,
			'penerimaan' => $todayTerima,
			'persenPenerimaan' => $persenTerima,
			'pegawai' => $pegawai[0]->jumlah,
			'uses' => $uses[0]->jumlah,
			'message' => 'ramdanegoe',
		);
		return response()->json($result);
	}
}