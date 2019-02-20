<?php
/**
 * Created by IntelliJ IDEA.
 * User: Prastiyo Beka
 * Date: 17/11/2017
 * Time: 14:05
 */

namespace App\Traits;

use App\Traits\message;
use App\Http\Requests;
use Validator;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Trait QueryBuilder
{
    use message;

    protected function getData_v1(Request $request){
        $part = array(
            "table" =>  $request->get('table'),
            "select"=>  $request->get('select'),
            "where" => $request->get('where'),
            "staticParams" => array(
                "KdProfile" => $request->header('kdprofile'),
                "KdDepartemen" => $request->header('kddepartemen'),
                "StatusEnabled" => 1
            )
        );

        if( $request->get('comparable')){
            $part['comparable'] = array(
                "tableComparable" => "StrukHistori_T",
                "paramsComparable" => "NoHistori",
                "between-start" => "TglAwal",
                "between-end" => "TglAkhir"
            );
        }

        //cek model exist
        if (class_exists('App\web\\' .$part['table'])) { //$modelName$this->getModelNameSpace($part['table']))
            $mdl ='App\web\\' .$part['table'];
            $model = new $mdl;
            if(method_exists($model, 'queryTable')){
                $data = $model->queryTable($request->header('kdprofile'), $request->header('kddepartemen'));
                if(key_exists('table_from', $data)){
                    $part['table'] = $data['table_from'];
                    $part['table_join'] = key_exists('table_join', $data)? $data['table_join']:null;
                    $query = $this->QueryBuilder($part);
                }else{
                    $query = $data;
                }
            }else{
                $query = $this->QueryBuilder($part);
            }
        } else {
            $query = $this->QueryBuilder($part);
        }

        /* Cek result Data */
        if(key_exists('code',$query)){
            return response()->json(array('code' => $query['code'], 'status' => $query['status'], 'message' => $query['message']), $query['code']);
        }else if(count($query->get()) == 0) {
            return response()->json(array('code' => 200, 'status' => 'success', 'message' => 'Data Kosong', 'data' => $query->get()), 200);
        }

        return response()->json(array('code' => 200,
            'status' => 'success',
            'message' => 'Data Berhasil Ditampilkan',
            'data' => array("totalRow" => count($data),
                "data" => $query->get())
        ), 200);
    }

    protected function QueryBuilder($part){
        $table = (key_exists('table', $part))?$part['table']:null;
        $select = (key_exists('select', $part))?$part['select']:null;
        $where = (key_exists('where', $part))?$part['where']:null;

        $wherein = (key_exists('comparable', $part))?$part['comparable']:null;
        $staticParams = (key_exists('staticParams', $part))?$part['staticParams']:null;

        if(DB::connection()->getPdo()) {
            /* Cek Table */
            $q = DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', '=', $table);
            if ($q->count() == 0) {
                $data = array('code' => 400, 'status' => 'error', 'message' => 'Tabel tidak ada !');
                return $data;
            }

            $data = DB::table($table);

            if (key_exists('table_join', $part) && is_array($part['table_join'])) {
                foreach ($part['table_join'] as $valueJoin) {
                    switch ($valueJoin['join']) {
                        case 'leftJoin' :
                            $sql = $data->leftJoin($valueJoin['table'], function ($join) use ($valueJoin, $part) {
                                $join->on($valueJoin['table'] . '.' . $valueJoin['on'], $valueJoin['operand'], $valueJoin['to'])->where($valueJoin['table'] . '.KdProfile', '=', $part['staticParams']['KdProfile']);
                            });
                            break;
                        case 'join' :
                            $sql = $data->leftJoin($table, function ($join) use ($valueJoin, $part) {
                                $join->on($valueJoin['table'] . '.' . $valueJoin['on'], $valueJoin['operand'], $valueJoin['to'])->where($valueJoin['table'] . '.KdProfile', '=', $part['staticParams']['KdProfile']);
                            });
                            break;
                    }
                }
            }

            //Untuk select field
            if ($select != '*') {
                $s = explode(',', $select);
                $select = array();
                for ($i = 0; $i < count($s); $i++) {
                    array_push($select, $s[$i]);
                }
            }
            if ($select != '*') {
                $q = DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', '=', $table);
                $kolom = 0;

                for ($r = 0; $r < count($select); $r++) {
                    foreach ($q->get() as $key => $dtKolom) {
                        if ($dtKolom->COLUMN_NAME == $select[$r]) {
                            $kolom += 1;
                        }
                    }
                }
                if ($kolom < 2) {
                    return response()->json(array('code' => 400, 'status' => 'error', 'message' => 'Kolom tidak ada !'), 400);
                }
            }
            $data->select($select);

            // Ini kondisi yang di pakai hampir di seluruh transaksi, contoh KdProfile, KdDepartemen, StatusEnabled
            if (is_array($staticParams)) {
                foreach ($staticParams as $keyStaticParams => $valueStaticParams) {
                    if (Schema::hasColumn($table, $keyStaticParams)) {
                        $data->where($table . '.' . $keyStaticParams, '=', $valueStaticParams);
                    }
                }
            }

            //Kondisi Where
            if (is_array($where)) {
                foreach ($where as $keyWhere => $valueWhere) {
                    if (Schema::hasColumn($table, $keyWhere)) {
                        $data->where($keyWhere, '=', $valueWhere);
                    }
                }
            }

            //Kondisi where compareable
            if (is_array($wherein)) {
                $data->join($wherein['tableComparable'], $wherein['tableComparable'] . '.' . $wherein['paramsComparable'], '=', $table . '.' . $wherein['paramsComparable']);
                $data->whereRaw('GETDATE() BETWEEN ' . $wherein['tableComparable'] . '.' . $wherein['between-start'] . ' AND ISNULL(' . $wherein['tableComparable'] . '.' . $wherein['between-end'] . ', GETDATE())');
            }
        }else{
            $data =  array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !');
            return $data;
        }
        return $data;
    }

}