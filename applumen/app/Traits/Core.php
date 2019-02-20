<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Validator;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Helper\FileHelper;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;



Trait Core
{
    protected $sequenceTable_namatable = 'namaTable';
    protected $sequenceTable_idTerakhir = 'idTerakhir';

    protected function getModelNameSpace($modelName){
        return 'App\web\\' . $modelName;
    }

    //Generate Code Versi 2
    public function GenerateCodeIncrement($table, $field, $KdProfile = null){
        if($KdProfile != null){
            $QueryTable = DB::table($table)
                ->where('KdProfile','=',$KdProfile)
                ->select(DB::raw('max('.$field.') as '.$field));
            $results = $QueryTable->first();
            $number = 1;
            if($results->{$field}){
                $number = $results->{$field}+1;
            }
        }else{
            $Query = DB::table($table)
                ->select(DB::raw('max('.$field.') as '.$field));
            $results = $Query->first();
            $number = 1;
            if($results->{$field}){
                $number = $results->{$field}+1;
            }
        }

        return $number;
    }
    public function GenerateCode($table = null, $KelompokTransaksi = null, $field = null, $KdProfile = null)
    {
        $get_SN = DB::table('MapKelompokTransaksiToSN_M as map')
            ->join('KelompokTransaksi_M as kelompok', function ($kelompok) {
                $kelompok->on('kelompok.KdKelompokTransaksi', '=', 'map.KdKelompokTransaksi');
            })
            ->join('StrukturNomor_M as nomor', function ($nomor) {
                $nomor->on('map.KdStrukturNomor', '=', 'nomor.KdStrukturNomor');
                $nomor->on('map.KdProfile', '=', 'nomor.KdProfile');
            })
            ->where('kelompok.KelompokTransaksi', '=', $KelompokTransaksi)
            ->select('nomor.KdStrukturNomor', 'nomor.FormatNomor', 'nomor.QtyDigitNomor');

        if ($KdProfile !== null) {
            $new_get_SN = $get_SN->where('map.KdProfile', '=', $KdProfile)->first();
        } else {
            $new_get_SN = $get_SN->first();
        }

        if(is_object($new_get_SN)){
           $set_SN = DB::table('StrukturNomorDetail_M')
            ->where('KdStrukturNomor', '=', $new_get_SN->KdStrukturNomor)
            ->get();
        }else{
            $set_SN = false;
        }



        $format = "";
        if ($set_SN) {
            foreach ($set_SN as $value) {
                switch ($value->FormatKode) {
                    case "dd":
                        $format .= date('d');
                        break;
                    case "mm":
                        $format .= date('m');
                        break;
                    case "yy":
                        $format .= date('y');
                        break;
                    case "yyyy":
                        $format .= date('Y');
                        break;
                    default:
                        $format .= $value->FormatKode;
                        break;
                }
            }
        }

        if ($KdProfile) {
            $checkData = DB::table('SequenceTable_M')
                        ->where($this->sequenceTable_namatable, '=', $table)
                        ->where('KdProfile', '=', $KdProfile)
                        ->get();
        } else {
            $checkData = DB::table('SequenceTable_M')
                ->where($this->sequenceTable_namatable, '=', $table)
                ->get();
        }
        if (count($checkData) > 1) {
            if ($KdProfile != null) {
                $GetMaxData = DB::table('SequenceTable_M as sequence')
                    ->join('SettingDataFixed_M as setting', function ($join) {
                        $join->on('setting.NilaiField', '=', 'sequence.KdSequenceTable');
                        $join->on('setting.KdProfile', '=', 'sequence.KdProfile');
                    })
                    ->where('sequence.' . $this->sequenceTable_namatable, '=', $table)
                    ->where('sequence.KdProfile', '=', $KdProfile)
                    ->where('setting.NamaField', '=', $field)
                    ->selectRaw($this->sequenceTable_idTerakhir);
            } else {
                $GetMaxData = DB::table('SequenceTable_M as sequence')
                    ->join('SettingDataFixed_M as setting', function ($join) {
                        $join->on('setting.NilaiField', '=', 'sequence.KdSequenceTable');
                        $join->on('setting.KdProfile', '=', 'sequence.KdProfile');
                    })
                    ->where('sequence.' . $this->sequenceTable_namatable, '=', $table)
                    ->where('setting.NamaField', '=', $field)
                    ->selectRaw($this->sequenceTable_idTerakhir);
            }
            $data = $GetMaxData->first();

        } else {
            $data = $checkData[0];
        }

        $setting_increment = str_pad($data->idTerakhir + 1, $new_get_SN->QtyDigitNomor, 0, STR_PAD_LEFT);
        $serial_number = $format . $setting_increment;
        $this->Increment($table, $KdProfile);
        return $serial_number;
    }

    protected function HistoryLogin($server = null, $kdProfile = 0, $kdModulAplikasi = 0, $kdUser = 0, $kdRuangan = 0, $status = "Login")
    {
        if ($status == "Login") {
            $data_insert = array();
            $data_insert['KdProfile'] = $kdProfile;
            $data_insert['KdHistoryLogin'] = $this->GenerateCode('HistoryLoginModulAplikasi_S', 'Master', 'KdHistoryLogin', $kdProfile);
            $data_insert['KdModulAplikasi'] = $kdModulAplikasi;
            $data_insert['KdUser'] = $kdUser;
            $data_insert['KdRuanganUser'] = $kdRuangan;
            $data_insert['NoRec'] = substr(Uuid::generate(), 0, 32);
            $data_insert['StatusEnabled'] = 1;
            $data_insert['NamaHost'] = $server;
            $data_insert['TglLogin'] = strtotime(date('Y-m-d H:i:s'));
            $data_insert['version'] = 1;
            $set_data = DB::table('HistoryLoginModulAplikasi_S')->insert($data_insert);
            if ($set_data) {
                $token = $this->insertToken($kdUser, $kdProfile, $kdModulAplikasi, $kdRuangan);
            } else {
                $token = false;
            }
            return $token;
        } else {
            $helper = new FileHelper;
            $arrayT = $helper->getData("files", "user", "token.json");
            $array = json_decode($arrayT);
            $KdHistoryLogin = array();
            foreach ($array as $keyArray => $valueArray) {
                if ($valueArray->KdUser == $kdUser) {
                    array_push($KdHistoryLogin, $valueArray->KdHistoryLogin);
                    array_splice($array, $keyArray, 1);
                    $helper->createFile('files', 'user', 'token.json', json_encode($array));
                }
            }
            $data_update = array();
            $data_update['TglLogout'] = strtotime(date('Y-m-d H:i:s'));
            if (count($KdHistoryLogin) > 0) {
                $set_data = DB::table('HistoryLoginModulAplikasi_S')
                    ->where('KdHistoryLogin', '=', $KdHistoryLogin[0])
                    ->update($data_update);
                if ($set_data) {
                    $token = true;
                } else {
                    $token = false;
                }
            } else {
                $token = false;
            }
            return $token;
        }
    }

    public static function HistoryUser($request, $NoRec, $data = array())
    {
        $aray_data = array();
        if (key_exists('alamaturlform', $request->header())) {
            $getURI = explode('#/', $request->header('alamaturlform'));
            $checkData = DB::table('ObjekModulAplikasi_S')->where('AlamatURLFormObjek', '=', $getURI[1])->first();
            $aray_data ["NamaObjekCRUD"] = $checkData->ObjekModulAplikasi;
            $aray_data ["DetailObjekCRUD"] = $checkData->DeskripsiObjek;
        }

        if ($request->server('REQUEST_METHOD') == "DELETE") {
            $aray_data ["TglCRUDout"] = date('Y-m-d H:i:s');
        }

        //Ambil data dari token.json
        $helper = new FileHelper;
        $arrayT = $helper->getData("files", "user", "token.json");
        $array = json_decode($arrayT);
        $KdHistoryLogin = array();
        foreach ($array as $keyArray => $valueArray) {
            if ($valueArray->token == $request->header('authorization')) {
                array_push($KdHistoryLogin, $valueArray->KdHistoryLogin);
            }
        }
        if (count($KdHistoryLogin) > 0) {
            $aray_data ["KdProfile"] = $request->header('KdProfile');
            $aray_data ["KdHistoryLogin"] = $KdHistoryLogin[0];
            $aray_data ["NoRecP"] = $NoRec;
            $aray_data ["isCRUD"] = 1;
            $aray_data ["NamaProses"] = $request->server('REQUEST_METHOD');
            $aray_data ["DetailDataCRUD"] = json_encode($data);
            $aray_data ["TglCRUDin"] = strtotime(date('Y-m-d H:i:s'));
            $aray_data ["StatusEnabled"] = 1;
            $aray_data ["version"] = 1;
            $aray_data ["NoRec"] = Uuid::generate();
            $insertHistory = DB::table('HistoryLoginUser_S')->insert($aray_data);
        } else {
            $insertHistory = true;
        }
        return $insertHistory;
    }

    protected function Increment($paramTable, $KdProfile = null)
    {
        if ($KdProfile) {
            $update_db = DB::update("Update SequenceTable_M set " . $this->sequenceTable_idTerakhir . " = " . $this->sequenceTable_idTerakhir . " + 1 where " . $this->sequenceTable_namatable . " = '" . $paramTable . "' AND KdProfile='" . $KdProfile . "'");
        } else {
            $update_db = DB::update("Update SequenceTable_M set " . $this->sequenceTable_idTerakhir . " = " . $this->sequenceTable_idTerakhir . " + 1 where " . $this->sequenceTable_namatable . " = '" . $paramTable . "'");

        }
        return $update_db;
    }

    /*public function insertToken($KdUser, $KdHistoryLogin = null)
    {
        $helper = new FileHelper;
        $arrayT = $helper->getData("files", "user", "token.json");
        $array = json_decode($arrayT);
        if (count($array) > 0) {
            foreach ($array as $keyArray => $valueArray) {
                if ($valueArray->KdUser == $KdUser) {
                    return null;
                }
            }
        } else {
            $array = array();
        }
        $data = array(
            "KdUser" => $KdUser,
            "KdHistoryLogin" => $KdHistoryLogin,
            "token" => sha1(time() . rand())
        );
        array_push($array, $data);
        File::put(storage_path('files/user/token.json'), json_encode($array));
        return $data;
    }*/

    public function insertToken($KdUser, $KdProfile, $KdModulAplikasi, $KdRuangan)
    {
        $class = new Builder();
        $signer = new Sha256();
        $txt = new \stdClass();
        $txt->isPasien = false;
        $txt->mod = false;
        $txt->isPasien = $KdModulAplikasi;
        $txt->ruang = $KdRuangan;
        $txt->suid = 0;
        $txt->dept = "01";
        $txt->prof = $KdProfile;
        $txt->uid = $KdUser;
        $txt->peg = "L0001";
        $txt->klmp = 1;
        $txt->enc = "26AIUEO026";
        $txt->isUser = true;
        $txt->user = "admin@performa.co.id";
        $txt->jabat = "02";
        $data = json_encode($txt);
        $token = $class->setSubject($data)
            ->sign($signer, "RUdJRVJBTURBTg==")
            ->getToken();
       return $token;
    }


    public function settingDataFixed($NamaField, $KdProfile=null){
        $Query = DB::table('settingdatafixed_m')
            ->where('namafield', '=', $NamaField);
        if($KdProfile){
            $Query->where('kdprofile', '=', $KdProfile);
        }
        $settingDataFixed = $Query->first();
        return $settingDataFixed->NilaiField;
    }


    public function SequenceTable($KdProfile, $tableName){
        $QueryData = DB::table('SequenceTable_M')
            ->where('KdProfile','=', $KdProfile)
            ->where('namaTable','=',$tableName);
        $cekData  = $QueryData->get();
        if(count($cekData) < 1){
            $QueryIncrement = DB::table('SequenceTable_M')
                ->where('KdProfile','=', $KdProfile)
                ->select(DB::raw('max(KdSequenceTable) as KdSequenceTable'));
            $cekIncrement = $QueryIncrement->first();

            $KdSequenceTable = 1;
            if($cekIncrement->KdSequenceTable){
                $KdSequenceTable = $cekIncrement->KdSequenceTable + 1;
            }

        }
        if(count($cekData) < 1){
            $data = array(
                'KdProfile'=>$KdProfile,
                'KdSequenceTable'=>$KdSequenceTable,
                'idTerakhir'=> 0,
                'namaTable' => $tableName,
                'version' => 1
            );

            $createData = DB::table('SequenceTable_M')->insert($data);
            return response()->json(array('status'=>false, 'message'=>$data['idTerakhir']));
        }
        return response()->json(array('status'=>false, 'message'=>'Data Already Exist'));
    }

    public function GetSequence($NamaTable, $KdProfile)
    {
        $data = \DB::table('SequenceTable_M')
                    ->select('idTerakhir')
                    ->where('KdProfile', $KdProfile)
                    ->where('namaTable', $NamaTable)->first();

        return $data->idTerakhir + 1;
    }

    public function UpdateSequence($NamaTable, $idTerakhir,$KdProfile)
    {
        $data = DB::table('SequenceTable_M')
                    ->where('KdProfile', $KdProfile)
                    ->where('namaTable', $NamaTable);

        return $data->update(['idTerakhir' => $idTerakhir]);
    }

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

    public function GenerateNoPlanning($KdProfile){
        $y = date('y');
        $m = date('m');
        $d = date('d');
        $date = $y.$m.$d;
        $GETMAX = DB::table('StrukPlanning_T')
            ->selectRaw('Right(NoPlanning, 4) as NoPlanning')
            ->where('KdProfile', '=', $KdProfile)
            ->where('NoPlanning', 'like', $date.'%' )
            ->orderBy('NoPlanning', 'DESC')
            ->limit(1)
            ->first();
        $hasil = (@$GETMAX->NoPlanning) ? $GETMAX->NoPlanning :0;
        $increment = intval($hasil) + 1;
        $set_value = str_pad($increment, 4, '0', STR_PAD_LEFT);
        $NoPlanning = $date.$set_value;
        return $NoPlanning;
    }
}
