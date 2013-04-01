<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/***************************************************************************
 * 
 * Copyright (c) 2011 Qingting.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/*
* Object Relationship Mapping
*
* base classes
*
*/
class MC_Orm {

    protected $table_name_;        ///<table_name init by sub classes  

    public function __construct() {
        $ci =& get_instance();
        //TODO load database
        $ci->load->database(); 
    }

    /**
     * @brief destruct
     *
     * @return:  public function 
     * @retval:   
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/18 18:26:47
    **/
    public function __destruct() {
    }

    public function mc_get($input) {
        $ci =& get_instance();
        $ci->load->library('log');

        $this->table_name_ = $input['table_name'];

        $sql = $this->_get($input);
        $ci->log->debug('SELECT SQL:'.$sql);

        $query = $ci->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return FALSE;
    }

    public function mc_add($input) {
        $ci =& get_instance();
        $ci->load->library('log');

        $this->table_name_ = $input['table_name'];
        
        $sql = $this->_add($input);
        $ci->log->debug('INSERT SQL:'.$sql);

        $query = $ci->db->query($sql);
        $insert_id = $ci->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return $ci->db->affected_rows();
    }

    public function mc_del($input) {
        $ci =& get_instance();
        $ci->load->library('log');

        $this->table_name_ = $input['table_name'];

        $sql = $this->_delete($input);
        $ci->log->debug('DELETE SQL:'.$sql);
        
        $query = $ci->db->query($sql);
        return $ci->db->affected_rows();
    }

    public function mc_update($input) {
        $ci =& get_instance();
        $ci->load->library('log');

        $this->table_name_ = $input['table_name'];

        $sql = $this->_update($input);
        $ci->log->debug('UPDATE SQL:'.$sql);
        
        $query = $ci->db->query($sql);
        return $ci->db->affected_rows();
    }

    public function mc_count($input) {
        $ci =& get_instance();
        $ci->load->library('log');

        $this->table_name_ = $input['table_name'];
        $sql = $this->_count($input);
        $ci->log->debug('COUNT SQL:'.$sql);

        $query = $ci->db->query($sql);
        if ($query->num_rows() > 0) {
            $ret = $query->result_array();
            return $ret[0];
        }
        return FALSE;
    }

    public function mc_query($input) {
        $ci =& get_instance();
        $ci->load->library('log');
        $sql = $input['sql']; 
        $type = $input['type'];

        $ci->log->debug('QUERY SQL:'.$sql);
       
        //query
        if ('query' == $type) {
            $query = $ci->db->query($sql);
            if ($query->num_rows() > 0) {
                return $query->result_array();
            }
        }
        //update
        if ('update' == $type) {
            $query = $ci->db->query($sql);
            return $ci->db->affected_rows();
        }
        return FALSE;
    }

