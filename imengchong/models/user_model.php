<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class User_Model extends MC_Model {
    //default value
    const VAL_DEFAULT_FOCUS_USER_PAGE = 8; 
    const VAL_AVATAR_RESIZE_L = 120;
    const VAL_AVATAR_RESIZE_M = 40;
    const VAL_AVATAR_RESIZE_S = 28;

    //table relate to user_model
    const TBN_USER = 'mc_user';
    const TBN_USER_STATUS = 'mc_user_status';
    const TBN_USER_CONFIRM = 'mc_user_confirm';
    const TBN_USER_FOCUS = 'mc_user_focus';
    const TBN_USER_AVATAR_STORE = 'mc_user_avatar_store';

    //default value
    const DEFAULT_SOURCE_COUNT = '{"source_count":[0,0,0,0,0,0]}';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief 获取用户信息
     *  $input = array (
     *      'user_id' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/27 22:53:42
    **/
    public function get_user_info($input) {
        $res = $this->get_user_info_batch(array('user_ids' => array($input['user_id'])));
        if ($res) {
            return $res[$input['user_id']];
        }
        return FALSE;
    }

    /**
     * @brief 获取用户的sina_id
     *  $input = array (
     *      'sina_id' =>　xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/28 23:17:54
    **/
    public function get_user_id_by_sina_id($input) {
        $select_input = array (
            'table_name' => self::TBN_USER,
            'field' => array ('user_id'),
            'where' => array ('sina_id' => $input['sina_id']),
        );
        return $this->mc_orm->mc_get($select_input);
    }

    /**
     * @brief 批量获取用户信息
     *  $input = array (
     *      'user_ids' => array(xx,xx,xx),
     *  );
     * @author wuyuanhao
     * @date 2012/03/25 22:37:04
    **/
    public function get_user_info_batch($input) {
        $user_ids = implode(",", $input['user_ids']);
        if (!$user_ids) {
            $this->log->warning('input user_ids is null');
            return FALSE;
        }
        //user
        $query = "SELECT * FROM ".self::TBN_USER." WHERE `user_id` IN (".$user_ids.") ORDER BY `user_id` DESC";
        $user_result = $this->mc_orm->mc_query(array('type'=>'query','sql'=>$query));
        $result = array();
        foreach ($user_result as $v) {
            $result[$v['user_id']] = $v;
        }
        //user_status
        $query = "SELECT * FROM ".self::TBN_USER_STATUS." WHERE `user_id` IN (".$user_ids.") ORDER BY `user_id` DESC";
        $user_status_result = $this->mc_orm->mc_query(array('type'=>'query', 'sql'=>$query));
        foreach ($user_status_result as $v) {
            foreach ($v as $kk => $vv) {
                $result[$v['user_id']][$kk] = $vv;
            }
        }
        return $result;
    }

    /**
     * @brief 关注列表,按关注时间倒排
     *  $input = array (
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  );
     *  
     * @author wuyuanhao
     * @date 2012/03/28 00:12:11
    **/
    public function get_user_focus_list($input) {
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_FOCUS_USER_PAGE;
        $select_input = array (
                'table_name' => self::TBN_USER_FOCUS,
                'field' => array ('focused_user_id'),
                'where' => array ('focus_user_id' => $input['user_id']),
                'order_by' => array ('create_time' => 'desc'),
                'limit' => array ($offset, $num),
                );
        return $this->mc_orm->mc_get($select_input); 
    }

    /**
     * @brief 获取头像数据
     *
     * @author wuyuanhao
     * @date 2012/03/29 01:13:45
    **/
    public function get_avatar_store($input) {
        $select_input = array (
            'table_name' => self::TBN_USER_AVATAR_STORE,
            'field' => array ('portrait_id', 'avatar_resize_l', 'avatar_resize_m', 'avatar_resize_s'),
            'where' => array ('portrait_id' => $input['portrait_id']),
        );
        return $this->mc_orm->mc_get($select_input);
    }

    /**
     * @brief 判断用户是否登陆
     *  $input = array (
     *      'user_id' => xx,
     *      'user_session' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/29 14:59:31
    **/
    public function check_login($input) {
        $select_input = array (
                'table_name' => self::TBN_USER_STATUS,
                'field' => array ('user_id'),
                'where' => array ('user_id' => $input['user_id'], 'user_session' => $input['user_session']),
                );
        return $this->mc_orm->mc_get($select_input);
    }

    /**
     * @brief 用户登陆
     *  $input = array (
     *      'user_id' => xx,
     *      'access_token' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/29 13:31:22
    **/
    public function user_login($input) {
        $update_input = array (
            'table_name' => self::TBN_USER_STATUS,
            'field_value' => array (
                'user_session' => $input['access_token'],
                'user_session_create_time' => time(),
            ),
            'where' => array ('user_id' => $input['user_id']),
        );
        $res = $this->mc_orm->mc_update($update_input);
        if (!$res) {
            return array('errno'=>MC_Errno::USER_LOGIN_FAILED);
        }
        return array('errno'=>MC_Errno::OK);
    }

    /**
     * @brief 用户第一次登陆，注册
     * $input = array (
     *      'sina_id' => xx,
     *      'user_name' => xx,
     *      'avatar_full_path' => xx,
     *      'avatar_raw_name' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/28 23:55:30
    **/
    public function user_register($input) {
        $protrait_id = 0;
        if (isset($input['avatar_full_path']) && isset($input['avatar_raw_name'])) {
            //头像压缩
            //resize l
            $config['image_library'] = 'gd2';
            $config['source_image'] = $input['avatar_full_path'];
            $config['create_thumb'] = TRUE;
            $config['maintain_ratio'] = TRUE;
            $config['quality'] = 100;
            $config['width'] = self::VAL_AVATAR_RESIZE_L;
            $config['height'] = self::VAL_AVATAR_RESIZE_L;
            $config['new_image'] = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_l.jpg';
            $this->load->library('image_lib', $config); 
            if (!$this->image_lib->resize()) {
                $error_msg = $this->image_lib->display_errors();
                $this->log->warning('avatar resize l failed! error:'.$error_msg.'input:'.var_export($input, TRUE));
                return array ('errno' => MC_Errno::USER_REGISTER_FAILED);
            }
            $new_image = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_l_thumb.jpg';
            $avatar_resize_l = file_get_contents($new_image);

            //resize m
            $config['width'] = self::VAL_AVATAR_RESIZE_M;
            $config['height'] = self::VAL_AVATAR_RESIZE_M;
            $config['new_image'] = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_m.jpg';
            $this->image_lib->initialize($config); 
            if (!$this->image_lib->resize()) {
                $error_msg = $this->image_lib->display_errors();
                $this->log->warning('avatar resize m failed! error:'.$error_msg.'input:'.var_export($input, TRUE));
                return array ('errno' => MC_Errno::USER_REGISTER_FAILED);
            }
            $new_image = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_m_thumb.jpg';
            $avatar_resize_m = file_get_contents($new_image);

            //resize s
            $config['width'] = self::VAL_AVATAR_RESIZE_S;
            $config['height'] = self::VAL_AVATAR_RESIZE_S;
            $config['new_image'] = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_s.jpg';
            $this->image_lib->initialize($config); 
            if (!$this->image_lib->resize()) {
                $error_msg = $this->image_lib->display_errors();
                $this->log->warning('avatar resize s failed! error:'.$error_msg.'input:'.var_export($input, TRUE));
                return array ('errno' => MC_Errno::USER_REGISTER_FAILED);
            }
            $new_image = ROOT_PATH.'/uploads/resize/'.$input['avatar_raw_name'].'_resize_s_thumb.jpg';
            $avatar_resize_s = file_get_contents($new_image);

            //avatar_insert
            $insert_input = array (
                    'table_name' => self::TBN_USER_AVATAR_STORE,
                    'field_value' => array (
                        'avatar_resize_l' => $avatar_resize_l,
                        'avatar_resize_m' => $avatar_resize_m,
                        'avatar_resize_s' => $avatar_resize_s,
                        )
                    );
            $portrait_id = $this->mc_orm->mc_add($insert_input);
            if (!$portrait_id) {
                $this->log->warning('insert mc_user_avatar_store failed! input:'.var_export($input, TRUE));
                return array('errno' => MC_Errno::USER_REGISTER_FAILED);
            }
        }

        $insert_input = array (
                'table_name' => self::TBN_USER,
                'field_value' => array (
                    'sina_id' => $input['sina_id'],
                    'user_name' => $input['user_name'],
                    'user_alias' => $input['user_name'],
                    'user_email' => '',
                    'status' => 0,
                    'portrait_id' => $protrait_id,
                    'create_time' => time(),
                    ),
                );
        $user_id = $this->mc_orm->mc_add($insert_input);
        if (!$user_id) {
            $this->log->warning('insert mc_user failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::USER_REGISTER_FAILED);
        }
        
        $source_count = self::DEFAULT_SOURCE_COUNT;
        $insert_input = array (
                'table_name' => self::TBN_USER_STATUS,
                'field_value' => array (
                        'user_id' => $user_id,
                        'focused_count' => 0,
                        'source_count' => $source_count,
                        'focus_count' => 0,
                        'like_animal_count' => 0,
                    ),
                );
        $res = $this->mc_orm->mc_add($insert_input);
        if (!$res) {
            $this->log->warning('insert mc_user_status failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::USER_REGISTER_FAILED);
        }
        
        return array('errno' => MC_Errno::OK);
    }

    /**
     * @brief 关注用户
     *  $input = array (
     *      'focus_user_id' => xx,
     *      'focused_user_id' => xx,
     *  );
     * @author wuyuanhao
     * @date 2012/03/27 17:32:31
    **/
    public function focus_user($input) {
        //check if record exist
        $select_input = array (
                'table_name' => self::TBN_USER_FOCUS,
                'field' => array ('focus_user_id'),
                'where' => array ('focus_user_id' => $input['focus_user_id'], 'focused_user_id' => $input['focused_user_id']),
                );
        $select_res = $this->mc_orm->mc_get($select_input);
        if ($select_res) {
            $this->log->warning('record exist input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::OK);
        }

        $add_input = array (
                'table_name' => self::TBN_USER_FOCUS,
                'field_value' => array (
                    'focus_user_id' => $input['focus_user_id'], 
                    'focused_user_id' => $input['focused_user_id'], 
                    'create_time' => time(),
                    ),
                );
        $add_res = $this->mc_orm->mc_add($add_input);
        if (!$add_res) {
            $this->log->warning('add mc_user_focus failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::FOCUS_USER_FAILED);
        }
        //add focus count
        $update_sql = "UPDATE `mc_user_status` SET `focus_count`=`focus_count`+1 WHERE `user_id`=".$input['focus_user_id'];
        $update_res = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>$update_sql));
        if (!$update_res) {
            $this->log->warning('update mc_user_status failed! input:'.var_export($input,TRUE));
            return array('errno' => MC_Errno::FOCUS_USER_FAILED);
        }
        //add focused count
        $update_sql = "UPDATE `mc_user_status` SET `focused_count`=`focused_count`+1 WHERE `user_id`=".$input['focused_user_id'];
        $update_res = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>$update_sql));
        if (!$update_res) {
            $this->log->warning('update mc_user_status failed! input:'.var_export($input,TRUE));
            return array('errno' => MC_Errno::FOCUS_USER_FAILED);
        }
 
        return array('errno' => MC_Errno::OK);
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
