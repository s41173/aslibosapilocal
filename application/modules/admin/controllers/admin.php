<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Admin extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Admin_model', 'model', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->city = new City_lib();
        $this->role = new Role_lib();
        $this->branch = new Branch_lib();
        $this->login = new Login_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title,$api,$acl;
    private $city,$role,$branch,$login;
     
    public function index()
    {
        if ($this->acl->otentikasi_admin() == TRUE){
            
          $datax = (array)json_decode(file_get_contents('php://input')); 
          if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
          if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; } 
            
          $this->resx = $this->model->get_last_user($this->limitx, $this->offsetx)->result(); 
          $this->count  = $this->model->get_last_user($this->limitx, $this->offsetx,1); 
          
          $data['record'] = $this->count; 
          $data['result'] = $this->resx;
          $this->output = $data;
          
        }else{ $this->reject_token(); }
        $this->response('content');
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
             $this->model->delete($cek[$i]);
             $x=$x+1;
          }
          $res = intval($jumlah-$x);
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          $this->error = $mess;
        }
        else
        { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
          $mess = "No $this->title Selected..!!";
          $this->reject($mess);
        }
      }else{ $this->reject_token(); }
      $this->response();
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi_admin() == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
          if ($this->model->force_delete($uid) == true){ $this->error = "$this->title successfully removed..!"; }else{ $this->reject('Failed to deleted');}         
        }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
        $this->response();
    }

    function add()
    {
        if ($this->acl->otentikasi_admin() == TRUE){

            // Form validation
            $this->form_validation->set_rules('tusername', 'UserName', 'required|callback_valid_username');
            $this->form_validation->set_rules('tpassword', 'Password', 'required');
            $this->form_validation->set_rules('tname', 'Name', 'required');
            $this->form_validation->set_rules('taddress', 'Address', 'required');
            $this->form_validation->set_rules('tphone', 'Phone', 'required|numeric');
            $this->form_validation->set_rules('ccity', 'City', 'required');
            $this->form_validation->set_rules('tmail', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('crole', 'Role', 'required');
            $this->form_validation->set_rules('tid', 'Yahoo Id', '');
            $this->form_validation->set_rules('rstatus', 'Status', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {
                $users = array('username' => $this->input->post('tusername'),'password' => $this->input->post('tpassword'),'name' => $this->input->post('tname'),
                               'address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone'), 'city' => $this->input->post('ccity'),
                               'email' => $this->input->post('tmail'), 'yahooid' => setnull($this->input->post('tid')), 'role' => $this->input->post('crole'), 
                               'branch_id' => $this->input->post('cbranch'), 'status' => $this->input->post('rstatus'), 'created' => date('Y-m-d H:i:s'));

                if ($this->model->add($users) != true){ $this->error = $this->reject('failed to post');
                }else{ $this->model->log('create'); $this->output = $this->model->get_latest(); }
            }
            else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response('c');
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get()
    {        
        if ($this->acl->otentikasi_admin() == TRUE){
            $decoded = $this->api->get_decoded('decoded');
            $this->output = $this->model->get_by_id($decoded->userid)->row();
        }else{ $this->reject_token(); }
        $this->response('content');
    }

    function valid_username()
    {
        $uname = $this->input->post('tusername');
        
        if ($this->model->valid('username',$uname) == FALSE)
        {
            $this->form_validation->set_message('valid_username', 'This user is already registered.!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_username($name,$id)
    {
	if ($this->model->validating('username',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_username', 'This user is already registered!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi_admin() == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 

	// Form validation
        $this->form_validation->set_rules('tusername', 'UserName', 'required|callback_validation_username['.$uid.']');
	$this->form_validation->set_rules('tpassword', 'Password', '');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('taddress', 'Address', 'required');
        $this->form_validation->set_rules('tphone', 'Phone', 'required|numeric');
        $this->form_validation->set_rules('ccity', 'City', 'required');
        $this->form_validation->set_rules('tmail', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('crole', 'Role', 'required');
        $this->form_validation->set_rules('rstatus', 'Status', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            if ($this->input->post('tpassword')){
            
              $users = array('username' => $this->input->post('tusername'),'password' => $this->input->post('tpassword'),'name' => $this->input->post('tname'),
                           'address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone'), 'city' => $this->input->post('ccity'),
                           'email' => $this->input->post('tmail'), 'yahooid' => setnull($this->input->post('tid')), 'role' => $this->input->post('crole'), 
                           'branch_id' => $this->input->post('cbranch'), 'status' => $this->input->post('rstatus'));     
            }
            else {
              $users = array('username' => $this->input->post('tusername'),'name' => $this->input->post('tname'),
                           'address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone'), 'city' => $this->input->post('ccity'),
                           'email' => $this->input->post('tmail'), 'yahooid' => setnull($this->input->post('tid')), 'role' => $this->input->post('crole'), 
                           'branch_id' => $this->input->post('cbranch'), 'status' => $this->input->post('rstatus'));
            }
            
            $this->login->reset_token($uid);
	    if ($this->model->update($uid,$users) != true){ $this->error = $this->reject('failed to post');
            }else{ $this->error = $this->title.' successfully saved..!'; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
        $this->response();
    }
    
    // ------ json login -------------------
    function login(){
        
        $datas = (array)json_decode(file_get_contents('php://input')); 
        $user = $datas['username'];
        $pass = $datas['password'];
        
        $logid = null;
        $token = null;
        

        $validuser = $this->model->login($user,$pass);
        if($validuser != TRUE){$this->reject('User Not Found..!',404);}
        else{
              $res = $this->model->get_by_username($user)->row(); 

              $logid = intval($this->log->max_log()+1);
              // $sms->send($user, $this->properti['name'].' : Login OTP Code : '.$logid);
              // $push->send_device($userid, $this->properti['name'].' : Kode OTP : '.$logid);
              
              
              $date = new DateTime();
              $rules = $this->role->get_by_name($res->role, 'rules');
              $payload['userid'] = $res->id;
              $payload['name'] = $res->name;
              $payload['username'] = $user;
              $payload['phone'] = $res->phone1;
              $payload['rules'] = $rules;
              $payload['log'] = $logid;
//              $payload['active'] = $res->status;
              $payload['iat'] = $date->getTimestamp();
//               $payload['exp'] = $date->getTimestamp() + 60*60*2;
              $token = JWT::encode($payload, 'vinkoo');
              
              
              $this->login->add($res->id, $logid, $token);
              $this->output = array('token' => $token, 'rules'=>$rules, 'log' => $logid, 'status' => intval($res->status), 'userid' => intval($res->id)); 

        }
        $this->response('c');
    }
    
    function decode_token(){
        if ($this->api->motentikasi() == TRUE  && $this->api->get_decoded() != null){
            $decoded = $this->api->otentikasi('decoded');
            $this->output = $decoded;
        }else{ $this->reject_token(); }
        $this->response('c');
    }
    
    function logout(){
        
        if ($this->api->motentikasi() == TRUE && $this->api->get_decoded() != null){

          $decoded = $this->api->otentikasi('decoded');
          $this->login->logout($decoded->userid);
          $this->date  = date('Y-m-d');
          $this->time  = waktuindo();
          $this->log->insert($decoded->userid, $this->date, $this->time, 'logout');
            
        }else{ $this->reject('Invalid Token or Expired..!',400); }
        $this->response();
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); } 

}

?>