<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rajaongkir_lib extends Main_model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'kabupaten';
        $this->apikey = "eb7f7529d68f6a2933b5a042ffeeac9d";
        $this->url = "http://pro.rajaongkir.com/api/";
    }

    private $apikey,$url;
       
    private function splits($val)
    {
      $res = explode(",",$val); 
      return $res[0];
    }
    
    // ==================================== API ==============================
    
    function get_location($type=0,$param=0)
    {
        if ($type == 0){ $url = $this->url.'province'; }elseif($type == 1){ $url = $this->url.'city'; }
        elseif($type == 2){ $url = $this->url.'subdistrict?city='.$param; }
        else{ $url = $this->url.'subdistrict?id='.$param; }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "key: $this->apikey"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {return "cURL Error #:" . $err;} 
        else {  return $response; }
    }
    
    function combo_province()
    {
        $json = $this->get_location(1);
        $datax = json_decode($json, true);
        $data['options'][""] = " -- Pilih Provinsi -- ";
        foreach ($datax['rajaongkir']['results'] as $row)
        {$data[$row['province']] = $row['province'];}
        return $data;
    }
    
    function combo_city()
    {
        $json = $this->get_location();
        $datax = json_decode($json, true);
        $data['options'][""] = " -- Pilih Kabupaten / Kota -- ";
        foreach ($datax['rajaongkir']['results'] as $row)
        {$data[$row['city_id']] = $row['city_name'];}
        return $data;
    }
    
    function combo_city_combine()
    {
        $json = $this->get_city();
        $datax = json_decode($json, true);
        $data['options'][""] = " -- Pilih Kabupaten / Kota -- ";
        if ($datax)
        {
          foreach ($datax['rajaongkir']['results'] as $row)
          {$data[$row['city_id'].'|'.$row['city_name']] = $row['city_name'];}
        }
        return $data;
    }
    
    // origin & dest == kecamatan
    function get_cost_fee($ori,$dest,$courier='jne',$weight=1000)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url.'cost',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "origin=".$ori."&originType=subdistrict&destination=".$dest."&destinationType=subdistrict&weight=".$weight."&courier=".$courier,
        CURLOPT_HTTPHEADER => array(
          "content-type: application/x-www-form-urlencoded",
          "key: eb7f7529d68f6a2933b5a042ffeeac9d"
        ),
      ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) 
        { 
//            echo "cURL Error #:" . $err; 
            return 0;
        }
        else 
        { 
          $data = json_decode($response, true); 
//          $paket = $data['rajaongkir']['results'][0]['costs'][4]['service']; 
//          $harga = intval($data['rajaongkir']['results'][0]['costs'][0]['cost'][0]['value']); 
          $json = $data['rajaongkir']['results'][0]['costs'];
          return $json;
//          $datax = null;
//          for ($i=0; $i<count($json); $i++)
//          {
//            $paket = $json[$i]['service']; $desc = $json[$i]['description'];
//            $harga = intval($json[$i]['cost'][0]['value']);
//            $etd = intval($json[$i]['cost'][0]['etd']);
//            $datax[$i] = $paket.'|'.$desc.'|'.$etd.'|'.$harga;
//          }
//          return $datax;
          
        }
    }
    
    function cek_awb($awb,$courier='jnt')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url.'waybill',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "waybill=".$awb."&courier=".$courier,
        CURLOPT_HTTPHEADER => array(
          "content-type: application/x-www-form-urlencoded",
          "key: eb7f7529d68f6a2933b5a042ffeeac9d"
        ),
      ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) { echo "cURL Error #:" . $err; }
        else 
        { 
          $data = json_decode($response, true); 
          return $data;
        }
    }
    
    // mengahasilkan combo box ongkir
    function get_ongkir_combo($ori,$dest, $courier='jne')
    {
        $hasil = $this->get_cost_fee($ori, $dest, $courier);
        $datax = null;
        $datax[''] = '--';
        if ($hasil)
        {
          foreach ($hasil as $res){ $paket = explode('|', $res); $datax[$res] = $paket[0]; }
          $js = "class='form-control' id='cpackage' tabindex='-1' style='min-width:100px;' "; 
	  return form_dropdown('cpackage', $datax, isset($default['package']) ? $default['package'] : '', $js);
        }
    }
        

}

/* End of file Property.php */