<?php

namespace App\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class FileHelper
{	
    //type: files, images
    //group: mengikuti fungsi data tersebut atau controller
    public function upload($file,$path,$fileName){
        $file->move($path, $fileName);
    }


    public function download($type, $group, $fileName){
        $path = storage_path($type . '/' . $group . '/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    //
    public function getData($type,$group,$fileName){
        $arrayT = "[]";
        if(File::exists(storage_path($type.'/'.$group.'/'.$fileName))){
            $arrayT = File::get(storage_path($type.'/'.$group.'/'.$fileName));
        }
        else{
            $this->createFile($type,$group,$fileName,$arrayT);
        }
        return $arrayT;
    }

    public function createFile($type,$group,$fileName,$content){
        File::put(storage_path($type.'/'.$group.'/'.$fileName),$content);
        return storage_path($type.'/'.$group.'/'.$fileName);
    }

}
