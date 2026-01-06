<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Camera_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'camera';
        $this->field = $this->db->list_fields($this->tableName);
    }

    protected $field;   
    
    function set($type=0){
       $trans = array('status' => $type); 
       $this->db->where('id', 1);
       return $this->db->update($this->tableName, $trans); 
    }
    
    
    function status()
    {
       $val = $this->db->get($this->tableName)->row(); 
       return $val->status;
    }


}

/* End of file Property.php */