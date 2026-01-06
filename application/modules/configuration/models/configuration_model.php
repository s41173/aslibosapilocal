<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Configuration_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('admin');
        $this->tableName = 'property';
//        $this->field = $this->db->list_fields($this->tableName);
    }
    
    
    protected $table = 'property';
    protected $field = array('id' ,'name', 'address', 'phone1', 'phone2', 'email', 'billing_email', 'technical_email',
                             'cc_email', 'zip', 'account_name', 'account_no', 'bank', 'city', 'site_name', 'notif_url',
                             'meta_description', 'meta_keyword', 'logo', 'manager', 'accounting', 'url_upload', 'image_url', 'shipping_integration');
    protected $com;
            
    
    function get_last($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }

}

?>