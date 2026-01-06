<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Property extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        // Do something with $params
        $this->deleted = $deleted;
        $this->tableName = 'property';
        $this->field = $this->db->list_fields($this->tableName);
    }

    protected $field;   

    public function get()
    {
//        $this->db->select('id,name,address,phone1,phone2,email,billing_email,technical_email, cc_email, zip,account_name,account_no,bank,city,site_name,meta_description,meta_keyword');
        $res = $this->db->get($this->tableName)->row();
        if (!$res->url_upload){ $urlupload = './'; }else{ $urlupload = $res->url_upload; }
        if (!$res->image_url){ $imageurl = base_url().'images/'; }else{ $imageurl = $res->image_url; }
        $val = array('name' => $res->name, 
                     'company_id' => $res->company_id,
                     'division_id' => $res->division_id,
                     'division_code' => $res->division_code,
                     'url_upload'=>$urlupload, 'image_url'=>$imageurl,
                     'web_image_url'=>$res->web_image_url,
                     'otpserver' => $res->otpserver
                    );
        return $val;
    }
    
    function valid_otp($otp=0){
      $this->db->select($this->field);
      $this->db->where('otpserver', $otp);
      $res = $this->db->get($this->tableName);
      if ($res->num_rows() > 0){ 
          return $res->row();
          
      }else{ return FALSE; }
    }
    
}

/* End of file Property.php */