<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Component extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Component_model', '', TRUE);
        $this->properti = $this->property->get();

        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->component = new Components();
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $acl;
    private $role, $component, $api;
    var $limit = 1000;

    function index()
    {
        if ($this->acl->otentikasi_admin() == TRUE){
           
            $datax = (array)json_decode(file_get_contents('php://input')); 
            
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
            
            if (!isset($datax['publish']) && !isset($datax['status']) && !isset($datax['active'])){
                $result = $this->Component_model->get_last($this->limit, $this->offsetx)->result();
                $this->count = $this->Component_model->get_last($this->limit, $this->offsetx,1);
            }else{ 
                $result = $this->Component_model->search($datax['publish'],$datax['status'],$datax['active'])->result(); 
                $this->count = $this->Component_model->search($datax['publish'],$datax['status'],$datax['active'],1); 
            }
            
            foreach($result as $res){

                $this->resx[] = array ("id" => $res->id, "name" => $res->name, "title" => $res->title, "publish" => $res->publish, "status" => $res->status, "active" => $res->aktif,
                                   "limit" => $res->limit, "role" => $res->role, "icon" => $res->icon, "order" => $res->order, "closing" => $res->closing,
                                   "created" => $res->created, "updated" => $res->updated, "deleted" => $res->deleted
                                  );
            }
            
           $data['record'] = $this->count; 
           $data['result'] = $this->resx;
           $this->output = $data;
           
        }else{ $this->reject_token(); }
        $this->response('c');
    }
         
    function reset()
    {
       if ($this->acl->otentikasi_admin() == TRUE){
           
            // start transaction 
           $this->db->trans_start();
           
           $result = $this->Component_model->get_closing_modul()->result();
           foreach ($result as $res) { $this->truncate($res->table_name);}
           
           if ($this->db->trans_status() === FALSE){ $this->error = "reset function error..!"; $this->status = 403;  }
           else { $this->error = "reset confirmed..!"; }
           
       }else{ $this->reject_token(); }
       $this->response();
    }
    
    private function truncate($val=null)
    {
        if ($val && $val != ''){
            $input = explode(",", $val);
            for($i=0; $i < count($input); $i++){ $this->db->truncate($input[$i]); } return TRUE;
        }else{ return FALSE; }
    }
    
    function closing($uid = null)
    {
       if ($this->acl->otentikasi_admin() == TRUE && $this->Component_model->valid_add_trans($uid, $this->title) == TRUE){
        $val = $this->Component_model->get_by_id($uid)->row();
        if ($val->closing == 0){ $lng = array('closing' => 1); }else { $lng = array('closing' => 0); }
         $this->Component_model->update($uid,$lng);
         $this->error = 'true|Status Changed...!';
       }else{ $this->valid_404($this->Component_model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response();
    }
    
    function delete_all()
    {
      if ($this->acl->otentikasi_admin() == TRUE){
      
        $cek = $this->input->post('cek');
        $jumlah = count($cek);

        if($cek)
        {
          $jumlah = count($cek);
          $x = 0;
          for ($i=0; $i<$jumlah; $i++)
          {
             $this->Component_model->force_delete($cek[$i]);
             $x=$x+1;
          }
          $res = intval($jumlah-$x);
          //$this->session->set_flashdata('message', "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!");
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          $this->error = $mess;
        }
        else
        { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
          $mess = "No $this->title Selected..!!";
          $this->error = $mess; $this->status = 401;
        }
      }else{ $this->reject_token(); }
      $this->api->response(array('error' => $this->error), $this->status);
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi_admin() == TRUE && $this->Component_model->valid_add_trans($uid, $this->title) == TRUE){
            $this->Component_model->force_delete($uid);
            $this->error = $this->title." successfully removed..!";
        }else{ $this->valid_404($this->Component_model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
        $this->response();
    }

    function add()
    {
        if ($this->acl->otentikasi_admin() == TRUE){

            // Form validation
            $this->form_validation->set_rules('tname', 'Modul Name', 'required|maxlength[50]|callback_valid_modul');
            $this->form_validation->set_rules('ttitle', 'Modul Title', 'required|maxlength[50]');
            $this->form_validation->set_rules('rpublish', 'Publish', 'required');
            $this->form_validation->set_rules('cstatus', 'Status', 'required');
            $this->form_validation->set_rules('raktif', 'Active', 'required');
            $this->form_validation->set_rules('tlimit', 'Limit', 'required');
            $this->form_validation->set_rules('torder', 'Order', 'required|numeric');
            $this->form_validation->set_rules('crole', 'Role', 'required|callback_valid_role');
            $this->form_validation->set_rules('ctable', 'Table', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {  
                $config['upload_path']   = './images/component/';
                $config['file_name']     = $this->input->post('tname');
                $config['allowed_types'] = 'png|jpg';
                $config['overwrite']     = TRUE;
                $config['max_size']	 = '1000';
                $config['max_width']     = '1000';
                $config['max_height']    = '1000';
                $config['remove_spaces'] = TRUE;
                
                $this->load->library('upload', $config);

            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $data['error'] = $this->upload->display_errors();
                $component = array('name' => $this->input->post('tname'), 'title' => $this->input->post('ttitle'),
                                   'publish' => $this->input->post('rpublish'), 'status' => $this->input->post('cstatus'),
                                   'aktif' => $this->input->post('raktif'), 'limit' => $this->input->post('tlimit'),
                                   'role' => $this->split_array($this->input->post('crole')), 'order' => $this->input->post('torder'),
                                   'table_name' => $this->split_array($this->input->post('ctable')),
                                   'icon' => 'default.png', 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                $component = array('name' => $this->input->post('tname'), 'title' => $this->input->post('ttitle'),
                                   'publish' => $this->input->post('rpublish'), 'status' => $this->input->post('cstatus'),
                                   'aktif' => $this->input->post('raktif'), 'limit' => $this->input->post('tlimit'),
                                   'role' => $this->split_array($this->input->post('crole')), 'order' => $this->input->post('torder'),
                                   'table_name' => $this->split_array($this->input->post('ctable')), 'icon' => $info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }

                $this->Component_model->add($component);
                if ($this->upload->display_errors()){ $this->error = $this->upload->display_errors(); $this->output = $this->Component_model->get_latest(); }
                else { $this->output = $this->Component_model->get_latest(); }
            }
            else{ $this->reject(validation_errors(),400); }
        }
        else { $this->reject_token(); }
        $this->response('c');
    }
    
    private function split_array($val)
    { return implode(",",$val); }
    
    function remove_img($id)
    {
        $img = $this->Component_model->get_by_id($id)->row();
        $img = $img->icon;
        if ($img){ $img = "./images/component/".$img; unlink("$img"); }
    }

    function get($uid=null)
    {  
       if ($this->api->otentikasi() == TRUE){ 
        if ($uid){
            $this->output = $this->Component_model->get_by_id($uid)->row_array();    
        }else{ $this->error = 'Parameter Required'; $this->status = 404; }
       }else { $this->reject_token(); }
       $this->response('c');
    }
    
    function get_by_name($name=null)
    {  
       if ($this->api->otentikasi() == TRUE){ 
        if ($name){
            $this->output = $this->Component_model->get_by_name($name)->row_array();    
        }else{ $this->error = 'Parameter Required'; $this->status = 404; }
       }else { $this->reject_token(); }
       $this->response('c');
    }

    function valid_role($val)
    {
        if(!$val)
        {
          $this->form_validation->set_message('valid_role', "role type required.");
          return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_table($val)
    {
        if(!$val)
        {
          $this->form_validation->set_message('valid_table', "table type required.");
          return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_modul($val)
    {
        if ($this->Component_model->valid('name',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_modul', $this->title.' registered');
            return FALSE;
        }
        else {  return TRUE; }
    }

    function validating_component($val,$id)
    {
	if ($this->Component_model->validating('name',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_component', "This $this->title name is already registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi_admin() == TRUE && $this->Component_model->valid_add_trans($uid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'Modul Name', 'required|maxlength[50]|callback_validating_component['.$uid.']');
        $this->form_validation->set_rules('ttitle', 'Modul Title', 'required|maxlength[50]');
        $this->form_validation->set_rules('rpublish', 'Publish', 'required');
        $this->form_validation->set_rules('cstatus', 'Status', 'required');
        $this->form_validation->set_rules('raktif', 'Active', 'required');
        $this->form_validation->set_rules('tlimit', 'Limit', 'required');
        $this->form_validation->set_rules('torder', 'Order', 'required|numeric');
        $this->form_validation->set_rules('crole', 'Active', 'required|callback_valid_role');
        $this->form_validation->set_rules('ctable', 'Table', 'required|callback_valid_table');

        if ($this->form_validation->run($this) == TRUE && isset($uid))
        {
            $config['upload_path']   = './images/component/';
            $config['file_name']     = $this->input->post('tname');
            $config['allowed_types'] = 'png|jpg';
            $config['overwrite']     = TRUE;
            $config['max_size']	     = '1500';
            $config['max_width']     = '1000';
            $config['max_height']    = '1000';
            $config['remove_spaces'] = TRUE;
            
            $this->load->library('upload', $config);
            
            if ( !$this->upload->do_upload("userfile"))
            {
                $data['error'] = $this->upload->display_errors();
                $component = array('name' => $this->input->post('tname'), 'title' => $this->input->post('ttitle'),
                                   'publish' => $this->input->post('rpublish'), 'status' => $this->input->post('cstatus'),
                                   'aktif' => $this->input->post('raktif'), 'limit' => $this->input->post('tlimit'),
                                   'table_name' => $this->split_array($this->input->post('ctable')),
                                   'role' => $this->split_array($this->input->post('crole')), 'order' => $this->input->post('torder'));
            }
            else
            {
                $info = $this->upload->data();
                $component = array('name' => $this->input->post('tname'), 'title' => $this->input->post('ttitle'),
                                   'publish' => $this->input->post('rpublish'), 'status' => $this->input->post('cstatus'),
                                   'aktif' => $this->input->post('raktif'), 'limit' => $this->input->post('tlimit'),
                                   'table_name' => $this->split_array($this->input->post('ctable')),
                                   'role' => $this->split_array($this->input->post('crole')), 'order' => $this->input->post('torder'),
                                   'icon' => $info['file_name']);
            }

	    $this->Component_model->update($uid, $component);
            if ($this->upload->display_errors()){ $this->error = $this->upload->display_errors(); }
            else { $this->error = 'true|Data successfully saved..!|'.base_url().'images/component/'.$info['file_name']; }

        }
        else{ $this->reject(validation_errors(),400); }
        }else { $this->valid_404($this->Component_model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
        $this->response();
    }

}

?>