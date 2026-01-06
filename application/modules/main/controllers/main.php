<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Main extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
//        $this->load->model('Main_model', 'model', TRUE);

        $this->properti = new Property();
        $this->title = strtolower(get_class($this));
        $this->api = new Api_lib();
//        $this->acl = new Acl();
        $this->barcode = new Barcode_item_lib();
        $this->camera = new Camera_lib();
        $this->devicelogin = new Device_login_lib();
        $this->result = new Result_lib();
        $this->conveyor = new Conveyor_lib();
        $this->product = new Product_lib();
        $this->produksi = new Produksi_lib();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 

    }

    private $properti, $title, $api, $devicelogin, $product;
    protected $sample,$camera,$barcode,$result,$conveyor,$produksi;

    function cronjobtest(){
        $cronjob = new Cronjob_lib();
        $cronjob->add();
    }
    
    function index(){
//        if ($this->api->check_api_server() == TRUE){
            $this->output = 'AsliBos API - Local : V1';
//        }else{ $this->reject("No Connection"); }
        $this->response('c');
    }
    
    function ping(){
        if ($this->api->check_api_server() == TRUE){
            $this->output = 'Connection Stable';
        }else{ $this->reject("No Connection"); }
        $this->response('c');
    }
    
    function configuration($otp=0){
        if ($this->api->check_api_server() == TRUE){
            $request = $this->api->request_get("main/barcode_list/".$otp);
            if ($request[1] == 200){
                $data = json_decode($request[0], true);
                $data = $data['content'];
                $configdata = array(
                        'name' => $data['company_name'],
                        'company_id' => $data['company'],
                        'division_id' => $data['division'],
                        'division_code' => $data['division_code'],
                        'otpserver' => $otp
                );
                if ($this->properti->update(1, $configdata) != true){ $this->reject(); }
                else{
                    $this->conveyor_fill($data['division']);
                    $this->product_fill($data['company']);
                    $this->barcode_fill($otp); 
                }

            }else{ 
                $this->reject($request[2],$request[1]);
            }
        }else{ $this->reject("No Connection"); }
        $this->response('c');
    }
    
    private function product_fill($company=0){
        $request = $this->api->request_get("main/product/".$company);
        if ($request[1] == 200){
            $data = json_decode($request[0], true);
            $data = $data['content']['result'];
            if ($this->product->cleaning() == true){ foreach ($data as $res) { $this->product->add($res);} }
        }else{ 
//            $this->reject($request[2],$request[1]);
        }
//        $this->response('c');
    }
    
    private function conveyor_fill($division=0){
        $request = $this->api->request_get("main/conveyor/".$division.'/1');
        if ($request[1] == 200){
            $data = json_decode($request[0], true);
            $data = $data['content']['result'];
            if ($this->conveyor->cleaning() == true){ foreach ($data as $res) { $this->conveyor->add($res);} }
        }else{ 
//            $this->reject($request[2],$request[1]);
        }
//        $this->response('c');
    }
    
    private function barcode_fill($otp=0){
        $request = $this->api->request_get("main/barcode_list/".$otp);
        $hasil = 0; $gagal=0;
        if ($request[1] == 200){
            $data = json_decode($request[0], true);
            $data = $data['content']['result'];
            foreach ($data as $res) {
                if ($this->barcode->fill($res['code'], $res['sides'], $res['created']) == TRUE){$hasil++;}else{ $gagal++; }
            }
            $this->output = $hasil." has inserted - ".$gagal." has failed to post";
        }else{ 
//            $this->reject($request[2],$request[1]);
        }
//        $this->response('c');
    }
    
    function product($company=0){
        $result = $this->product->get_last($company)->result();
        $data['record'] = $this->product->get_last($company,1);
        $data['result'] = $result;
        $this->output = $data;
        $this->response('c');
    }
    
    function produksi($company=0){
        
        $result = $this->produksi->get_last()->result();
        foreach ($result as $res) {
            $this->resx[] = array ("id" => $res->id, 
                                   "conveyor" => $this->conveyor->get_by_id($res->conveyor,'code'),
                                   "product" => $this->product->get_by_id($res->product,'name'),
                                   "status" => $res->status,
                                   "created" => tglin($res->created).' - '. timein($res->created)
                                  );
        }
        
        $data['record'] = $this->produksi->get_last(1);
        $data['result'] = $this->resx;
        $this->output = $data;
        $this->response('c');
    }
    
    // get from api
