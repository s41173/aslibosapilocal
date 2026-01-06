<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Quinos_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->property = new Property();
        $this->property = $this->property->get();
        $this->deleted = $deleted;
        $this->tableName = 'pos_transaction';
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('sales');
        $this->api = new Api_lib();
        $this->field = $this->db->list_fields($this->tableName);
        $this->url = trim($this->property['pos_url']);
        $this->user = trim($this->property['pos_token']);
        $this->pass = null;
        $this->membership = 'Basic';
    }

    // using xendit
    private $api,$url,$user,$pass,$property,$membership;
    protected $field;
    
    function register($data=null){
       $nilai = '{ "token":"'.$this->user.'", "customer":{ "code":"'.$data['code'].'", "name": "'.$data['name'].'", "email":"'.$data['email'].'", "membership":"'.$this->membership.'", '
               . ' "occupation":"'.$data['occupation'].'", "address":"'.$data['address'].'", "city":"'.$data['city'].'", "state":"'.$data['state'].'",'
               . ' "postcode":"'.$data['postcode'].'", "phone":"'.$data['phone'].'", "mobile":"'.$data['mobile'].'", "dob":"'.$data['dob'].'"}}'; 
       $code=0;
       $req = $this->request('customers/createOrUpdate',$nilai, null, 'POST');
       $result = json_decode($req, true); 
       if ($result != null){
           $code = $result['code'];
       }
       return $code;
    }
    
     function update_customer($data=null){
       $nilai = '{ "token":"'.$this->user.'", "customer":{ "code":"'.$data['code'].'", "name": "'.$data['name'].'", "email":"'.$data['email'].'", "membership":"'.$data['membership'].'", '
               . ' "occupation":"'.$data['occupation'].'", "address":"'.$data['address'].'", "city":"'.$data['city'].'", "state":"'.$data['state'].'",'
               . ' "postcode":"'.$data['postcode'].'", "phone":"'.$data['phone'].'", "mobile":"'.$data['mobile'].'", "dob":"'.$data['dob'].'"}}'; 
       $code=0;
       $req = $this->request('customers/createOrUpdate',$nilai, null, 'POST');
       $result = json_decode($req, true); 
       if ($result != null){
           $code = $result['code'];
       }
       return $code;
    }
    
    function request($urltype=null,$param=null,$type=null,$method='POST')
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
          'Content-Type: application/x-www-form-urlencoded',
          'X-Auth-Token: '.$this->user
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
