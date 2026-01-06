<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_lib
{
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'login_status';
        $this->ci = & get_instance();
    }

    private $ci,$tableName,$deleted;
    
    public function add($user=0, $log=0, $token=null)
    {
        $trans = array('userid' => $user, 'log' => $log, 'token' => $token, 'joined' => date('Y-m-d H:i:s'));
        if ($this->cek($user) == TRUE){ $this->ci->db->insert($this->tableName, $trans); }
        else { $this->edit($user,$log,$token); }
    }

    private function cek($user)
    {
        $this->ci->db->where('userid', $user);
        $num = $this->ci->db->get($this->tableName)->num_rows();
        if ($num > 0){ return FALSE; }else { return TRUE; }
    }
    
    private function edit($user,$log,$token)
    {
        $trans = array('log' => $log, 'token' => $token, 'joined' => date('Y-m-d H:i:s'));
        $this->ci->db->where('userid', $user);
        $this->ci->db->update($this->tableName, $trans);
    }
    
    function reset_token($user){
        $trans = array('token' => null);
        $this->ci->db->where('userid', $user);
        $this->ci->db->update($this->tableName, $trans);
    }
    
    function valid($user,$log)
    {
       $this->ci->db->where('userid', $user);
       $this->ci->db->where('token', $log);
       $num = $this->ci->db->get($this->tableName)->num_rows(); 
       if ($num > 0){ return TRUE; }else { return FALSE; }
    }
    
    function logout($user){
        $trans = array('log' => null, 'token' => null, 'joined' => null);
        $this->ci->db->where('userid', $user);
        $this->ci->db->update($this->tableName, $trans);
    }
    
}


/* End of file Property.php */