//    function product($company=0){
//        if ($this->api->check_api_server() == TRUE){
//            $request = $this->api->request_get("main/product/".$company);
//            $hasil = 0; $gagal=0;
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true);
//                $data = $data['content']['result'];
//                $this->output = $data;
//            }else{ 
//                $this->reject($request[2],$request[1]);
//            }
//        }else{ $this->reject("No Connection"); }    
//        $this->response('c');
//    }
    
     // ================= qc ===========================
    // inet cloud
//    function login_qc()
//    {
//        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
//        if ($this->form_validation->run() == TRUE && $this->api->check_api_server() == TRUE)
//        {
//            // Ubah jadi array agar dikirim sebagai x-www-form-urlencoded
//            $param = array("otp" => $this->input->post('otp'));
//            $request = $this->api->request("customer/login_qc/", $param);
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true); $data = $data['content'];
//                $this->output = $data;
//            }else{ $this->reject($request[2],$request[1]); }
//        }
//        elseif ($this->api->check_api_server() != TRUE){ $this->reject("No Connection"); }
//        else { $this->reject(validation_errors(), 400);}
//        $this->response('c');  
//    }
    
    function login_spa()
    {
        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
        if ($this->form_validation->run() == TRUE)
        {
            $result = $this->properti->valid_otp($this->input->post('otp'));
            if ($result != FALSE){
              $data['company_id'] = $result->company_id;
              $data['company'] = $result->name;
              $data['division_id'] = $result->division_id;
              $data['division_code'] = $result->division_code;
              $data['otp'] = $result->otpserver;
              $token = JWT::encode($data, 'aslibos');
              $this->output = $token;
            }else{ $this->reject('Invalid Server OTP'); }
        }
        else { $this->reject(validation_errors(), 400);}
        $this->response('c');  
    }
    
    function history()
    {
        $decoded = $this->api->get_decoded('decoded');
        if (isset($decoded) == TRUE){
            
          $datax = (array)json_decode(file_get_contents('php://input')); 
          
          $produksi = null; $sync=null;
          
          if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
          if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; } 
          if (isset($datax['produksi'])){ $produksi = $datax['produksi']; } 
          if (isset($datax['sync'])){ $sync = $datax['sync']; }
          
          $decoded = $this->api->get_decoded();
          
          $result = $this->result->history($produksi,$sync, $this->limitx, $this->offsetx)->result();
          $this->count = $this->result->history($produksi,$sync,$this->limitx,$this->offsetx,1);
          $property = $this->properti->get();
          
          foreach($result as $res)
          {   
               $this->resx[] = array ("id" => $res->id,
                                      "code" => $res->code, "code2" => $res->code2,
                                      "product" => $res->product, "brand" => $res->brand, "lini" => $res->lini, 
                                      "description" => $res->description, "scan_date" => tglin($res->scan_date).' - '. timein($res->scan_date),
                                      "factory_unit" => $res->factory_unit, 
                                      "location" => $res->location,
                                      "receive_date" => tglin($res->receive_date).' - '.timein($res->receive_date),
                                      "distributor" => $res->distributor,
                                      "pict_top" => $property['image_url'].'barcode/'.$res->pict_top,
                                      "pict_top_created" => tglin($res->pict_top_created).' - '. timein($res->pict_top_created),
                                      "pict_bot" => $property['image_url'].'barcode/'.$res->pict_bot,
                                      "pict_bot_created" => tglin($res->pict_bot_created).' - '. timein($res->pict_bot_created),
                                      "finish_status" => $res->finish_status, 
                                      "produksi_id" => $res->produksi_id,
                                      "log" => $res->log, 
                                      "company_id" => $res->company_id, 
                                      "division_id" => $res->division_id,
                                      "conveyor_id" => $res->conveyor_id,
                                      "conveyor" => $res->conveyor,
                                      "otpserver" => $res->otpserver,
                                      "sync" => timein($res->sync).' - '. tglin($res->sync)
                                       );
          } 
          
//          $this->resx = $this->result->get_by_customer($decoded->company)->result();
//          $this->count = $this->model->get_last($decoded->userid,$this->limitx, $this->offsetx,1);  
//
          $data['record'] = $this->count; 
          $data['result'] = $this->resx;
          $this->output = $data;

        }else{ $this->reject_token(); }
        $this->response('content');
    }
    
    function barcode_list(){
        
        $datax = (array)json_decode(file_get_contents('php://input')); 
          
        $usedtop = null; $usedbot = null; $sides=null;

        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; } 
        if (isset($datax['usedtop'])){ $usedtop = $datax['usedtop']; } 
        if (isset($datax['usedbot'])){ $usedbot = $datax['usedbot']; } 
        if (isset($datax['sides'])){ $sides = $datax['sides']; }
        
        $decoded = $this->api->get_decoded('decoded');
        if (isset($decoded) == TRUE){
            
          $result = $this->barcode->get_last($usedtop, $usedbot, $sides, $this->limitx, $this->offsetx)->result();
          $this->count = $this->barcode->get_last($usedtop, $usedbot, $sides, $this->limitx, $this->offsetx,1);
          $data['count'] = $this->count;
          $data['result'] = $result;
          $this->output = $data;  
          
        }else{ $this->reject('Token Not Found'); } 
        $this->response('c');  
    }   
    
    function login_qc()
    {
        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
        if ($this->form_validation->run() == TRUE)
        {
            $result = $this->conveyor->get_by_otp($this->input->post('otp'), 0);
            if ($result){
              $data['company_id'] = $result->company;
              $this->output = $data;
            }else{ $this->reject('Invalid OTP'); }
        }
        else { $this->reject(validation_errors(), 400);}
        $this->response('c');  
    }
    
    function login_qc_2()
    {
        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
        $this->form_validation->set_rules('company', 'company', 'required|numeric');
        $this->form_validation->set_rules('product', 'product-id', 'required|numeric');
        if ($this->form_validation->run() == TRUE)
        {
            // Ubah jadi array agar dikirim sebagai x-www-form-urlencoded
           $result = $this->conveyor->get_by_otp($this->input->post('otp'),0);
           
           $data['otp'] = $this->input->post('otp');
           $data['company'] = $result->company;
           $data['division'] = $result->division;
           $data['conveyor'] = $result->id;
           $data['conveyor_code'] = $result->code;
           $data['conveyor_name'] = $result->name;
           $data['product'] = $this->input->post('product');
           $data['product_name'] = $this->product->get_by_id($this->input->post('product'), 'name');
           $token = JWT::encode($data, 'aslibos');
           
           $this->produksi->create($result->id, $this->input->post('product'));
           $this->devicelogin->login_qc(intval($result->id),$token);
           $this->output = $token;
        }
        else { $this->reject(validation_errors(), 400);}
        $this->response('c');  
    }
    
    
    // inet function
