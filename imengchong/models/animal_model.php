<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class Animal_Model extends MC_Model {
    //default values
    const VAL_DEFAULT_LIKE_ANIMAL_PAGE = 5;
    const VAL_CROP_M_WIDTH = 60;
    const VAL_CROP_M_HEIGHT = 60;
    const VAL_CROP_L_WIDTH = 100;
    const VAL_CROP_L_HEIGHT = 90;

    //animal_sex define
    const FEMALE = 1;
    const MALE = 2;

    //animal_class define
    const UNDEFINED = 1;
    const CAT = 2;
    const DOG = 3;
    
    //table relate to animal_model
    const TBN_ANIMAL = 'mc_animal';
    const TBN_ANIMAL_CLASS = 'mc_animal_class';
    const TBN_ANIMAL_LIKED = 'mc_animal_liked';
    const TBN_ANIMAL_STATUS = 'mc_animal_status';
    const TBN_ANIMAL_AVATAR_STORE = 'mc_animal_avatar_store';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief 获取单个宠物信息
     *
     *  $input = array (
     *      'animal_id' => xxxx, //宠物id
     *  );
     *  
     *  $output = array (
     *      'user_id' => xxxx,
     *      'animal_name' => xxxx,
     *      'sign' => xxxx,
     *      ...
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/19 18:02:03
    **/
    public function get_animal_info($input) {
        $animal_ids =  array('animal_ids' => array($input['animal_id']));
        $res = $this->get_animal_info_batch($animal_ids);
        if ($res) {
            return $res[$input['animal_id']];
        }
        return FALSE;
    }

    /**
     * @brief 批量获取宠物信息
     * 
     *  $input = array (
     *      'animal_ids' => array (xxxx, xxxx, xxxx);
     *  );
     *
     *  $output = array (
     *      0 => array ('user_id'=>xxxx, 'animal_name'=>xx, 'sign'=>xx, ...),
     *      1 => array (),
     *  );
     *  
     * @author wuyuanhao
     * @date 2012/03/19 18:06:11
    **/
    public function get_animal_info_batch($input) {
        $animal_ids = implode(",", $input['animal_ids']);
        if (!$animal_ids) {
            $this->log->warning('input animal_ids is null');
            return FALSE;
        }
        $query = "SELECT * FROM ".self::TBN_ANIMAL." WHERE `animal_id` IN (".$animal_ids.") ORDER BY `animal_id` DESC";
        $animal_result = $this->mc_orm->mc_query(array('type'=>'query','sql'=>$query));
        $result = array();
        foreach ($animal_result as $v) {
            $result[$v['animal_id']] = $v;
        }
        return $result;
    }

    /**
     * @brief 获取用户宠物id
     * $input = array (
     *      'user_id' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/27 23:02:41
     **/
    public function get_animal_id_by_user_id($input) {
        if (!isset($input['user_id'])) {
            $this->log->warning('get_animal_id_by_user_id param error! input:'.var_export($input, TRUE));
            return FALSE;
        }
        $select = array (
            'table_name' => self::TBN_ANIMAL,
            'field' => array ('animal_id'),
            'where' => array ('user_id' => $input['user_id']),
        );
        return $this->mc_orm->mc_get($select);
    }

    /**
     * @brief 创建宠物
     *
     *  $input = array (
     *      'create_type' => xx, //'quick','base'(快速创建、基本创建)
     *      'animal_info' => array(),
     *  );
     *
     *  $output = array (
     *       'errno' => errno
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/19 17:51:00
    **/
    public function create_animal($input) {
        $insert_param_mc_animal_field_value = array();
        if (isset($input['create_type']) && 'quick' == $input['create_type']) {
            //create by quick 
            $insert_param_mc_animal_field_value['user_id'] = $input['animal_info']['user_id'];
            $insert_param_mc_animal_field_value['sex'] = $input['animal_info']['sex'];
            $insert_param_mc_animal_field_value['type'] = $input['animal_info']['type'];
            $insert_param_mc_animal_field_value['animal_name'] = $input['animal_info']['animal_name'];
        } else if (isset($input['create_type']) && 'base' == $input['create_type']) {
            //create by base
            $insert_param_mc_animal_field_value['user_id'] = $input['animal_info']['user_id'];
            $insert_param_mc_animal_field_value['sex'] = $input['animal_info']['sex'];
            $insert_param_mc_animal_field_value['type'] = $input['animal_info']['type'];
            $insert_param_mc_animal_field_value['animal_name'] = $input['animal_info']['animal_name'];
        } else {
            $this->log->warning('param is invalied. param:'.var_export($input, true));
            return array ('errno'=>MC_Errno::MODEL_PARAM_ERROR);
        }

        //animal
        $insert_param_mc_animal_field_value['create_time'] = time();
        $insert_param_mc_animal = array (
            'table_name' => self::TBN_ANIMAL,
            'field_value' => $insert_param_mc_animal_field_value,
        );
        $insert_id = $this->mc_orm->mc_add($insert_param_mc_animal);
        $animal_id = $insert_id;
        //animal_liked
        $insert_param_mc_animal_status = array (
            'table_name' => self::TBN_ANIMAL_STATUS,
            'field_value' => array (
                'animal_id' => $insert_id,
                'liked_count' => 0,
            ),
        );
        $insert_res = $this->mc_orm->mc_add($insert_param_mc_animal_status);
        if (!$insert_id || !$insert_res) {
            $this->log->warning("create_animal failed! insert_id[$insert_id] insert_res[$insert_res] input[".var_export($input, true)."]");
        }
        return array('errno'=>MC_Errno::OK, 'animal_id'=>$animal_id);
    }

    /**
     * @brief 修改宠物
     *
     *  $input = array (
     *  );
     *
     *  $output = array (
     *       'errno' => errno
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/19 17:59:57
    **/
    public function edit_animal($input) {
        return array('errno'=>MC_Errno::OK);
    }

    
    /**
     * @brief 获取宠物头像
     *  $input = array (
     *      'animal_avatar_id' => xx,
     *  );
     * @author wuyuanhao
     * @date 2012/03/29 17:21:14
    **/
    public function get_animal_avatar_store($input) {
        $select_input = array (
            'table_name' => self::TBN_ANIMAL_AVATAR_STORE,
            'field' => array ('animal_avatar_id', 'avatar_resize_l', 'avatar_resize_m'),
            'where' => array ('animal_avatar_id' => $input['animal_avatar_id']),
        );
        return $this->mc_orm->mc_get($select_input);
    }

    /**
     * @brief 宠物头像存储
     *
     * @author wuyuanhao
     * @date 2012/03/29 16:20:20
    **/
    public function animal_avatar_store($input) {
        $photo_data = $input['animal_avatar_data'];
        //宠物头像压缩和转换
        //resize l
        $config['image_library'] = 'gd2';
        $config['source_image'] = $photo_data['full_path'];
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['quality'] = 90;
        $config['width'] = 0;
        $config['height'] = 0;
        if ($photo_data['image_width'] > $photo_data['image_height']) {
            //以高为基准
            $config['height'] = self::VAL_CROP_L_HEIGHT < $photo_data['image_height'] ? self::VAL_CROP_L_HEIGHT : $photo_data['image_height'];
            $config['width'] = intval($config['height'] * $photo_data['image_width'] / $photo_data['image_height']);
        } else {
            //以宽为基准
            $config['width'] = self::VAL_CROP_L_WIDTH < $photo_data['image_width'] ? self::VAL_CROP_L_WIDTH : $photo_data['image_width'];
            $config['height'] = intval($config['width'] * $photo_data['image_height'] / $photo_data['image_width']);
        }
        $config['new_image'] = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_l'.$photo_data['file_ext'];
        $this->load->library('image_lib', $config); 
        if (!$this->image_lib->resize()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image resize l failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::ANIMAL_AVATAR_STORE_FAILED);
        }
        $tmp_resize_image = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_l_thumb'.$photo_data['file_ext'];
        $tmp_resize_width = $config['width'];
        $tmp_resize_height = $config['height'];
        //crop photo l
        $config = array();
        $config['image_library'] = 'gd2';
        $config['source_image'] = $tmp_resize_image;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['quality'] = 90;
        $config['width'] = self::VAL_CROP_L_WIDTH;
        $config['height'] = self::VAL_CROP_L_HEIGHT;
        $config['new_image'] = $photo_data['file_path'].'crop/'.$photo_data['raw_name'].'_crop_l'.$photo_data['file_ext'];
        $config['x_axis'] = intval(($tmp_resize_width - $config['width']) / 2.0);
        $config['y_axis'] = intval(($tmp_resize_height - $config['height']) / 2.0);
        $this->image_lib->initialize($config);
        if (!$this->image_lib->crop()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image crop l failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::ANIMAL_AVATAR_STORE_FAILED);
        }
        $new_image = $photo_data['file_path'].'crop/'.$photo_data['raw_name'].'_crop_l_thumb'.$photo_data['file_ext'];
        $avatar_resize_l = file_get_contents($new_image);

        //resize m
        $config = array();
        $config['image_library'] = 'gd2';
        $config['source_image'] = $photo_data['full_path'];
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['quality'] = 90;
        $config['width'] = 0;
        $config['height'] = 0;
        if ($photo_data['image_width'] > $photo_data['image_height']) {
            //以高为基准
            $config['height'] = self::VAL_CROP_M_HEIGHT < $photo_data['image_height'] ? self::VAL_CROP_M_HEIGHT : $photo_data['image_height'];
            $config['width'] = intval($config['height'] * $photo_data['image_width'] / $photo_data['image_height']);
        } else {
            //以宽为基准
            $config['width'] = self::VAL_CROP_M_WIDTH < $photo_data['image_width'] ? self::VAL_CROP_M_WIDTH : $photo_data['image_width'];
            $config['height'] = intval($config['width'] * $photo_data['image_height'] / $photo_data['image_width']);
        }
        $config['new_image'] = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_m'.$photo_data['file_ext'];
        $this->image_lib->initialize($config); 
        if (!$this->image_lib->resize()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image resize m failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::ANIMAL_AVATAR_STORE_FAILED);
        }
        $tmp_resize_image = $photo_data['file_path'].'resize/'.$photo_data['raw_name'].'_resize_m_thumb'.$photo_data['file_ext'];
        $tmp_resize_width = $config['width'];
        $tmp_resize_height = $config['height'];
        //crop photo m
        $config = array();
        $config['image_library'] = 'gd2';
        $config['source_image'] = $tmp_resize_image;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['quality'] = 90;
        $config['width'] = self::VAL_CROP_M_WIDTH;
        $config['height'] = self::VAL_CROP_M_HEIGHT;
        $config['new_image'] = $photo_data['file_path'].'crop/'.$photo_data['raw_name'].'_crop_m'.$photo_data['file_ext'];
        $config['x_axis'] = intval(($tmp_resize_width - $config['width']) / 2.0);
        $config['y_axis'] = intval(($tmp_resize_height - $config['height']) / 2.0);
        $this->image_lib->initialize($config);
        if (!$this->image_lib->crop()) {
            $error_msg = $this->image_lib->display_errors();
            $this->log->warning('image crop m failed! error:'.$error_msg.'photo_data:'.var_export($photo_data, TRUE));
            return array ('errno' => MC_Errno::ANIMAL_AVATAR_STORE_FAILED);
        }
        $new_image = $photo_data['file_path'].'crop/'.$photo_data['raw_name'].'_crop_m_thumb'.$photo_data['file_ext'];
        $avatar_resize_m = file_get_contents($new_image);


        //头像存储
        $insert_input = array (
            'table_name' => self::TBN_ANIMAL_AVATAR_STORE,
            'field_value' => array (
                'avatar_data' => json_encode($photo_data),
                'avatar_resize_l' => $avatar_resize_l,
                'avatar_resize_m' => $avatar_resize_m,
            ),
        );
        $insert_id = $this->mc_orm->mc_add($insert_input);
        if (!$insert_id) {
            $this->log->warning('insert animal_avatar_store failed! input:'.var_export($input, TRUE));
            return array ('errno' => MC_Errno::ANIMAL_AVATAR_STORE_FAILED);
        }
        return array ('errno' => MC_Errno::OK, 'return' => array ('animal_avatar_id' => $insert_id));
    }

    /**
     * @brief 获取宠物分类
     *  $input = array (
     *      'class_type' => 1 (2猫 3狗 1未知),
     *      'order_by' => 'animal_count' or 'last_update_time',
     *  );
     *  $output = array (
     *      0 => array ('type_id', 'father_type_id', 'class_name', 'animal_count', 'last_update_time'),
     *  )
     *
     * @author wuyuanhao
     * @date 2012/03/19 15:51:02
    **/
    public function get_animal_class($input) {
        if ((!isset($input['class_type']))
        || ((self::CAT != $input['class_type']) && (self::DOG != $input['class_type']) && (self::UNDEFINED != $input['class_type']))) {
            $this->log->warning('param is invalied. param:'.var_export($input, true));
        }
        
        $arr_query = array (
            'table_name' => self::TBN_ANIMAL_CLASS,
            'field' => array ('type_id', 'father_type_id', 'class_name', 'animal_count'),
            'where' => array ('father_type_id' => $input['class_type']),
        );
        if (isset($input['order_by'])) {
            $arr_query['order_by'] = array ($input['order_by'] => 'desc'); 
        }
        return $this->mc_orm->mc_get($arr_query);
    }

    /**
     * @brief 用户喜欢的萌宠列表,按喜欢时间倒排
     *  $input = array (
     *      'user_id' => xx,
     *      'offset' => xx,
     *      'num' => xx,
     *  );
     *
     * @author wuyuanhao
     * @date 2012/03/28 16:45:19
    **/
    public function get_user_like_animal_list($input) {
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $num = isset($input['num']) ? $input['num'] : self::VAL_DEFAULT_LIKE_ANIMAL_PAGE;
        $select_input = array (
                'table_name' => self::TBN_ANIMAL_LIKED,
                'field' => array ('animal_id'),
                'where' => array ('user_id' => $input['user_id']),
                'order_by' => array ('create_time' => 'desc'),
                'limit' => array ($offset, $num),
                );
        return $this->mc_orm->mc_get($select_input);
    }

    /**
     * @brief 喜欢宠物
     *  $input = array (
     *      'photo_id' => xx,
     *      'user_id' => xx,
     *  );
     * @return:  public function 
     * @retval:   
     * @param:   
     * @see 
     * @note 
     * @author wuyuanhao
     * @date 2012/03/27 16:59:44
    **/
    public function like_animal($input) {
        //check if record exist
        $select_input = array (
                'table_name' => self::TBN_ANIMAL_LIKED,
                'field' => array ('animal_id'),
                'where' => array ('animal_id' => $input['animal_id'], 'user_id' => $input['user_id']),
                );
        $select_res = $this->mc_orm->mc_get($select_input);
        if ($select_res) {
            $this->log->warning('record exist input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::OK);
        }

        $add_input = array (
                'table_name' => self::TBN_ANIMAL_LIKED,
                'field_value' => array (
                    'animal_id' => $input['animal_id'], 
                    'user_id' => $input['user_id'], 
                    'create_time' => time(),
                    ),
                );
        $add_res = $this->mc_orm->mc_add($add_input);
        if (!$add_res) {
            $this->log->warning('add mc_animal_liked failed! input:'.var_export($input, TRUE));
            return array('errno' => MC_Errno::LIKE_ANIMAL_FAILED);
        }
        //add like_animal_count
        $update_sql = "UPDATE `mc_user_status` SET `like_animal_count`=`like_animal_count`+1 WHERE `user_id`=".$input['user_id'];
        $update_res = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>$update_sql));
        if (!$update_res) {
            $this->log->warning('update mc_user_status failed! input:'.var_export($input,TRUE));
            return array('errno' => MC_Errno::LIKE_ANIMAL_FAILED);
        }
        //add animal liked count
        $update_sql = "UPDATE `mc_animal_status` SET `liked_count`=`liked_count`+1 WHERE `animal_id`=".$input['animal_id'];
        $update_res = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>$update_sql));
        if (!$update_res) {
            $this->log->warning('update mc_animal_status failed! input:'.var_export($input,TRUE));
            return array('errno' => MC_Errno::LIKE_ANIMAL_FAILED);
        }

        return array('errno' => MC_Errno::OK);
 
   
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
