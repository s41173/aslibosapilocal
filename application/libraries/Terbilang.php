<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Terbilang
{

    function baca($n)
    {
        $str = null;
        $this->dasar = array(1 => 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam','tujuh', 'delapan', 'sembilan');
        $this->angka = array(1000000000, 1000000, 1000, 100, 10, 1);
        $this->satuan = array('milyar', 'juta', 'ribu', 'ratus', 'puluh', '');

        $i = 0;
        if($n==0){
           $str = "nol";
        }else{
           while ($n != 0) {
              $count = (int)($n/$this->angka[$i]);
              if ($count >= 10) {
                  $str .= $this->baca($count). " ".$this->satuan[$i]." ";
              }else if($count > 0 && $count < 10){
                  $str .= $this->dasar[$count] . " ".$this->satuan[$i]." ";
              }
              $n -= $this->angka[$i] * $count;
              $i++;
           }
           $str = preg_replace("/satu puluh (\w+)/i", "\\1 belas", $str);
           $str = preg_replace("/satu (ribu|ratus|puluh|belas)/i", "se\\1", $str);
        }
        return $str;
    }

}

/* End of file Property.php */