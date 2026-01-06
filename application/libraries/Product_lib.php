<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'product';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
   function get_last($company=0,$count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('publish', 1);
        $this->db->where('company', $company);
        $this->db->order_by('name', 'asc'); 
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
   }
   
   function cleaning(){
       return $this->db->truncate($this->tableName);
   }
   
   function get_by_otp($otp=0,$type=0){
      $this->db->select($this->field);
      $this->db->from($this->tableName); 
      $this->db->where('deleted', $this->deleted);
      if ($type == 0){ $this->db->where('token_qc', $otp); }
      elseif ($type == 1){ $this->db->where('token_top', $otp); }
      elseif ($type == 2){ $this->db->where('token_bot', $otp); }
      return $this->db->get()->row();
   }
    
}

/* End of file Property.php */