//    function login_qc_2_inet()
//    {
//        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
//        $this->form_validation->set_rules('company', 'company', 'required|numeric');
//        $this->form_validation->set_rules('product', 'product-id', 'required|numeric');
//        if ($this->form_validation->run() == TRUE && $this->api->check_api_server() == TRUE)
//        {
//            // Ubah jadi array agar dikirim sebagai x-www-form-urlencoded
//            $param = array("otp" => $this->input->post('otp'),
//                           "company" => $this->input->post('company'), 
//                           "product" => $this->input->post('product')
//                    );
//            $request = $this->api->request("customer/login_qc_2/", $param);
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true); $data = $data['content'];
//                $token = JWT::encode($data, 'aslibos');
////                print_r($data);
//                $this->devicelogin->login_qc(intval($data['conveyor']),$token);
//                $this->output = $data;
//            }else{ $this->reject($request[2],$request[1]); }
//        }
//        elseif ($this->api->check_api_server() != TRUE){ $this->reject("No Connection"); }
//        else { $this->reject(validation_errors(), 400);}
//        $this->response('c');  
//    }
    
    function logout_qc(){
        $decoded = $this->api->get_decoded();
        if ($this->devicelogin->cek_login($decoded->conveyor) == TRUE){
           if ($this->devicelogin->logout_qc(intval($decoded->conveyor)) != true){ $this->reject("Failed to logout");}
        }
        $this->response('c');
    }
    
    // inet function
