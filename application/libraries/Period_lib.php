<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Period_lib extends Main_model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'periods';
    }

    private $ci;

    protected $field = array('id', 'month', 'year', 'closing_month', 'start_month', 'start_year', 'status', 'created', 'updated', 'deleted');


    public function get($type=null)
    {
       $this->db->select($this->field);
       $val = $this->db->get($this->tableName)->row();
       if ($type == 'month'){ return $val->month; }
       elseif ($type == 'year') { return $val->year; }
       else { return $val; }
    }
    
    function update_period($uid, $users)
    {
        $this->db->where('id', $uid);
        $this->db->update($this->tableName, $users);
        
        $val = array('updated' => date('Y-m-d H:i:s'));
        $this->db->where('id', $uid);
        $this->db->update($this->tableName, $val);
    }
    
    function next_period()
    {
        $ps = new Period();
        $ps = $ps->get();
        
        $month = $ps->month;
        $year = $ps->year;
        
        if ($month == 12){$nmonth = 1;}else { $nmonth = $month +1; }
        if ($month == 12){ $nyear = $year+1; }else{ $nyear = $year; }
        $res[0] = $nmonth; $res[1] = $nyear;
        return $res;
    }
    
}

/* End of file Property.php */