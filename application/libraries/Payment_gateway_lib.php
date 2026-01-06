<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_gateway_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->property = new Property();
        $this->property = $this->property->get();
        $this->deleted = $deleted;
        $this->tableName = 'payment_gateway';
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('sales');
        $this->api = new Api_lib();
        $this->field = $this->db->list_fields($this->tableName);
        $this->url = trim($this->property['pg_url']);
        $this->user = trim($this->property['pg_token']);
//        $this->url = "https://api.xendit.co/v2/";
//        $this->user = "xnd_development_IlslGaTrNIeUJvRPgTK6Meg5vD2G03nBKLQLGlt6tcVQSzZnMMAv87AWREcZm";
        $this->pass = null;
    }

    // using xendit
    private $api,$url,$user,$pass,$property;
    protected $field;
    
    function request($urltype=null,$param=null,$type=null,$method='GET')
    {   
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url.$urltype,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Basic '. base64_encode("$this->user:$this->pass")
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
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            return $result;
        } 
    }

}

/* End of file Property.php */
