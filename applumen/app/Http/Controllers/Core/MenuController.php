<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 10/02/2019
 * Time: 09.37
 */



namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller{

	function validasi($request) {
		$this->validate($request, []);
	}

	function buildTree($elements, $parentId = "0") {
		$branch = array();
		foreach ($elements as $element) {
			if ($element->parent_id === $parentId) {

				$children = $this->buildTree($elements, $element->id);
				if ($children) {
					$element->items = $children;
				}
				$branch[] = $element;
			}
		}
		return $branch;
	}

	public function getMenu(Request $request){
		$get_data_modul = DB::table('modulaplikasi_s as modul')
//			->leftJoin('MapLoginUserToProfile_S as map', 'map.KdModulAplikasi', '=', 'modul.KdModulAplikasi')
			->where('modul.statusenabled','=',true)
//			->where('modul.id','=',$request->input('kdmodulaplikasi'))
//			->where('modul.kdprofile','=',$request->input('KdProfile'))
//			->where('map.KdRuangan','=',$request->input('KdRuangan'))
//			->where('map.KdUser','=',$request->input('KdUser'))
			->select('modul.id as kdmodulaplikasi','modul.kdmodulaplikasihead','modul.modulaplikasi')
			->orderBy('modul.kdmodulaplikasihead','ASC')->get();
//
		$kelompokUserFk = $request->input('kelompokuserid');
		$get_data_menu = DB::select(DB::raw("select objek.id as kdobjekmodulaplikasi, 
			 objek.kdobjekmodulaplikasihead, 
			 mods.modulaplikasi,
			 main.objekmodulaplikasiid as kdmodulaplikasi, 
				 objek.objekmodulaplikasi, 
				 objek.alamaturlform, 
				 groups.simpan, 
				 groups.hapus, 
				 groups.edit, 
				 groups.cetak
				 from mapobjekmodulaplikasitomodulaplikasi_s as main
				 inner join objekmodulaplikasi_s as objek on main.objekmodulaplikasiid = objek.id and objek.statusenabled = true
				 inner join mapobjekmodultokelompokuser_s as groups on main.objekmodulaplikasiid = groups.objectobjekmodulaplikasifk 
				  inner join modulaplikasi_s as mods on main.modulaplikasiid = mods.id 
				 where main.statusenabled = TRUE 
				-- and main.kdmodulaplikasi is null 
			--and groups.objectkelompokuserfk =  $kelompokUserFk
				-- and main.kdprofile is null
				 order by objek.nourut asc;"));
//		return $get_data_menu;
		//var_dump($get_data_modul);
		//var_dump($get_data_menu);
		$data_menu = array();
		$data_module = array();
		foreach ($get_data_modul as $key => $value){
			$obj = new \stdClass();
			$obj->id = $value->kdmodulaplikasi;
			$obj->parent_id = ($value->kdmodulaplikasihead === NULL)?"0":$value->kdmodulaplikasihead;
			$obj->label = $value->modulaplikasi;
			$obj->icon = 'fa fa-fw fa-sign-in';
			// $obj->routerLink = [''];
			$obj->badge = '';
			$obj->badgeStyleClass = 'orange-badge';
			array_push($data_module,$obj );
		}


		foreach ($get_data_menu as $key => $value){
			$obj = new \stdClass();
			$obj->id = $value->kdobjekmodulaplikasi.'_child';
			$obj->parent_id = ($value->kdobjekmodulaplikasihead < 1)? $value->kdmodulaplikasi : $value->kdobjekmodulaplikasihead.'_child' ;
			$obj->label = $value->modulaplikasi;
			$obj->icon = 'fa fa-fw fa-sign-in';
			$obj->routerLink = ($value->alamaturlform)?['./'.$value->alamaturlform]:['./'];
			$obj->badge = '';
			$obj->badgeStyleClass = 'orange-badge';
			array_push($data_menu,$obj );
		}
		$data = array_merge($data_module,$data_menu);
//		return $data;
		$results = $this->buildTree($data);

		return response()->json($results);
	}

	public function profile(Request $request, $KdProfile){
		$results = DB::table('Profile_M')
			->where('KdProfile', '=', $KdProfile)
			->where('StatusEnabled', '=', 1)
			->first();

		return response()->json($results);

	}
}