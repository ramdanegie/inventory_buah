<?php
namespace App\Traits;

use App\Traits\message;
use App\Traits\Core;
use DB;
use Validator;
use Webpatser\Uuid\Uuid;
use App\Exceptions\ExecuteQueryException;
use App\Transformers\Master;


Trait Crud_master_parent
{
   use message;
   use Core;
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
    protected $KdProfile = 0;
    protected $KelompokTransaksi = null;

    //untuk nyisipin fungsi
    protected $errorMessage=array();
    protected $extraFunc=array();

    protected function getModelNameSpace($modelName){
        return 'App\web\\' . $modelName;
    }
    protected function getModelMasterNameSpace($modelName){
        return 'App\master\\' . $modelName;
    }
    protected function getTransformerNameSpace($modelName){
        return 'App\Transformers\Master\\'.$modelName.'Transformer';
    }
    protected function getModulLabel(){
        $modul = ($this->modulName == null) ? strtolower($this->getModelName()) : $this->modulName;
        return trans($this->modulPath . "." . $modul);
    }
    /* Fungsi CRUD */
    protected function insert_relation($request, $m){
        $lastNomor=0;

        for($a=0; $a<count($request->input($m['json-key']));$a++){
            $lastNo = \DB::table('SequenceTable_M')
                                        ->where([
                                                ['namaTable', '=', $m['table']], 
                                                ['KdProfile', '=', $request->header('KdProfile')]
                                            ])
                                        ->first()->idTerakhir + 1;

            $mdl = $this->getModelNameSpace($m['table']);
            $relasi = new $mdl;
            $data = array_keys($m['field']);
            for($i=0; $i<count($data); $i++){
                $key = $data[$i];
                if(@$request->input($m['json-key'])[$a][$key]){
                    $m['field'][$data[$i]] = $request->input($m['json-key'])[$a][$key];
                }
                if(@$request->input($m['json-key'])[$a][$m['PK']]){
                    $m['field'][$data[$i]] = $key == $m['PK'] ? $request->input($m['json-key'])[$a][$m['PK']] : $m['field'][$data[$i]];
                }else{
                    $m['field'][$data[$i]] = $key == $m['PK'] ? $lastNo : $m['field'][$data[$i]];
                }
                $m['field'][$data[$i]] = $key == 'NoRec' ? substr(Uuid::generate(), 0, 32) : $m['field'][$data[$i]];
                $m['field'][$data[$i]] = $key == 'KdProfile' ? $request->header('KdProfile') : $m['field'][$data[$i]];
                $m['field'][$data[$i]] = $key == 'KdDepartemen' ? $request->header('KdDepartemen') : $m['field'][$data[$i]];
                $m['field'][$data[$i]] = $key == 'KdRuangan' ? $request->header('KdRuangan') : $m['field'][$data[$i]];
                if(substr($key, 0,3) == 'Tgl'){
                    $m['field'][$data[$i]] = strtotime($request->input($m['json-key'])[$a][$key]);
                }
                if(count($request->input($m['json-key'])) >= 1 && $m['hirarki'] == true){
                    $relasi->{$m['hirarkiKey']} = $lastNomor;
                }
                $relasi->{$data[$i]} = $m['field'][$data[$i]];
            }
            \DB::query("ALTER TABLE ".$m['table']." NOCHECK CONSTRAINT ALL;");
            if(@$request->input($m['json-key'])[$a][$m['PK']]){
                \DB::table($m['table'])
                           ->where($m['PK'], $relasi->{$m['PK']})
                           ->update($relasi['attributes']);
            }else{
                $relasi->save();
                $this->update_sequenceTbl($m['table'], $request->header('KdProfile'));
            }
            
            if($a==0){
                
                if(@$request->input($m['json-key'])[$a][$m['PK']]){
                     $lastNomor = $request->input($m['json-key'])[$a][$m['PK']];
                }else{
                    $lastNomor = $lastNo;
                }

            }
        }
        return  $lastNomor;
    }
    protected function saveParent($request){
        /* Handle JSON Kosong */
        if(count($request->all())){
            $mdl = $this->getModelNameSpace($this->modelName['modelName']);

            $class['model'] = new $mdl;
            /* Transformer */
            $cl = $this->getTransformerNameSpace($request->get('table'));
            $transformer = new $cl;
        
            $request = $transformer->transformToReq($request, $class['model']);
            $data = $this->transformValidation($class, $request, 'simpan');

            /* Handle Error dalam proses insert */
            if($data['status'] == 401){
                return message::showvalidation($data['message']);
            }else if($data['status'] == 400){
                return message::PK_error($data['message']);
            }else{
                DB::beginTransaction();
                try{
                    $alter_contrain = DB::query("ALTER TABLE ".$class['model']['table']." NOCHECK CONSTRAINT ALL;");
                    if($class['model']['generateRelation']){
                        for($b=0; $b < count($class['model']['generateRelation']); $b++){
                           $m = $class['model']['generateRelation'][$b];
                           $data->{$m['FK']} = $this->insert_relation($request, $m);
                        }
                    }
                    $data->save();
                    $this->transStatus = true;
                }catch(\Exception $e){
                    dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Penyimpanan Gagal, Error insert data (Duplikat/Kolom tidak lengkap/Kolom salah) !";
                }
                if($this->transStatus){
                    /* Update Id increment pada table */
                    DB::commit();
                    $this->insert_log($request, $data->NoRec, $data['attributes']);
                    if($class['model']['generateCode']){
                        $update = $this->update_sequenceTbl($class['model']['table'], $request->header('KdProfile'));
                    }
                    return message::createdResponse($data);
                }else{
                    DB::rollBack();
                    $result = array("status" => 400, "message" => $this->transMessage);
                    return response()->json($result, 400);
                }
            }
        }else{
            DB::rollBack();
            $result = array("status" => 400,"message" => 'Json Kosong !');
            return response()->json($result, 400); 
        }
    }
    protected function insert_log($request, $norec, $data){
       Core::HistoryUser($request, $norec, $data);
    }
    protected function saveMapping($request){
        /* Handle JSON Kosong */
        if(count($request->all())){
            $mdl = $this->getModelNameSpace($this->modelName['modelName']);
            $class['model'] = new $mdl;
            $data = $this->transformValidationMapJoin($class, $request, 'simpan');
            /* Handle Error dalam proses insert */
            if($data['status'] == 401){
                return message::showvalidation($data['message']);
            }else if($data['status'] == 400){
                return message::PK_error($data['message']);
            }else{
                DB::beginTransaction();
                try{  
                    // Simpan/Update data baru 
                    for($i=0; $i < count($data['data']); $i++){
                        $cek = DB::table($class['model']['table']);
                        $where = [];
                        foreach($class['model']['primaryKey'] as $key => $value){
                            $cek->where($value, '=', $data['data'][$i]['data'][$value]);
                            array_push($where, [$value, '=', $data['data'][$i]['data'][$value]]);
                        }
                        if($cek->count()){
                            $update = DB::table($data['data'][$i]['table'])
                                        ->where($where)
                                        ->update($data['data'][$i]['data']);
                        }else{
                            DB::table($data['data'][$i]['table'])->insert($data['data'][$i]['data']);
                        }
                    }
                    $this->transStatus = true;
                }catch(\Exception $e){
                    //dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Penyimpanan Gagal, Error insert data (Duplikat/Kolom tidak lengkap) !";
                }
                if($this->transStatus){
                    DB::commit();
                    /* Update Id increment pada table */
                    // Non Aktifkan data yang unchecked
                    $cek = DB::table($class['model']['table']);

                    $where = [];
                    for($i=0; $i < count($data['data']); $i++){
                        foreach($class['model']['parentKey'] as $key => $value){
                            $cek->where($value, '=', $data['data'][$i]['data'][$value]);
                            array_push($where, [$value, '=', $data['data'][$i]['data'][$value]]);
                        }
                        break;
                    }
                    $keyChild = $class['model']['keyChild'];
                    $count = 0;
                    $nilai = [];
                    foreach($cek->get() as $key => $value){
                        for($i=0; $i < count($data['data']); $i++){
                            if($value->$keyChild == $data['data'][$i]['data'][$keyChild]){
                                $nilai[$count] =array(0 =>$keyChild, 1 =>'=', 2 => $value->$keyChild);
                                $count++;
                            }
                        }
                    }
                    $update = DB::table($class['model']['table'])->where($where)->update(array('StatusEnabled' => 0));
                    $c=count($where);
                    for($akhir=0; $akhir < count($nilai); $akhir++){
                        $where[$c] = array(0 =>$nilai[$akhir][0], 1 =>$nilai[$akhir][1], 2 => $nilai[$akhir][2]);
                        $update = DB::table($class['model']['table'])
                                            ->where($where)
                                            ->update(array('StatusEnabled' => 1));
                    }
                    $log = $this->insert_log($request, $data['data'][0]['data']['NoRec'], $data['data'][0]['data']);
                    return message::createdResponse($data);
                }else{
                    DB::rollBack();
                    $result = array("status" => 400, "message" => $this->transMessage);
                    return response()->json($result, 400);
                }
            }
        }else{
            DB::rollBack();
            $result = array("status" => 400,"message" => 'Json Kosong !');
            return response()->json($result, 400); 
        }
    }
    protected function saveUpdate($request){
        /* Handle JSON Kosong */
        if(count($request->all())){
            $mdl = $this->getModelNameSpace($this->modelName['modelName']);
            $class['model'] = new $mdl;
            /* Transformer */
            $cl = $this->getTransformerNameSpace($request->get('table'));
            $transformer = new $cl;
        
            $request = $transformer->transformToReq($request, $class['model']);

            if($request->input('details')){
                 $data = $this->transformValidationJoin($class, $request, 'update');
            }else{
                 $data = $this->transformValidation($class, $request, 'update');
            }

            /* Handle Error dalam proses update */
            if($data['status'] == 401){
                return message::showvalidation($data['message']);
            }else if($data['status'] == 400){
                return message::PK_error($data['message']);
            }else{
                DB::beginTransaction();
                try{
                    if($class['model']['generateRelation']){
                        for($b=0; $b < count($class['model']['generateRelation']); $b++){
                           $m = $class['model']['generateRelation'][$b];
                           $data->{$m['FK']} = $this->insert_relation($request, $m);
                        }
                    }
                    $where = [];
                    foreach($data['primaryKey'] as $key => $value){
                        array_push($where, [$value, '=', $data->$value]);
                    }
                    //$data->save();
                    $update = \DB::table($class['model']['table'])
                                        ->where($where)
                                        ->update($data['attributes']);

                    $this->transStatus = true;
                }catch(\Exception $e){
                    dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Penyimpanan Gagal, Error update data !";
                }
                if($this->transStatus){
                    DB::commit();
                    $this->insert_log($request, $data->NoRec, $data['attributes']);
                    return message::createdResponse($data);
                }else{
                    DB::rollBack();
                    $result = array("status" => 400, "message" => $this->transMessage);
                    return response()->json($result, 400);
                }
            }
        }else{
            DB::rollBack();
            $result = array("status" => 400, "message" => 'Json Kosong !');
            return response()->json($result, 400); 
        } 
    }
    /*****************************************************************************************************/
    /* Fungsi Bantuan Transaksi */
    protected function update_sequenceTbl($table, $kdprofile){
        $last_no = DB::table('SequenceTable_M')
                ->where([['namaTable', '=', $table],
                         ['KdProfile', '=', $this->KdProfile]])
                ->first()->idTerakhir;

        $update  = DB::table('SequenceTable_M');
        if($this->KdProfile){
            $update->where('namaTable', $table)
                   ->where('KdProfile', $this->KdProfile)
                   ->update(['idTerakhir' => $last_no + 1]);   
        } 
        return $last_no;
    }
    protected function gen_code($kdStrukturNomor, $paramTable){
        $dataM = DB::table('StrukturNomor_M')->where([
                                                        ['KdProfile', '=', 3], 
                                                        ['KdStrukturNomor', '=', $kdStrukturNomor]
                                                    ])->first();
        $dataD = DB::table('StrukturNomorDetail_M')->where([
                                                        ['KdProfile', '=', 3], 
                                                        ['KdStrukturNomor', '=', $kdStrukturNomor]
                                                    ]);
        $last_no = DB::table('SequenceTable_M')->where('namaTable', $paramTable)->first()->idTerakhir;
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
    protected function get_combo_string($request, $comboString){
        $combo = array();
        foreach($comboString as $key => $data){
            if(is_numeric($request->input($key))){
                $combo[$key] = $request->input($key);
                $combo[$data] = '';
            }else{
                $combo[$key] = '';
                $combo[$data] = $request->input($data);
            }
        }

        return $combo;
    }
    protected function get_sequence($tablename, $request){
        $last_no = DB::table('SequenceTable_M')
                     ->where([['namaTable', '=', $tablename],['KdProfile', '=', $request->header('KdProfile')]]);
        if($last_no->count()){
            $last_no = $last_no->first()->idTerakhir;
        }else{
            $idMax = DB::table('SequenceTable_M')
                     ->max('KdSequenceTable');
            $data = array(
                        'KdProfile' => $request->header('KdProfile'),
                        'KdSequenceTable' => (int)$idMax + 1,
                        'idTerakhir' => 0,
                        'namaTable' => $tablename,
                        'version' => 1,
                    );
            DB::table('SequenceTable_M')->insert($data);
            $last_no = 0;
        }
        return $last_no + 1;   
    }
    /* Master Parent */
    protected function compare_column($request, $class, $condition){
        $tablename = $class['model']['table'];
        $index = $class['model']['generateCode']; 
        $comboString = $class['model']['comboString'];
        $combo_string = array();

        $q = $this->get_column_table($tablename);
        if($index){
            $keyIndex = $this->get_sequence($tablename, $request);
        }
        $result = [];

        foreach ($q as $key => $value) {
            $name = $value->COLUMN_NAME;
            $class['model']->$name = $request->input($value->COLUMN_NAME);
            $convert_date = substr($value->COLUMN_NAME,0,3);
            if(strtolower($convert_date) == 'tgl'){
                if($request->input($value->COLUMN_NAME)){
                    if($value->DATA_TYPE == 'smalldatetime'){
                        $class['model']->$name = $request->input($value->COLUMN_NAME).':00';
                    }
                }
            }
            if(@$value->COLUMN_NAME == 'KdProfile' && $tablename != 'Profile_M'){
                $class['model']->KdProfile = $request->header('KdProfile');
            }
            if(@$value->COLUMN_NAME == 'KdDepartemen' && $tablename != 'Departemen_M'){
                 $class['model']->KdDepartemen = $request->header('KdDepartemen');
            }
            if(@$value->COLUMN_NAME == 'KdRuangan' && $tablename != 'Ruangan_M'){
                 $class['model']->KdRuangan = $request->header('KdRuangan');
            }
            if(@$value->COLUMN_NAME == 'NoRec'){
                 $class['model']->NoRec = substr(Uuid::generate(), 0, 32);
            }
            if($index && $condition == 'simpan'){
                 $class['model']->$index = $keyIndex;
            }
        }
         $class['model']->version = 1;

        /* Combo String */
        if($comboString){
            $combo_string = $this->get_combo_string($request, $comboString);

            foreach ($combo_string as $k => $d){
                 $class['model']->$k = $combo_string[$k];
            }
        }
        return $class['model'];
    }
    protected function transformValidation($class, $request, $condition){
        // Step first Get Required Column from table
        $model = $this->get_validationColumn($class['model']);
        $dataModel = $this->compare_column($request, $class, $condition);

        if($dataModel == false){
            $msg = array("status" => 400, "message" => 'Sequence Table belum diset !');
            return $msg;
        }else{
            $validatorMain = \Validator::make($dataModel['attributes'], $model);
            
            if($validatorMain->fails()){
                $error = $validatorMain->errors();
                $msg = array("status" => 401, "message" => $error);
                return $msg;
            }else{
                return $dataModel;
            }
        }
    }
    /* Master Detail */
    protected function compare_column_join($idx, $request, $class, $condition){
        $tablename = $class['model']['table'];
        $index = $class['model']['generateCode']; 
        $comboString = $class['model']['comboString'];
        $combo_string = array();

        $result = [];
        $q = $this->get_column_table($tablename);
        foreach ($q as $key => $value) {
            $name = $value->COLUMN_NAME;
            $class['model']->$name = @$request->input('details')[$idx][$value->COLUMN_NAME];
            $convert_date = substr($value->COLUMN_NAME,0,3);
            if(strtolower($convert_date) == 'tgl'){
                if($request->input($value->COLUMN_NAME)){
                    if($value->DATA_TYPE == 'smalldatetime'){
                        $class['model']->$name = @$request->input('details')[$idx][$value->COLUMN_NAME].':00';
                    }
                }
            }
            if(@$value->COLUMN_NAME == 'KdProfile' && $tablename != 'Profile_M'){
                $class['model']->KdProfile = $request->header('KdProfile');
            }
            if(@$value->COLUMN_NAME == 'KdDepartemen' && $tablename != 'Departemen_M'){
                 $class['model']->KdDepartemen = $request->header('KdDepartemen');
            }
            if(@$value->COLUMN_NAME == 'KdRuangan' && $tablename != 'Ruangan_M'){
                 $class['model']->KdRuangan = $request->header('KdRuangan');
            }
            if(@$value->COLUMN_NAME == 'NoRec'){
                 $class['model']->NoRec = substr(Uuid::generate(), 0, 32);
            }
        }
        $class['model']->version = 1;
        /* Combo String */
        if($comboString){
            $combo_string = $this->get_combo_string($request, $comboString);
            foreach ($combo_string as $k => $d){
                $class['model']->$k = $combo_string[$k];
            }
        }     
        return $class['model'];
    }
    protected function transformValidationJoin($class, $request, $condition){
        // Step first Get Required Column from table
        $model = $this->get_validationColumn($class['model']);

        for($i=0; $i<count($request->input('details')); $i++){
            $dataModel = $this->compare_column_join($i, $request, $class, $condition);

            $validatorMain = \Validator::make($dataModel['attributes'], $model);            
            if($validatorMain->fails()){
                $error = $validatorMain->errors();
                $msg = array("status" => 401, "message" => $error);
                return $msg;
            }else{
                return $dataModel;
            }
        }
         $msg = array("status" => 201, "data" => $insert);
         return $msg;
    }
    /* Master Map */
    protected function compare_column_map_join($idx, $request, $class, $condition){
        $tablename = $class['model']['table'];
        $index = $class['model']['generateCode']; 
        $comboString = $class['model']['comboString'];
        $combo_string = array();

        $result = [];
        $q = $this->get_column_table($tablename);
        foreach ($q as $key => $value) {
            $name = $value->COLUMN_NAME;
            $result[$name] = @$request->input('details')[$idx][$value->COLUMN_NAME];
            $convert_date = substr($value->COLUMN_NAME,0,3);
            if(strtolower($convert_date) == 'tgl'){
                if($request->input($value->COLUMN_NAME)){
                    if($value->DATA_TYPE == 'smalldatetime'){
                        $result[$name] = @$request->input('details')[$idx][$value->COLUMN_NAME].':00';
                    }
                }
            }
            if(@$value->COLUMN_NAME == 'KdProfile' && $tablename != 'Profile_M'){
                $result['KdProfile'] = $request->header('KdProfile');
            }
            if(@$value->COLUMN_NAME == 'KdDepartemen' && $tablename != 'Departemen_M'){
                $result['KdDepartemen'] = $request->header('KdDepartemen');
            }
            if(@$value->COLUMN_NAME == 'KdRuangan' && $tablename != 'MapFasilitasToRuangan_M' && $tablename != 'Ruangan_M' && $class['model']['keyInsert'] != 'KdRuangan' && $tablename != 'MapRuanganToJurusan_M'){
                 $result['KdRuangan'] = $request->header('KdRuangan');
            }
            if(@$value->COLUMN_NAME == 'NoRec'){
                 $result['NoRec'] = substr(Uuid::generate(), 0, 32);
            }
        }
        $result['version'] = 1;
        /* Combo String */
        if($comboString){
            $combo_string = $this->get_combo_string($request, $comboString);
            foreach ($combo_string as $k => $d){
                $result[$k] = $combo_string[$k];
            }
        }     
        return $result;
    }
    protected function transformValidationMapJoin($class, $request, $condition){
        // Step first Get Required Column from table

        $model = $this->get_validationColumn($class['model']);
        $x=0;
        for($i=0; $i<count($request->input('details')); $i++){
            $dataModel = $this->compare_column_map_join($i, $request, $class, $condition);

            $validatorMain = \Validator::make($dataModel, $model);            
            if($validatorMain->fails()){
                $error = $validatorMain->errors();
                $msg = array("status" => 401, "message" => $error);
                return $msg;
            }else{
                $insert[$x]['table']= $class['model']['table'];
                $insert[$x]['data']= $dataModel;
                $x++;
            }
        }
        $msg = array("status" => 201, "data" => $insert);
        return $msg;
    }
    /*****************************************************************************************************/
    /* Query Builder */
    protected function cekTabel($table){
        $cek = explode('_', $table);
        if(@$cek[1]){
            $table = $table;
        }else{
            if(class_exists($this->getModelNameSpace($table.'_M'))){
                $table = $table.'_M';
            }else if(class_exists($this->getModelNameSpace($table.'_S'))){
                $table = $table.'_S';
            }else if(class_exists($this->getModelNameSpace($table.'_T'))){
                $table = $table.'_T';
            }else{
                $table = $table.'_M';
            }
        }
        return $table;
    }
    protected function transform($model, $value){
        /*
        $class = 'App\\Transformers\\Master\\AgamaTransformer';
        $this->transformer = new $class();

        $transformer = new $this->transformer;

        var_dump($transformer->transformCollection($value));
        die();

        $q = $this->get_column_table($model['table']);

        Schema::table($model['table'], function($table){
            $table->renameColumn('KdProfile', 'KDPROFILE')->comment = "Test Rename KdProfile";
        });

        foreach ($q as $key => $val) {
            foreach($value as $k => $v){
                if(@$model['fillable'][$k]){
                    break;
                    $index = $model['fillable'][$k];
                    $value->$index = $v;
                    unset($value->$k);
                }

            }
        }
        return $value;
        */
    }

    /*
    Schema::table('users', function($table)
    {
        $table->renameColumn('from', 'to')->comment = "New Comment of Product name column";
    });
    */

    protected function gen_kelompok_transaksi($param){
        $q = DB::table('KelompokTransaksi_M')->where('KelompokTransaksi', '=', $param);
        return @$q->first()->KdKelompokTransaksi;
    }
}