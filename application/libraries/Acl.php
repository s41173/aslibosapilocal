<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Acl extends Custom_Model {

    public function __construct()
    {
        // Do something with $params
//        $this->login = new Login_lib();
//        $this->admin = new Admin_lib();
//        $this->api = new Api_lib();
    }

    private $ci,$login,$admin,$api;

    function otentikasi1($title)
    {
        if ($this->api->otentikasi() == TRUE){
            
            $decoded = $this->api->motentikasi('decoded');
            
            $this->db->select('id, name, publish, status, aktif, limit, role');
            $this->db->where('name', $title);
            $mod = $this->db->get('modul')->row();

            $mod = $mod->role;
            $mod = explode(",", $mod);

            foreach ($mod as $row) { if ($row == $decoded->role) {$val = TRUE; break;} else {$val = FALSE;} }
            if ($val != TRUE){ return FALSE; } else {return TRUE;}
            
        }else{ return FALSE; }
    }
    
    function otentikasi2($title=null)
    {
        if ($this->api->otentikasi() == TRUE){
            
            $decoded = $this->api->motentikasi('decoded');
            $this->db->select('id, name, publish, status, aktif, limit, role');
            $this->db->where('name', $title);
            $mod = $this->db->get('modul')->row();

            $mod = $mod->role;
            $mod = explode(",", $mod);

            foreach ($mod as $row){ if ($row == $decoded->role) {$val = TRUE; break;} else {$val = FALSE;} }
            if ($val != TRUE || $decoded->rules == 1 || $decoded->rules == 4){ return FALSE;}else {return TRUE;}
            
        }else{ return FALSE; }
    }

    function otentikasi3($title=null)
    {
        if ($this->api->otentikasi() == TRUE){
            
            $decoded = $this->api->motentikasi('decoded');
            $this->db->select('id, name, publish, status, aktif, limit, role');
            $this->db->where('name', $title);
            $mod = $this->db->get('modul')->row();

            $mod = $mod->role;
            $mod = explode(",", $mod);

            foreach ($mod as $row){ if ($row == $decoded->role) {$val = TRUE; break;} else {$val = FALSE;} }
            if ($val != TRUE || $decoded->rules != 3){ return FALSE;}else {return TRUE;}
            
        }else{ return FALSE; }
    }

    function otentikasi4($title)
    {
        if ($this->api->otentikasi() == TRUE){
            
            $decoded = $this->api->motentikasi('decoded');
            $this->ci->db->select('id, name, publish, status, aktif, limit, role');
            $this->ci->db->where('name', $title);
            $mod = $this->ci->db->get('modul')->row();

            $mod = $mod->role;
            $mod = explode(",", $mod);

            foreach ($mod as $row){ if ($row == $decoded->role) {$val = TRUE; break;} else {$val = FALSE;} }
            if ($val != TRUE || $decoded->rules == 1 || $decoded->rules == 2 ){ return FALSE; }else {return TRUE;}
        }else{ return FALSE; }
    }

    function otentikasi_admin()
    {
        if ($this->api->motentikasi() == TRUE){
          $decoded = $this->api->motentikasi('decoded');
          if ($decoded->rules != 3){ return FALSE; }else{ return TRUE; }  
        }else{ return FALSE; }
    }
}

/* End of file Property.php */