//    function logout_qc(){
//        if ($this->api->check_api_server() == TRUE){
//            $decoded = $this->api->get_decoded();
//            $request = $this->api->request_get("customer/logout_qc/".$decoded->otp);
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true);
//                $data = $data['content'];
//                $this->devicelogin->logout_qc(intval($decoded->conveyor));
//                $token = JWT::encode(null, 'aslibos');
//                $this->output = $data;
//            }else{ 
//                $this->reject($request[2],$request[1]);
//            }
//        }else{ $this->reject("No Connection"); } 
//        $this->response('c');
//    }
    
    function finish_produksi(){
        $decoded = $this->api->otentikasi('decoded');
        if ($this->devicelogin->cek_login($decoded->conveyor) == TRUE){
            $this->produksi->cek_produksi($decoded->conveyor, $decoded->product);
        }else{ $this->reject("Session Expired"); }
       $this->response('c');      
    }
    
    // inet function
//    function finish_produksi(){
//       if ($this->api->check_api_server() == TRUE){
//            $decoded = $this->api->get_decoded();
//            if ($this->devicelogin->cek_login(intval($decoded->conveyor)) == TRUE){
//               $request = $this->api->request_get("customer/finish_produksi/".$decoded->otp.'/'.$decoded->product);
//               if ($request[1] == 200){
//                 $data = json_decode($request[0], true);
//                 $data = $data['content'];
//                 $this->output = $data;
//               }else{ $this->reject($request[2],$request[1]);}
//            }else{ $this->reject('QC Session Expired'); }
//       }else{ $this->reject("No Connection"); }
//       $this->response('c');      
//    }

    
    // ================= qc ===========================
    
    // ================= scanner ===========================
    
    function login_scanner(){
        // Form validation
        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
        if ($this->form_validation->run() == TRUE)
        {     
            $mess = null; $position = 'top';
            $count_top = $this->conveyor->get_by_otp($this->input->post('otp'), 1,1);
            $count_bot = $this->conveyor->get_by_otp($this->input->post('otp'), 2,1);
            
            if ($count_top == 1){ $result = $this->conveyor->get_by_otp($this->input->post('otp'), 1); }
            elseif ($count_bot == 1){ $result = $this->conveyor->get_by_otp($this->input->post('otp'), 2); $position = 'bot'; }
            elseif ($count_top == 0 && $count_bot == 0){$mess = 'Invalid OTP Scanner';}
            
            if ($mess == null && $this->devicelogin->cek_login($result->id) == TRUE){
                $data['otp'] = $this->input->post('otp');
                $data['company'] = $result->company;
                $data['division'] = $result->division;
                $data['conveyor'] = $result->id;
                $data['conveyor_code'] = $result->code;
                $data['conveyor_name'] = $result->name;
                $data['product'] = $this->produksi->get_by_conveyor($result->id);
                $data['product_name'] = $this->product->get_by_id($data['product'], 'name');
                $data['produksi'] = $this->produksi->get_by_conveyor($result->id,1);
                $data['position'] = $position;
                $token = JWT::encode($data, 'aslibos');
                $this->devicelogin->set_token($data['conveyor'], $data['position'], $token);
                $this->output = $token;
            }
            elseif ($this->devicelogin->cek_login($result->id) == FALSE){ $this->reject('QC Session Expired'); }
            else{ $this->reject($mess); }
        }
        else{ $this->reject(validation_errors(),400); }
        $this->response('c');  
    }
    
    // inet function
//    function login_scanner_inet(){
//        // Form validation
//        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
//        if ($this->form_validation->run() == TRUE && $this->api->check_api_server() == TRUE)
//        {     
//            $param = array("otp" => $this->input->post('otp'));
//            $request = $this->api->request("customer/login_scanner/", $param);
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true); $data = $data['content'];
//                $token = JWT::encode($data, 'aslibos');
//                $this->devicelogin->set_token($data['conveyor'], $data['position'], $token);
//                $this->output = $data;
//            }else{ $this->reject($request[2],$request[1]); } 
//        }
//        elseif ($this->api->check_api_server() != TRUE){ $this->reject("No Connection"); }
//        else{ $this->reject(validation_errors(),400); }
//        $this->response('c');  
//    }
    
    function logout_scanner(){
        $decoded = $this->api->get_decoded();
        if ($decoded != null){
           $this->devicelogin->logout_scanner($decoded->conveyor, $decoded->position);    
        }
        $this->response('c');
    }
    
    // inet function
