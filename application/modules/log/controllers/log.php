<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Log extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Log_model', 'model', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->user = new Admin_lib();
        $this->com = new Components();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        $this->decoded = $this->api->otentikasi('decoded');
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $api, $acl, $decoded;
    private $user,$com;
     
    function index()
    {
       if ($this->acl->otentikasi_admin() == TRUE){ 
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        
        $logid = null; $user = null; $activity = null; $modul = null; $date = null;
        if (isset($datax['logid'])){ $logid = $datax['logid']; }
        if (isset($datax['user'])){ $user = $datax['user']; }
        if (isset($datax['activity'])){ $activity = $datax['activity']; }
        if (isset($datax['modul'])){ $modul = $datax['modul']; }
        if (isset($datax['date'])){ $date = $datax['date']; }
        
        if ($logid != null){ $result = $this->model->search($logid)->result(); }
        else{ $result = $this->model->search($logid,$user,$activity,$modul,$date, $this->limitx, $this->offsetx)->result(); 
           $this->count = $this->model->search($logid,$user,$activity,$modul,$date, $this->limitx, $this->offsetx,1);
        }
        
         foreach($result as $res)
	 {
            $this->resx[] = array ("id"=>$res->id, "user"=>$this->user->get_username($res->userid), "date"=>tglin($res->date), 
                                     "time"=>$res->time, "component"=>$this->com->get_name($res->component_id), "activity"=>$res->activity,
                                     "field"=>$res->field, "desc"=>$res->description, "pre_val"=>$res->prev_val,
                                     "created"=>$res->created, "updated"=>$res->updated, "deleted"=>$res->deleted
                             );
	 } 
         
        $data['record'] = $this->count; 
        $data['result'] = $this->resx;
        $this->output = $data;
         
       }else{ $this->reject_token(); }
       $this->response('content');
    }
    
    function delete_all()
    {
      $this->acl->otentikasi_admin($this->title);
      
      $cek = $this->input->post('cek');
      $jumlah = count($cek);

      if($cek)
      {
        $jumlah = count($cek);
        $x = 0;
        for ($i=0; $i<$jumlah; $i++)
        {
           $this->model->delete($cek[$i]);
           $x=$x+1;
        }
        $res = intval($jumlah-$x);
        //$this->session->set_flashdata('message', "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!");
        $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
        echo 'true|'.$mess;
      }
      else
      { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
        $mess = "No $this->title Selected..!!";
        echo 'false|'.$mess;
      }
   //   redirect($this->title);
    }

    function delete($uid)
    {
       if ($this->acl->otentikasi_admin() == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
         if ($this->model->delete($uid) == true){ $this->error = "$this->title successfully removed..!"; }else{ $this->reject('Failed to deleted');}         
       }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response();
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
       if ($this->acl->otentikasi_admin() == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){  
        $res = $this->model->get_by_id($uid)->row();
        $this->output = array ("id"=>$res->id, "user"=>$this->user->get_username($res->userid), "date"=>tglin($res->date), 
                                     "time"=>$res->time, "component"=>$this->com->get_name($res->component_id), "activity"=>$res->activity,
                                     "field"=>$res->field, "desc"=>$res->description, "pre_val"=>$res->prev_val,
                                     "created"=>$res->created, "updated"=>$res->updated, "deleted"=>$res->deleted
                             );   
	
        }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response('content');
    }

    function valid_username()
    {
        $uname = $this->input->post('tusername');
        
        if ($this->model->valid_name($uname) == FALSE)
        {
            $this->form_validation->set_message('valid_username', 'This user is already registered.!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_username($name)
    {
	$id = $this->session->userdata('langid');
	if ($this->model->validation_username($name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_username', 'This user is already registered!');
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function report()
    {
       if ($this->acl->otentikasi_admin() == TRUE){  
        $data['title'] = $this->properti['name'].' | Report '.ucwords($this->modul['title']);

        $user  = $this->input->post('cuser');
        $modul = $this->input->post('ccom');
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['user'] = $this->user->get_username($user);
        $data['modul'] = $this->com->get_name($modul);
        $data['rundate'] = tglin(date('Y-m-d'));
        $data['log'] = $this->decoded->log;

//        Property Details
        $data['company'] = $this->properti['name'];
        $result = null;
        foreach ($this->model->report($user,$modul,$start,$end)->result() as $res) {
            $result[] = array ("id"=>$res->id, "user"=>$this->user->get_username($res->userid), "date"=>tglin($res->date), 
                                     "time"=>$res->time, "component"=>$this->com->get_name($res->component_id), "activity"=>$res->activity,
                                     "field"=>$res->field, "desc"=>$res->description, "pre_val"=>$res->prev_val,
                                     "created"=>$res->created, "updated"=>$res->updated, "deleted"=>$res->deleted
                             );   
        }
        $data['result'] = $result;
        $this->output = $data;
       }else{ $this->reject_token(); }
       $this->response('content');
    }
    
            
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); } 


}

?>