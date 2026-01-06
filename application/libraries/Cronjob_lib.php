<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cronjob_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'cronjobtest';
        $this->field = $this->db->list_fields($this->tableName);
    }

    protected $field;   
    
    public function add()
    {
        $trans = array('created' => date('Y-m-d H:i:s'));
        if ($this->cek() == TRUE){ return $this->db->insert($this->tableName, $trans); }
        else{ $this->edit(); }
    }
    
    private function edit(){
        $trans = array('created' => date('Y-m-d H:i:s'));
        $this->db->where('id', 1);
        $this->db->update($this->tableName, $trans);
    }
    
    private function cek()
    {
        $num = $this->db->get($this->tableName)->num_rows();
        if ($num > 0){ return FALSE; }else { return TRUE; }
    }


}

/* End of file Property.php */