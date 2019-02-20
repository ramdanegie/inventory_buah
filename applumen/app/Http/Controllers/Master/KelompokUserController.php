<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 16/02/2019
 * Time: 15.44
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Standar\KelompokUser_S;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  KelompokUserController extends Controller
{
	/*
	 * https://github.com/lcobucci/jwt/blob/3.2/README.md
	 */
	use Core;
	use JsonResponse;

	public function getAll (Request $request)
	{
		// ini_set('memory_limit', '128M');
		$kelUser = KelompokUser_S::where('statusenabled',true)
			->select('id','kelompokuser');
		$kelUser = $kelUser->get();
		// $kelUser = DB::table('produk_m')
		// 	->select('*')
		// 	->get();
		if ($kelUser->count() > 0) {
			$result['code'] = 200;
			$result['data'] = $kelUser;
			$result['as'] = "ramdanegie";
		} else {
			$result['code'] = 500;
			$result['status'] = false;
			$result['as'] = "ramdanegie";
		}
		return response()->json($result);
	}
}