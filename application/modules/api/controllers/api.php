<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends MX_Controller {

   public function __construct()
   {
        parent::__construct();

        $this->load->helper('date');
        $this->log = new Log_lib();
        $this->load->library('email');
        $this->login = new Login_lib();
        $this->com = new Components();
//        $this->com = $this->com->get_id('login');
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        $this->sample = new Sample_lib();

        $this->properti = $this->property->get();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 

        // Your own constructor code
   }

   protected $log,$login,$api,$acl,$holding;
   private $properti,$com;
   private $error = null;
   private $status = 200;
   private $output = null;
   protected $sample;
              
   function index(){}   
   
   function barcode($code=0){
       $res = $this->sample->get_by_code($code);
       if ($res != FALSE){ $this->output = $res; }
       else{ $this->reject("Code Not Found",404); }
       $this->response('c');
   }
   
   function otentikasi($title=null,$type=null){
       
        if ($this->api->otentikasi() == TRUE){    
          if ($title != null && $this->com->valid($title) != FALSE){  
            $res = FALSE;  
            
            if ($type == null || $type == 1){$res = $this->acl->otentikasi1($title);}
            elseif ($type == 2){$res = $this->acl->otentikasi2($title);}
            elseif ($type == 3){$res = $this->acl->otentikasi3($title);}
            elseif ($type == 4){$res = $this->acl->otentikasi4($title);}

            if ($res == FALSE){ $this->error = 'Invalid Authentication'; $this->status = 401; }
            
          }else{ $this->error = 'Component title required'; $this->status = 404; }  
        }else{ $this->error = 'Invalid Token or Expired..!'; $this->status = 400; }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
   }
    
    function contact(){
        
       $datax = (array)json_decode(file_get_contents('php://input')); 
       
       $this->load->library('email');
       $this->email->from($datax['email'], $datax['name']);
       $this->email->to($this->properti['email']);  
       $this->email->subject('Contact Message : '.$datax['name']);
       $this->email->message($datax['message']);	

       if ($this->email->send()){ $stts = 'true'; }else{ $stts = (string)$this->email->print_debugger(); }

       $response = array('status' => $stts);
       $this->output
        ->set_status_header(201)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($response, 128))
        ->_display();
        exit;
   }
    
    function send_email($username)
    {
        $email = $this->Login_model->get_email($username);
        $pass = $this->Login_model->get_pass($username);
        $mess = "
          ".$this->properti['name']." - ".base_url()."
          FORGOT PASSWORD :

          Your Username is: ".$username."
          Your Password : ".$pass." <hr />
Your password for this account has been recovered . You don�t need to do anything, this message is simply a notification to protect the security of your account.
Please note: your password may take awhile to activate. If it doesn�t work on your first try, please try it again later
DO NOT REPLY TO THIS MESSAGE. For further help or to contact support, please email to ".$this->properti['email']."
****************************************************************************************************************** ";

        $params = array($this->properti['email'], $this->properti['name'], $email, 'Password Recovery', $mess, 'text');
        $se = $this->load->library('send_email',$params);

        if ( $se->send_process() == TRUE ){ return TRUE; }else { return FALSE; }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */