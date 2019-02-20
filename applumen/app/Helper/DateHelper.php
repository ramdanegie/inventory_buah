<?php

namespace App\Helper;

class DateHelper
{
	public function getDaysInMonth($month, $year){
        return cal_days_in_month(CAL_GREGORIAN,$month,$year);
    }

    public function getDay($number){
        $days = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
        return $days[$number];
    }

    public function getMonth($number){
        $months = array('Januari','Februari','Maret','April','Mei',
               'Juni','Juli','Agustus','September','Oktober','November','Desember');
        return $months[$number];
    }

   public function getDuration($in,$out){
        $diff=date_diff(date_create($in),date_create($out));
        $result = array(
            'tahun'=>$diff->format('%y'),
            'bulan'=>$diff->format('%m'),
            'status'=>$diff->format('%R'),
            'hari'=>$diff->format('%a'),
            'jam'=>$diff->format('%h'),
            'menit'=>$diff->format('%i')
            );
        return $result;
    }

    public function getCalendar($year){
        $helper = new APIHelper;
        $dummyKey = 'b3732bb7-d0d9-4e53-bbfb-33ade465269f';
        $liveKey = '7a0898a5-fecf-4fad-b69b-d2257ab1131d'; //300 calls per month
        $url = 'https://holidayapi.com/v1/holidays?key='.$dummyKey.'&country=ID&year='.($year);
        $localJSON = __DIR__.'/../../public/json/HariLibur.json'; 
        
        $holiday = $helper->getAPIfromURL($url);
        $holidayJSON = json_decode($holiday);

        $result = array();
        $i = 1;
        while($i<=12){
            $result[$i]['bulan']=$this->getMonth($i-1);
            $j = 1;
            $mingguKe = 1;
            while($j<=$this->getDaysInMonth($i,$year)){
                $result[$i]['hari'][$j]['mingguKe'] = $mingguKe;
                if(date('w',mktime(0,0,0,$i, $j, $year)) == 0){
                    $mingguKe = $mingguKe +1;
                }               
                $date = date('Y-m-d', mktime(0,0,0,$i,$j,($year)));
                $result[$i]['hari'][$j]['nama'] = $this->getDay(date('w',mktime(0,0,0,$i, $j, $year)));
                $result[$i]['hari'][$j]['kd'] = date('w',mktime(0,0,0,$i, $j, $year));
                if(isset($holidayJSON->holidays->$date[0]->name)){
                    $result[$i]['hari'][$j]['holiday'] = $holidayJSON->holidays->$date[0]->name;
                }
                else{
                    $result[$i]['hari'][$j]['holiday'] = '';
                }
                $j=$j+1;
            }
            $i=$i+1;
        }
        return $result;
    }
	function UbahTanggalString(){
		$bulan = array("January","Pebruary","Maret","April","Mei","Juni","Juli","Agustus","September","Okotober","Nopember","Desember");

		$hari  = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
		$month = intval(date('m')) - 1;
		$days  = date('w');
		$tg_angka = date('d');
		$year  = date('Y');
		echo 'Sekarang ini hari '.$hari[$days].' , Tanggal '.$tg_angka.' - '.$bulan[$month].' - '.$year;
	}

	function SelisihHari(){
		$awal  = date_create('1995-11-21');
  		$akhir = date_create(); // waktu sekarang
  		$diff  = date_diff( $awal, $akhir );

  		echo 'Selisih waktu: ';
  		echo $diff->y . ' tahun, ';
  		echo $diff->m . ' bulan, ';
		echo $diff->d . ' hari, ';
		echo $diff->h . ' jam, ';
		echo $diff->i . ' menit, ';
		echo $diff->s . ' detik, ';
		// Output: Selisih waktu: 28 tahun, 5 bulan, 9 hari, 13 jam, 7 menit, 7 detik

		echo 'Total selisih menit : ' . $diff->seconds;
		// Output: Total selisih hari: 10398  
	}

	function GantiNamaHari(){
		$bulan = array("January","Pebruary","Maret","April","Mei","Juni","Juli","Agustus","September","Okotober","Nopember","Desember");

		//Buat daftar nama hari dalam bahasa indonesia
		$hari  = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
		$month = intval(date('m')) - 1;
		$days  = date('w');
		$tg_angka = date('d');

		$year  = date('Y');

		echo 'Sekarang ini hari '.$hari[$days].' , Tanggal '.$tg_angka.' - '.$bulan[$month].' - '.$year;	
	}
	
}