//    function logout_scanner(){
//        if ($this->api->check_api_server() == TRUE){
//            $decoded = $this->api->get_decoded();
//            $request = $this->api->request_get("customer/logout_scanner/".$decoded->otp);
//            if ($request[1] == 200){
//                $data = json_decode($request[0], true);
//                $data = $data['content'];
//                $this->devicelogin->set_token($decoded->conveyor, $decoded->position, null);
//                $this->output = $data;
//            }else{ 
//                $this->reject($request[2],$request[1]);
//            }
//        }else{ $this->reject("No Connection"); }
//        $this->response('c');
//    }
    
    function decode_scanner(){
        
        $decoded = $this->api->otentikasi('decoded');
        if ($this->devicelogin->cek_login($decoded->conveyor) == TRUE){
            $this->output = $decoded;
            //        $request = $this->api->request_get("customer/decode_scanner/".$otp);
//        if ($request[1] == 200){
//            $data = json_decode($request[0], true);
//            $data = $data['content'];
////            $this->output = $data;
//            $decoded = $this->api->otentikasi('decoded');
//        }else{ 
//            $this->reject($request[2],$request[1]);
//        }
            
        }else{ $this->reject('Session Expired'); }
        $this->response('c');
    }
    
    // ini wajib scan database local aja
    function scan()
    {
        $decoded = $this->api->otentikasi('decoded');
        if ($this->devicelogin->cek_login($decoded->conveyor) == TRUE){
            $this->form_validation->set_rules('code', 'Code', 'required');
        
            if ($this->form_validation->run($this) == TRUE)
            {     
                $code = str_replace(' ', '', $this->input->post('code'));
                $position = $decoded->position;
                if ($this->barcode->cek_used($code, $position) == FALSE){
                  $this->reject("Invalid Code");
                }
                else{
                  $sides = $this->barcode->get_by_code($code);
                  $this->output = $sides->sides;
                }
            }
            else{ $this->reject(validation_errors(),400); } 
        }else{ $this->reject('Session Expired'); }
        $this->response('c'); 
    }  
    
    
    function post_barcode() {
        
        $decoded = $this->api->otentikasi('decoded');
        if ($this->devicelogin->cek_login($decoded->conveyor) == TRUE){
         $property = $this->properti->get();
            
         $this->form_validation->set_rules('code', 'Code', 'required|callback_valid_code['.$decoded->position.']');
         $code = str_replace(' ', '', $this->input->post('code')); 
         
        if ($this->form_validation->run($this) == TRUE && $this->result->valid_trans_status($code, $decoded->position) == TRUE)
        {  
            $otp = $property['otpserver'];
            $position = $decoded->position;
            $conveyor = $decoded->conveyor;
            $conveyor_code = $decoded->conveyor_code;
            $produksi = $decoded->produksi;
            $division = $decoded->division;
            $product  = $decoded->product;
            $factory = "";
            $company = $decoded->company;
            $product_name = $decoded->product_name;
            $code = str_replace(' ', '', $this->input->post('code'));  
           
           
           // start trans
           // Validasi manual untuk file upload
            if (empty($_FILES['userfile']['name'])) { 
                $this->reject('File upload is required', 400);
            }

            // Ambil informasi produk      
            $isValid = $this->result->searching_finish_status();

            if ($isValid == TRUE) {
                if ($position == 'bot'){
                    $barcodedata = array(
                        'code2' => $code,
                        'code2status' => 1,
                        'updated' => date('Y-m-d H:i:s'),
                        'finish_status' => 1
                    );
                }elseif ($position == 'top'){
                    $barcodedata = array(
                    'code' => $code,
                    'code1status' => 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'finish_status' => 1
                );
                }
                // Jika data sudah ada, lakukan update
                 
                $updateStatus = $this->result->edit_code($code, $this->input->post('position'), $barcodedata);
                if (!$updateStatus) { 
                    $this->reject('Failed to update data'); 
                }
            } else if ($isValid == FALSE) {
                
                if ($position == 'top'){
                        $barcodedata = array(
                        'code'          => $code,
                        'product'       => $product,
                        'brand'         => $product_name,
                        'lini'          => "",
                        'description'   => "",
                        'scan_date'     => date('Y-m-d H:i:s'),
                        'factory_unit'  => $factory,
                        'conveyor'      => $conveyor_code,
                        'holding'       => 'None',
                        'customer'      => 0,
                        'location'      => null,
                        'log'           => 0,
                        'company_id'    => $company,
                        'division_id'   => $division,
                        'conveyor_id'   => $conveyor,
                        'otpserver'     => $otp,
                        'produksi_id'   => $produksi,
                        'code1status'   => 1,
                        'created'       => date('Y-m-d H:i:s')
                    );
                }elseif ($position == 'bot'){
                    $barcodedata = array(
                    'code2'          => $code,
                    'product'       => $product,
                    'brand'         => $product_name,
                    'lini'          => "",
                    'description'   => "",
                    'scan_date'     => date('Y-m-d H:i:s'),
                    'factory_unit'  => $factory,
                    'conveyor'      => $conveyor_code,
                    'holding'       => 'None',
                    'customer'      => 0,
                    'location'      => null,
                    'log'           => 0,
                    'company_id'    => $company,
                    'division_id'   => $division,
                    'conveyor_id'   => $conveyor,
                    'otpserver'     => $otp,
                    'produksi_id'   => $produksi,
                    'code2status'   => 1,
                    'created'       => date('Y-m-d H:i:s')
                );
                }
                
                 
                // Jika data belum ada, lakukan insert
                $insertStatus = $this->result->create($code, $this->input->post('position'), $barcodedata);
                if (!$insertStatus) { 
                    $this->reject('Failed to insert data'); 
                }
            }
            
            // **Upload File Setelah Insert/Update Berhasil**
            $uploadConfig['upload_path']   = $property['url_upload'].'barcode/';
            $uploadConfig['file_name']     = split_space($code.'_'.$position.'_'.date('Y-m-d H:i:s'));
            $uploadConfig['allowed_types'] = 'jpg|gif|png|jpeg|PNG';
            $uploadConfig['overwrite']     = true;
            $uploadConfig['max_size']      = '5000';
            $uploadConfig['max_width']     = '30000';
            $uploadConfig['max_height']    = '30000';
            $uploadConfig['remove_spaces'] = TRUE;

            $this->load->library('upload', $uploadConfig);

            if (!$this->upload->do_upload("userfile")) { 
                $this->reject($this->upload->display_errors());
            }

            // Update data dengan file yang baru diupload
            $info = $this->upload->data();
            $fileColumn = ($position == 'top') ? 'pict_top' : 'pict_bot';
            $timeColumn = ($position == 'top') ? 'pict_top_created' : 'pict_bot_created';

            $updateFileData = array(
                $fileColumn => setnull($info['file_name']),
                $timeColumn => date('Y-m-d H:i:s')
            );

            $this->result->edit_code_picture($code, $position, $updateFileData);

            $data['result'] = "Barcode posted";
            $data['run_status'] = $this->result->cek_run_status($code);
            $this->output = $data;
           
        }
        elseif ($this->result->valid_trans_status($code, $decoded->position) != TRUE){ $this->reject("Result Position Not Empty"); }
        else{ $this->reject(validation_errors(),400); }
       }else{ $this->reject('Session Expired'); }
        $this->response('c');  
    }
    
    // ================= scanner ===========================
      
    
    // ================ reset ================
    function reset(){
        $reset = $this->result->cleaning();
        $reset2 = $this->barcode->cleaning();
        if ($reset == true && $reset2 == true){ $this->output = "Success";  }
        else{ $this->reject("failed to reset"); }
        $this->response('c');
    }
    
    function add_barcode() { 
        $this->form_validation->set_rules('code', 'Code', 'required');
        $isValid = $this->barcode->valid('code', $this->input->post('code'));
        if ($this->form_validation->run($this) == TRUE && $isValid == TRUE)
        {        
            $member = array('code' => strtoupper($this->input->post('code')),
                            'sides' => 2,
                            'used_top' => 0, 'used_bot' => 0,
                            'created' => date('Y-m-d H:i:s'));

            if ($this->barcode->add($member) != true){ $this->reject('Failed to post');}
        }
        elseif ($isValid == FALSE){ $this->reject('Barcode Registered'); }
        else{ $this->reject(validation_errors(),400); }
        $this->response('c');  
    }
    // ================ reset ================
    
    // ================= camera function ======================
    
    function start_camera(){
        if ($this->camera->set(1) == TRUE){
             $this->output = null;
        }else{ $this->reject("Failed to start camera"); }    
        $this->response('c');
    }
    
    function stop_camera(){
        if ($this->camera->set(0) == TRUE){
          $this->output = null;
        }else{ $this->reject("Failed to stop camera"); }    
        $this->response('c');
    }
    
    function status_camera(){
       $this->output = intval($this->camera->status());
       $this->response('c');
    }
    
    // ================= camera function ======================
    
    // ================= sync ================
    
    function sync($limit=50){
        
        if (!$this->api->check_api_server()) {
            log_message('error', 'API server tidak dapat dijangkau. Sinkronisasi dibatalkan.');
            return;
        }
        
        $result = $this->result->searching_unsync_data($limit);
        foreach ($result as $res) {
            $this->set_sync($res->id);
            usleep(200000); // delay 200ms antar sync
        }
    }
    
    private function set_sync($uid = 0)
    {
        try {
            $property = $this->properti->get();
            $result = $this->result->get_by_id($uid)->row();

            $img1 = FCPATH . 'images/barcode/' . $result->pict_top;
            $img2 = FCPATH . 'images/barcode/' . $result->pict_bot;

            if (!file_exists($img1)) {
                throw new Exception('File barcode atas tidak ditemukan.');
            }

            // Step 1: Upload barcode top
            $param1 = array(
                "otp" => $property['otpserver'],
                "code" => $result->code,
                "position" => 'top',
                "conveyor" => $result->conveyor_id,
                "produksi" => $result->produksi_id,
                'userfile' => new CURLFile($img1, mime_content_type($img1), basename($img1))
            );

            $request1 = $this->api->request_upload("main/post_barcodev2/", $param1);
            if ($request1[1] !== 200) {
                throw new Exception("Gagal upload barcode atas: " . $request1[2]);
            }

            // Step 2: Lanjutkan hanya jika request1 sukses
            if (!file_exists($img2)) {
                throw new Exception('File barcode bawah tidak ditemukan.');
            }

            $param2 = array(
                "otp" => $property['otpserver'],
                "code" => $result->code2,
                "position" => 'bot',
                "conveyor" => $result->conveyor_id,
                "produksi" => $result->produksi_id,
                'userfile' => new CURLFile($img2, mime_content_type($img2), basename($img2))
            );

            $request2 = $this->api->request_upload("main/post_barcodev2/", $param2);
            if ($request2[1] !== 200) {
                throw new Exception("Gagal upload barcode bawah: " . $request2[2]);
            }

            // Jika sukses dua-duanya
            $sync = date('Y-m-d H:i:s');
            $this->result->set_stts_sync($uid, $sync, null);

        } catch (Exception $e) {
            $this->result->set_stts_sync($uid, null, $e->getMessage());
            $this->reject($e->getMessage(), 500);
        }

        $this->response('c');
    }

    
    private function xset_sync($uid=0){
       $property = $this->properti->get();
       $result = $this->result->get_by_id($uid)->row(); 
       $img1 = FCPATH.'images/barcode/'.$result->pict_top;
       $img2 = FCPATH.'images/barcode/'.$result->pict_bot;
       $imgfile1 = new CURLFile($img1, mime_content_type($img1), basename($img1));
       $imgfile2 = new CURLFile($img2, mime_content_type($img2), basename($img2));
       $sync = null; $error = null;
       $param1 = array(
                     "otp" => $property['otpserver'],
                     "code" => $result->code,
                     "position" => 'top',
                     "conveyor" => $result->conveyor_id,
                     "produksi" => $result->produksi_id,
                     'userfile' => $imgfile1);
       
       $param2 = array(
                     "otp" => $property['otpserver'],
                     "code" => $result->code2,
                     "position" => 'bot',
                     "conveyor" => $result->conveyor_id,
                     "produksi" => $result->produksi_id,
                     'userfile' => $imgfile2);
       
       
        $request1 = $this->api->request_upload("main/post_barcodev2/", $param1);
        if ($request1[1] == 200){
          $request2 = $this->api->request_upload("main/post_barcodev2/", $param2);  
          if ($request2[1] == 200){
              $sync = date('Y-m-d H:i:s');
          }else{ $this->reject($request2[2],$request2[1]); $error = $request2[2]; }
        }
        else{ $this->reject($request1[2],$request1[1]); $error = $request1[2]; }
       
       $this->result->set_stts_sync($uid, $sync, $error);
       $this->response('c');
    }
     
    
    // ================= sync ================

    function rollback()
    {
        try {
            if ($this->api->get_decoded() != null) {
                $property = $this->properti->get();
                $datax = (array)json_decode(file_get_contents('php://input'));
                $decoded = $this->api->get_decoded();

                $uid = $this->result->counter_model();
                $upload_dir = FCPATH . 'images/';
                if ($uid > 0){
                    
                   $result = $this->result->get_by_id($uid)->row();
                   if ($result->sync == null){
                      
                       // proses upload
                        if ($this->barcode->unset_used($result->code) === true && $this->barcode->unset_used($result->code2) === true) {
                            $pict_top = !empty($result->pict_top) ? $upload_dir . 'barcode/' . $result->pict_top : null;
                            $pict_bot = !empty($result->pict_bot) ? $upload_dir . 'barcode/' . $result->pict_bot : null;


                            if (!empty($pict_top) && file_exists($pict_top) && is_file($pict_top)) {
                             if (!unlink($pict_top)) {
                                 throw new Exception("Gagal menghapus file: $pict_top");
                             }
                            }

                             if (!empty($pict_bot) && file_exists($pict_bot) && is_file($pict_bot)) {
                             if (!unlink($pict_bot)) {
                               throw new Exception("Gagal menghapus file: $pict_bot");
                             }
                            }
                            $this->result->force_delete($uid);
                        } else {
                            throw new Exception('Failed to reject barcode usage.');
                        }
                       // proses upload
                   }
                }
                
            } else { $this->reject_token(); return;}
            $this->response('c');

        } catch (Exception $e) {
            // Log error jika diperlukan
            // log_message('error', $e->getMessage());
            $this->reject($e->getMessage());
        }
    }
    
    function searching_code($code=0){
        $this->output = $this->result->searching_code_type($code);
        $this->response('c');
    }
    
    function get_last(){
       $property = $this->properti->get(); 
       $uid = $this->result->counter_model(1); 
       $res = $this->result->get_by_id($uid)->row();
       if ($uid > 0 && $res->sync == null){ 
           $data['result'] = $res;
           $data['pict_top'] = $property['image_url'].'barcode/'.$res->pict_top;
           $data['pict_bot'] = $property['image_url'].'barcode/'.$res->pict_bot;
           
           $this->output = $data; 
       }
       elseif ($uid == 0){ $this->reject("Code Not Found",404); }
       elseif ($res->sync != null){ $this->reject("Last transaction has been synchronize"); }
       $this->response('c');
   }
   
   function valid_otp_server($otp){
       if ($this->server->get_by_otp($otp) == FALSE){
           $this->form_validation->set_message('valid_otp_server','Invalid Server OTP..!');
           return FALSE;
       }else{ return TRUE; }
   }
   
   function valid_code($code,$position){
       $code = str_replace(' ', '', $code);
       if ($this->barcode->cek_used($code, $position) == FALSE){
           $this->form_validation->set_message('valid_code','Invalid Code..!');
           return FALSE;
       }else{ return TRUE; }
   }
   
//   // fungsi untuk cek apakah conveyor produksi == conveyor yang diinput
//   function valid_produksi($produksi,$conveyor){
//       if ($this->produksi->cek_trans('id', $produksi) == TRUE){
//           if ($this->produksi->get_by_id($produksi, 'conveyor') == $conveyor){ return TRUE; }else{
//             $this->form_validation->set_message('valid_produksi','Produksi Conveyor Not Match..!'); return FALSE;    
//           }
//       }else{ $this->form_validation->set_message('valid_produksi','Produksi Not Found..!'); return FALSE; }
//   }
           
   // ========================== api ==========================================
    
    
    private function crop_image($filename,$width=500,$height=500){
        
        $config['image_library'] = 'gd2';
        $config['source_image'] = $this->properti['url_upload'].'customer/'.$filename;
        $config['maintain_ratio'] = TRUE;
        $config['width']  = $width;
        $config['height'] = $height;
        $this->load->library('image_lib', $config); 
        if (!$this->image_lib->resize()){ return FALSE; }
    }

}

?>