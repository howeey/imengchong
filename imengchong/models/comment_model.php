<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class Comment_Model extends MC_Model {
    //default value
    const VAL_DEFAULT_COMMENT_PAGE = 10; 

    //logci flag
    const FLAG_VALIED_COMMENT = 0;
    
    //table relate to comment_model
    const TBN_COMMENT = 'mc_comment';
    const TBN_COMMENT_REFFER = 'mc_comment_reffer';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief 添加评论
     *  $input = array (
     *      'user_id' => xx,
     *      'reffer_id' => xx,
     *      'content' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/26 17:59:01
    **/
    public function add_comment($input) {
        //comment
        $insert_mc_comment = array (
                'table_name' => self::TBN_COMMENT,
                'field_value' => array (
                    'reffer_id' => $input['reffer_id'],
                    'user_id' => $input['user_id'],
                    'content' => $input['content'],
                    'create_time' => time(),
                    'flag' => self::FLAG_VALIED_COMMENT,
                    )
                );
        $insert_id = $this->mc_orm->mc_add($insert_mc_comment);
        if (!$insert_id) {
            $this->log->warning('insert mc_comment failed! input:'.var_export($input, TRUE));
            return array ('errno' => MC_Errno::ADD_COMMENT_FAILED);
        }

        //comment count
        $select = array (
            'table_name' => self::TBN_COMMENT_REFFER,
            'field' => array('reffer_id'),
            'where' => array('reffer_id' => $input['reffer_id']),
        );
        $res = $this->mc_orm->mc_get($select); 
        if ($res) {
            //record exist
            $sql = "UPDATE `".self::TBN_COMMENT_REFFER."` SET `comment_count`=`comment_count`+1 WHERE `reffer_id`=".$input['reffer_id'];
            $sql_res = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>$sql)); 
            if (!$sql_res) {
                $this->log->warning('update mc_comment_reffer failed! input:'.var_export($input, TRUE));
                return array ('errno' => MC_Errno::ADD_COMMENT_FAILED);
            }
        } else {
            //record not exist
            $insert_mc_comment_reffer = array (
                'table_name' => self::TBN_COMMENT_REFFER,
                'field_value' => array (
                    'reffer_id' => $input['reffer_id'],
                    'comment_count' => 1,
                ),
            );
            $insert_res = $this->mc_orm->mc_add($insert_mc_comment_reffer);
            if (!$insert_res) {
                $this->log->warning('insert mc_comment_reffer failed! input:'.var_export($input, TRUE));
                return array ('errno' => MC_Errno::ADD_COMMENT_FAILED);
            }
        }

        return array ('errno' => MC_Errno::OK);
    }

    /**
     * @brief 获取评论列表
     *  $input = array (
     *      'reffer_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/26 21:26:22
    **/
    public function get_comment($input) {
        if (!isset($input['reffer_id'])) {
            $this->log->warning('get_comment model param error! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::MODEL_PARAM_ERROR);
        }
        $reffer_id = $input['reffer_id'];
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_COMMENT_PAGE;
        $select = array (
                'table_name' => self::TBN_COMMENT,
                'field' => array ('comment_id', 'reffer_id', 'user_id', 'create_time', 'content'),
                'where' => array ('reffer_id' => $reffer_id, 'flag'=>self::FLAG_VALIED_COMMENT),
                'order_by' => array ('create_time' => 'desc'),
                );
        $select_res = $this->mc_orm->mc_get($select);
        return $select_res;
    }

    
    /**
     * @brief 获取评论主题的信息，如评论数等
     *  $input = array (
     *      'reffer_ids' => array (1,2,3),
     *  )
     *  $output = array (
     *      'id_0' => array ('comment_count' => xx, 'reffer_id' =>xx,)
     *      'id_1' => array ('comment_count' => xx, 'reffer_id' =>xx,)
     *  )
     * @author wuyuanhao
     * @date 2012/03/27 15:11:45
    **/
    public function get_comment_status($input) {
        $in_id = implode(",", $input['reffer_ids']);
        if (!$in_id) {
            $this->log->warning('input reffer_ids is null');
            return FALSE;
        }
        $query = "SELECT * FROM ".self::TBN_COMMENT_REFFER. " WHERE `reffer_id` IN($in_id) ORDER BY `reffer_id` DESC";
        $query_result = $this->mc_orm->mc_query(array('type'=>'query','sql'=>$query));
        if (!$query_result) {
            return array();
        }
        $result = array();
        foreach ($query_result as $v) {
            $result[$v['reffer_id']] = $v;
        }
        foreach ($input['reffer_ids'] as $v) {
            if (!isset($result[$v])) {
                $result[$v] = array();
            }
        }
        return $result;
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
