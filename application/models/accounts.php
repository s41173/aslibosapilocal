<?php

class Accounts extends DataMapper
{
//   var $has_one = array("country");
   var $table = "accounts";
   var $has_one = array("classification");
   var $has_many = array("balance", "control", "cost");
   var $auto_populate_has_many = TRUE;
   var $auto_populate_has_one = TRUE;

   function __construct($id = NULL)
   {
      parent::__construct($id);
   }

}

/* End of file user.php */
/* Location: ./application/models/user.php */
