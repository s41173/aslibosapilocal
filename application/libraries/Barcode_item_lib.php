<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barcode_item_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'barcode_item';
        $this->field = $this->db->list_fields($this->tableName);
    }

    protected $field;   
    
    function cleaning(){
       $trans = array('used_top' => 0, 'used_bot' => 0);
       return $this->db->update($this->tableName, $trans); 
    }
    
    function cek_used($code=0,$position='top'){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('code', $code);
        if ($position == 'top'){ $this->db->where('used_top', 0); }
        elseif ($position == 'bot'){ $this->db->where('used_bot', 0); }
        
        $res = $this->db->get()->num_rows(); 
        if ($res > 0){ return TRUE; }else{ return FALSE; }
    }
    
    function get_by_code($code=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('code', $code);
        return $this->db->get()->row();
    }

    function cek($code=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('code', $code);
        $res = $this->db->get()->num_rows(); 
        if ($res > 0){ return FALSE; }else{ return TRUE; }
    }
    
    function fill($code=0,$sides=0,$created=null){
        if ($this->cek($code) == TRUE){
           $trans = array('code' => $code, 'sides' => $sides, 'used_top' => 0, 'used_bot' => 0, 'created' => $created);
           return $this->add($trans);
        }
    }
    
    function set_used($code,$position='top'){
       $type = $this->get_by_code($code);
       if ($type->sides == 1){
          if ($position == 'top'){ $trans = array('used_top' => 1); }
          elseif ($position == 'bot'){ $trans = array('used_bot' => 1); }
       }
       else{ $trans = array('used_top' => 1, 'used_bot' => 1); }
       $this->db->where('code', $code);
       return $this->db->update($this->tableName, $trans); 
    }
    
    function unset_used($code){
       $trans = array('used_top' => 0, 'used_bot' => 0); 
       $this->db->where('code', $code);
       return $this->db->update($this->tableName, $trans); 
    }


    function get_last($usedtop=null,$usedbot=null,$sides=null,$limit=50,$offset=0,$count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($usedtop, 'used_top');
        $this->cek_null($usedbot, 'used_bot');
        $this->cek_null($sides, 'sides');
        
        $this->db->limit($limit, $offset);
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }


}

/* End of file Property.php */