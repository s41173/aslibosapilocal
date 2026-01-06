<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ledger_model extends Custom_Model
{
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('ledger');
    }
    
    var $table = null;
    
    function get_ledger($acc=null,$start=null,$end=null)
    {
        $this->db->select('gls.id, gls.no, gls.dates, gls.code, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_null($acc,"transactions.account_id");
        $this->cek_between($start, $end);
        $this->db->where('gls.approved', 1);
        $this->db->order_by('gls.dates', 'asc');
        $this->db->order_by('transactions.id', 'asc');
        return $this->db->get(); 
    }
    
    function get_monthly($acc=null,$month=0,$year=0)
    {
        $this->db->select('accounts.id, gls.id, gls.no, gls.dates, gls.code, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('MONTH(gls.dates)', $month);
        $this->db->where('YEAR(gls.dates)', $year);
        $this->db->where('gls.approved', 1);
        $this->db->order_by('gls.dates', 'asc');
        return $this->db->get(); 
    }

    
    function get_balance($acc=null,$no=0)
    {
        
        $this->db->select_sum('transactions.vamount');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where("gls.no", $no);
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('gls.approved', 1);
        return $this->db->get(); 
    }
    
    function get_sum_balance($acc=null,$start=null,$end=null)
    {
        
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_between($start, $end);
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('gls.approved', 1);
        return $this->db->get(); 
    }
    
    function report($acc1=0, $acc2=0)
    {
        $this->db->select('accounts.id, accounts.name, accounts.code,
                           transactions.debit, transactions.credit, transactions.vamount');
        
        $this->db->from('transactions, accounts');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_between_acc($acc1, $acc2);
        $this->db->order_by('accounts.code', 'asc');
        $this->db->group_by("transactions.account_id"); 
        return $this->db->get();
    }
    
    function report_between($acc1=0, $acc2=0, $start=null, $end=null)
    {
        $this->db->select('accounts.id, accounts.name, accounts.code,
                           transactions.debit, transactions.credit, transactions.vamount');
        
        $this->db->from('transactions, accounts');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_between_acc($acc1, $acc2);
        $this->db->order_by('accounts.code', 'asc');
        $this->db->group_by("transactions.account_id"); 
        return $this->db->get();
    }
    
    private function cek_between_acc($acc1,$acc2)
    {
        if ($acc1 == 0 || $acc2 == 0 ){return null;}
        else { return $this->db->where("transactions.account_id BETWEEN ".$acc1." AND ".$acc2.""); }
    }
    
    private function cek_between($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("gls.dates BETWEEN '".$start."' AND '".$end."'"); }
    }
   

}

?>
