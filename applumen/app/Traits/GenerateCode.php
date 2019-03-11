<?php
namespace App\Traits;

use App\Model\Master\KodeGenerate;
use Illuminate\Support\Facades\DB;
use Validator;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;

Trait GenerateCode
{

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
                        case 'yy' : $prefix .= date('Y');break;
                        case 'yyyy' : $prefix .= date('Y');break;
                        default : $prefix .= $value->FormatKode; break;
                    }
                }
                $id = $prefix.$id;
            }
        }
        return $id;
    }

    //Generate Code Versi 2
    protected function gen_code_V2($table = null, $KelompokTransaksi = null, $field=null, $KdProfile = null){
        $get_SN = DB::table('MapKelompokTransaksiToSN_M as map')
            ->join('KelompokTransaksi_M as kelompok', 'kelompok.KdKelompokTransaksi', '=', 'map.KdKelompokTransaksi')
            ->join('StrukturNomor_M as nomor', 'map.KdStrukturNomor', '=', 'nomor.KdStrukturNomor')
            ->where('kelompok.KelompokTransaksi','=',$KelompokTransaksi)
            ->select('nomor.KdStrukturNomor', 'nomor.FormatNomor', 'nomor.QtyDigitNomor')->first();

        $set_SN = DB::table('StrukturNomorDetail_M')
            ->where('KdStrukturNomor', '=', $get_SN->KdStrukturNomor)
            ->get();

        $format = "";
        if(count($set_SN) > 0){
            foreach ($set_SN as $value){
                switch($value->FormatKode){
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


        $checkData = DB::table('SequenceTable_M')
            ->where('namaTable','=', $table)
            ->where('KdProfile','=', $KdProfile)
            ->get();

        if(count($checkData) > 1){
            $GetMaxData = DB::table('SequenceTable_M as sequence')
                ->join('SettingDataFixed_M as setting', function($join){
                    $join->on('setting.NilaiField', '=', 'sequence.KdSequenceTable');
                    $join->on('setting.KdProfile', '=', 'sequence.KdProfile');
                })
                ->where('sequence.namaTable','=', $table)
                ->where('sequence.KdProfile','=', $KdProfile)
                ->where('setting.NamaField','=', $field)
                ->selectRaw('idTerakhir');
            $data = $GetMaxData->first();

        }else{
            $data = $checkData[0];
        }
        $setting_increment =  str_pad($data->idTerakhir + 1, $get_SN->QtyDigitNomor, 0, STR_PAD_LEFT);
        $serial_number = $format.$setting_increment;
        return $serial_number;
    }

    protected function update_code($paramTable,$KdProfile){
        $update_db = DB::update("Update SequenceTable_M set idTerakhir = idTerakhir + 1 where namaTable = '".$paramTable."' and KdProfile=".$KdProfile);
        return $update_db;
    }

	protected function generateCode($table, $field, $length=8, $kodeDepan=''){
		$result = $table->where($field, 'LIKE', $kodeDepan.'%')->max($field);
		$prefixLen = strlen($kodeDepan);
		$subPrefix = substr(trim($result),$prefixLen);
		return $kodeDepan.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));
	}
	protected function getNewCode( $jeniskode, $length=8, $format=''){
		DB::beginTransaction();
		try {
			$result = KodeGenerate::where('format', 'LIKE', $format.'%')
				->where('jeniskode',$jeniskode)
				->max('format');
			$formatLen = strlen($format);
			$subFormat = substr(trim($result),$formatLen);
			$SN = $format.(str_pad((int)$subFormat+1, $length-$formatLen, "0", STR_PAD_LEFT));

			$newSN = new KodeGenerate();
			$newSN->id = KodeGenerate::max('id')+ 1;
			$newSN->format = $SN;
			$newSN->jeniskode = $jeniskode;
			$newSN->save();
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}

		if ($transStatus == 'true') {
			DB::commit();
			return $SN;
		} else {
			DB::rollBack();
			return '';
		}

	}
	protected function generateUid(){
    	return substr(Uuid::generate(), 0, 32);
	}
	public function getTerbilang($number){
		$x = abs($number);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
			"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($number <12) {
			$temp = " ". $angka[$number];
		} else if ($number <20) {
			$temp = $this->getTerbilang($number - 10). " belas";
		} else if ($number <100) {
			$temp = $this->getTerbilang($number/10)." puluh". $this->getTerbilang($number % 10);
		} else if ($number <200) {
			$temp = " seratus" . $this->getTerbilang($number - 100);
		} else if ($number <1000) {
			$temp = $this->getTerbilang($number/100) . " ratus" . $this->getTerbilang($number % 100);
		} else if ($number <2000) {
			$temp = " seribu" . $this->getTerbilang($number - 1000);
		} else if ($number <1000000) {
			$temp = $this->getTerbilang($number/1000) . " ribu" . $this->getTerbilang($number % 1000);
		} else if ($number <1000000000) {
			$temp = $this->getTerbilang($number/1000000) . " juta" . $this->getTerbilang($number % 1000000);
		} else if ($number <1000000000000) {
			$temp = $this->getTerbilang($number/1000000000) . " milyar" . $this->getTerbilang(fmod($number,1000000000));
		} else if ($number <1000000000000000) {
			$temp = $this->getTerbilang($number/1000000000000) . " trilyun" . $this->getTerbilang(fmod($number,1000000000000));
		}
		return $temp;
	}
}