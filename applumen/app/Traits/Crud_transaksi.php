<?php
namespace App\Traits;

use App\Traits\message;
use App\Traits\StrukMaster;
use DB;
use Validator;
use Webpatser\Uuid\Uuid;
use App\StrukHistori_M as StrukHistori_T;
use App\Exceptions\ExecuteQueryException;

Trait Crud_transaksi
{
   use message;
   use StrukMaster;

    /**
     * @var
    */

    //for validation
    protected $useValidation = false;
    protected $ruleCustomMessages = null;
    protected $rules = null;
    protected $list = [];

    //for translate message
    protected $modulPath = 'lib.modul';
    protected $modulName = null;

    //untuk nyisipin fungsi
    protected $errorMessage=array();
    protected $extraFunc=array();

    public function getModelName(){
        return $this->modelName;
    }

    protected function getModelNameSpace(){
        return 'App\\' . $this->getModelName();
    }

    protected function getModulLabel(){
        $modul = ($this->modulName == null) ? strtolower($this->getModelName()) : $this->modulName;
        return trans($this->modulPath . "." . $modul);
    }

    protected function joinFunc($query, $param, $table){
        switch($param['join']){
            case 'leftJoin' : $sql = $query->leftJoin($param['table'], $param['table'].'.'.$param['on'], $param['operand'], $param['to']); break;
        }
        return $sql;
    }

    protected function listData($request, $param){

        $limit = $request->input('limit') ? $request->input('limit') : null;
        $sort_type = 'asc';

        //query
        $listdata = DB::table($param['table_from']);

        //join
        if(count(@$param['table_join'])){
            for($i=0; $i<count($param['table_join']); $i++){
                $this->joinFunc($listdata, $param['table_join'][$i], $param['table_from']);
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

        foreach($dt['data'] as $key => $value){
            foreach($value as $t => $v){
                $convert_date = substr($t,0,3); 
                if(strtolower($convert_date) == 'tgl'){
                    if($v){
                        $nilai = $this->convert_tanggal('select', $v);
                    }
                }
            }      
        }

                
        $dt['label'] = '';
        if($param['label']){
            $dt['label'] = [$param['label']];
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

    protected function convert_tanggal($param, $date){
       
        if(!is_numeric($date)){
            $t = explode('/', $date);
            $date = strtotime($t[2].'-'.$t[1].'-'.$t[0]);
        }

        date_default_timezone_set("Asia/Jakarta");

        $hasil = $param == 'insert' ? $date : date('Y-m-d', ($date));

        return $hasil;
    }

    protected function setData($model, $data){
        try{
            $last_no = DB::table('SequenceTable_M')->where('namaTabel', $model['table'])->first()->idTerahir;
            
            foreach ($data as $key => $value) {
                $convert_date = substr($key,0,3);

                if(strtolower($convert_date) == 'tgl'){
                    $value = $this->convert_tanggal('insert', $value);
                }
                /* Ambil dari session */
                $model->{'KdProfile'} = 1;
                $model->{'NoRec'} = substr(Uuid::generate(), 0, 32);
                if($model->{$key} == 'KdDepartemen'){ $model->{'KdDepartemen'} = 1;}
                $model->{'version'} = 1;
                $model->{'StatusEnabled'} = 1;
                
                $model->{$key} = $value;
            }
            return $model;
        }
        catch(\Exception $e){
            dd($e->getMessage());
        }
    }

    protected function transformValidation($class, $request, $condition){
        $data =  new $class;

        $listdata = DB::table($data['table']);
        $where = [];
        for($i=0; $i < count($data['primaryKey']); $i++){
            if($data['primaryKey'][$i] == 'KdProfile'){
                $kode =1; 
            }else{
                $kode = $request->input($data['primaryKey'][$i]);
            }
            array_push($where,[$data['primaryKey'][$i], '=', $kode]);  
        }
        $listdata->where($where);

//        if($listdata->count() > 0 && $condition == 'simpan'){
//            $response = [
//                'code' => 404,
//                'status' => 'error',
//                'message' => 'Data Sudah Ada !',
//                'data' => $request->All()
//            ];
//            echo json_encode($response, $response['code']);
//            die();
//        }
        
        $q = DB::table('INFORMATION_SCHEMA.COLUMNS')
             ->where('TABLE_NAME', '=', $data['table'])
             ->get();

        $result = [];

        foreach ($q as $key => $value) {

            if($value->IS_NULLABLE == 'NO'){
                $index = $data['table'] == 'Profile_M' ? 0 : 1;
                if( $value->COLUMN_NAME != "KdProfile" && $value->COLUMN_NAME != "NoRec" && $value->COLUMN_NAME != "KdDepartemen" && $value->COLUMN_NAME != "version" && $value->COLUMN_NAME != @$data['primaryKey'][$index]){
                    $result[$value->COLUMN_NAME] = 'required';
                }
            }
        }
        return $result;
    }

    protected function update_sequenceTbl($table){
        $last_no = DB::table('SequenceTable_M')->where('namaTabel', $table)->first()->idTerahir;
        DB::table('SequenceTable_M')->where('namaTabel', $table)->update([
                                'idTerahir' => $last_no + 1]);    
        return $last_no;
    }

    protected function saveCreate($request){
        $class = $this->getModelNameSpace();
        $data = $this->transformValidation($class, $request, 'simpan');

        if($this->useValidation){
            $validator  = \Validator::make($request->all(), $data);

            if ($validator->fails()) {
                $error = $validator->errors();
                $msg = array(
                            "status" => 400,
                            "message" => $error
                        );

                return $msg;
            }else{
                DB::beginTransaction();

                $newdata =  new $class;
                $newdata = $this->setData($newdata, $request->all());

                try{
                    if(substr($newdata['table'],-1) != 'T'){
                        $newdata->save();
                    }else{

                    }
                    $this->transStatus = true;
                }catch(\Exception $e){
                    dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Penyimpanan Gagal";
                }
                if($this->transStatus){
                    DB::commit();
                    $sequence = $this->update_sequenceTbl($newdata['table']);
                    return message::createdResponse($newdata);
                }else{
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $this->transMessage,
                    );
                    return response()->json($result, $result['code']);
                }
            }
        }
    }

    protected function saveUpdate($request){
        $class = $this->getModelNameSpace();
        $data = $this->transformValidation($class, $request, 'update');

        if($this->useValidation){
            $validator  = \Validator::make($request->all(), $data);

            if ($validator->fails()) {
                $error = $validator->errors();
                $msg = array("status" => 400, "message" => $error);
                return $msg;
            }else{
                DB::beginTransaction();
                $newdata =  new $class;
                try{
                    if(substr($newdata['table'],-1) != 'T'){
                        $dt = array();
                        foreach($request->all() as $k => $v){
                            if($k != '_$visited'){
                                $dt[$k] = $v;
                            }
                        }
                        
                        $where = [];
                        for($i=0; $i < count($newdata['primaryKey']); $i++){
                            if($newdata['primaryKey'][$i] == 'KdProfile'){
                                $kode =1; 
                            }else{
                                $kode = $request->input($newdata['primaryKey'][$i]);
                            }
                            array_push($where,[$newdata['primaryKey'][$i], '=', $kode]);  
                        }
                        $update = DB::table($newdata['table'])->where($where)->update($dt);
                    }else{

                    }
                    $this->transStatus = true;
                }catch(\Exception $e){
                    dd($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Update Gagal";
                }
                if($this->transStatus){
                    DB::commit();
                    return message::createdResponse($request->all());
                }else{
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $this->transMessage,
                    );
                    return response()->json($result, $result['code']);
                }
            }
        }
    }

    protected function saveDelete($id){
        DB::beginTransaction();
        $result = array();
        try{
            $class = $this->getModelNameSpace();
            $newdata =  new $class;
            if(substr($newdata['table'],-1) != 'T'){
                $index = $newdata['table'] == 'Profile_M' ? 0 : 1;
                $update = DB::table($newdata['table'])
                            ->where($newdata['primaryKey'][$index], $id)
                            ->update(['statusenabled' => false]);
                $this->transStatus = true;
            }else{

            }
        }catch(\Exception $e){
            dd($e->getMessage());
            $this->transStatus = false;
        }

        if($this->transStatus){
            DB::commit();
            return message::deletedResponse();
        }else{
            DB::rollBack();
            return message::deletedResponseFailed();
        }        
    }
}