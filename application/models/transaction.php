<?php

class Transaction extends DataMapper
{
//   var $has_one = array("country");
   var $table = "transactions";
//   var $has_many = array("account");
   var $has_one = array("gl");
   var $auto_populate_has_many = TRUE;
   var $auto_populate_has_one = TRUE;

   function __construct($id = NULL)
   {
      parent::__construct($id);
   }

}

/* End of file user.php */
/* Location: ./application/models/user.php */