<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends Custom_Model
{
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('admin');
        $this->tableName = 'user';
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $field,$com,$logs;
            
   
    function get_last_user($limit, $offset=null, $count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('name', 'asc'); 
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function login($username,$password){
        
        $this->db->select($this->field);
        $this->db->where('username', $username);
        $this->db->where('password', $username);
        $this->db->where('deleted', $this->deleted);
        $this->db->where('status', 1);
        $res = $this->db->get($this->tableName)->num_rows();
        if ($res > 0){ return TRUE; }else{ return FALSE; }
    }
    
    function get_by_username($username=null){
        
        $this->db->select($this->field);
        $this->db->where('username', $username);
        return $this->db->get($this->tableName);
    }

}

?>