    /**
     * @brief  get interface
     *
     * $input = array (
     *     'table_name' => 'tb_name',   //table_name
     *     'field' => array ('f1', 'f2', 'f3'),    //获取的字段
     *     'where' => 3种, //option, 条件语句 4种方法，和CI Active Record一样
     *     //1 array('name' => $name, 'title' => $title, 'status' => $status);
     *     //2 array('name !=' => $name, 'id <' => $id, 'date >' => $date);
     *        //3 "name='Joe' AND status='boss' OR status='active'";
     *     'limit' => array(10) 或者 'limit' => array(10,20),
     *     'order_by'=> array ('title' => 'asc', 'name' => 'desc', 'f3'=>'random'),
     * ),
     *
     * @return:  public function 
     * @retval:  sql string 
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/19 14:09:17
    **/
    protected function _get($input) {
        //get ci instance
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_get');
            return '';
        }
        //field
        if (!array_key_exists('field', $input) || !is_array($input['field']) || 0 == count($input['field'])) {
            $ci->log->warning('input param.field is null! @Orm::_get');
            return '';
        }
        $ci->db->select(implode(",", $input['field']));
        //from
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_get');
            return '';
        }
        $ci->db->from($this->table_name_);
        //where
        if (array_key_exists('where', $input) && (is_array($input['where']) || is_string($input['where'])) ) {
            $ci->db->where($input['where']);
        }
        //order_by
        if (array_key_exists('order_by', $input) && is_array($input['order_by'])) {
            foreach ($input['order_by'] as $k => $v) {
                $ci->db->order_by($k, $v);
            }
        }
        //limit
        if (array_key_exists('limit', $input) && is_array($input['limit'])) {
            if (1 == count($input['limit'])) {
                $ci->db->limit($input['limit'][0]);    
            } else if (2 == count($input['limit'])) {
                $ci->db->limit($input['limit'][1], $input['limit'][0]);    
            }
        }
        return $ci->db->get_sql();
    }

    /**
     * @brief insert sql interface
     *
     *     'input' => 1种，//字段=value
     *     //1 array ('f1'=>'v1', 'f2'=>'v2'),
     *
     * @return:  public function 
     * @retval:  sql str 
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/19 14:09:21
    **/
    protected function _add($input) {
        //get ci instance
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_add');
            return '';
        }
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_add');
            return '';
        }
        if (!is_array($input)) {
            $ci->log->warning('input param is null! @Orm::_add');
            return '';
        }
        
        if (!array_key_exists('field_value', $input) || !is_array($input['field_value'])) {
            $ci->log->warning('input param.field_value is null! @Orm::_add');
            return '';
        }
 
        return $ci->db->insert_sql($this->table_name_, $input['field_value']);
    }

    /**
     * @brief update sql interface
     *
     *      'input' => array (
     *          'field_value' => array ('f1'=>'v1', 'f2'=>'v2'),
     *          'where' => 和get方法一样
     *      ),
     *
     * @return:  public function 
     * @retval:   
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/19 14:09:25
    **/
    protected function _update($input) {
        //get ci instance
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_update');
            return '';
        }
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_update');
            return '';
        }
        if (!is_array($input)) {
            $ci->log->warning('input param is null! @Orm::_update');
            return '';
        }
        if (array_key_exists('where', $input) && (is_string($input['where']) || is_array($input['where'])) ) {
            $ci->db->where($input['where']);
        }

        if (!array_key_exists('field_value', $input) || !is_array($input['field_value'])) {
            $ci->log->warning('input param.field_value is null! @Orm::_update');
            return '';
        }
        return $ci->db->update_sql($this->table_name_, $input['field_value']);
    }

    /**
     * @brief delete sql interface
     *
     * @return:  public function 
     * @retval:   
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/19 14:09:28
    **/
    protected function _delete($input) {
        //get ci instance
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_delete');
            return '';
        }
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_delete');
            return '';
        }
        if (!is_array($input)) {
            $ci->log->warning('input param is null! @Orm::_delete');
            return '';
        }

        //where
        if (array_key_exists('where', $input) && (is_array($input['where']) || is_string($input['where'])) ) {
            $ci->db->where($input['where']);
        }

        return $ci->db->delete_sql($this->table_name_);
    }

    /**
     * @brief: count sql interface
     *
     * @return:  protected function 
     * @retval: string(sql)
     * @param: $input = array('where' => string(where_sql))
     * @see 
     * @note 
     * @author eddix
     * @date 2011/06/28 16:12:52
    **/
    protected function _count($input) {
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_get');
            return '';
        }
        //field
        $ci->db->select("count(*) as count_number");
        //from
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_count');
            return '';
        }
        $ci->db->from($this->table_name_);
        // where
        if (array_key_exists('where', $input) && (is_string($input['where']) || is_array($input['where']))) {
            $ci->db->where($input['where']);
        }
        return $ci->db->get_sql();
    }
    /**
     * @brief compare and set sql interface
     *
     *      'input' => array (
     *          'field_value' => array('f1' => 1, 'f2' => -2),
     *          'where' => 和get方法一样
     *      ),
     *
     * @return:  public function 
     * @retval:   
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2011/06/19 14:09:25
    **/
    protected function _cas($input) {
        $ci =& get_instance();
        if (NULL == $ci->db) {
            $ci->log->warning('db object is null! @Orm::_update');
            return '';
        }
        if (!is_string($this->table_name_) || 0 == strlen($this->table_name_)) {
            $ci->log->warning('this->table_name_ is null! @Orm::_cas');
            return '';
        }
        if (!is_array($input)) {
            $ci->log->warning('input param is null! @Orm::_cas');
            return '';
        }
        if (! array_key_exists('where', $input)) {
            $ci->log->warning('where param is null! @Orm::_cas');
            return '';
        }
        if(!(is_string($input['where']) ||is_array($input['where']))){
            $ci->log->warning('where param is neither array nor string! @Orm::_cas');
            return '';
        }
        if (! array_key_exists('field_value', $input) || !is_array($input['field_value'])) {
            $ci->log->warning('field_value is null! @Orm::_cas');
            return '';
        }

        //where
        $ci->db->where($input['where']);
        $field_value = array();
        foreach($input['field_value'] as $field => $delta) {
            if ($delta < 0) {
                $field_value[$field] = sprintf("%s%d", $field, $delta);
            } else {
                $field_value[$field] = sprintf("%s+%d", $field, $delta);
            }
        }
        $ci->db->set($field_value, null, false); // disable eacapse value
       
        return $ci->db->update_sql($this->table_name_);
    }

}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
