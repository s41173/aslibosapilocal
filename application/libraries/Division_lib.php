<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Division_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'division';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
    
    function get_last($company=0,$count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('status', 1);
        $this->db->where('company', $company);
        $this->db->order_by('created', 'asc'); 
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
}

/* End of file Property.php */