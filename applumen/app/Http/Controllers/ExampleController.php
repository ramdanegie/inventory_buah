<?php

namespace App\Http\Controllers;



use App\Model\Master\Agama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getAgama(Request $request){
        $data = DB::select(DB::raw("select *  from agama_m"));
        if(count($data) > 0){
            $results['code'] = 200;
            $results['status'] = 'success';
            $results['message'] = 'Data berhasil di tampilkan';
            $results['data'] = $data;
        }else{
            $results['code'] = 200;
            $results['status'] = 'success';
            $results['message'] = 'Data Kosong';
            $results['data'] = $data;
        }
        return response()->json($results);
    }
}
