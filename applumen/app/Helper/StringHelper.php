<?php

namespace App\Helper;

class StringHelper{

	public function getRomawi($number){
        $hasil = '';
        $iromawi = array('','I','II','III','IV','V','VI','VII','VIII','IX','X',20=>'XX',30=>'XXX',40=>'XL',50=>'L',
        60=>'LX',70=>'LXX',80=>'LXXX',90=>'XC',100=>'C',200=>'CC',300=>'CCC',400=>'CD',500=>'D',600=>'DC',700=>'DCC',
        800=>'DCCC',900=>'CM',1000=>'M',2000=>'MM',3000=>'MMM',4000=>'MMMM');
        if(array_key_exists($number,$iromawi)){
            $hasil = $iromawi[$number];
        }elseif($number >= 11 && $number <= 99){
            $i = $number % 10;
            $hasil = $iromawi[$number-$i] . $this->getRomawi($i);
        }elseif($number >= 101 && $number <= 999){
            $i = $number % 100;
            $hasil = $iromawi[$number-$i] . $this->getRomawi($i);
        }else{
            $i = $number % 1000;
            $hasil = $iromawi[$number-$i] . $this->getRomawi($i);
        }
        return $hasil;
   }

   public function getTerbilang($number){
       $abil = array("", "satu", "dua", "tiga", "empat",
                 "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        if ($number < 12)
            if($number == 0){
                return 'nol';
            }else{
                return " ".$abil[$number];
            }
        elseif ($number < 20)
            return $this->getTerbilang($number - 10) . " belas";
        elseif ($number < 100)
            return $this->getTerbilang($number / 10) . " puluh" . $this->getTerbilang($number % 10);
        elseif ($number < 200)
            return " seratus" . $this->getTerbilang($number - 100);
        elseif ($number < 1000)
            return $this->getTerbilang($number / 100) . " ratus" . $this->getTerbilang($number % 100);
        elseif ($number < 2000)
            return " seribu" . $this->getTerbilang($number - 1000,0);
        elseif ($number < 1000000)
            return $this->getTerbilang($number / 1000) . " ribu" . $this->getTerbilang($number % 1000);
        elseif ($number < 1000000000)
            return $this->getTerbilang($number / 1000000) . " juta" . $this->getTerbilang($number % 1000000);
   }

   public function getCodeSub($num, $digit){
        $i = 0;
        $temp = $num.'';
        while($i + strlen($num) < $digit){
            $temp = '0'.$temp;
            $i=$i+1;
        }
        return $temp;
   }

   public function getCodeWhole($jenis,$urut){
        return date('y').date('m').$this->getCodeSub($jenis,2).$this->getCodeSub($urut,4);
   }

   public function isNumber($chr){
       $numbers = array('0','1','2','3','4','5','6','7','8','9');
       $i = 0;
       while($i < sizeof($numbers)){
           if($numbers[$i] == $chr){
               return true;
           }
           $i = $i+1;
       }
       return false;
   }

   public function isOperator($chr){
       $operator = array('+','-','*','x',':','/');
       $i = 0;
       while($i < sizeof($operator)){
           if($operator[$i] == $chr){
               return true;
           }
           $i = $i+1;
       }
       return false;
   }

   //input : string operasi matematika 
   //output : true jika valid, false jika invalid
   public function isValidEquation($str){
        $i = 0;
        $parenCount = 0;
        $numTemp = array();
        while($i < strlen($str)){
            if($this->isNumber($str[$i])){
                if($i == 0){
                    $numTemp[sizeof($numTemp)] = $str[$i];
                }
                else{
                    if($this->isNumber($numTemp[sizeof($numTemp)-1][0])){
                        $temp = ($numTemp[sizeof($numTemp)-1]).$str[$i];
                        $numTemp[sizeof($numTemp)-1] = $temp;
                    }
                    else{
                        $numTemp[sizeof($numTemp)] = $str[$i];
                    }
                }
            }
            else if($this->isOperator($str[$i])){
                if($str[$i]=='x'){
                    $str[$i]='*';
                }else if($str[$i]==':'){
                    $str[$i]='/';
                }
                $numTemp[sizeof($numTemp)]=$str[$i];
            }   
            else if($str[$i] == '('){
                $parenCount = $parenCount +1;
                $numTemp[sizeof($numTemp)]=$str[$i];
            }
            else if($str[$i] == ')'){
                $parenCount = $parenCount -1;
                $numTemp[sizeof($numTemp)]=$str[$i];
            }
            if($parenCount < 0){
                return false;
            }
            $i = $i+1;
        }
        if($parenCount == 0){
            return true;
        }
        else{
            return false;
        }
   }

   public function getCalStr($str)
   {
       if($this->isValidEquation($str)){
           return eval('return '.$str.';');
       }
       else{
           return "invalid equation";
       }
       
   }

   //input: adika Suta
   //output: ADIKA SUTA
   public function getAllCaps($str){
       return strtoupper($str);
   }

   //input: ADika Suta
   //output: adika suta
   public function getAllLow($str){
       return strtolower($str);
   }

   //input: ADIKA Suta
   //output: aDIKA Suta
   public function getFirstLow($str){
       return lcfirst($str);
   }

   //input: adika suta
   //output: Adika Suta
   public function getFirstCaps($str){
       return ucwords($str);
   }
    
    function ubahHurufKapital(){
    	$str = "Mary Had A Little Lamb and She LOVED It So";
		$str = strtoupper($str);
		echo $str;
    }

    function UbahHurufKecil(){
    	echo strtolower("Hello WORLD.");
    }

 	function generate_numbers($start, $count, $digits) {
		$result = array(); 
		for ($n = $start; $n < $start + $count; $n++) {
			$result[] = str_pad($n, $digits, "0", STR_PAD_LEFT);
		} 
	return $result;
	generate_numbers(10, 20, 10);   
	
	}
    
	function AngkajadiBulan(){
		function tampil_bulan ($x) {
    	$bulan 	= array (1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
             	5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
             	9=>'September',10=>'Oktober',11=>'November',12=>'Desember');
    return $bulan[$x];  
		}
	}

	function AngkajadiRomawi(){

		$hasil = "";
  		$iromawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X",20=>"XX",30=>"XXX",40=>"XL",50=>"L",
    	60=>"LX",70=>"LXX",80=>"LXXX",90=>"XC",100=>"C",200=>"CC",300=>"CCC",400=>"CD",500=>"D",600=>"DC",700=>"DCC",
    	800=>"DCCC",900=>"CM",1000=>"M",2000=>"MM",3000=>"MMM");
  		if(array_key_exists($n,$iromawi)){
    		$hasil = $iromawi[$n];
  		}elseif($n >= 11 && $n <= 99){
    		$i = $n % 10;
    		$hasil = $iromawi[$n-$i] . Romawi($n % 10);
  		}elseif($n >= 101 && $n <= 999){
    		$i = $n % 100;
    		$hasil = $iromawi[$n-$i] . Romawi($n % 100);
  		}else{
    		$i = $n % 1000;
    		$hasil = $iromawi[$n-$i] . Romawi($n % 1000);
  		}
  	return $hasil;
	}

}