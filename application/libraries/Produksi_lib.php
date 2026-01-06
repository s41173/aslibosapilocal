<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Produksi_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'produksi';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
    function create($conveyor=0,$product=0)
    {
       $this->db->select($this->field); 
       $this->db->where('conveyor',$conveyor);
       $this->db->where('product',$product);
       $this->db->where('status',0);
       $query = $this->db->get($this->tableName)->num_rows();
       
       if ($query == 0){ 
          $trans = array('conveyor' => $conveyor, 'product' => $product,'created' => date('Y-m-d H:i:s'));
          return $this->db->insert($this->tableName, $trans); 
       }
    }
    
    private function finish_produksi($conveyor=0,$product=0){
       $trans = array('status' => 1);
       $this->db->where('conveyor',$conveyor);
       $this->db->where('product',$product);
       $this->db->where('status',0);
       return $this->db->update($this->tableName, $trans); 
    }
    
     function cek_produksi($conveyor=0,$product=0){
       $this->db->where('conveyor',$conveyor);
       $this->db->where('product',$product);
       $this->db->where('status',0);
       $query = $this->db->get($this->tableName)->num_rows();
       if ($query > 0){ $this->finish_produksi($conveyor, $product); return TRUE; }else{ return FALSE; }
    }
    
    function get_by_conveyor($conveyor=0,$type=0){
       $this->db->where('conveyor',$conveyor);
       $this->db->where('status',0); 
       $this->db->order_by('id', 'desc'); 
       $query = $this->db->get($this->tableName)->row();
       if ($type == 0){ return $query->product; }else{ return $query->id; }
       
    }
    
    function get_last($count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('created', 'asc'); 
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
   }
    
    
    
}

/* End of file Property.php */