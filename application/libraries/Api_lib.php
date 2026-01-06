<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'libraries/jwt/JWT.php';
require_once APPPATH.'libraries/jwt/ExpiredException.php';
use \Firebase\JWT\JWT;

class Api_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
//        $this->clogin = new Customer_login_lib();
//        $this->properti = $this->property->get();
        
        $this->url = "https://apiv2.shteamhosting.com/";
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $url;
    
    // ==================================== API ============================== 
    
    function check_api_server()
    {
        $ch = curl_init($this->url); // Buat endpoint ringan seperti /main/ping
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode == 200);
    }
    
    function gmttimes(){
        
        $startTime = date("Y-m-d H:i:s");
        $cenvertedTime = date('Y-m-d H:i:s',strtotime('-7 hour',strtotime($startTime)));
 
        //  return $this->response(array('gmt7' => $startTime, 'gmt' => lockcode_format($cenvertedTime))); 
        return lockcode_format($cenvertedTime);
   }
    
       
    function request_get($url=null,$type='null',$method='GET')
    {   
        $curl = curl_init();
        $param = null;
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url.$url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
      ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
//        $data = json_decode($response, true); 

        curl_close($curl);
        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $data = json_decode($response, true);
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            if ($result[1] != '200'){ $result[2] = $data['error']; }else{ $result[2] = null; }
            return $result;
        } 
    }
   
    function request($url = null, $param = null, $type ='null', $method = 'POST')
    {
        $curl = curl_init();

        // Tentukan Content-Type dan format data POST
        $headers = [];
        $postfields = null;

        if ($method === 'POST') {
            if (is_array($param)) {
                // Gunakan form-urlencoded
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                $postfields = http_build_query($param);
            } else {
                // Jika bukan array, diasumsikan sudah dalam bentuk string
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                $postfields = $param;
            }
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $data = json_decode($response, true);
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            if ($result[1] != '200'){ $result[2] = $data['error']; }else{ $result[2] = null; }
            return $result;
        } 
    }

       
    function request_upload($url = null, $param = null, $type = 'null', $method = 'POST')
    {
        $curl = curl_init();

        $options = array(
            CURLOPT_URL => $this->url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
        );

        if ($method === 'POST' && is_array($param)) {
            // PHP 5.6+ aman pakai CURLFile
            // Jika CURLFile tidak dipakai, fallback ke string "@filepath" untuk PHP 5.4–5.5 (tidak direkomendasikan)
            foreach ($param as $key => $value) {
                if (is_string($value) && file_exists($value)) {
                    // Pastikan ini adalah path file
                    $param[$key] = new CURLFile($value, mime_content_type($value), basename($value));
                }
            }

            $options[CURLOPT_POSTFIELDS] = $param;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$type) {
            return $err ?: $response;
        } else {
            $data = json_decode($response, true);
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            $result[2] = ($result[1] != 200) ? (isset($data['error']) ? $data['error'] : 'Unknown Error') : null;
            return $result;
        }
    }

    
    function response($data, $status = 200){ 
       if ($this->input->server('REQUEST_METHOD') == 'OPTIONS'){ $status = 200; $data = null;}
       
         $this->output
          ->set_status_header($status)
          ->set_content_type('application/json', 'utf-8')
          ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ))
          ->_display();
          exit;  
    }
     
    function otentikasi($type=null){
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            // harus mencocokan decoded mobile no dengan log di database
            try{
              $decoded  = JWT::decode($jwt, 'aslibos', array('HS256'));
              if (!$type){
                return $this->clogin->valid($decoded->userid, $jwt);   
              }else{ return $decoded; }
            }
            catch (\Exception $e){ 
    //            $response = array('error' => 'Error token..!');
    //            return $this->response($response,401);
                return FALSE;
            }
        }else{ return TRUE; }
    }
    
    function get_decoded(){
        $jwt = $this->input->get_request_header('X-auth-token');
        if ($jwt != null){
         $decoded  = JWT::decode($jwt, 'aslibos', array('HS256'));
         return $decoded;
        }else{ return null; }
    }

}

/* End of file Property.php */