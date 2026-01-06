<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Control_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    var $table = 'controls';
    
    function get_id($no)
    {
        $this->db->select('account_id');
        $this->db->where('no', $no);
        $res = $this->db->get($this->table)->row();
        if ($res){ return $res->account_id; }
        else { return 0; }
    }

}

?>