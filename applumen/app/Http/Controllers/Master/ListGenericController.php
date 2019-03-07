<?php

namespace App\Http\Controllers\master;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Traits\Crud_master_parent;
use App\Traits\Date_Comparable;
use DB;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Settings;
use App;

/**
 * Description of List Generic
 *
 * @author Egie Ramdan
 */
class ListGenericController extends Controller{

	use Crud_master_parent;

	public function __construct(){
		$model['modelName'] = false;
		$model['modelNameHistori'] = false;
		$model['modelNameJoin'][0] = false;

		$this->modelName= $model;
		$this->param= array();
		$this->extraFunc['SaveCreate'] = 'generateId';
		$this->useValidation = true;
		$KdProfile = 0;
	}
	public function getSettingDataFixed($NamaField, $KdProfile=null){
		$Query = DB::table('SettingDataFixed_M')
			->where('NamaField', '=', $NamaField);
		if($KdProfile){
			$Query->where('KdProfile', '=', $KdProfile);
		}
		$settingDataFixed = $Query->first();
		$hasil = '';
		if(is_object($settingDataFixed)){
			$hasil = $settingDataFixed->NilaiField;
		}
		return $hasil;
	}
	public function combo(Request $request){
		$table = $this->cekTabel($request->get('table'));
		$select = $request->get('select');
		$where = $request->get('where'); //&where=KdKondisi&condition=nilai
		$order_by = $request->get('sortBy');
		$sort_by = $request->get('dir');
		$rows = $request->get('rows');

		if($select != '*'){
			$s = explode(',', $request->get('select'));
			$select = array();
			for($i=0; $i<count($s); $i++){
				array_push($select, $s[$i]);
			}
		}

		$KdProfile = $request->header('KdProfile');
		$kdDepartemen = $request->header('KdDepartemen');
		if($table == 'JenisKelamin_M' || $table == 'TitlePegawai_M' || $table == 'TitlePasien_M'){
			$KdProfile = 3;
			$kdDepartemen = $kdDepartemen;

		}
		$statusEnabled = 1;

		/* Cek Koneksi*/
		if(\Illuminate\Support\Facades\DB::connection()->getPdo()){
			/* Cek Table */
			$q = DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', '=', $table);

			if($q->count() == 0){
				return response()->json(array('code' => 400, 'status' => 'error', 'message' => 'Tabel tidak ada !'), 400);
			}
			/* Cek Kolom */
			if($select != '*'){
				$kolom = 0;
				for($r=0; $r < count($select); $r++){
					foreach($q->get() as $key => $dtKolom){
						if($dtKolom->COLUMN_NAME == $select[$r]){
							$kolom +=1;
						}

					}
				}
				if($kolom < 2){
					return response()->json(array('code' => 400, 'status' => 'error', 'message' => 'Kolom tidak ada !'), 400);
				}
			}
			$data = DB::table($table);
			$data->select($select);
			if($where){
				if($KdProfile){
					$data->where('KdProfile', '=', $KdProfile);
					$data->where('StatusEnabled', '=', 1);
				}
				if($request->get('condition') == 'null'){
					$data->whereNotNull($where);
				}else{
					if(is_numeric($request->get('condition'))){
						$data->where($where,'=',$request->get('condition'));
					}else{
						$config_dataFix = $this->getSettingDataFixed( $request->get('condition'), $KdProfile);
						$data->where($where, $config_dataFix);
					}

				}
			}else{
				if($table == 'TypeDataObjek_S' || $table == 'ModulAplikasi_S' || $table == 'JenisProfile_M' || $table == 'KelompokTransaksi_M'){
					$data->where([
						['StatusEnabled', '=', 1]
					]);
				}else{
					if($KdProfile){
						if($table != 'Student_M' && $table != 'SuratKeputusan_T'){
							$data->where('KdDepartemen', '=', $kdDepartemen);
						}
						$data->where('KdProfile', '=', $KdProfile);
					}
					$data->where('StatusEnabled', '=', 1);
				}
			}
			/* Cek result Data */
			if($data->count() == 0){
				return response()->json(array('code' => 200, 'status' => 'success', 'message' => 'Data Kosong', 'data' => $data->get()), 200);
			}
			if($order_by){
				$data->orderBy($order_by, $sort_by);
			}
			if($rows){
				$data->limit($rows);
			}
			$grid = $data->get();
			foreach($grid as $key => $data){
				foreach($data as $k => $val){
					if(substr($k, 0, 3)=='Tgl' && is_numeric($val)){
						//$q = $this->get_column_table($table);
						//foreach($q as $p => $v){
						//if($k == $v->COLUMN_NAME){
						//if($v->DATA_TYPE == 'int'){
						$data->{$k} = date('Y-m-d H:i:s', $val);
						//}
						//}
						//}

					}
				}
			}
			/* Transformer *

			$grid = $data->get();
			$middle = substr($table, -1);
			switch($middle){
				case 'M' : $middle = 'Master'; break;
				case 'T' : $middle = 'Transaksi'; break;
				case 'S' : $middle = 'System'; break;
				default : $middle = 'Master'; break;
			}
			$class = 'App\\Transformers\\'.$middle.'\\'.$request->get('table').'Transformer';
			$transformer = new $class();
			$grid = $transformer->transformCollection($grid);

			*/

			return response()->json(array('code' => 200,
				'status' => 'success',
				'message' => 'Data Berhasil Ditampilkan',
				'data' => array("totalRow" => count($grid),
					"data" => $grid)
			), 200);
		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}
	public function gridAll(Request $request){
		/* Param */
		$where = $request->get('where');
		$condition = $request->get('condition');

		$KdProfile = $request->header('KdProfile');
		$KdDepartemen = $request->header('KdDepartemen');

		$middle = substr($request->get('table'), -1);
		switch($middle){
			case 'M' : $path = 'App\\master\\'.$request->get('table'); break;
			case 'T' : $path = 'App\\transaksi\\'.$request->get('table'); break;
			case 'S' : $path = 'App\\sequence\\'.$request->get('table'); break;
			default : $path = 'App\\web\\'.$request->get('table').'_M'; break;
		}
		/* Model */
		$model = new $path;
		$data = $model->queryTable($KdProfile, $KdDepartemen);

		/* Paging */
		$page= $request->get('page') != '' ? (int)$request->get('page') : 1;
		$rows= $request->get('rows') != '' ? (int)$request->get('rows') : 10;
		$dir = $request->get('dir');
		$sort = $request->get('sort');

		if($dir){
			$data->orderBy($dir, $sort);
		}
		if($condition){
			$data->where($where, 'like', '%'.urldecode($condition).'%');
		}

		$start = ($page>1) ? $page : 0;
		$total = $data->count();
		$pages = ceil($total/$rows);

		if(@$request->get('page') || @$request->get('rows')){
			$data->offset($start);
			$data->limit($rows);
		}
		$no = $start+1;
		$grid = $data->get();

		// Set Penomoran Row
		$k=0;
		$nomor = 1;
		foreach($grid as $key => $value){
			$value->No = $k + $no;
			$k++;
		}

		/* Cek Koneksi */
		if(DB::connection()->getPdo()){
			$middle = substr($request->get('table'), -1);
			switch($middle){
				case 'M' : $middle = 'Master'; break;
				case 'T' : $middle = 'Transaksi'; break;
				case 'S' : $middle = 'System'; break;
				default : $middle = 'Master'; break;
			}
			$class = 'App\\Transformers\\'.$middle.'\\'.$request->get('table').'Transformer';
			$transformer = new $class();
			$grid = $transformer->transformCollection($grid);


			return response()->json(array('code' => 200,
				'status' => 'success',
				'message' => 'Data Berhasil Ditampilkan',
				'totalRow' => $total,
				'totalPages' => $pages,
				'data' => $grid), 200);
		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}

	public function gridAllAdmin(Request $request){
		$table = $request->get('table');
		$where = $request->get('where'); //&where=KdKondisi&condition=nilai
		$condition = $request->get('condition');

		$KdProfile = $request->header('KdProfile');
		$KdDepartemen = $request->header('KdDepartemen');

		$path = $this->getModelNameSpace($table);
		$model = new $path;
		$data = $model->queryTable($KdProfile, $KdDepartemen);

		if( $request->get( 'KdDetailJenisProduk' ) == '20' ) {
			$data = $data->where( 'Produk_M.KdDetailJenisProduk',20 );
		}
		$page= $request->get('page') != '' ? (int)$request->get('page') : 1;
		$rows= $request->get('rows') != '' ? (int)$request->get('rows') : 10;
		$dir = $request->get('dir');
		$sort = $request->get('sort');

		if($dir){
			$data->orderBy($dir, $sort);
		}
		if($request->get('TglAwal') && $request->get('TglAkhir')){
			$data->where('StrukHistori_T.TglAkhir', '>=', "'".$request->get('TglAwal')."'");
			$data->where('StrukHistori_T.TglAkhir', '<=', "'".$request->get('TglAkhir')."'");
		}
		if($request->get('TglPlanningAwal') && $request->get('TglPlanningAkhir')){
			$data->where($table.'.TglPlanningAkhir', '>=', strtotime($request->get('TglPlanningAwal')));
			$data->where($table.'.TglPlanningAkhir', '<=', strtotime($request->get('TglPlanningAkhir')));
		}
		if($condition){
			$data->where($where, 'like', '%'.urldecode($condition).'%');
		}

		$start = ($page>1) ? $page : 0;
		$total = $data->count();
		$pages = ceil($total/$rows);
		$data->offset($start);
		$data->limit($rows);
		$no = $start+1;

		$grid = $data->get();
		$k=0;
		$nomor = 1;
		foreach($grid as $key => $value){
			// Set Penomoran Row
			$value->No = $k + $no;
			$k++;
		}

		/* Cek Koneksi */
		if(DB::connection()->getPdo()){
			/* Transformer */
			$middle = substr($table, -1);
			switch($middle){
				case 'M' : $middle = 'Master'; break;
				case 'T' : $middle = 'Transaksi'; break;
				case 'S' : $middle = 'System'; break;
				default : $middle = 'Master'; break;
			}
			$class = 'App\\Transformers\\'.$middle.'\\'.$request->get('table').'Transformer';
			$transformer = new $class();
			$grid = $transformer->transformCollection($grid);

			return response()->json(array('code' => 200,
				'status' => 'success',
				'message' => 'Data Berhasil Ditampilkan',
				'totalRow' => $total,
				'totalPages' => $pages,
				'data' => $grid), 200);
		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}
	private function getParam($param){
		$gparam = explode('_',$param);
		$where = [];
		foreach($gparam as $prm){
			$p = explode('-',$prm);
			$condition = [$p[0],'=',$p[1]];
			array_push($where, $condition);
		}
		return $where;
	}
	public function gridExpand(Request $request){
		$table = $request->get('table').'_M';
		$tablecek = $request->get('tablecek').'_M';
		$param = $request->get('param');
		$where = $request->get('where'); //&where=KdKondisi&condition=nilai
		$condition = $request->get('condition');

		$KdProfile = $request->header('KdProfile');
		$KdDepartemen = $request->header('KdDepartemen');

		$path = $this->getModelNameSpace($table);
		$model = new $path;
		$data = $model->queryMap($KdProfile, $KdDepartemen, $condition, $where);
		if( $request->get( 'KdDetailJenisProduk' ) == 23 ) {
			$data = $data->where( 'Produk_M.KdDetailJenisProduk', 23 );
		} else if( $request->get( 'KdDetailJenisProduk' ) == 20 ) {
			$data = $data->where( 'Produk_M.KdDetailJenisProduk', 20 );
		}
		$page= $request->get('page') != '' ? (int)$request->get('page') : 1;
		$rows= $request->get('rows') != '' ? (int)$request->get('rows') : 10;
		$dir = $request->get('dir');
		$sort = $request->get('sort');

		if($dir){
			$data->orderBy($table.'.'.$dir, $sort);
		}

		if($request->get('request') == 'all') {
			$start = ($page>1) ? $page : 0;
			$total = $data->count();
		} else {
			$start = ($page>1) ? $page : 0;
			$total = $data->count();
			$pages = ceil($total/$rows);
			$data->offset($start);
			$data->limit($rows);
		}

		$no = $start+1;

		$grid = $data->get();
		$gparam = $this->getParam($param);
		$k=0;
		$nomor = 1;
		foreach($grid as $key => $value){
			// Set Penomoran Row
			$value->No = $k + $no;
			$k++;
			// Set Checked
			$s='';
			if($tablecek == 'MapJurusanToProduk_M' || $tablecek == 'MapPegawaiToProduk_M'){
				$s='S';
			}
			$kode = 'Kd'.$request->get('table');
			$tCek = DB::table($tablecek);
			$tCek->where($tablecek.'.KdProfile', '=', $KdProfile);
			$tCek->where($gparam);
			$tCek->where([['Kd'.$request->get('table').$s,'=',$value->$kode]]);
			$checked = 0;
			if($tCek->count()){
				if(intval(@$tCek->first()->StatusEnabled) == 0){
					$checked = 0;
				}else{
					$checked = 1;
				}
			}
			$value->Checked = $checked;
			$value->QtyProduk = @$tCek->first()->QtyProduk;
			$value->StatusEnabled = @$tCek->first()->StatusEnabled;
		}

		/* Cek Koneksi */
		if(DB::connection()->getPdo()){
			/* Transformer
			$middle = substr($table, -1);
			switch($middle){
				case 'M' : $middle = 'Master'; break;
				case 'T' : $middle = 'Transaksi'; break;
				case 'S' : $middle = 'System'; break;
				default : $middle = 'Master'; break;
			}
			$class = 'App\\Transformers\\'.$middle.'\\'.$request->get('table').'Transformer';
	        $transformer = new $class();
			$grid = $transformer->transformCollection($grid);
			*/
			return response()->json(array('code' => 200,
				'status' => 'success',
				'message' => 'Data Berhasil Ditampilkan',
				'totalRow' => $total,
				'totalPages' => $page,
				'data' => $grid), 200);
		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}
	public function update(Request $request){
		$table = $request->get('table').'_M';
		$this->KdProfile = $request->header('KdProfile');
		$this->modelName['modelName'] = $table;
		return $this->saveUpdate($request);
	}
	public function insert(Request $request){
		$table = $request->get('table').'_M';
		$this->modelName['modelName'] = $table;
		$this->KdProfile = $request->header('KdProfile');

		if($request->input('details')){
			return $this->saveMapping($request);
		}else{
			return $this->saveParent($request);
		}
	}
	public function delete(Request $request){
		$table = $this->cekTabel($request->get('table'));
		$norec = $request->get('norec');
		$statusEnabled = $request->get('status');
		DB::beginTransaction();
		try{
			$update = DB::table($table)->where('NoRec', $norec)
				->update(['StatusEnabled' => $statusEnabled]);
			$this->transStatus = true;
		}catch(\Exception $e){
			dd($e->getmessage());
			$this->transStatus = false;
			$this->transmessage = "Hapus Data Gagal !";
		}
		if($this->transStatus){
			DB::commit();
			$status = 200;
			$message = 'Hapus Data Berhasil !';
		}else{
			DB::rollBack();
			$status = 400;
			$message = $this->transmessage;
		}
		$result = array(
			"status" => $status,
			"message" => $message,
		);
		return response()->json($result, $status);
	}
	public function upload(Request $request){
		$file = $request->file('image');
		$module = $request->get('module');
		$filename = '';
		$path = storage_path();
		/*
			- Prestasi
			- Galeri
			- Rekanan
			- Pegawai
			- Dokumen
			- Laporan
			- Fasilitas
		*/
		switch($module){
			case 'prestasi' : $path_module = 'prestasi'; break;
			case 'galeri' : $path_module = 'event_galery'; break;
			case 'rekanan' : $path_module = 'rekanan'; break;
			case 'pegawai' : $path_module = 'pegawai'; break;
			case 'dokumen' : $path_module = 'dokumen'; break;
			case 'laporan' : $path_module = 'laporan'; break;
			case 'profile' : $path_module = 'profile'; break;
			case 'fasilitas' : $path_module = 'fasilitas'; break;
			case 'penyuluhan_kesehatan' : $path_module = 'penyuluhan_kesehatan'; break;
		}
		if($request->hasFile('image')){

			$fileName = $file->getClientOriginalName();
			$nama_baru = str_replace(' ', '_', $fileName);
			$request->file('image')->move($path."/images/module/".$path_module."/", $nama_baru);
			$filename = $file->getClientOriginalName();
			$result = array(
				"status" => 201,
				"message" => 'Upload '.$fileName.' Sukses !',
			);
			return response()->json($result, 201);
		}
		$result = array(
			"status" => 400,
			"message" => 'Upload Gagal !',
		);
		return response()->json($result, 400);
	}
	public function downloadFile(Request $request){
		$file= storage_path(). "/files/xls/".$request->get('path');
		$headers = array(
			'Content-Type: csv/excel',
			'Content-Disposition:attachment; filename="'.$request->get('path').'"',
			'Content-Transfer-Encoding:binary',
			'Content-Length:max-age=0',
		);
		return response()->download($file, $request->get('path'), $headers);
	}

	public function export(Request $request, $KdProfile, $KdDepartemen){
		/* Param */
		DB::enableQueryLog();
		$table = $this->cekTabel($request->get('table'));
		$select = $request->get('select');
		$TglAwal = $request->get('TglAwal');
		$TglAkhir = $request->get('TglAkhir');
		$tabledate = $request->get('tabledate');
		$where = $request->get('where'); //&where=KdKondisi&condition=nilai
		$orwhere = $request->get('orwhere');
		$condition = $request->get('condition');
		$param = $request->get('param');

		//$KdProfile = $request->header('KdProfile');
		//$KdDepartemen = $request->header('KdDepartemen');

		/* Model */
		$path = $this->getModelNameSpace($table);
		$model = new $path;
		$data = $model->queryTable($KdProfile, $KdDepartemen);

		/* Paging */
		$dir = $request->get('dir');
		$sort = $request->get('sort');

		if($select != '*'){
			$s = explode(',', $request->get('select'));
			$select = array();
			for($i=0; $i<count($s); $i++){
				array_push($select, $s[$i]);
			}
			$data->select($select);
		}

		if($tabledate){
			if($TglAwal && $TglAkhir) {
				$wheredate = [
					[$tabledate.'.TglAwal', '<=', strtotime($TglAwal)],
					[$tabledate.'.TglAkhir', '>', strtotime($TglAkhir)],
				];
				$data->where($wheredate);
			}
		} else {
			if($TglAwal && $TglAkhir) {
				$wheredate = [
					[$table.'.TglAwal', '<=', strtotime($TglAwal)],
					[$table.'.TglAkhir', '>', strtotime($TglAkhir)],
				];
				$data->where($wheredate);
			}
		}

		if($where){
			$data->where($where, 'like', '%'.urldecode($condition).'%');
		}

		if($orwhere){
			$data->orwhere($orwhere, $condition);
		}

		if($dir){
			$data->orderBy($dir, $sort);
		}

		$grid = $data->get();

		// Set Penomoran Row
		$k=0;
		$nomor = 1;
		foreach($grid as $key => $value){
			//$value->No = $k + $no;
			$k++;
		}
		/* Cek Koneksi */
		if(DB::connection()->getPdo()){
			/* Transformer */
			$middle = substr($table, -1);
			switch($middle){
				case 'M' : $middle = 'Master'; break;
				case 'T' : $middle = 'Transaksi'; break;
				case 'S' : $middle = 'System'; break;
				default : $middle = 'Master'; break;
			}

			$class = 'App\\Transformers\\'.$middle.'\\'.$request->get('table').'Transformer';
			$transformer = new $class();
			if($select == '*'){
				$grid = $transformer->transformCollection($grid);
			}

			return $this->xls_export($request, $grid, $model, $table, $param, $KdProfile, $KdDepartemen);

		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}
	private function xls_export($request, $grid, $config, $table, $param, $KdProfile, $KdDepartemen){
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$rowCount = 1;
		$column = 'B';
		$rowCount = 6;
		/* Head Setup*/
		$path = $this->getModelNameSpace('Profile_M');
		$model = new $path;
		//$profile = $model->queryTable($request->header('KdProfile'), $request->header('KdDepartemen'))->first();

		$profile = $model->queryTable($KdProfile, $KdDepartemen)->first();

		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(12);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->getStartColor()->setARGB('#333');
		$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(10);

		$objPHPExcel->getActiveSheet()->setCellValue('A'. 2, strtoupper($profile->NamaLengkap));
		$objPHPExcel->getActiveSheet()->setCellValue('A'. 3, $profile->AlamatLengkap);
		$objPHPExcel->getActiveSheet()->setCellValue('B'. 5, strtoupper('Data Master '.$config['config']['title']));
		$objPHPExcel->getActiveSheet()->getStyle('B5')->getFont()->setBold(true);

		/* Konten Setup */
		$header_key = array_keys($config['config']['header']);
		$header = $config['config']['header'];
		for($i=0; $i < count($header_key); $i++){
			/** Borders for heading */
			$BStyle = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'FAFAFA')
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':'.$column.$rowCount)->applyFromArray($BStyle);

			$objPHPExcel->getActiveSheet()->setCellValue($column. $rowCount, $header[$header_key[$i]]);
			$rowData = 7;
			foreach ($grid as $key => $value) {
				$objPHPExcel->getActiveSheet()->setCellValue($column. $rowData, $value[$header_key[$i]]);
				$BStyle = array(
					'borders' => array(
						'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
						)
					),
				);
				$objPHPExcel->getActiveSheet()->getStyle($column. $rowData.':'.$column. $rowData)->applyFromArray($BStyle);
				$rowData++;
			}
			$column++;
		}
		foreach(range('B',$column) as $column){
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
		}

		if($param == 'xls'){
			if(file_exists(storage_path(). '/files/xls/'.$table.'.xls')){
				unlink(storage_path(). '/files/xls/'.$table.'.xls');
			}
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save(storage_path(). '/files/xls/'.$table.'.xls');

			$file= storage_path(). '/files/xls/'.$table.'.xls';
			$headers = array(
				'Content-Type: application/pdf',
				'Content-Disposition:attachment; filename="'.$table.'xls"',
				'Content-Transfer-Encoding:binary',
				'Content-Length: max-age=0',
			);

			// return response()->json(array('code' => 200,
			// 							  'status' => 'success',
			// 							  'message' => 'Data Berhasil Diexport',
			// 							  'link_url' => $file), 200);

			return response()->download($file);
		}else{

			// $rendererName = PHPExcel_Settings::PDF_RENDERER_DOMPDF;
			// $rendererLibrary = 'mPDF5.4';
			// $rendererLibraryPath = dirname(__FILE__). 'libs/classes/dompdf/' . $rendererLibrary;
			// $objPHPExcel->getActiveSheet()->setTitle('Orari');
			// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
			// $objWriter->setSheetIndex(0);
			$pdf = App::make('dompdf.wrapper');
			$pdf->loadHTML($this->html_pdf($profile->NamaLengkap,$table, $config, $grid));
			$pdf->setPaper('F4', $config['config']['layout']);
			return $pdf->stream();
		}
	}

	private function html_pdf($name_profile, $name_table, $config, $grid)
	{
		$text = '';
		$text .=  '<h3>'.$name_profile.'</h3>';
		$text .=  '<h3>'.$config['config']['title'].'</h3>';
		$header_key = array_keys($config['config']['header_pdf']);
		$header = $config['config']['header_pdf'];
		$text .= '<table style="font-family:arial,sans-serif;border-collapse:collapse;width:100%;font-size:10px;">';
		$text .= '<thead><tr>';
		$text .= '<td style="border:1px solid #000;font-weight:bolder;padding:8px;">No Urut</td>';
		for($i=0; $i < count($header_key); $i++)
		{
			$text .= '<td style="border:1px solid #000;font-weight:bolder;padding:8px;">';
			$text .= $header[$header_key[$i]];
			$text .= '</td>';
		}
		$text .= '</tr></thead>';

		$text .= '<tbody>';
		foreach ($grid as $key => $value)
		{
			$text .= '<tr>';
			$text .= '<td style="border:1px solid #000;text-align:left;padding:8px;">';
			$text .= $key + 1;
			$text .= '</td>';
			for($i=0; $i < count($header_key); $i++)
			{
				$text .= '<td style="border:1px solid #000;text-align:left;padding:8px;">';
				$text .= $value[$header_key[$i]];
				$text .= '</td>';
			}
			$text .= '</tr>';
		}
		$text .= '</tr>';
		$text .= '</tbody>';
		$text .= '</table>';

		return $text;
	}

	private function pdf_export($request, $grid, $config, $table){
		$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
		$a = new PHPExcel();

		$a->getProperties()->setCreator("Maarten Balliauw")
			->setLastModifiedBy("Maarten Balliauw")
			->setTitle("PDF Test Document")
			->setSubject("PDF Test Document")
			->setDescription("Test document for PDF, generated using PHP classes.")
			->setKeywords("pdf php")
			->setCategory("Test result file");

		$a->setActiveSheetIndex(0)
			->setCellValue('A1', 'Hello')
			->setCellValue('B2', 'world!')
			->setCellValue('C1', 'Hello')
			->setCellValue('D2', 'world!');
		$a->setActiveSheetIndex(0)
			->setCellValue('A4', 'Miscellaneous glyphs')
			->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
		$a->getActiveSheet()->setTitle('Simple');
		$a->getActiveSheet()->setShowGridLines(false);
		$a->setActiveSheetIndex(0);

		$rendererName = PHPExcel_Settings::PDF_RENDERER_DOMPDF;
		//$rendererLibrary = 'tcPDF5.9';
		//$rendererLibrary = 'mPDF5.4';
		$rendererLibrary = 'tcPDF5.9';
		$rendererLibraryPath = '/' . $rendererLibrary;
		$objPHPExcel->getActiveSheet()->setTitle('Orari');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		$objWriter->setSheetIndex(0);
		//$objWriter->save('esp.pdf');
		//$objWriter = PHPExcel_IOFactory::createWriter($a, 'PDF');
		//$objWriter->save('php://output');
		exit;
	}
	public function CekdataDuplicate(Request $request){
		$table =$request->get('table').'_M';
		$path = $this->getModelNameSpace($table);
		$model = new $path;
		$data = $model->queryTable($request->header('KdProfile'), $request->header('KdDepartemen'));
		$filter = explode(',', $request->get('filter'));
		$filter_value = explode(',', $request->get('filter_value'));
		$text_filter = [];
		for ($i=0; $i < count($filter) ; $i++) {
			$array = [$table.'.'.$filter[$i], '=', $filter_value[$i]];
			array_push($text_filter, $array);
		}
		$data->where($text_filter);
		$result = array(
			"status" => 201,
			"jumlah_data" => $data->count(),
		);
		return response()->json($result, 201);
	}
	public function getGlobalMaster(Request $request){
		$table = $this->cekTabel($request->get('table'));
		$select = $request->get('select');
		$group = $request->get('group');
		$where = $request->get('where'); //&where=KdKondisi&condition=nilai
		$order_by = $request->get('sortBy');
		$sort_by = $request->get('dir');
		$table_relasi = $request->get('table_relasi');
		$relasi_select = $request->get('relasi_select');

		if($select != '*'){
			$s = explode(',', $request->get('select'));
			$select = array();
			for($i=0; $i<count($s); $i++){
				array_push($select, $s[$i]);
			}
		}

		if($relasi_select != '*'){
			$ss = explode(',', $request->get('relasi_select'));
			$relasi_select = array();
			for($c=0; $c<count($ss); $c++){
				array_push($relasi_select, $ss[$c]);
			}
		}

		$KdProfile = 3; //($request->header('kdprofile'))?$request->header('kdprofile'):null;
		$kdDepartemen = 1;
		$statusEnabled = 1;

		/* Cek Koneksi*/
		if(DB::connection()->getPdo()){
			/* Cek Table */
			$q = DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', '=', $table);
			if($q->count() == 0){
				return response()->json(array('code' => 400, 'status' => 'error', 'message' => 'Tabel tidak ada !'), 400);
			}
			$data = DB::table($table);
			$data->select($select);
			if($where){
				if($KdProfile){
					$data->where('KdProfile', '=', $KdProfile);
					$data->where('StatusEnabled', '=', 1);
				}
				if($request->get('condition') == 'null'){
					$data->whereNotNull($where);
				}else{
					$data->where($where,'=',$request->get('condition'));
				}
			}else{
				if($table == 'TypeDataObjek_S' || $table == 'ModulAplikasi_S' || $table == 'JenisProfile_M' || $table == 'KelompokTransaksi_M'){
					$data->where([
						['StatusEnabled', '=', 1]
					]);
				}else{
					if($KdProfile){
						$data->where('KdProfile', '=', $KdProfile);
					}
					$data->where('StatusEnabled', '=', 1);
				}
			}
			$data->where('KdDepartemen', '=', $kdDepartemen);
			$data->groupBy($select);
			/* Cek result Data */
			if($data->count() == 0){
				return response()->json(array('code' => 200, 'status' => 'success', 'message' => 'Data Kosong', 'data' => $data->get()), 200);
			}
			if($order_by){
				$data->orderBy($order_by, $sort_by);
			}
			$grid = $data->get();
			foreach($grid as $key => $data){
				foreach($data as $k => $val){
					if(substr($k, 0, 3)=='Tgl' && is_numeric($val)){
						$data->{$k} = date('Y-m-d H:i:s', $val);
					}
				}

				$relasi = DB::table($table_relasi);
				$relasi->select($relasi_select);
				$relasi->where([['KdProfile','=',$KdProfile],
					['KdDepartemen', '=', $kdDepartemen]
				]);

				$setting_data_fix = DB::table('SettingDataFixed_M');
				$setting_data_fix->select('*');
				$setting_data_fix->where([['KdProfile','=',$KdProfile],
					['KdDepartemen', '=', $kdDepartemen],
					['NamaField','=', $data->{$select[1]}]
				]);

				//var_dump($setting_data_fix->first()->NilaiField, $data->{$select[1]});
				$R = explode('-', $setting_data_fix->first()->NilaiField);
				$where = '';
				for($f=0; $f<count($R); $f++){
					if($f==0){
						$where .= ' (';
					}
					if($f > 0){
						$where .= ' OR ';
					}
					$where .= $relasi_select[0].' = '.$R[$f];
					if($f == (count($R)-1)){
						$where .= ')';
					}
				}
				$relasi->whereRaw($where);

				$data_relasi = array();
				foreach($relasi->get() as $key => $dt_relasi){
					$data_relasi[$key][$relasi_select[0]] = $dt_relasi->{$relasi_select[0]};
					$data_relasi[$key][$relasi_select[1]] = $dt_relasi->{$relasi_select[1]};
				}
				$data->relasi = $data_relasi;
			}
			return response()->json(array('code' => 200,
				'status' => 'success',
				'message' => 'Data Berhasil Ditampilkan',
				"totalRow" => count($grid),
				"data" => $grid
			), 200);
		} else {
			return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
		}
	}

	public function convert_umur(Request $request, $tglLahir){
		$hari_ini = date('Y-m-d');
		$date1 = date_create($tglLahir);
		$date2 = date_create($hari_ini);
		$interval = date_diff($date1,$date2);
		//return $interval->format('%y Tahun %m Bulan %d Hari' );
		$data = array(
			'Tahun' => $interval->format('%y'),
			'Bulan' => $interval->format('%m'),
			'Hari' => $interval->format('%d')

		);
		return response()->json(array('code' => 200,
			'status' => 'success',
			'message' => 'Umur nya adalah ',
			'data' => $data
		), 200);
	}
}


/* Fungsi Import Excel
try {
	$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($inputFileName);
  } catch (Exception $e) {
	die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' .
		$e->getmessage());
  }

  $sheet = $objPHPExcel->getSheet(0);
  $highestRow = $sheet->getHighestRow();
  $highestColumn = $sheet->getHighestColumn();

  for ($row = 1; $row <= $highestRow; $row++) {
	$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
									null, true, false);

	//Prints out data in each row.
	//Replace this with whatever you want to do with the data.
	echo '<pre>';
	  print_r($rowData);
	echo '</pre>';
}
*/
