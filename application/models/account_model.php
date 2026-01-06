<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Account_model extends Custom_Model
{
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('account');
    }
    
    protected $table = null;
    protected $com;
    
    function get_account($cla=null)
    {
        $this->db->select('accounts.id, accounts.name, accounts.alias, accounts.code');
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id', $cla);
        $this->db->order_by('accounts.code','asc');
        $this->db->distinct();
        return $this->db->get(); 
    }
    
    function get_all_account($cla=null)
    {
        $this->db->select('accounts.id, accounts.name, accounts.alias, accounts.code, classification_id');
        $this->db->from('accounts');
        $this->cek_null($cla, 'classification_id');
        $this->db->order_by('accounts.code','asc');
        $this->db->where('accounts.deleted', NULL);
//        $this->db->where('accounts.status', 1);
        $this->db->distinct();
        return $this->db->get(); 
    }
    
    function get_cash_group_account()
    {
        $group = array('7', '8');
        
        $this->db->select('accounts.id, accounts.name, accounts.alias, accounts.code, classification_id');
        $this->db->from('accounts');
        $this->db->where_in('classification_id', $group);
        $this->db->where('status', 1);
        $this->db->order_by('accounts.code','desc');
        $this->db->distinct();
        return $this->db->get(); 
    }
    
    // fungsi untuk mendapatkan akun terkait saldo awal
    function get_begin_saldo_account($limit=null,$offset=null,$count=0)
    {
        $this->db->select('accounts.id, accounts.currency, accounts.name, accounts.alias, accounts.code, accounts.classification_id');
        $this->db->from('accounts,classifications');
        $this->db->where('accounts.classification_id = classifications.id');
        
        $names = array('harta', 'modal', 'kewajiban');
        $this->db->where_in('classifications.type', $names);
        $this->db->where('accounts.deleted', NULL);
        
        $this->db->order_by('accounts.code','asc');
        $this->db->limit($limit, $offset);
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function get_balance($acc=null,$month=null,$year=null)
    {
//        $this->db->select('accounts.id, gls.id, gls.no, gls.dates, gls.currency, gls.notes, gls.balance,
//                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('MONTH(dates)', $month);
        $this->db->where('YEAR(dates)', $year);
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('gls.approved', 1);
        $this->db->where('accounts.deleted', NULL);
        return $this->db->get(); 
    }
    
    function get_period_balance($cur='IDR',$acc=null,$month=null,$year=null,$emonth=null,$eyear=null)
    {
//        $this->db->select('accounts.id, gls.id, gls.no, gls.dates, gls.currency, gls.notes, gls.balance,
//                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->cek_between_month($month, $emonth);
        $this->cek_between_year($year, $eyear);
        $this->db->where('gls.currency', $cur);
//        $this->db->where('MONTH(dates)', $month);
//        $this->db->where('YEAR(dates)', $year);
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('gls.approved', 1);
        $this->db->where('accounts.deleted', NULL);
        return $this->db->get(); 
    }
    
    function get_annual_period_balance($cur='IDR',$acc=null,$eyear=null)
    {
//        $this->db->select('accounts.id, gls.id, gls.no, gls.dates, gls.currency, gls.notes, gls.balance,
//                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('YEAR(dates)', $eyear);
        $this->db->where('gls.currency', $cur);
        $this->cek_null($acc,"transactions.account_id");
        $this->db->where('gls.approved', 1);
        $this->db->where('accounts.deleted', NULL);
        return $this->db->get(); 
    }
    
    function get_balance_by_classification($cur='IDR',$cla=null,$month=null,$year=null,$emonth=null,$eyear=null,$publish=1)
    {      
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between_month($month, $emonth);
        $this->cek_between_year($year, $eyear);
        $this->db->where('gls.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return floatval($res->vamount);
    }
    
    // sama seperti fitur di atas hanya beda ouput result
    function get_outcome_balance_by_classification($cur='IDR',$month=null,$year=null,$emonth=null,$eyear=null,$publish=1,$limit=0,$offset=0,$count=0)
    {      
        $this->db->select('accounts.id, gls.id, gls.no, gls.code, gls.dates, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between_month($month, $emonth);
        $this->cek_between_year($year, $eyear);
        $this->db->where('gls.currency', $cur);
        
        $class = array('15', '19', '24', '17', '25');
        $this->db->where_in('classifications.id', $class);
        
//        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    // get summary balance by flexible years
    function get_balance_anual_by_classification($cur='IDR',$cla=null,$start=null,$end=null,$publish=1)
    { 
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between_year($start, $end);
        $this->db->where('gls.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        
        $res = $this->db->get()->row(); 
        return floatval($res->vamount);
    }
    
    function get_outcome_balance_anual_by_classification($cur='IDR',$start=null,$end=null,$publish=1,$limit=0,$offset=0,$count=0)
    { 
        $this->db->select('accounts.id, gls.id, gls.no, gls.code, gls.dates, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between_year($start, $end);
        $this->db->where('gls.currency', $cur);
        
        $class = array('15', '19', '24', '17', '25');
        $this->db->where_in('classifications.id', $class);
        
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);

        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    // get summary balance by flexible dates
    function get_balance_period_by_classification($cur='IDR',$cla=null,$start=null,$end=null,$publish=1)
    {
//        $this->db->select('accounts.id, gls.id, gls.no, gls.dates, gls.currency, gls.notes, gls.balance,
//                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between($start, $end);
        $this->db->where('gls.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return floatval($res->vamount);
    }
    
    // get transaction balance by flexible dates
    function get_balance_trans_period_by_classification($cur='IDR',$cla=null,$start=null,$end=null,$publish=1)
    {
        $this->db->select('accounts.id, gls.id, gls.no, gls.code, gls.dates, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between($start, $end);
        $this->db->where('gls.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        return $this->db->get()->result(); 
    }
    
    // get outcome balance trans period
    function get_outcome_balance_trans_period($cur='IDR',$start=null,$end=null,$publish=1,$limit=0,$offset=0,$count=0)
    {
        $this->db->select('accounts.id, gls.id, gls.no, gls.code, gls.dates, gls.currency, gls.notes, gls.balance,
                           transactions.debit, transactions.credit, transactions.vamount, gls.approved');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->cek_between($start, $end);
        $this->db->where('gls.currency', $cur);
//        $this->cek_null($cla,"classifications.id");
        
        $class = array('15', '19', '24', '17', '25');
        $this->db->where_in('classifications.id', $class);
        
        $this->db->where('gls.approved', $publish);
        $this->db->where('accounts.deleted', NULL);
        
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    
    function get_start_balance_by_classification($cur='IDR',$cla=null,$start=null)
    {
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        
        $this->db->where('gls.dates <', $start);
        $this->db->where("MONTH(gls.dates)", date('n', strtotime($start)));
        $this->db->where("YEAR(gls.dates)", date('Y', strtotime($start)));
        
        $this->db->where('gls.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('gls.approved', 1);
        $this->db->where('accounts.deleted', NULL);

        $res = $this->db->get()->row(); 
        if ($res){ return floatval($res->vamount);  }else { return 0; }
    }
    
    function get_begining_balance_classification($cur='IDR',$cla=null,$start=null)
    {   
        $this->db->select_sum('balances.beginning');
        
        $this->db->from('balances, accounts, classifications');
        $this->db->where('balances.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->db->where('balances.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('balances.month', date('n',  strtotime($start)));
        $this->db->where('balances.year', date('Y',  strtotime($start)));
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return floatval($res->beginning);
    }
    
    function get_begining_balance_classification_by_month($cur='IDR',$cla=null,$month=null,$year)
    {   
        $this->db->select_sum('balances.beginning');
        
        $this->db->from('balances, accounts, classifications');
        $this->db->where('balances.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->db->where('balances.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('balances.month', $month);
        $this->db->where('balances.year', $year);
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return floatval($res->beginning);
    }
    
    function get_end_balance_classification($cur='IDR',$cla=null,$month=null,$year=null)
    {   
        $this->db->select_sum('balances.end');
        
        $this->db->from('balances, accounts, classifications');
        $this->db->where('balances.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');
        $this->db->where('balances.currency', $cur);
        $this->cek_null($cla,"classifications.id");
        $this->db->where('balances.month', $month);
        $this->db->where('balances.year', $year);
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return floatval($res->end);
    }
    
    function get_start_balance($cur='IDR',$acc=null,$month=null,$year=null)
    {   
        $this->db->select_sum('balances.beginning');
        
        $this->db->from('balances');
        $this->db->where('balances.account_id', $acc);
        $this->db->where('balances.month', $month);
        $this->db->where('balances.year', $year);
        $res = $this->db->get()->row(); 
        return floatval($res->beginning);
    }
    
    function get_end_balance($cur='IDR',$acc=null,$month=null,$year=null)
    {   
        $this->db->select_sum('balances.end');
        
        $this->db->from('balances');
        $this->db->where('balances.account_id', $acc);
        $this->db->where('balances.month', $month);
        $this->db->where('balances.year', $year);
        $res = $this->db->get()->row(); 
        return floatval($res->end);
    }
    
    function get_cash_flow_acc($cur='IDR',$cla=null,$start=null,$end=null)
    {
        $this->db->select('accounts.id, accounts.name, accounts.code, accounts.classification_id');
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');

        $this->cek_between($start, $end);
        $this->db->where('gls.currency', $cur);
        $this->db->where('accounts.classification_id',$cla);
        $this->db->where('gls.approved', 1);
        $this->db->where('gls.cf', 1);
        $this->db->where('accounts.deleted', NULL);
        $this->db->distinct();
        return $this->db->get(); 
    }
    
    function get_cash_flow($cur='IDR',$acc=null,$start=null,$end=null)
    {   
        $this->db->select_sum('transactions.vamount');
        $this->db->select_sum('transactions.debit');
        $this->db->select_sum('transactions.credit');
        
        $this->db->from('gls, transactions, accounts, classifications');
        $this->db->where('gls.id = transactions.gl_id');
        $this->db->where('transactions.account_id = accounts.id');
        $this->db->where('accounts.classification_id = classifications.id');

        $this->cek_between($start, $end);
        $this->db->where('gls.currency', $cur);
        $this->db->where('transactions.account_id',$acc);
        $this->db->where('gls.approved', 1);
        $this->db->where('gls.cf', 1);
        $this->db->where('accounts.deleted', NULL);
        $res = $this->db->get()->row(); 
        return $res->vamount;
    }
    
    // budget
    
    function get_budget($cur='IDR',$acc=null,$month=null,$year=null)
    {
        $this->db->select_sum('balances.budget');
        $this->db->from('balances');
        $this->db->where('currency', $cur);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->cek_null($acc,"account_id");
        return $this->db->get(); 
    }
    
    function get_period_budget($cur='IDR',$acc=null,$month=null,$year=null,$emonth=null,$eyear=null)
    {
        $this->db->select_sum('balances.budget');
        $this->db->from('balances');
        $this->db->where('currency', $cur);
        $this->db->where("month BETWEEN '".$month."' AND '".$emonth."'");
        $this->db->where("year BETWEEN '".$year."' AND '".$eyear."'");
        $this->cek_null($acc,"account_id");
        return $this->db->get(); 
    }
    
    function get_period_vamount($cur='IDR',$acc=null,$month=null,$year=null,$emonth=null,$eyear=null)
    {
        $this->db->select_sum('balances.vamount');
        $this->db->select_sum('balances.end');
        $this->db->from('balances');
        $this->db->where('currency', $cur);
        $this->db->where("month BETWEEN '".$month."' AND '".$emonth."'");
        $this->db->where("year BETWEEN '".$year."' AND '".$eyear."'");
        $this->cek_null($acc,"account_id");
        return $this->db->get(); 
    }
    
    private function cek_between_month($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("MONTH(gls.dates) BETWEEN '".$start."' AND '".$end."'"); }
    }
    
    private function cek_between_year($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("YEAR(gls.dates) BETWEEN '".$start."' AND '".$end."'"); }
    }
    
    private function cek_between($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("gls.dates BETWEEN '".$start."' AND '".$end."'"); }
    }

}

?>
