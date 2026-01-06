<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Log_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('log'));
        $this->com = $this->com->get_id('log');
        $this->field = $this->db->list_fields($this->tableName);
        $this->user = new Admin_lib();
    }
    
    protected $field,$com,$user;
    
    function get_last_user($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }
    
    function search($logid=null,$user=null,$activity=null,$modul=null,$date=null,$limit=0,$offset=null,$count=0)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($logid, 'id');
        $this->cek_null($date, 'date'); 
        $this->cek_nol($user, 'userid');
        $this->cek_null($activity, 'activity');
        $this->cek_null($modul, 'component_id');
//        $this->db->limit($limit, $offset);
        $this->db->order_by('id', 'desc'); 
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        return $this->db->get(); 
    }
    
    function report($user=null,$modul=null,$start,$end)
    {
       $this->db->select($this->field);
       $this->db->from($this->tableName); 
       $this->db->where('deleted', $this->deleted);
       $this->between("log.date", $start, $end);
       $this->cek_null($user, 'userid');
       $this->cek_null($modul, 'component_id');
       $this->db->order_by('id', 'desc'); 
       return $this->db->get(); 
    }
    
    function combo_user()
    {
        $this->db->select($this->field);
        $this->db->where('deleted', $this->deleted);
        $this->db->distinct();
        if ($this->db->get($this->tableName)->num_rows()>0){
            $val = $this->db->get($this->tableName)->result();
            foreach($val as $row){$data['options'][$row->userid] = strtolower($this->user->get_username($row->userid));}
        }else{ $data['options'][''] = strtoupper('--'); }
        return $data;
    }
    
    function combo_activity()
    {
        $this->db->select($this->field);
        $this->db->where('deleted', $this->deleted);
        $this->db->distinct();
        if ($this->db->get($this->tableName)->num_rows()>0){
            $val = $this->db->get($this->tableName)->result();
            foreach($val as $row){$data['options'][$row->activity] = strtolower($row->activity);}
        }else{ $data['options'][''] = strtoupper('--'); }
        return $data;
    }

}

?>