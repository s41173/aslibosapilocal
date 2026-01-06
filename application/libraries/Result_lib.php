<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Result_lib extends Custom_Model {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'result';
        $this->field = $this->db->list_fields($this->tableName);
        $this->barcode_item = new Barcode_item_lib();
    }
    protected $field,$barcode_item;
    
    function cek_relation($pid=0,$field='product'){
        $this->db->select($this->field);
        $this->db->where($field, $pid);
        $val = $this->db->get($this->tableName)->num_rows();
        if ($val > 0){ return FALSE; }else{ return TRUE; }
    }
    
    function cleaning(){
        return $this->db->truncate($this->tableName);
    }
    
    function counter_model($type=1)
    {
       $this->db->select_max('id');
       $query = $this->db->get($this->tableName)->row_array(); 
       if ($type == 0){ return intval($query['id']+1); }else { return intval($query['id']); }
    }
    
    function create($code,$position,$data){
        
           $res = $this->db->insert($this->tableName, $data);
            if ($res == true){
              if ($this->barcode_item->set_used($code, $position) == true){  return TRUE;}else{ return FALSE; }
            }
            else{ return FALSE; }  
    }
    
    function searching_unsync_data($limit=50){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('finish_status', 1);
        $this->db->where('code1status', 1);
        $this->db->where('code2status', 1);
        $this->db->where('sync IS NULL');
        $this->db->limit($limit);
        return $this->db->get()->result(); 
    }
    
    function set_stts_sync($uid=0,$sync=null,$error=null){
        $trans = array("sync" => setnull($sync),"sync_error" => setnull($error));
        return $this->update($uid, $trans);
    }
    
    function searching_finish_status($type=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('finish_status', 0);
        if ($type == 0){
            $query = $this->db->get()->num_rows(); 
            // jika ada yang masih waiting maka rturn TRUE, jika TRUE maka update jika FALSE maka insert
            if ($query > 0){ return TRUE; }else{ return FALSE; }
        }
        else{
            $query = $this->db->get()->row(); 
            return $query;
        }
        
    }
    
    function searching_code_type($code=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('code', $code);
        $this->db->or_where('code2', $code);

        $query = $this->db->get()->num_rows(); 
        if ($query > 0){ return 1; }else{ return 0; }
    }
    
    function edit_code($code,$position,$data){
       
       $val = $this->searching_finish_status(1);
       $this->db->where('id', $val->id);
       $res = $this->db->update($this->tableName, $data);
        if ($res == true){
          if ($this->barcode_item->set_used($code, $position) == true){  return TRUE;}else{ return FALSE; }
        }
        else{ return FALSE; }  
    }
    
     function edit_code_picture($code,$position,$data){
       
       $this->db->where('code', $code);
       $this->db->or_where('code2', $code);
       $res = $this->db->update($this->tableName, $data);
        if ($res == true){
          if ($this->barcode_item->set_used($code, $position) == true){  return TRUE;}else{ return FALSE; }
        }
        else{ return FALSE; }  
    }
    
    function cek_run_status($code=0){
       $this->db->select($this->field);
       $this->db->from($this->tableName); 
       $this->db->where('code', $code);
       $this->db->or_where('code2', $code);
       $res = $this->db->get()->row();
       if ($res){
         if ($res->finish_status == 1){ return TRUE; }else{ return FALSE; }    
       }else{ return FALSE; }
    }
    
    function cek_valid($code, $position) {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->where('code', $code);

        $query = $this->db->get();

        // Jika data tidak ditemukan, berarti kode belum pernah dipakai → return TRUE
        if ($query->num_rows() == 0) {
            return TRUE;
        }
        // Jika sudah ada data dan gambar sudah dibuat → return FALSE
        return FALSE;
    }
    
    function cek_conveyor($code)
    {
        $this->db->select($this->field);
        $this->db->where('code', $code);
        $val = $this->db->get($this->tableName)->row();
        if ($val->pict_top_created != null && $val->pict_bot_created != null){
            return TRUE;
        }else{ return FALSE; }
    }
    
    function valid_trans_status($code="",$position='top'){
        if ($this->searching_finish_status() == TRUE){
            $result = $this->searching_finish_status(1);
            if ($position == 'top'){
                if ($result->code == NULL || $result->code == ""){ return TRUE; }else{ return FALSE; } 
            }elseif ($position == 'bot'){
                if ($result->code2 == NULL || $result->code2 == ""){ return TRUE; }else{ return FALSE; } 
            }
        }
        else{ return TRUE; }
    }
    
    function history($produksi=null,$sync=null,$limit=50,$offset=0,$count=0){
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($produksi, 'produksi_id');
        
        if ($sync == 1){
          $this->db->where('sync IS NOT NULL');
        }elseif ($sync == 0){
          $this->db->where('sync IS NULL');  
        }
        
        $this->db->limit($limit, $offset);
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    
}

/* End of file Property.php */