<?php
/**
 * Created by IntelliJ IDEA.
 * User: ng-SitepuMan
 * Date: 18/02/2019
 * Time: 20.27
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\ProdukControler;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;

class  ProdukController extends Controller
{
	/*
	 * https://github.com/lcobucci/jwt/blob/3.2/README.md
	 */
	use Core;
	use JsonResponse;


}