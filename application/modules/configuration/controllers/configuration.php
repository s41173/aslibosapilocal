<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Configuration extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Configuration_model', '', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->city = new City_lib();
        $this->period = new Period_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title;
    private $role,$city,$period,$api,$acl;

    function index()
    {
//       if ($this->api->otentikasi() == TRUE){
          $ps = $this->period->get(); 
          $result = $this->Configuration_model->get_last($this->modul['limit'])->row_array();
          $period = array('logo_url' => $result['logo'], 'month' => $ps->month, 'year' => $ps->year, 'start_month' => $ps->start_month,
                          'start_year' => $ps->start_year, 'closing_month' => $ps->closing_month, 
                          'url_upload' => $result['url_upload'], 'image_url' => $result['image_url'],
                          'notif_url' => $result['notif_url'], 'shipping_integration' => $result['shipping_integration']
                         );
          
          $this->output = array_merge($result,$period);

//       }else{ $this->reject_token(); }
       $this->response('c');
    }
    
    function get_city_db(){
        
        if ($this->api->otentikasi() == TRUE){
            
          $result = $this->db->get('kabupaten')->result();
          foreach($result as $res){ $this->output[] = array ("value" => $res->id, "label" => $res->nama);}  
            
        }else{ $this->reject_token(); }
        $this->response('c');
    }
    
    function get_city()
    {
      if ($this->api->otentikasi() == TRUE){
        $curl = curl_init();

        curl_setopt_array($curl, array(
//          CURLOPT_URL => "http://api.rajaongkir.com/starter/province",
          CURLOPT_URL => "http://api.rajaongkir.com/starter/city",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "key: eb7f7529d68f6a2933b5a042ffeeac9d"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { $this->error = "cURL Error #:" . $err; $this->status = 401;} 
        else { $this->output = json_decode($response, true); }
        
        }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }

    function update_pos()
    {
        if ($this->acl->otentikasi3($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'Property', 'required|max_length[100]');
        $this->form_validation->set_rules('taddress', 'Address', 'required');
        $this->form_validation->set_rules('tphone', 'Phone1', 'required|max_length[15]');
        $this->form_validation->set_rules('tmail', 'Property Mail', 'required|valid_email|max_length[100]');

        if ($this->form_validation->run($this) == TRUE)
        {
            $property = array('name' => $this->input->post('tname'), 'address' => $this->input->post('taddress'),
                              'phone1' => $this->input->post('tphone'), 
                              'email' => $this->input->post('tmail'));

            $this->Configuration_model->update(1, $property);
            $this->error = "One $this->title has successfully updated..! ";
                
        }else{ $this->reject(validation_errors(),400); }
      }
      else{ $this->reject_token(); }
      $this->response();
    }
    
    // Fungsi update untuk mengupdate db
    function update($param=0)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE){

	// Form validation
        if ($param == 1)
        {
           $this->form_validation->set_rules('tname', 'Property', 'required|max_length[100]');
           $this->form_validation->set_rules('taddress', 'Address', 'required');
	   $this->form_validation->set_rules('tphone1', 'Phone1', 'required|max_length[15]');
           $this->form_validation->set_rules('tphone2', 'Phone2', 'required|max_length[15]');
           $this->form_validation->set_rules('tmail', 'Property Mail', 'required|valid_email|max_length[100]');
           $this->form_validation->set_rules('tbillmail', 'Billing Email', 'required|valid_email|max_length[100]');
           $this->form_validation->set_rules('ttechmail', 'Technical Email', 'required|valid_email|max_length[100]');
           $this->form_validation->set_rules('tccmail', 'CC Email', 'required|valid_email|max_length[100]');
	   $this->form_validation->set_rules('ccity', 'City', 'required|max_length[25]');
           $this->form_validation->set_rules('tzip', 'Zip Code', 'required|numeric|max_length[25]');
        }
        elseif ($param == 2)
        {
            $this->form_validation->set_rules('taccount_name', 'Account Name', 'required|max_length[100]');
            $this->form_validation->set_rules('taccount_no', 'Account No', 'required|max_length[100]');
            $this->form_validation->set_rules('tbank', 'Bank Name', 'required'); 
        }
        elseif ($param == 3)
        {
            $this->form_validation->set_rules('tsitename', 'Site Name', 'required');
            $this->form_validation->set_rules('tmetadesc', 'Global Meta Description', '');
            $this->form_validation->set_rules('tmetakey', 'Global Meta Keyword', ''); 
        }
        elseif ($param == 4)
        {
            $this->form_validation->set_rules('tmanager', 'Manager', '');
            $this->form_validation->set_rules('taccounting', 'Accounting', '');
            $this->form_validation->set_rules('twebmail', 'Webmail', '');
            $this->form_validation->set_rules('turl_upload', 'Url Upload', '');
            $this->form_validation->set_rules('timage_url', 'Image Url', '');
        }
        elseif ($param == 5)
        {
            $this->form_validation->set_rules('cbegin_month', 'Begin Month', 'required|callback_valid_starting_period'); 
            $this->form_validation->set_rules('tbegin_year', 'Begin Year', 'required|numeric|callback_valid_starting_period'); 
            $this->form_validation->set_rules('cmonth', 'Period Month', 'required'); 
            $this->form_validation->set_rules('tyear', 'Period Year', 'required|numeric'); 
            $this->form_validation->set_rules('cend_month', 'End-Closing Month', 'required'); 
        }

        if ($this->form_validation->run($this) == TRUE)
        {
            if ($param == 1)
            {
                $property = array('name' => $this->input->post('tname'), 'address' => $this->input->post('taddress'),
                                  'phone1' => $this->input->post('tphone1'), 'phone2' => $this->input->post('tphone2'),
                                  'cc_email' => $this->input->post('tccmail'), 'email' => $this->input->post('tmail'),
                                  'billing_email' => $this->input->post('tbillmail'), 'technical_email' => $this->input->post('ttechmail'),
                                  'zip' => $this->input->post('tzip'),'city' => $this->input->post('ccity'));

                $this->Configuration_model->update(1, $property);
                $this->error = "One $this->title has successfully updated..! ";
            }
            elseif ($param == 2)
            {
                $property = array( 'bank' => $this->input->post('tbank'), 'account_name' => $this->input->post('taccount_name'), 'account_no' => $this->input->post('taccount_no'));
                $this->Configuration_model->update(1, $property);
                $this->error = "One $this->title has successfully updated..! ";
            }
            elseif ($param == 4)
            {   
                $property = array( 'manager' => $this->input->post('tmanager'), 'url_upload' => setnull($this->input->post("turl_upload")), 'image_url' => setnull($this->input->post("timage_url")),
                                   'accounting' => $this->input->post('taccounting'), 'email_link' => $this->input->post('twebmail'));
                $this->Configuration_model->update(1, $property);
                $this->error = "One $this->title has successfully updated..! ";
            }
            elseif ($param == 5)
            {   
                $ps = $this->period->get();
                if ($ps->status == 1)
                {
                    $monthperiod = $this->input->post('cmonth');
                    $yearperiod = $this->input->post('tyear');
                    $monthend = $ps->closing_month;
                    $startmonth = $ps->start_month;
                    $startyear = $ps->start_year;
                }
                elseif($ps->status == 0) 
                {
                   $monthperiod = $this->input->post('cmonth');
                   $yearperiod = $this->input->post('tyear');
                   $monthend = $this->input->post('cend_month');
                   $startmonth = $this->input->post('cbegin_month');
                   $startyear = $this->input->post('tbegin_year');
                }

                $property = array( 'start_month' => $startmonth, 'start_year' => $startyear,
                                   'month' => $monthperiod, 'year' => $yearperiod,
                                   'closing_month' => $monthend
                                 );
                
                $this->period->update_period(1, $property);
                $this->error = "One $this->title has successfully updated..! ";
            }
            elseif ($param == 3){
            
               $config['upload_path']   = './images/property/';
               $config['allowed_types'] = 'gif|jpg|png';
               $config['overwrite']     = TRUE;
               $config['max_size']      = '15000';
               $config['max_width']     = '10000';
               $config['max_height']    = '10000';
               $config['remove_spaces'] = TRUE;

               $this->load->library('upload', $config); 
               
               if ( !$this->upload->do_upload("userfile")){
                   $data['error'] = $this->upload->display_errors();
                   $property = array('site_name' => $this->input->post('tsitename'), 'meta_description' => $this->input->post('tmetadesc'), 'meta_keyword' => $this->input->post('tmetakey'));
               }
               else{
                   $info = $this->upload->data();
                   $property = array('site_name' => $this->input->post('tsitename'), 'meta_description' => $this->input->post('tmetadesc'), 'meta_keyword' => $this->input->post('tmetakey'), 'logo' => base_url().'images/property/'.$info['file_name']);
               }
               
               $this->Configuration_model->update(1, $property);
               if ($this->upload->display_errors()){ $this->reject($this->upload->display_errors()); }
               else { $this->error = "One $this->title has successfully updated..! "; }
            }
            
        } else{ $this->reject(validation_errors(),400); }
      }
      else{ $this->reject_token(); }
      $this->response();
    }
    
    function remove_img()
    {
        $img = $this->Configuration_model->get_by_id(1)->row();
        $img = $img->logo;
        if ($img){ $img = "./images/property/".$img; unlink("$img"); }
    }
    
    //  callback period validation
    public function valid_closing_period($val=null)
    {
        $end = $this->input->post('cyearend');
        $year = $this->input->post('tyearperiod'); 
        
        if ($this->ps->status == 1)
        {
            if ($end != $this->ps->closing_month || $year != $this->ps->year)
            {
                $this->form_validation->set_message('valid_closing_period', "Year Period & Year-End Cannot Changed..!");
                return FALSE;
            }
            else { return TRUE; }
        }
        else { return TRUE; }
    }
    
    public function valid_starting_period($val=null)
    {
        $smonth = $this->input->post('cbegin_month');
        $emonth = $this->input->post('cmonth');
        $syear = $this->input->post('tbegin_year');
        $eyear = $this->input->post('tyear');
        
        if ($syear > $eyear)
        {
           $this->form_validation->set_message('valid_starting_period', "Invalid Begin Year..!!");
           return FALSE;
        }
        else
        {
            if ($syear == $eyear) { if ($smonth > $emonth){ $this->form_validation->set_message('valid_starting_period', "Invalid Begin Month..!!"); return FALSE; }else { return TRUE; }}
            else { return TRUE; }
        }
    }
   
}

?>