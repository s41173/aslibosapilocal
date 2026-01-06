<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Device_login_lib extends Custom_Model
{
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'device_login_status';
    }
    
    public function login_qc($conveyor=0,$tokenqc=null)
    {
        $trans = array('conveyor_id' => $conveyor, 'qc_status' => 1, 'token_qc' => $tokenqc);
        if ($this->cek($conveyor) == TRUE){ return $this->db->insert($this->tableName, $trans); }
        else{ $this->edit($conveyor, $tokenqc); }
    }
    
    private function edit($conveyor,$tokenqc){
        $trans = array('qc_status' => 1, 'token_qc' => $tokenqc);
        $this->db->where('conveyor_id', $conveyor);
        $this->db->update($this->tableName, $trans);
    }
    
    private function cek($conveyor)
    {
        $this->db->where('conveyor_id', $conveyor);
        $num = $this->db->get($this->tableName)->num_rows();
        if ($num > 0){ return FALSE; }else { return TRUE; }
    }
    
    function logout_qc($conveyor=0){
        $trans = array('qc_status' => 0, 'token_top' => null, 'token_bot' => null, 'token_qc' => null);
        $this->db->where('conveyor_id', $conveyor);
        return $this->db->update($this->tableName, $trans);
    }
    
    function logout_scanner($conveyor=0,$position='top'){
        if ($position == 'top'){ $trans = array('token_top' => null);}
        elseif ($position == 'bot'){ $trans = array('token_bot' => null);}
        $this->db->where('conveyor_id', $conveyor);
        return $this->db->update($this->tableName, $trans);
    }
    
    function set_token($conveyor=0,$position,$token=null){
        if ($position == 'top'){ $trans = array('token_top' => $token); }
        elseif ($position == 'bot'){ $trans = array('token_bot' => $token); }
        elseif ($position == 'qc'){ $trans = array('token_qc' => $token); }
        $this->db->where('conveyor_id', $conveyor);
        $this->db->update($this->tableName, $trans);
    }
    
    function cek_login($conveyor=0){
       $this->db->where('conveyor_id', $conveyor);
       $val = $this->db->get($this->tableName)->row(); 
       if ($val->qc_status == 1){ return TRUE; }else{ return FALSE; }
    }
     
}


/* End of file Property.php */