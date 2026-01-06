<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
 *
 * @access	public
 * @param	mixed	// will be cast as int
 * @return	string
 */


if (! function_exists('setnull'))
{
    function setnull($value)
    {if ($value == ""){$value = null;}return $value;}
}

if (! function_exists('setnol'))
{
    function setnol($value)
    {if ($value == ""){$value = 0;}return $value;}
}

if (! function_exists('replace'))
{
    function replace($replace,$replacewith,$inme)
    {
        $doit = str_replace ("$replace", "$replacewith", $inme);
        return strtolower("$doit");
    }
}

if (! function_exists('status'))
{
    function status($val){if ($val == "0"){$value = 'N';}else{ $value = "Y"; }return $value;}
}



/* End of file combo_helper.php */
/* Location: ./system/helpers/combo_helper.php */