<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 18/02/2019
 * Time: 20.27
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\Pegawai;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  PegawaiController extends Controller
{
	/*
	 * https://github.com/lcobucci/jwt/blob/3.2/README.md
	 */
	use Core;
	use JsonResponse;

	public function getPegawaiByNama ($nama)
	{
		$pegawai = Pegawai::where('statusenabled',true)
			->where('namalengkap','ilike','%'.$nama.'%')
			->select('id','namalengkap')
			->orderBy('namalengkap');

		$pegawai = $pegawai->get();
		if ($pegawai->count() > 0) {
			$result['code'] = 200;
			$result['data'] = $pegawai;
			$result['as'] = "ramdanegie";
		} else {
			$result['code'] = 200;
			$result['data'] = [];
			$result['as'] = "ramdanegie";
		}
		return response()->json($result);
	}
}