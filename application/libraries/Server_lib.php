<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Server_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'local_server';
        $this->field = $this->db->list_fields($this->tableName);
    }
   protected $field;
   
   private function cek_by_otp($otp=0){
      $this->db->select($this->field);
      $this->db->from($this->tableName); 
      $this->db->where('deleted', $this->deleted);
      $this->db->where('pin', $otp);
      $this->db->where('status', 1);
      $val = $this->db->get()->num_rows(); 
      if ($val > 0){ return TRUE; }else{ return FALSE; }
   }
   
   function get_by_otp($otp=0){
      if ($this->cek_by_otp($otp) == TRUE){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('pin', $otp);
        return $this->db->get()->row();    
      }else{ return FALSE; }
   }
    
}

/* End of file Property.php */