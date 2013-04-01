<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/


class Photo_Model extends MC_Model {
    //default value
    const VAL_DEFAULT_PHOTO_PAGE = 10; 
    const VAL_RESIZE_L_WIDTH = 560;
    const VAL_RESIZE_M_WIDTH = 205;

    //tag define
    const TAG_UNDEFINED = 1;

    //photo_source define
    const FROM_UNDEFINED = 1;
    const FROM_HOME = 2;
    const FROM_FRIEND = 3;
    const FROM_WEB = 4;
    const FROM_STREET = 5;

    //table relate to photo_model
    const TBN_USER_FOCUS = 'mc_user_focus';
    const TBN_USER_STATUS = 'mc_user_status';
    const TBN_ANIMAL_LIKED = 'mc_animal_liked';
    const TBN_PHOTO = 'mc_photo';
    const TBN_PHOTO_EXIF = 'mc_photo_exif';
    const TBN_PHOTO_MENG = 'mc_photo_meng';
    const TBN_PHOTO_STATUS = 'mc_photo_status';
    const TBN_PHOTO_TAG = 'mc_photo_tag';
    const TBN_PHOTO_STORE = 'mc_photo_store';
    const TBN_TAG_NEWS_LINK = 'mc_tag_news_link';


    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief 获取单张图片信息
     *
     *  $input = array ('photo_id'=>xx);
     *
     *  $output = array (
     *      'photo_id' =>xx,
     *       ...
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/19 18:16:29
     **/
    public function get_photo_info($input) {
        return array();
    }

    /**
     * @brief 上传图片 
     *
     * @author wuyuanhao
     * @date 2012/03/19 18:15:16
     **/
    public function create_photo($input) {
        $insert_mc_photo = array (
                'table_name' => self::TBN_PHOTO,
                'field_value' => array (
                    'animal_id' =>  $input['animal_id'],
                    'user_id' => $input['user_id'],
                    'store_ids' => $input['store_ids'],
                    'tag_ids' => $input['tag_ids'],
                    'source' => $input['source'],
                    'type' => $input['type'],
                    'class' => $input['class'],
                    'photo_num' => $input['photo_num'],
                    'description' => $input['description'],
                    'create_time' => time(),
                    )
                );
        $insert_id = $this->mc_orm->mc_add($insert_mc_photo);
        $photo_id = $insert_id;
        if (!$insert_id) {
            $this->log->warning('insert mc_photo failed! input:'.var_export($input,TRUE));
            return array ('errno' => MC_Errno::CREATE_PHOTO_FAILED);
        }

        $insert_mc_photo_status = array (
                'table_name' => self::TBN_PHOTO_STATUS,
                'field_value' => array (
                    'photo_id' => $insert_id,
                    'meng_count' => 0, 
                    'share_record' => self::JSON_NULL,
                    ),
                );
        $insert_res = $this->mc_orm->mc_add($insert_mc_photo_status);
        if (!$insert_res) {
            $this->log->warning('insert mc_photo_status failed! input:'.var_export($input,true));
            return array ('errno' => MC_Errno::CREATE_PHOTO_FAILED);
        }

        //json format 
        //{"tag_ids":[0,1,2,3]}
        $json_tag_ids = '';
        if (isset($input['tag_ids'])) {
            $json_tag_ids = $input['tag_ids']; 
        } else {
            $json_tag_ids = '{"tag_ids":['.self::TAG_UNDEFINED.']}';
        }
        $tag_ids = json_decode($input['tag_ids'],TRUE);
        foreach ($tag_ids['tag_ids'] as $v) {
            $insert_mc_tag_news_link = array (
                    'table_name' => self::TBN_TAG_NEWS_LINK,
                    'field_value' => array (
                        'tag_id' => $v,
                        'reffer_id' => $insert_id,
                        'create_time' => time(),
                        ),
                    );
            $insert_res = $this->mc_orm->mc_add($insert_mc_tag_news_link);
            if (!$insert_res) {
                $this->log->warning('insert mc_tag_news_link failed! input:'.var_export($input,true)); 
                return array ('errno' => MC_Errno::CREATE_PHOTO_FAILED);
            }
        }

        //update source_count
        $select = array (
            'table_name' => self::TBN_USER_STATUS,
            'field' => array('source_count'),
            'where' => array('user_id' => $input['user_id']),
        );
        $select_res = $this->mc_orm->mc_get($select);
        if (!$select_res) {
            $this->log->warning('select user_status failed! input:'.var_export($input,true)); 
            return array ('errno' => MC_Errno::CREATE_PHOTO_FAILED);
        }
        $source_count = json_decode($select_res[0]['source_count'], TRUE);
        $source_count['source_count'][$input['source']] += 1;
        $source_count_json = json_encode($source_count);
        $update = array (
            'table_name' => self::TBN_USER_STATUS,
            'field_value' => array ('source_count' => $source_count_json),
            'where' => array('user_id' => $input['user_id']),
        );
        $update_res = $this->mc_orm->mc_update($update);
        if (!$update_res) {
            $this->log->warning('update user_status failed! input:'.var_export($input,true)); 
            return array ('errno' => MC_Errno::CREATE_PHOTO_FAILED);
        }

        return array('errno' => MC_Errno::OK, 'photo_id' => $photo_id);
    }

    /**
     * @brief 获取图片二进制数据
     *
     * @author wuyuanhao
     * @date 2012/03/24 18:03:29
     **/
    public function get_photo_store($input) {
        $get_mc_photo_store = array (
                'table_name' => self::TBN_PHOTO_STORE,
                'field' => array ('store_id', 'photo_resize', 'photo_resize_l', 'photo_resize_m'),
                'where' => array ('store_id' => $input['store_id']),
                );
        return $this->mc_orm->mc_get($get_mc_photo_store);
    }

    /**
     * @brief 图片存储
     *
     * @author wuyuanhao
     * @date 2012/03/24 18:03:15
     **/
    public function photo_store($input) {
        //图片压缩和转换
        $photo_data = $input['photo_data'];
        $photo_binary = file_get_contents($photo_data['full_path']);

        //resize l
        $config['image_library'] = 'gd2';
        $config['source_image'] = $photo_data['full_path'];
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['quality'] = 90;
        $config['width'] = self::VAL_RESIZE_L_WIDTH < $photo_data['image_width'] ? self::VAL_RESIZE_L_WIDTH : $photo_data['image_width'];
        $config['height'] = intval($config['width'] * $photo_data['image_height'] / $photo_data['image_width']);
        $config['new_image'] = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_l_'.$config['width'].$photo_data['file_ext'];

        $this->load->library('image_lib', $config); 
        if (!$this->image_lib->resize()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image resize l failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::PHOTO_STORE_FAILED);
        }
        $new_image = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_l_'.$config['width'].'_thumb'.$photo_data['file_ext'];
        $photo_binary_l = file_get_contents($new_image);

        //resize m
        $config['width'] = self::VAL_RESIZE_M_WIDTH < $photo_data['image_width'] ? self::VAL_RESIZE_M_WIDTH : $photo_data['image_width'];
        $config['height'] = intval($config['width'] * $photo_data['image_height'] / $photo_data['image_width']);
        $config['new_image'] = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_m_'.$config['width'].$photo_data['file_ext'];
        $this->image_lib->initialize($config); 
        if (!$this->image_lib->resize()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image resize m failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::PHOTO_STORE_FAILED);
        }
        $new_image = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_m_'.$config['width'].'_thumb'.$photo_data['file_ext'];
        $photo_binary_m = file_get_contents($new_image);


