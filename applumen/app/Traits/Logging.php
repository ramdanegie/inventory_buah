<?php
/**
 * Created by IntelliJ IDEA.
 * User: Prastiyo Beka
 * Date: 24/11/2017
 * Time: 16:01
 */

namespace App\Traits;

use DB;
use Validator;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;

Trait Logging
{
    use \App\Traits\GenerateCode;
    protected  function insertHistory($request, $NoRec, $data = array()){
        $aray_data = array();
        if(key_exists('alamaturlform',$request->header())){
            $getURI = explode('#/',$request->header('alamaturlform'));
            $checkData = DB::table('ObjekModulAplikasi_S')->where('AlamatURLFormObjek', '=', $getURI[1] )->first();
            $aray_data ["NamaObjekCRUD"] = $checkData->ObjekModulAplikasi;
            $aray_data ["DetailObjekCRUD"] = $checkData->DeskripsiObjek;
        }

        if($request->server('REQUEST_METHOD') == "DELETE"){
            $aray_data ["TglCRUDout"] = date('Y-m-d H:i:s');
        }
        $aray_data ["KdProfile"] = $request->header('KdProfile');
        $aray_data ["KdHistoryLogin"] = $this->gen_code_V2('HistoryLoginModulAplikasi_S', 'Master', 'KdHistoryLogin',  $request->header('KdProfile'));
        $aray_data ["NoRecP"] = $NoRec;
        $aray_data ["isCRUD"] = 1;
        $aray_data ["NamaProses"] = $request->server('REQUEST_METHOD');
        $aray_data ["DetailDataCRUD"] = json_encode($data);
        $aray_data ["TglCRUDin"] = date('Y-m-d H:i:s');
        $aray_data ["StatusEnabled"] = 1;
        $aray_data [ "NoRec"] = Uuid::generate();
        $insertHistory = DB::table('HistoryLoginUser_S')->insert($aray_data);
        return $insertHistory;
    }

}