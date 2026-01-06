<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/** load the CI class for Modular Extensions **/
require dirname(__FILE__).'/Base.php';
require_once(APPPATH.'core/Custom_Model.php');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library replaces the CodeIgniter Controller class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Controller.php
 *
 * @copyright	Copyright (c) 2011 Wiredesignz
 * @version 	5.4
 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Controller 
{
	public $autoload = array();
	
	public function __construct() 
	{
            $class = str_replace(CI::$APP->config->item('controller_suffix'), '', get_class($this));
            log_message('debug', $class." MX_Controller Initialized");
            Modules::$registry[strtolower($class)] = $this;	

            /* copy a loader instance and initialize */
            $this->load = clone load_class('Loader');
            $this->load->initialize($this);	

            /* autoload module items */
            $this->load->_autoloader($this->autoload);
            $this->modelx = new Custom_Model();
            $this->apix = new Api_lib();
            $this->aclx = new Acl();
//            $this->log = new Log_lib();
            $this->decodedd = $this->apix->otentikasi('otentikasi');

            $this->form_validation->set_error_delimiters('', '');
	}
        
        protected $modelx,$apix,$aclx,$log;
        public $decodedd;
        public $limitx,$offsetx,$orderby, $order;

        public $error = null;
        public $status = 200, $status404=TRUE;
        public $output = null, $resx = null, $count = 0;

        public function __get($class) {
	  return CI::$APP->$class;
	}
        
        function get_deleted(){
            $result = $this->model->get_deleted()->result(); 
            $output = null;
            $error = null;
            $status = 200;
            $this->apix->response(array('error' => $error, 'content' => $result), $status);
        }
        
        function restore($uid=null){
            $error = 'Success Restored';
            $status = 200;
            if ($this->model->restore($uid) != true){ $error = 'Failed to restore'; $status = 401;}
            $this->apix->response(array('error' => $error, 'content' => $result), $status);
        }
                
        function response($type=null){
           if ($this->status != 200){
              if ($type){ $this->apix->response(array('error' => $this->error, 'content' => $this->output), $this->status);  }
              else{ $this->apix->response(array('error' => $this->error), $this->status);  } 
           }else{
              if ($type){ $this->apix->response(array('content' => $this->output), $this->status);  }
              else{ $this->apix->response(array('content' => $this->error), $this->status);  }
           } 
        }
        
        function valid_404($val=TRUE){ if ($val == FALSE){ $this->status404 = FALSE; }}
        function reject($mess='Failed to posted',$status=403){ $this->error = $mess; $this->status = $status; }
        private function reject_404(){ $this->error = 'ID not found'; $this->status = 404; }
        function reject_token($mess='Invalid Token or Expired..!',$status=401){
            if ($this->status404 == FALSE){ $this->reject_404(); }else{ $this->error = $mess; $this->status = $status; }
        }
        
        
//        function reject($mess='Failed to posted',$status=401){$this->error = $mess; $this->status = $status; }
//        function reject_token($mess='Invalid Token or Expired..!',$status=400){$this->error = $mess; $this->status = $status; }
}