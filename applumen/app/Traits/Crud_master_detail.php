<?php
namespace App\Traits;

use App\Traits\message;
use DB;
use Validator;
use Webpatser\Uuid\Uuid;
use App\Exceptions\ExecuteQueryException;


Trait Crud_Web
{
   use message;

    /**
     * @var
    */
    public function __construct(){
        $this->table = false;
        $this->on = false;
        $this->operand = false;
        $this->to = false;
    }
    //for validation
    protected $useValidation = false;
    protected $ruleCustomMessages = null;
    protected $rules = null;
    protected $list = [];
    //for translate message
    protected $modulPath = 'lib.modul';
    protected $modelName = array();
    protected $KelompokTransaksi = null;

    //untuk nyisipin fungsi
    protected $errorMessage=array();
    protected $extraFunc=array();
    protected function getModelNameSpace($modelName){
        return 'App\web\\' . $modelName;
    }
    protected function getModulLabel(){
        $modul = ($this->modulName == null) ? strtolower($this->getModelName()) : $this->modulName;
        return trans($this->modulPath . "." . $modul);
    }
    /* Fungsi Select Data All Transaksi */
    protected function listData($request, $param){
        $limit = $request->input('limit') ? $request->input('limit') : null;
        $sort_type = 'asc';

        //query
        $listdata = DB::table($param['table_from']);

        //join
        if(count(@$param['table_join'])){
            for($i=0; $i<count($param['table_join']); $i++){
                $this->joinFunc($request, $listdata, $param['table_join'][$i], $param['table_from']);
            }
        }
        //where
        if(count(@$param['where'])){
            $where = [];
             for($i=0; $i<count($param['where']); $i++){
                array_push($where,[$param['where'][$i]['fieldname'], $param['where'][$i]['operand'], $param['where'][$i]['is']]);
            }  
            $listdata->where($where);
        }
        //select
        if(count($param['select'])){
            $listdata->select($param['select']);
        }
        //sort
        if ($request->input('sort') && $request->input('sort') != ""){
            $arraySort = $this->clearEmptyArray(explode(',', $request->input('sort')));
            foreach ($arraySort as $key => $value) {
                $sort = explode(':', $value);
                $sort[0] = trim($sort[0]);
                if(!empty($sort[0])){
                    $sid = (!isset($sort[1]) && empty($sort[1])) ? $sort_type : $sort[1];
                    $listdata = $listdata->orderBy($this->transformer->transformSigleField($sort[0]), $sid);
                }
            }
        }
        //search
        if($request->get('TglAwal') && $request->get('TglAkhir')){
            //var_dump($this->convert_tanggal('insert', $request->get('TglAwal')));
            if($param['table_join'][0]['table'] == 'StrukHistori_T'){
                $date_awal = 'StrukHistori_T.TglAwal';
                $date_akhir= 'StrukHistori_T.TglAkhir';
            }
            if($param['table_join'][0]['table'] == 'StrukPlanning_T'){
                $date_awal = 'StrukPlanning_T.TglSiklusAwal';
                $date_akhir= 'StrukPlanning_T.TglSiklusAkhir';
            }
            $listdata->where([
                                [$date_awal,'>=',$this->convert_tanggal('insert', $request->get('TglAwal'))],
                                [$date_akhir,'<=',$this->convert_tanggal('insert', $request->get('TglAkhir'))]
                            ]);
        }
        if($request->input('search') && $request->input('search') != ""){
            $arraySearch = $this->clearEmptyArray(explode(',', $request->input('search')));
            foreach ($arraySearch as $key => $value) {
                $search = explode(':', $value);
                $search[0] = trim($search[0]);
                if(isset($search[1]) && !empty($search[1]) && !empty($search[0]) && $this->transformer->isListed($search[0])){
                    $search_type = (strrpos($search[1], '%')!==FALSE) ? 'LIKE' : '=';
                    $listdata = $listdata->where($this->transformer->transformSigleField($search[0]), $search_type, $search[1]);
                }
            }
        }
        //limit
        if ($limit) {
            $listdata = $listdata->paginate($limit);
            $page_info = $listdata->toArray();
            $data = array(
                'total' =>$page_info['total'],
                'per_page' => intval($page_info['per_page']),
                'current_page' =>$page_info['current_page'],
                'last_page' =>$page_info['last_page'],
                'from' =>$page_info['from'],
                'to' =>$page_info['to'],
            );
            return $this->respond($data);
        }
        $dt['data'] = $listdata->get();
        $k=0;
        $no = 1; 
        foreach($dt['data'] as $key => $value){
            $value->No = $k + $no;
            foreach($value as $t => $v){
                $convert_date = substr($t,0,3); 
                if(strtolower($convert_date) == 'tgl'){
                    if($v){
                        $nilai = $this->convert_tanggal('select', $v);
                        $dt['data'][$k]->$t = $nilai;
                    }
                }

                if($t == 'StatusEnabled'){
                    //$dt['data'][$k]->$t = $v == 1 ? 'Aktif' : 'Tidak Aktif';
                }
            }  
            $k++;    
        }

                
        $dt['label'] = '';
        if(@$param['label']){
            $dt['label'] = [@$param['label']];
        }

        if (DB::connection()->getDatabaseName())
        {
            if($listdata->count()){
                return message::showResponse($dt['data'], $dt['label']);
            }else{
                return message::notFoundResponse();
            }
        }else{
            return message::connection_timeout();
        }
    }
    protected function joinFunc($request, $query, $param, $table){
        $this->table = $param['table'];
        $this->on = $param['on'];
        $this->operand = $param['operand'];
        $this->to = $param['to'];
        $this->KdProfile = $request->header('KdProfile');


        /*
        $data->leftJoin($table_join, function($join){
            $column_join = $this->param['COLUMN_NAME'];
            $table_join = $this->param['table_join'];

            switch($this->param['COLUMN_NAME']){
                case 'KdPegawaiKepala' :$table_join = 'Pegawai_M'; $column_join = 'KdPegawai'; break;
                case 'KdPegawaiPembuat' :$table_join = 'Pegawai_M'; $column_join = 'KdPegawai'; break;
            }

            $join->on($table_join.'.'.$column_join, '=', $this->param['table'].'.'.$this->param['COLUMN_NAME'])->where($table_join.'.KdProfile', '=', $this->param['KdProfile']);
        });
        */
        switch($param['join']){
            case 'leftJoin' : $sql = $query->leftJoin($this->table, function($join){
                                        $join->on($this->table.'.'.$this->on, $this->operand, $this->to)->where($this->table.'.KdProfile', '=', $this->KdProfile);
                                     }); 
                                     break;
            case 'join' :  $sql = $query->leftJoin($table, function($join){
                                        $join->on($table.'.'.$on, $operand, $to)->where($table.'.'.$on, '=', $request->header('KdProfile'));
                                     }); 
                                     break;
        }
        return $sql;
    }
    /*****************************************************************************************************/
    /* Fungsi Insert Data Transaksi */
    protected function cek_duplicate($class, $request){
        /* Cek Periode Duplikat */
        if(@$class['model']['key_duplicate']){
            $cek_tgl = DB::table($class['model']['table'])
                         ->where([
                                    ['KdProfile', '=', $request->header('KdProfile')],
                                    ['StatusEnabled', '=', 1],
                                 ])
                         ->get();

            foreach($cek_tgl as $key_cek => $dt_cek){
                $tgl_awal = $this->convert_tanggal('select', $dt_cek->TglPlanningAwal);
                $tgl_akhir = $this->convert_tanggal('select', $dt_cek->TglPlanningAwal);
                $tgl_input_awal = $request->input('tgl_awal');
                $tgl_input_akhir = $request->input('tgl_akhir');
                var_dump($tgl_awal);
            }
            die();
        }
    }
    protected function saveCreate($request){
        /* Handle JSON Kosong */
        if(count($request->all())){
            $class = $this->getSourceModel();
            $data = $this->transformValidation($class, $request, 'simpan');
            //$cek = $this->cek_duplicate($class, $request);
            /* Handle Error dalam proses insert */
            if($data['status'] == 401){
                return message::showvalidation($data['message']);
            }else{
                DB::beginTransaction();
                try{
                    for($i=0; $i < count($data['data']); $i++){
                        /* Hilangkan Constrains dalam table relasi */
                        $alter_contrain = DB::query("ALTER TABLE ".$data['data'][$i]['table']." NOCHECK CONSTRAINT ALL;");
                        $key = array_keys($data['data'][$i]['data']);
                        for($t=0; $t<count($key); $t++){
                            $convert_gambar = substr($key[$t],0,10);
                            if(strtolower($convert_gambar) == 'filegambar'){
                                $file = $request->file($key[$t]);
                                $path = storage_path();

                                $fileName = $file->getClientOriginalName();
                                $request->file($key[$t])->move($path."/logs", $fileName);
                                //var_dump($fileName);
                                //DB::enableQueryLog();
                                
                                $gen_image = DB::select("SELECT BulkColumn FROM OPENROWSET(BULK N'".$path.'/logs/'.$fileName."', SINGLE_BLOB) as IMG_DATA");
                                
                                $data['data'][$i]['data'][$key[$t]] = $gen_image[0]->BulkColumn;
                            }
                        }
                        DB::table($data['data'][$i]['table'])->insert($data['data'][$i]['data']);
                    }
                    $this->transStatus = true;
                }catch(\Exception $e){
                    dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Penyimpanan Gagal, Duplikat Foreign Key atau kolom FK kurang !";
                }
                if($this->transStatus){
                    DB::commit();
                    /* Update Id increment pada table */
                    if(@$class['model']['generateCode']){
                        if(@$data['data'][1]){
                            $c=1;
                        }else{
                            $c=0;
                        }
                       
                        $sequence = $this->update_sequenceTbl($data['data'][$c]['table']);
                    }

                    if(@$data['data'][1]['generateCode']){
                        $sequence = $this->update_sequenceTbl($data['data'][1]['table']);
                    }
                    if(@$data['data'][2]['generateCode']){
                        $sequence = $this->update_sequenceTbl($data['data'][2]['table']);
                    }
                    return message::createdResponse($data);
                }else{
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $this->transMessage,
                    );
                    return response()->json($result, 400);
                }
            }
        }else{
            DB::rollBack();
                $result = array(
                    "status" => 400,
                    "message" => 'Json Kosong !',
                );
                return response()->json($result, 400); 
        }
    }
    /*****************************************************************************************************/
    /* Fungsi Update Data Transaksi */
    protected function saveUpdate($request){
        $class = $this->getSourceModel();
        // Step first Get Required Column from table
        if(@$class['model-histori']){
            // Step two check validation table with form 
            //First StrukHistori
            if($class['model-histori']['table'] == 'StrukHistori_T'){
                $data_histori = \DB::table($class['model-histori']['table'])
                                    ->where("NoHistori", $request->input('NoHistori'));
            }else{
                 $data_histori = \DB::table($class['model-histori']['table'])
                                    ->where("NoPlanning", $request->input('NoPlanning'));
            }
            $data_histori_count = $data_histori->count();
        }else{
            $data_histori_count = 1;
        }                  
        if($data_histori_count){
            if(@$class['model-histori']){
                $model_histori = $this->get_validationColumn($class['model-histori']);
                $data_histori = $data_histori->first();
                $dtHistori['KdProfile'] = $data_histori->KdProfile;
                $dtHistori['NoRec'] = $data_histori->NoRec;
                $dtHistori['version'] = $data_histori->version;
                $dtHistori['StatusEnabled'] =  $request->input('StatusEnabled');

                if(@$class['model-histori']['table'] == 'StrukHistori_T'){
                    $dtHistori['NoHistori'] = $data_histori->NoHistori;
                    $dtHistori['KdKelompokTransaksi'] = $data_histori->KdKelompokTransaksi;
                    $dtHistori['TglHistori'] = $data_histori->TglHistori;
                    $dtHistori['KdRuangan'] = $data_histori->KdRuangan;
                    $dtHistori['TglBerlakuAwal'] =$this->convert_tanggal('insert', $request->input('TglAwal'));
                    $dtHistori['TglAwal'] = $this->convert_tanggal('insert', $request->input('TglAwal'));
                    $dtHistori['TglAkhir'] = $this->convert_tanggal('insert', $request->input('TglAkhir'));
                    $dtHistori['NoSk'] = $request->input('NoSK');
                }else if(@$class['model-histori']['table'] == 'StrukPlanning_T'){
                    $dtSK = array();
                    if($request->input('NoSK')){
                        $dtSK = DB::table('SuratKeputusan_T')->where([
                                                                      ['NoSK', '=', $request->input('NoSK')],
                                                                      ['KdProfile', '=', $request->header('KdProfile')]
                                                                     ])->first();
                    }
                    $dtHistori['NoPlanning'] = $data_histori->NoPlanning;
                    $dtHistori['KdRuangan'] = $data_histori->KdRuangan;
                    $dtHistori['KdRuanganAsal'] = $data_histori->KdRuanganAsal;
                    $dtHistori['KdAlamatTujuan'] = $request->input('KdAlamatTempatTujuan');
                    $dtHistori['KdDokumenSK'] = @$dtSK->KdDokumen;
                    $dtHistori['KdJenisTempat'] = $request->input('KdJenisTempat');
                    $dtHistori['NamaPlanning'] = $request->input('NamaPenyuluhan');
                    $cekUrutanRuangan = DB::table('StrukPlanning_T')
                                            ->where([
                                                      ['KdRuangan', '=', $request->header('KdRuangan')],
                                                      ['KdProfile', '=', $request->header('KdProfile')]
                                                     ])
                                            ->orderBy('NoUrutRuangan', 'desc')
                                            ->first();
                    $dtHistori['NoUrutLogin'] = '';
                    $dtHistori['NoUrutRuangan'] = intval($cekUrutanRuangan->NoUrutRuangan) + 1;
                    if(is_numeric($request->input('KdRekanan'))){
                        $dtHistori['KdRekanan'] = $request->input('KdRekanan');
                    }else{
                        $dtHistori['NamaRekanan'] = $request->input('KdRekanan');
                    }
                    $dtHistori['TglPlanning'] = $data_histori->TglPlanning;
                    $dtHistori['TglPengajuan'] = $this->convert_tanggal('insert', $request->input('TglPlanningAwal'));
                    $dtHistori['TglSiklusAwal'] = $this->convert_tanggal('insert', $request->input('TglPlanningAwal'));
                    $dtHistori['TglSiklusAkhir'] = $this->convert_tanggal('insert', $request->input('TglPlanningAkhir'));
                }


                $validatorHistori = \Validator::make($dtHistori, $model_histori);
                $validator = $validatorHistori->fails();
            }else{
                $dtHistori['KdProfile'] = $request->input('KdProfile');
                $dtHistori['NoHistori'] = substr(Uuid::generate(), 0, 10);
                $dtHistori['TglHistori'] = $this->convert_tanggal('insert', date('d/m/Y'));
                $dtHistori['StatusEnabled'] =  $request->input('StatusEnabled');
                $validator = false;
            }
            if ($validator) {
                $error = $validatorHistori->errors();
                $msg = array("status" => 401, "message" => $error);
                return $msg;
            }else{
                //Second Main Model
                $model = $this->get_validationColumn($class['model']);
                $dataModel = $this->compare_column_update($request, $dtHistori, $class['model']['table'], $class['model']['generateCode']);
                //var_dump($dataModel);
                $validatorMain = \Validator::make($dataModel, $model);
                if($validatorMain->fails()) {
                    $error = $validatorMain->errors();
                    $msg = array("status" => 401, "message" => $error);
                    return $msg;
                }else{

                    //Thread Join Model
                    if(@$class['model-histori']){
                        $insert[0]['table']=$class['model-histori']['table'];
                        $insert[0]['data']= $dtHistori;
                        $insert[0]['key']= 'NoHistori';
                        $insert[1]['table']=$class['model']['table'];
                        $insert[1]['data']= $dataModel;
                        $insert[1]['key']= $class['model']['generateCode'];
                    }else{
                        $insert[0]['table']=$class['model']['table'];
                        $insert[0]['data']= $dataModel;
                        $insert[0]['key']= @$class['model']['generateCode'] != '' ? @$class['model']['generateCode'] : 'KdProfile';
                    }

                    if(isset($class['join'])){
                        if(@$class['model-histori']){
                            $x=2;
                        }else{
                            $x=1;
                        }

                        for($i=0; $i<count($class['join']); $i++){
                            $model_join = $this->get_validationColumn($class['join'][$i]);
                            $cls = $class['join'][$i];
                            if(is_numeric(array_keys($request->input('details')[$i])[0])){
                                $dM = $this->compare_column_join_many($i, $request, $dtHistori, 
                                                                          $cls['table'], 
                                                                          $cls['foregnKey'], 
                                                                          $dataModel, 
                                                                          $cls);
                                for($g=0; $g<count($dM); $g++){
                                    $validatorJoin = \Validator::make($dM[$g], $model_join);
                                    if($validatorJoin->fails()) {

                                        $error = $validatorJoin->errors();
                                        $msg = array("status" => 401, "message" => $error);
                                        return $msg;
                                    }else{
                                        $insert[$x]['table']= $cls['table'];
                                        $insert[$x]['data']= $dM[$g];
                                        $insert[$x]['generateCode']= $cls['generateCode'];
                                        $x++;
                                    }
                                }
                            }else{

                                $dM = $this->compare_column_join($i, $request, $dtHistori, $dataModel, $cls);
                                //var_dump($dM);
                                $validatorJoin = \Validator::make($dM, $model_join);
                                if($validatorJoin->fails()) {

                                    $error = $validatorJoin->errors();
                                    $msg = array("status" => 401, "message" => $error);
                                    return $msg;
                                }else{
                                    $insert[$x]['table']= $cls['table'];
                                    $insert[$x]['data']= $dM;
                                    $insert[$x]['generateCode']= $cls['generateCode'];
                                    $x++;
                                }
                            }
                        }
                        $mData = $insert;
                    }else{
                        $mData = $insert;
                    }
                    DB::beginTransaction();
                    DB::enableQueryLog();  
                    try{
                        //var_dump($insert);
                        for($i=0; $i < count($mData); $i++){
                            //var_dump($mData[$i]);
                            $update = DB::table($mData[$i]['table'])
                                        ->where('NoRec', $mData[$i]['data']['NoRec'])
                                        ->update($mData[$i]['data']);
                            //dd(DB::getQueryLog());
                        }
                        $this->transStatus = true;
                    }catch(\Exception $e){
                        dd($e->getmessage());
                        $this->transStatus = false;
                        $this->transMessage = "update Gagal";
                    }
                    if($this->transStatus){
                        DB::commit();
                        return message::createdResponse($mData);
                    }else{
                        DB::rollBack();
                        $result = array(
                            "status" => 400,
                            "message" => $this->transMessage,
                        );
                        return response()->json($result, 400);
                    }
                }
            }
        }else{
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => "Update gagal, No Histori tidak boleh kosong !",
            );
            return response()->json($result, 400);        
        }
    }
    protected function get_where($primary, $data){
        return $data['NoHistori'];
        /*
        if(is_array($primary)){
            $where = [];
            for($i=0; $i<count($primary); $i++){
                array_push($where,[$primary[$i], "=", $data[$primary[$i]]]);
            } 
        }else{
            $where = [];
            array_push($where, [$primary, "=", $data[$primary]]);
        }
        return $where;
        */
    }
    protected function compare_column_update($request, $StrukHistori, $tablename, $index, $dt_master=0){
        $q = $this->get_column_table($tablename);
        $result = [];
        foreach ($q as $key => $value) {
            //var_dump($request->input('details')[0]['NoRec']);
            $convert_date = substr($value->COLUMN_NAME,0,3);
            if(strtolower($convert_date) == 'tgl'){
               $result[$value->COLUMN_NAME] = $this->convert_tanggal('insert', $request->input($value->COLUMN_NAME));
            }else{
                $result[$value->COLUMN_NAME] = $request->input($value->COLUMN_NAME);
            }
            $result['KdProfile'] = $StrukHistori['KdProfile'];
            if($value->COLUMN_NAME == 'NoHistori'){
                $result['NoHistori'] = $StrukHistori['NoHistori'];
            }
            if($tablename == 'ProfileHistoriLaporan_T'){
                $NoRec = $request->input('details')[0]['NoRec'];
            }else{
                $NoRec = $request->input('NoRec');
            }
            $result['NoRec'] = $NoRec;
            //$result['version'] = $StrukHistori['version'];
            $result['StatusEnabled'] = $request->input('StatusEnabled');
            if($index){
                $result[$index] = $request->input($index);
            }
        }

        return $result;
    }
    /*****************************************************************************************************/
    /* Fungsi Delete Data Transaksi */
    protected function saveDelete($id){
        $class = $this->getSourceModel();
        DB::beginTransaction();
        DB::enableQueryLog();  
        try{
            $this->transStatus = false;
            $table = $this->get_table($class);

            for($x=0; $x < count($table); $x++){
               if($table[$x]){
                    $cek = DB::table($table[$x])
                               ->select('StatusEnabled')
                               ->where('NoRec','=',$id)
                               ->first();
                    $status = (int)$cek->StatusEnabled == 1 ? 0 : 1;
                    $update = DB::table($table[$x])
                                ->where('NoRec', $id)
                                ->update(['StatusEnabled' => $status]);
                    if($update){
                        $this->transStatus = true;
                    }
                }
            }
        }catch(\Exception $e){
            //dd($e->getMessage());
            $this->transStatus = false;
            $this->transMessage = "update Gagal";
        }
        if($this->transStatus){
            DB::commit();
            $msg = array("status" => 201, "message" => 'Status berhasil di rubah !');
            return $msg;
        }else{
            DB::rollBack();
            $msg = array("status" => 401, "message" => 'Rubah status gagal !');
            return $msg;
        }  
    }
    protected function get_table($class){
        $table = array($class['model']['table'], @$class['model-histori']['table']);
        if(@$class['join']){
            for($i=0; $i < count($class['join']); $i++){
                array_push($table, $class['join'][$i]['table']);
            }
        }
        return $table;
    }
    /*****************************************************************************************************/
    /* Fungsi Bantuan Transaksi */
    protected function gen_code($kdStrukturNomor, $paramTable){
        $dataM = DB::table('StrukturNomor_M')->where([
                                                        ['KdProfile', '=', 3], 
                                                        ['KdStrukturNomor', '=', $kdStrukturNomor]
                                                    ])->first();
        $dataD = DB::table('StrukturNomorDetail_M')->where([
                                                        ['KdProfile', '=', 3], 
                                                        ['KdStrukturNomor', '=', $kdStrukturNomor]
                                                    ]);
        $last_no = DB::table('SequenceTable_M')->where('namaTabel', $paramTable)->first()->idTerahir;
        $FormatNomor = is_numeric($dataM->FormatNomor) ? true : false;
        if(!$FormatNomor){
            $id = $dataM->FormatNomor.($last_no + 1);
        }else{
            $no = intval($dataM->QtyDigitNomor);
            $id = sprintf("%0".$no."s", $last_no + 1);
            $prefix = '';
            if($dataD->count()){
                foreach($dataD->get() as $key => $value){
                    switch(strtolower($value->FormatKode)){
                        case 'dd' : $prefix .= date('d');break;
                        case 'mm' : $prefix .= date('m');break;
                        case 'yy' : $prefix .= substr(date('Y'), $value->QtyDigitKode);break;
                        case 'yyyy' : $prefix .= substr(date('Y'), $value->QtyDigitKode); break;
                        default : $prefix .= $value->FormatKode; break;
                    }
                }
                $id = $prefix.$id;
            }
        }
        return $id;
    }
    protected function get_column_table($tablename){
        $q = DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', '=', $tablename)->get();
        return $q;
    }
    protected function get_combo_string($request, $comboString){
        $combo = array();
        foreach($comboString as $key => $data){
            if($key != 'KdRuangan'){
                if(is_numeric($request->input($key))){
                    $combo[$key] = $request->input($key);
                    $combo[$data] = '';
                }else{
                    $combo[$key] = '';
                    $combo[$data] = $request->input($key);
                }
            }
        }
        return $combo;
    }
    protected function compare_column($request, $StrukHistori, $class){
        $tablename = $class['model']['table'];
        $index = $class['model']['generateCode']; 
        $dt_master=0;
        $datePK = $class['model']['datePK'];
        $comboString = $class['model']['comboString'];
        $combo_string = array();

        $q = $this->get_column_table($tablename);
        if($dt_master==0 && $index){
            $last_no = DB::table('SequenceTable_M')->where('namaTabel', $tablename)->first()->idTerahir;
            $keyIndex = $last_no + 1;
        }else{
            if(is_array($dt_master)){
                $keyIndex = $dt_master[$index];
            }
        }

        $result = [];

        foreach ($q as $key => $value) {
            $convert_date = substr($value->COLUMN_NAME,0,3);
            $convert_file = substr($value->COLUMN_NAME,0,4);
            $convert_gambar = substr($value->COLUMN_NAME,0,10);

            if(strtolower($convert_date) == 'tgl'){
                if($request->input($value->COLUMN_NAME)){
                    $result[$value->COLUMN_NAME] = $this->convert_tanggal('insert', $request->input($value->COLUMN_NAME));
                }
            }else if(strtolower($convert_gambar) == 'filegambar'){
                $result[$value->COLUMN_NAME] = '';

            }else{
                $result[$value->COLUMN_NAME] = $request->input($value->COLUMN_NAME);
            }

            $result['KdProfile'] = $StrukHistori['KdProfile'];

            if(@$value->COLUMN_NAME == 'NoHistori'){
                $result['NoHistori'] = $StrukHistori['NoHistori'];
            }
            if(@$value->COLUMN_NAME == 'NoPlanning'){
                $result['NoPlanning'] = $StrukHistori['NoPlanning'];
            }
            if(@$value->COLUMN_NAME == 'NoRec'){
                $result['NoRec'] = substr(Uuid::generate(), 0, 32);
            }
            if(@$value->COLUMN_NAME == 'version'){
                $result['version'] = $StrukHistori['version'];
            }
            if(@$value->COLUMN_NAME == 'StatusEnabled'){
                $result['StatusEnabled'] = 1;
            }
            if(@$value->COLUMN_NAME == 'KdDepartemen'){
                $result['KdDepartemen'] = $request->header('KdDepartemen');
            }

            if(@$value->COLUMN_NAME == 'KdRuangan'){
                $result['KdRuangan'] = $request->header('KdRuangan');
            }
            if(@$class['model']['name_on_table']){
                $result['ReportDisplay'] = $request->input($class['model']['name_on_table']);
            }
            
            if($index){
                if(@$index != 'NoPlanning'){
                    $result[$index] = $keyIndex;
                }
            }
        }
        /* Combo String */
        if($comboString){
            $combo_string = $this->get_combo_string($request, $comboString);
            foreach ($combo_string as $k => $d){
                $result[$k] = $combo_string[$k];
            }
        }

        return $result;
    }
    protected function compare_column_join($idx, $request, $StrukHistori, $dt_master=0, $class){
        $tablename = $class['table'];
        $index = $class['foregnKey']; 
        $datePK = $class['datePK'];
        $comboString = $class['comboString'];
        $combo_string = array();
        $q = $this->get_column_table($tablename);
        if($dt_master == 0 && !isset($index)){
            $last_no = DB::table('SequenceTable_M')->where('namaTabel', $tablename)->first()->idTerahir;
            $keyIndex = $last_no + 1;
        }else{
            $keyIndex = $index == "" ? "" : $dt_master[$index];
        }
        $result = [];

        foreach ($q as $key => $value) {
            
            $result['KdProfile'] = $StrukHistori['KdProfile'];
            if ($value->COLUMN_NAME == 'version'){
                $result['version'] = $StrukHistori['version'];
            }
            if ($value->COLUMN_NAME == 'NoHistori'){
                $result['NoHistori'] = $StrukHistori['NoHistori'];
            }
            if(@$value->COLUMN_NAME == 'KdDepartemen'){
                $result['KdDepartemen'] = $request->header('KdDepartemen');
            }
            if(@$value->COLUMN_NAME == 'KdRuangan'){
                $result['KdRuangan'] = $request->header('KdRuangan');
            }
            $result['NoRec'] = substr(Uuid::generate(), 0, 32);
            $result['StatusEnabled'] = 1;

            if($index){
                $result[$index] = $keyIndex;
            }
            if(@$cls['generateCode']){
                $last_no = DB::table('SequenceTable_M')->where('namaTabel', $tablename)->first()->idTerahir + 1;
                $result[@$cls['generateCode']] = $last_no;
            }
            $convert_date = substr($value->COLUMN_NAME,0,3);
            $request->input('details')[$idx][$value->COLUMN_NAME] = $result['KdProfile'];

            if(strtolower($convert_date) == 'tgl'){
                $result[$value->COLUMN_NAME] = $this->convert_tanggal('insert', $request->input('details')[$idx][$value->COLUMN_NAME]);
            }else{
                if($value->COLUMN_NAME != 'KdProfile' &&
                   $value->COLUMN_NAME != 'version' &&
                   $value->COLUMN_NAME != 'NoHistori' &&
                   $value->COLUMN_NAME != 'NoRec' &&
                   $value->COLUMN_NAME != 'StatusEnabled' &&
                   //$value->COLUMN_NAME != 'KodeExternal' &&
                  // $value->COLUMN_NAME != 'NamaExternal' &&
                   // $value->COLUMN_NAME != 'ReportDisplay' &&
                   $value->COLUMN_NAME != $index){
                    $result[$value->COLUMN_NAME] = $request->input('details')[$idx][$value->COLUMN_NAME];
                }
            }
        }
        return $result;
    }
    protected function compare_column_join_many($idx, $request, $StrukHistori, $dt_master=0, $class){
        $tablename = $class['table'];
        $index = $class['generateCode']; 
        $datePK = $class['datePK'];
        $comboString = $class['comboString'];
        $combo_string = array();
        //var_dump($class);
        $q = $this->get_column_table($tablename);
        if($dt_master == 0 && !isset($index)){
            $last_no = DB::table('SequenceTable_M')->where('namaTabel', $tablename)->first()->idTerahir;
            $keyIndex = $last_no + 1;
        }else{
            $keyIndex = $index == "" ? "" : $dt_master[$index];
        }
        $result = [];
        for($x=0; $x<count($request->input('details')[$idx]); $x++){
            foreach ($q as $key => $value) {
                $result[$x]['KdProfile'] = $StrukHistori['KdProfile'];
                if ($value->COLUMN_NAME == 'version'){
                    $result[$x]['version'] = $StrukHistori['version'];
                }
                if ($value->COLUMN_NAME == 'NoHistori'){
                    $result[$x]['NoHistori'] = $StrukHistori['NoHistori'];
                }
                if(@$value->COLUMN_NAME == 'KdDepartemen'){
                    $result['KdDepartemen'] = $request->header('KdDepartemen');
                }
                if(@$value->COLUMN_NAME == 'KdRuangan'){
                    $result['KdRuangan'] = $request->header('KdRuangan');
                }
                $result[$x]['NoRec'] = substr(Uuid::generate(), 0, 32);
                $result[$x]['StatusEnabled'] = 1;

                if($index){
                    $result[$x][$index] = $keyIndex;
                }
                if(@$cls['generateCode']){
                    $last_no = DB::table('SequenceTable_M')->where('namaTabel', $tablename)->first()->idTerahir + 1;
                    $result[$x][@$cls['generateCode']] = $last_no;
                }
                $convert_date = substr($value->COLUMN_NAME,0,3);
                $request->input('details')[$idx][$x][$value->COLUMN_NAME] = $result[$x]['KdProfile'];

                if(strtolower($convert_date) == 'tgl'){
                    $result[$x][$value->COLUMN_NAME] = $this->convert_tanggal('insert', $request->input('details')[$idx][$x][$value->COLUMN_NAME]);
                }else{
                    if($value->COLUMN_NAME != 'KdProfile' &&
                       $value->COLUMN_NAME != 'version' &&
                       $value->COLUMN_NAME != 'NoHistori' &&
                       $value->COLUMN_NAME != 'NoRec' &&
                       $value->COLUMN_NAME != 'StatusEnabled' &&
                       $value->COLUMN_NAME != 'KeteranganLainnya' &&
                       $value->COLUMN_NAME != $index){
                        $result[$x][$value->COLUMN_NAME] = @$request->input('details')[$idx][$x][$value->COLUMN_NAME];
                    }
                }
            }
        }
        return $result;
    }
    protected function get_validationColumn($class){
        $q = $this->get_column_table($class['table']);
        $result = [];
        foreach ($q as $key => $value) {
            if($value->IS_NULLABLE == 'NO'){
                $result[$value->COLUMN_NAME] = 'required';
            }
        }
        return $result;
    }
    protected function get_KdKelompokTransaksi(){
        switch($this->KelompokTransaksi){
            case 'Berita' : $KdKelompokTransaksi = 46; break;
            case 'Promosi' : $KdKelompokTransaksi = 47; break;
            case 'Penyuluhan Kesehatan' : $KdKelompokTransaksi = 47; break;
            case 'Pendidikan & Pelatihan' : $KdKelompokTransaksi = 49; break;
            case 'Visi' : $KdKelompokTransaksi = 50; break;
            case 'Misi' : $KdKelompokTransaksi = 51; break;
            case 'Slogan' : $KdKelompokTransaksi = 52; break;
            case 'Tujuan' : $KdKelompokTransaksi = 53; break;
            case 'Event/Galeri' : $KdKelompokTransaksi = 54; break;
            case 'Prestasi/Awards' : $KdKelompokTransaksi = 55; break;
            case 'Karir' : $KdKelompokTransaksi = 56; break;
            case 'Laporan Rumah Sakit' : $KdKelompokTransaksi = 57; break;
            case 'Survey Kepuasan' : $KdKelompokTransaksi = 58; break;
            case 'Pengaduan' : $KdKelompokTransaksi = 59; break;
            case 'Kritik & Saran' : $KdKelompokTransaksi = 60; break;
            case 'ChatController With Us' : $KdKelompokTransaksi = 61; break;
            default : $KdKelompokTransaksi = 0; break;
        }
        return $KdKelompokTransaksi;
    }
    protected function histori_table_exec($class, $request, $condition){
        $dtHistori['KdProfile'] = $request->header('KdProfile');
        $dtHistori['NoRec'] = substr(Uuid::generate(), 0, 32);
        $dtHistori['version'] = 1; //$request->input('version');
        $dtHistori['KdRuangan'] = $request->header('KdRuangan');
        $dtHistori['StatusEnabled'] = $request->input('StatusEnabled');

        if(@$class['model-histori']['table'] == 'StrukHistori_T'){
            $dtHistori['NoHistori'] = substr(Uuid::generate(), 0, 10);
            $dtHistori['KdRuanganTerima'] = $request->header('KdRuangan');
            $dtHistori['KdKelompokTransaksi'] = $this->get_KdKelompokTransaksi();
            $dtHistori['TglHistori'] = $this->convert_tanggal('insert', date('d/m/Y H:i'));
            $dtHistori['TglBerlakuAwal'] = $this->convert_tanggal('insert', $request->input('TglAwal'));
            $dtHistori['TglAwal'] = $this->convert_tanggal('insert', $request->input('TglAwal'));
            $dtHistori['TglAkhir'] = $this->convert_tanggal('insert', $request->input('TglAkhir'));
            $dtHistori['NoSk'] = $request->input('NoSK');

            $cekUrutanRuangan = DB::table('StrukHistori_T')
                                    ->where([
                                              ['KdRuangan', '=', $request->header('KdRuangan')],
                                              ['KdProfile', '=', $request->header('KdProfile')]
                                             ])
                                    ->orderBy('NoUrutRuangan', 'desc')
                                    ->first();
            $dtHistori['NoUrutLogin'] = '';
            $dtHistori['NoUrutRuangan'] = intval(@$cekUrutanRuangan->NoUrutRuangan) + 1;

        }else if(@$class['model-histori']['table'] == 'StrukPlanning_T'){
            $dtSK = array();
            if($request->input('NoSK')){
                $dtSK = DB::table('SuratKeputusan_T')->where([
                                                              ['NoSK', '=', $request->input('NoSK')],
                                                              ['KdProfile', '=', $request->header('KdProfile')]
                                                             ])->first();
            }
            $dtHistori['NoPlanning'] = $this->gen_code(1, 'PlanningEvents_T');
            $dtHistori['KdRuanganAsal'] = $request->header('KdRuangan');
            $dtHistori['KdKelompokTransaksi'] = $this->get_KdKelompokTransaksi();
            $dtHistori['KdAlamatTujuan'] = $request->input('KdAlamatTempatTujuan');
            $dtHistori['KdDokumenSK'] = @$dtSK->KdDokumen;
            $dtHistori['KdJenisTempat'] = $request->input('KdJenisTempat');
            $dtHistori['NamaPlanning'] = $request->input('NamaPenyuluhan');
            $cekUrutanRuangan = DB::table('StrukPlanning_T')
                                    ->where([
                                              ['KdRuangan', '=', $request->header('KdRuangan')],
                                              ['KdProfile', '=', $request->header('KdProfile')]
                                             ])
                                    ->orderBy('NoUrutRuangan', 'desc')
                                    ->first();
            $dtHistori['NoUrutLogin'] = '';
            $dtHistori['NoUrutRuangan'] = intval(@$cekUrutanRuangan->NoUrutRuangan) + 1;
            if(is_numeric($request->input('KdRekanan'))){
                $dtHistori['KdRekanan'] = $request->input('KdRekanan');
            }else{
                $dtHistori['NamaRekanan'] = $request->input('KdRekanan');
            }

            $dtHistori['TglPlanning'] = $this->convert_tanggal('insert', date('d/m/Y H:i'));
            $dtHistori['TglPengajuan'] = $this->convert_tanggal('insert', $request->input('TglPlanningAwal'));
            $dtHistori['TglSiklusAwal'] = $this->convert_tanggal('insert', $request->input('TglPlanningAwal'));
            $dtHistori['TglSiklusAkhir'] = $this->convert_tanggal('insert', $request->input('TglPlanningAkhir'));
        }
        return $dtHistori;
    }
    protected function transformValidation($class, $request, $condition){
        // Step first Get Required Column from table
        $dtHistori = $this->histori_table_exec($class, $request, $condition);
        if(@$class['model-histori']){
            $model_histori = $this->get_validationColumn($class['model-histori']);
            // Step two check validation table with form 
            // First StrukHistori
            $validatorHistori = \Validator::make($dtHistori, $model_histori);
            $validator = $validatorHistori->fails();
        }else{
            $validator = false;
        }

        if ($validator) {
            $error = $validatorHistori->errors();
            $msg = array("status" => 401, "message" => $error);
            return $msg;
        }else{
            //Second Main Model
            $model = $this->get_validationColumn($class['model']);
            $dataModel = $this->compare_column($request, $dtHistori, $class);

            $validatorMain = \Validator::make($dataModel, $model);

            if($validatorMain->fails()) {

                $error = $validatorMain->errors();
                $msg = array("status" => 401, "message" => $error);
                return $msg;
            }else{
                //Thread Join Model
                if(@$class['model-histori']){
                    $insert[0]['table']=$class['model-histori']['table'];
                    $insert[0]['data']= $dtHistori;
                    $insert[1]['table']=$class['model']['table'];
                    $insert[1]['data']= $dataModel;
                }else{
                    $insert[0]['table']=$class['model']['table'];
                    $insert[0]['data']= $dataModel;
                }

                if(isset($class['join'])){
                    if(@$class['model-histori']){
                        $x=2;
                    }else{
                        $x=1;
                    }
                    for($i=0; $i<count($class['join']); $i++){
                        $model_join = $this->get_validationColumn($class['join'][$i]);
                        $cls = $class['join'][$i];
                        if(is_numeric(array_keys($request->input('details')[$i])[0])){

                            $data_det = $this->compare_column_join_many($i, $request, $dtHistori, 
                                                                      $dataModel, 
                                                                      $cls);
                            //var_dump($data_det);
                            for($g=0; $g<count($data_det); $g++){
                                if($data_det[$g]){
                                    $validatorJoin = \Validator::make($data_det[$g], $model_join);
                                    if($validatorJoin->fails()) {
                                        $error = $validatorJoin->errors();
                                        $msg = array("status" => 401, "message" => $error);
                                        return $msg;
                                    }else{
                                        $insert[$x]['table']= $cls['table'];
                                        $insert[$x]['data']= $data_det[$g];
                                        $insert[$x]['generateCode']= $cls['generateCode'];
                                        $x++;
                                    }
                                }
                            }
                        }else{                            
                            $data_det = $this->compare_column_join($i, $request, $dtHistori, 
                                                                   $dataModel, $cls);

                            $validatorJoin = \Validator::make($data_det, $model_join);
                            if($validatorJoin->fails()) {
                                $error = $validatorJoin->errors();
                                $msg = array("status" => 401, "message" => $error);
                                return $msg;
                            }else{
                                $insert[$x]['table']= $cls['table'];
                                $insert[$x]['data']= $data_det;
                                $insert[$x]['generateCode']= $cls['generateCode'];
                                $x++;
                            }
                        }
                    }
                    $msg = array("status" => 201, "data" => $insert);
                    return $msg;
                }else{
                    $msg = array("status" => 201, "data" => $insert);
                    return $msg;
                }
            }
        }
    }
    protected function getSourceModel(){
        if($this->modelName['modelName']){
            $mdl = $this->getModelNameSpace($this->modelName['modelName']);
            $model['model'] = new $mdl;
        }

        if($this->modelName['modelNameHistori']){
            $mdlHistori = $this->getModelNameSpace($this->modelName['modelNameHistori']);
            $model['model-histori'] = new $mdlHistori;
        }

        if($this->modelName['modelNameJoin'][0]){
            for($i=0; $i<count($this->modelName['modelNameJoin']); $i++){
               $join = $this->getModelNameSpace($this->modelName['modelNameJoin'][$i]);
                $model['join'][$i] = new $join;
            }
        }
        return $model;    
    }
    protected function update_sequenceTbl($table){
        $last_no = DB::table('SequenceTable_M')->where('namaTabel', $table)->first()->idTerahir;
        DB::table('SequenceTable_M')->where('namaTabel', $table)->update(['idTerahir' => $last_no + 1]);    
        return $last_no;
    }
    protected function convert_tanggal($param, $date){
        date_default_timezone_set("Asia/Jakarta");

        if(!is_numeric($date) && $date != ""){
            $t = $param == 'insert' ? explode('/', $date) : $date;
            if(is_array($t)){
                $date = date('Y-m-d H:i', strtotime($t[0].'-'.$t[1].'-'.$t[2]));
            }
            //$date = $date.' '.date('H:i:s');
        }
        
        $hasil = $param == 'insert' ? strtotime($date) : date('d/m/Y H:i', $date);
        return $hasil;
    }
    protected function cek_type_data($tablename, $columnname){
        $q = $this->get_column_table($tablename);
        foreach ($q as $key => $value) {
            //var_dump($value->DATA_TYPE);
            foreach($columnname as $t => $v){
                if($value->COLUMN_NAME.' '.$t){
                    var_dump($value->COLUMN_NAME.' '.$value->DATA_TYPE);
                    //$v
                    //}
                    break;
                }
                //$convert_date = substr($t,0,3); 
                //if(strtolower($convert_date) == 'tgl'){
                    //$nilai = $this->convert_tanggal('select', $v);
                    //$value->$t = $nilai;
                //}
            }      
        }
        //var_dump($columnname);
    }
    /*****************************************************************************************************/
}