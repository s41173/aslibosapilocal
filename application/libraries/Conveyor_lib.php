<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Conveyor_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'conveyor';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
   function get_last($division=0,$count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('status', 1);
        $this->db->where('division', $division);
        $this->db->order_by('name', 'asc'); 
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
   }
   
   function cleaning(){
       return $this->db->truncate($this->tableName);
   }
   
   
   function get_by_otp($otp=0,$type=0,$count=0){
      $this->db->select($this->field);
      $this->db->from($this->tableName); 
      $this->db->where('deleted', $this->deleted);
      if ($type == 0){ $this->db->where('token_qc', $otp); }
      elseif ($type == 1){ $this->db->where('token_top', $otp); }
      elseif ($type == 2){ $this->db->where('token_bot', $otp); }
      if ($count == 0){ return $this->db->get()->row(); }else{ return $this->db->get()->num_rows(); }
   }
    
}

/* End of file Property.php */