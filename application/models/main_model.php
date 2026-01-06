<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main_model extends Custom_Model
{
    private $logs;
    
    function __construct()
    {
        parent::__construct();
//        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('main');
    }
     
    protected $table = 'admin_menu';
    protected $field = array('id', 'parent_id', 'name', 'modul', 'url', 'menu_order', 'class_style', 'id_style', 
                             'icon', 'target', 'parent_status');
    protected $com;
    
    function get_last_ar_between($val1,$val2)
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        $this->db->where("dates BETWEEN (NOW() - INTERVAL ".$val1." DAY) AND (NOW() - INTERVAL ".$val2." DAY) ");
        $this->db->where('confirmation', 0);
        $this->db->where('deleted', null);
        return $this->db->get(); 
    }

    function get_last_ar($val1)
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        $this->db->where("dates <= (NOW() - INTERVAL ".$val1." DAY)");
        $this->db->where('confirmation', 0);
//        $this->db->where('approved', 1);
        $this->db->where('deleted', null);
        return $this->db->get();
    }

    function get_ar_list()
    {
        $this->db->select('id, dates, cust_id, amount');
        $this->db->from('sales');
        $this->db->where('paid_date IS NULL');
//        $this->db->where('currency', 'IDR');
        $this->db->where('confirmation', 0);
        $this->db->where('deleted', null);
        return $this->db->get();
    }

    // ============== purchase ===================================

    function get_last_ap_between($val1,$val2)
    {
        $this->db->select('SUM(p2) AS total');
        $this->db->from('purchase');
        $this->db->where("dates BETWEEN (NOW() - INTERVAL ".$val1." DAY) AND (NOW() - INTERVAL ".$val2." DAY) ");
        $this->db->where('approved', 1);
        $this->db->where('currency', 'IDR');
        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        return $this->db->get();
    }

    function get_last_ap($val1)
    {
        $this->db->select('SUM(p2) AS total');
        $this->db->from('purchase');
        $this->db->where("dates <= (NOW() - INTERVAL ".$val1." DAY)");
        $this->db->where('approved', 1);
        $this->db->where('currency', 'IDR');
        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        return $this->db->get();
    }

    function get_ap_list($type=null)
    {
        $this->db->select('no, dates, vendor, p2');
        $this->db->from('purchase');
        $this->db->where('approved', 1);
        $this->db->where('currency', 'IDR');
        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        return $this->db->get();
    }
    
    // mobile api need
    function get_ap_sum($type=0,$cur='IDR',$start,$end)
    {
        // 0 == 1 hari, 1 == 1 bulan, 2 == 1 tahun, 3 == custom
        $ps = new Period();
        $ps = $ps->get();
        
        $this->db->select('SUM(p2) AS total');
        $this->db->from('purchase');
        if ($type == 0){ $this->cek_between($start, $start); }
        elseif ($type == 1){ $this->cek_between_month($ps->month, $ps->month); }
        elseif ($type == 2){ $this->cek_between_year($ps->year, $ps->year); }
        elseif ($type == 3){ $this->cek_between($start, $end); }
        $this->db->where('approved', 1);
        $this->db->where('currency', $cur);
//        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['total']);
    }
    
    // ringkasan pembelian
    // jumlah pembelian yang belum d bayar / status 0
    function get_ap_sum_credit($cur='IDR')
    {
        $this->db->select('SUM(p2) AS total');
        $this->db->from('purchase');
        $this->db->where('approved', 1);
        $this->db->where('currency', $cur);
        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['total']);
    }
    
    // jumlah pembelian yang belum d bayar & jatuh tempo > 30 hari
    function get_ap_sum_credit_overdue($cur='IDR',$val1=30)
    {
        $this->db->select('SUM(p2) AS total');
        $this->db->from('purchase');
        $this->db->where("dates <= (NOW() - INTERVAL ".$val1." DAY)");
        $this->db->where('approved', 1);
        $this->db->where('currency', $cur);
        $this->db->where('status', 0);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['total']);
    }
    
    // jumlah pelunasan pembelian dalam tempo 30 hari
    function get_ap_payment_sum($cur='IDR',$val1=30,$val2=0)
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('ap_payment');
        $this->db->where("dates BETWEEN (NOW() - INTERVAL ".$val1." DAY) AND (NOW() - INTERVAL ".$val2." DAY) ");
        $this->db->where('approved', 1);
        $this->db->where('currency', $cur);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['amount']);
    }
    
   // ================== batas pembelian =======================  
    
    // ringkasan penjualan
    // jumlah penjualan yang belum d bayar / status 0
    function get_ar_sum_credit($cur='IDR')
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        $this->db->where('paid_date', null);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['amount']);
    }
    
    // jumlah pembelian yang belum d bayar & jatuh tempo > 30 hari
    function get_ar_sum_credit_overdue($cur='IDR',$val1=30)
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        $this->db->where("dates <= (NOW() - INTERVAL ".$val1." DAY)");
        $this->db->where('paid_date', null);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['amount']);
    }
    
    // jumlah pelunasan pembelian dalam tempo 30 hari
    function get_ar_payment_sum($cur='IDR',$val1=30,$val2=0)
    {
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        $this->db->where("paid_date BETWEEN (NOW() - INTERVAL ".$val1." DAY) AND (NOW() - INTERVAL ".$val2." DAY) ");
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['amount']);
    }
    
    // ===========  batas ringkasan ==================
    
    function get_ar_sum($type=0,$cur='IDR',$start,$end)
    {
        // 0 == 1 hari, 1 == 1 bulan, 2 == 1 tahun, 3 == custom
        $ps = new Period();
        $ps = $ps->get();
        
        $this->db->select('SUM(amount) AS amount');
        $this->db->from('sales');
        if ($type == 0){ $this->cek_between($start, $start); }
        elseif ($type == 1){ $this->cek_between_month($ps->month, $ps->month); }
        elseif ($type == 2){ $this->cek_between_year($ps->year, $ps->year); }
        elseif ($type == 3){ $this->cek_between($start, $end); }
//        $this->db->where('confirmation', 0);
        $this->db->where('deleted', null);
        $res = $this->db->get()->row_array(); return floatval($res['amount']);
    }
    
    private function cek_between_month($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("MONTH(dates) BETWEEN '".$start."' AND '".$end."'"); }
    }
    
    private function cek_between_year($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("YEAR(dates) BETWEEN '".$start."' AND '".$end."'"); }
    }
    
    private function cek_between($start,$end,$field='dates')
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("DATE(".$field.") BETWEEN '".setnull($start)."' AND '".setnull($end)."'"); }
    }

    // ===================== check in ========================================

    function checkin()
    {
        $this->db->select('check_no, no, bank, currency, dates, due, amount');
        $this->db->from('ar_payment');
//        $this->db->where("dates BETWEEN '".setnull($start)."' AND '".setnull($end)."'");
        $this->db->where('approved', 1);
        $this->db->where('check_no IS NOT NULL', null, false);

        return $this->db->get();
    }


    function checkout($table=null)
    {
        $this->db->select('check_no, no, bank, currency, dates, due, amount');
        $this->db->from($table);
//        $this->db->where("dates BETWEEN '".setnull($start)."' AND '".setnull($end)."'");
        $this->db->where('approved', 1);
        $this->db->where('check_no IS NOT NULL', null, false);

        return $this->db->get();
    }

    function get_min_product()
    {
        $this->db->select('product.id, product.sku, product.manufacture, product.category, product.currency, product.name,
                           stock.qty, product.unit, product.price');
        $this->db->from('product,stock');
        $this->db->where('product.id = stock.product_id');
        $this->db->where('stock.qty <=', 1);
        $this->db->where('product.deleted', NULL);
        $this->db->where('product.publish', 1);
        $this->db->order_by('product.name', 'asc');
        $this->db->where('deleted', null);
        return $this->db->get();
    }

          

}

?>