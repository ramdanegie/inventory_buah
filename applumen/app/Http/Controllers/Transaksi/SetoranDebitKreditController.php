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
        $result = array(
            'status' => 200,
            'data' => $datapegawai,
            'as' => "{ng-SitepuMan}"
        );
        return response()->json($result);
    }
}