        //图片存储
        $insert_mc_photo_store = array (
                'table_name' => self::TBN_PHOTO_STORE,
                'field_value' => array (
                    'photo_resize' => $photo_binary,
                    'photo_resize_l' => $photo_binary_l,
                    'photo_resize_m' => $photo_binary_m,
                    'photo_data' => json_encode($photo_data),
                    ),
                );
        $insert_id = $this->mc_orm->mc_add($insert_mc_photo_store);
        if (!$insert_id) {
            $this->log->warning('insert mc_photo_store failed! input:'.var_export($input, TRUE));
            return array ('errno' => MC_Errno::PHOTO_STORE_FAILED);
        }
        return array('errno' => MC_Errno::OK, 'return' => array ('store_id' => $insert_id));
    }

    /**
     * @brief 获取首页最新图片id
     *  $input = array (
     *      'type_id' => type类型id //option 1未知 2猫 3狗
     *      'class_id' => type类型id //option 2级宠物分类
     *      'num' => xx,
     *      'offset' => xx,
     *  );
     *  
     * @author wuyuanhao
     * @date 2012/03/30 00:04:28
    **/
    public function get_newest_photo_id($input) {
        $select_input = array (
            'table_name' => self::TBN_PHOTO,
            'field' => array ('photo_id'),
            'order_by' => array ('create_time' => 'desc'),
        );
        if (isset($input['type_id'])) {
            $select_input['where'] = array ('type' => $input['type_id']);
        } else if (isset($input['class_id'])) {
            $select_input['where'] = array ('class' => $input['class_id']);
        }
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_PHOTO_PAGE;
        $select_input['limit'] = array ($offset, $num);
        return $this->mc_orm->mc_get($select_input);
    }


    /**
     * @brief 获取首页最新图片id
     *  $input = array (
     *      'tag_id' => tag类型id //option 无时返回所有类型的tag数据
     *      'num' => 个数 //option 查询个数 default 10
     *      'offset' => 开始游标 //option default 0
     *  );
     *
     * @date 2012/03/25 18:23:30
     **/
    public function get_newest_photo_id_by_tag_id($input) {
        //$query = array (
        //        'table_name' => self::TBN_TAG_NEWS_LINK,
        //        'field' => array ('tag_id', 'reffer_id', 'create_time'),
        //        'order_by' => array ('create_time' => 'desc'),
        //        );
        $offset = 0;
        $num = self::VAL_DEFAULT_PHOTO_PAGE;
        if (isset($input['offset'])) {
            $offset = $input['offset']; 
        }
        if (isset($input['num'])) {
            $num = $input['num'];
        }
        //$query['limit'] = array($offset, $num);
        //if (isset($input['tag_id'])) {
        //    $query['where'] = array ('tag_id' => $input['tag_id']);
        //}
        //
        $query = "SELECT DISTINCT `reffer_id` from ".self::TBN_TAG_NEWS_LINK;
        if (isset($input['tag_id'])) {
            $query .= " WHERE `tag_id`=".$input['tag_id'];
        }
        $query .= " ORDER BY `create_time` DESC";
        $query .= " LIMIT $offset, $num";
        return $this->mc_orm->mc_query(array('type'=>'query', 'sql'=>$query));
    }

    /**
     * @brief 获取用户收藏图片id, 按照收藏时间倒排
     *  $input = array (
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *      'source' => xx, //option
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/27 17:54:22
     **/
    public function get_user_collection_photo_id($input) {
        if (!isset($input['user_id'])) {
            $this->log->warning('get_user_collection_photo_id param error! input:'.var_export($input, TRUE));
            return FALSE;
        }
        $user_id = $input['user_id'];
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_PHOTO_PAGE;
        $select = array (
                'table_name' => self::TBN_PHOTO_MENG,
                'field' => array ('photo_id'),
                'order_by' => array ('create_time' => 'desc'),
                'limit' => array ($offset, $num),
                );
        if (isset($input['source'])) {
            $select['where'] = array ('user_id' => $user_id, 'source' => $input['source']);
        } else {
            $select['where'] = array ('user_id' => $user_id);
        }
        return $this->mc_orm->mc_get($select);    
    }

    /**
     * @brief 获取用户宠物图片id,按照图片上传时间倒排
     *  input = array (
     *      'animal_id' => xx, //option
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/27 19:04:02
     **/
    public function get_user_animal_photo_id($input) {
        if (!isset($input['user_id'])) {
            $this->log->warning('get_user_animal_photo_id param error! input:'.var_export($input, TRUE));
            return FALSE;
        }
        $user_id = $input['user_id'];
        $animal_id = isset($input['animal_id']) ? $input['animal_id'] : 0;
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_PHOTO_PAGE;
        $select = array (
                'table_name' => self::TBN_PHOTO,
                'field' => array ('photo_id'),
                'order_by' => array ('create_time' => 'desc'),
                'limit' => array ($offset, $num),
                );
        if ($animal_id) {
            $select['where'] = array ('animal_id' => $animal_id, 'user_id' => $user_id, 'source' => self::FROM_HOME);
        } else {
            $select['where'] = array ('user_id' => $user_id, 'source' => self::FROM_HOME);
        }
        return $this->mc_orm->mc_get($select);
    }

    /**
     * @brief 获取用户所有图片id,按照图片上传时间倒排
     *  input = array (
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/27 19:04:02
     **/
    public function get_user_master_photo_id($input) {
        if (!isset($input['user_id'])) {
            $this->log->warning('get_user_master_photo_id param error! input:'.var_export($input, TRUE));
            return FALSE;
        }
        $user_id = $input['user_id'];
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_PHOTO_PAGE;
        $select = array (
                'table_name' => self::TBN_PHOTO,
                'field' => array ('photo_id'),
                'where' => array('user_id' => $user_id),
                'order_by' => array ('create_time' => 'desc'),
                'limit' => array ($offset, $num),
                );
        return $this->mc_orm->mc_get($select);
    }

    /**
     * @brief 获取用户关注的所有图片id,按照图片上传时间倒排
     *  input = array (
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/27 19:04:02
     **/
    public function get_user_focus_photo_id($input) {
        if (!isset($input['user_id'])) {
            $this->log->warning('get_user_focus_photo_id param error! input:'.var_export($input, TRUE));
            return FALSE;
        }
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_PHOTO_PAGE;
        //get focus
        $select = array (
                'table_name' => self::TBN_USER_FOCUS,
                'field' => array ('focused_user_id'),
                'where' => array ('focus_user_id' => $input['user_id']),
                );
        $focused_user_id = $this->mc_orm->mc_get($select);
        $in_user_id = '';
        if ($focused_user_id) {
            $user_ids = array_col_val($focused_user_id, 'focused_user_id');
        }
        $user_ids[] = $input['user_id'];
        $in_user_id = implode(",", $user_ids);
        //get like animal
        $select = array (
                'table_name' => self::TBN_ANIMAL_LIKED,
                'field' => array ('animal_id'),
                'where' => array('user_id' => $input['user_id']),
                );
        $animal_id = $this->mc_orm->mc_get($select);
        $in_animal_id = '';
        if ($animal_id) {
            $animal_ids = array_col_val($animal_id, 'animal_id');
            $in_animal_id = implode(",", $animal_ids);
        }
        //TODO these can not use index.
        if ('' != $in_animal_id) {
            $sql = "SELECT `photo_id` FROM `mc_photo` WHERE `user_id` IN($in_user_id) OR `animal_id` IN($in_animal_id) ORDER BY `create_time` DESC LIMIT $offset, $num";
        } else {
            $sql = "SELECT `photo_id` FROM `mc_photo` WHERE `user_id` IN($in_user_id) ORDER BY `create_time` DESC LIMIT $offset, $num";
        }
        return $this->mc_orm->mc_query(array('type'=>'query', 'sql'=>$sql));
    }

    /**
     * @brief 获取图片详情,返回顺序按id递减
     *  $input = array ('photo_ids' =>array(0,1,3,4,5,2));
     *
     * @author wuyuanhao
     * @date 2012/03/25 18:55:13
     **/
    public function get_photo_detail($input) {
        $in_id = implode(",", $input['photo_ids']);
        if (!$in_id) {
            $this->log->warning("input photo_ids is null");
            return FALSE;
        }
        $result = array();
        //photo_result
        $query = "SELECT * FROM ".self::TBN_PHOTO. " WHERE `photo_id` IN ($in_id) ORDER BY `photo_id` DESC";
        $photo_result = $this->mc_orm->mc_query(array('type'=>'query', 'sql'=>$query));
        foreach ($photo_result as $v) {
            $result[$v['photo_id']] = $v;
        }
        //photo_status
        $query = "SELECT * FROM ".self::TBN_PHOTO_STATUS. " WHERE `photo_id` IN ($in_id) ORDER BY `photo_id` DESC";
        $photo_status_result = $this->mc_orm->mc_query(array('type'=>'query', 'sql'=>$query));
        foreach ($photo_status_result as $v) {
            foreach ($v as $kk => $vv) {
                $result[$v['photo_id']][$kk] = $vv;
            }
        }
        return $result;
    }


    /**
     * @brief 图片打萌
     *  $input = array (
     *      'photo_id' => xx,
     *      'user_id' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/27 16:24:24
     **/
    public function meng_photo($input) {
        //check if record exist
        $select_input = array (
                'table_name' => self::TBN_PHOTO_MENG,
                'field' => array ('photo_id'),
                'where' => array ('photo_id' => $input['photo_id'], 'user_id' => $input['user_id']),
                );
        $select_res = $this->mc_orm->mc_get($select_input);
        if ($select_res) {
            $this->log->warning('record exist input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::OK);
        }

        $select_input = array (
                'table_name' => self::TBN_PHOTO,
                'field' => array('source'),
                'where' => array('photo_id'=>$input['photo_id']),
                );
        $select_res = $this->mc_orm->mc_get($select_input);
        if (!$select_res) {
            $this->log->warning('get photo source failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::MENG_PHOTO_FAILED);
        }

        $add_input = array (
                'table_name' => self::TBN_PHOTO_MENG,
                'field_value' => array (
                    'photo_id' => $input['photo_id'], 
                    'user_id' => $input['user_id'], 
                    'source' => $select_res[0]['source'],
                    'create_time' => time(),
                    ),
                );
        $add_res = $this->mc_orm->mc_add($add_input);
        if (!$add_res) {
            $this->log->warning('add mc_photo_status failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::MENG_PHOTO_FAILED);
        }
        return array('errno' => MC_Errno::OK);
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
