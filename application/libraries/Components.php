<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Components {

    public function __construct($params=null)
    {
        // Do something with $params
        $this->tableName = 'modul';
        $this->ci = & get_instance();
    }
    
    private $ci,$tableName;

    public function get($name = null)
    {
        $this->ci->db->where('name', $name);
        $res = $this->ci->db->get($this->tableName)->row();
        $val = array('id' => $res->id, 'name' => $res->name, 'title' => $res->title, 'table' => $res->table_name, 'limit' => $res->limit, 'publish' => $res->publish,
                     'status' => $res->status,'aktif' => $res->aktif, 'role' => $res->role, 'icon' => $res->icon, 'order' => $res->order
                    );
        return $val;
    }
    
    public function get_name($id = null)
    {
        $this->ci->db->where('id', $id);
        $res = $this->ci->db->get($this->tableName)->row();
        if ($res){ return $res->name; }
    }
    
    public function get_title($name = null)
    {
        $this->ci->db->where('name', $name);
        $res = $this->ci->db->get($this->tableName)->row();
        if ($res){ return $res->title; }
    }
    
    public function get_id($name = null)
    {
        $this->ci->db->where('name', $name);
        $res = $this->ci->db->get($this->tableName)->row();
        if ($res){ return $res->id; }
    }
    
    public function valid($name = null)
    {
        $this->ci->db->where('name', $name);
        $res = $this->ci->db->get($this->tableName)->num_rows();
        if ($res>0){ return TRUE; }else{ return FALSE; }
    }
    
    public function get_table($id = null)
    {
        $this->ci->db->where('id', $id);
        $res = $this->ci->db->get($this->tableName)->row();
        if ($res){ return $res->table_name; }
    }

    function combo()
    {
        $this->ci->db->select('name');
        $this->ci->db->where('aktif', 'Y');
        $val = $this->ci->db->get($this->tableName)->result();
        foreach($val as $row){$data['options'][$row->name] = $row->name;}
        return $data;
    }
    
    function combo_table()
    {
        $tables = $this->ci->db->list_tables();
        foreach($tables as $row){$data['options'][$row] = ucfirst($row);}
        return $data;
    }
    
    function combo_id()
    {
        $this->ci->db->select('id,name');
        $this->ci->db->where('aktif', 'Y');
        $this->ci->db->order_by('name','asc');
        $val = $this->ci->db->get($this->tableName)->result();
        foreach($val as $row){$data['options'][$row->id] = ucfirst($row->name);}
        return $data;
    }
    
    function combo_id_all()
    {
        $this->ci->db->select('id,name');
        $this->ci->db->where('aktif', 'Y');
        $this->ci->db->order_by('name','asc');
        $val = $this->ci->db->get($this->tableName)->result();
        
        $data['options'][''] = '-- Select --';
        
        foreach($val as $row){$data['options'][$row->id] = ucfirst($row->name);}
        return $data;
    }
    
    function get_closing_aktif()
    {
       $this->ci->db->select('name,table');
       $this->ci->db->where('aktif', 'Y'); 
       $this->ci->db->where('closing', '1'); 
       return $this->ci->db->get($this->tableName)->result();
    }
}

/* End of file Property.php */