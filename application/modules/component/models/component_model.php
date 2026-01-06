<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Component_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = 0;
        $this->tableName = 'modul';
        
    }
    
    protected $field = array('id', 'name', 'title', 'publish', 'status', 'aktif', 'limit', 'role', 'icon', 'order', 'closing',
                              'table_name', 'created', 'updated', 'deleted');
    protected $com;   

    function get_last($limit, $offset=null, $count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('name', 'asc'); 
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function search($publish=null,$status=null,$active=null, $count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_nol($publish, 'publish');
        $this->cek_nol($status, 'status');
        $this->cek_nol($active, 'aktif');
        $this->db->order_by('name', 'asc'); 
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }    
    
    function get_closing_modul()
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('closing', 1);
        $this->db->order_by('name', 'asc'); 
        return $this->db->get(); 
    }
    
    function get_by_name($name)
    {
        $this->db->select($this->field);
        $this->db->where('name', $name);
        return $this->db->get($this->tableName);
    }
   
}

?>