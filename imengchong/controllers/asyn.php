<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class asyn extends MC_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo 'asyn/index';
    }

    /**
     * @brief 给前端返回图片上传图片数据
     *
     * @author wuyuanhao
     * @date 2012/03/30 15:33:30
     **/
    public function upload_form_data() {
        $this->load->model('Animal_Model'); 
        $user_id = $this->userinfo['user_id'];
        $user_id = 1;
        $animal_id = $this->Animal_Model->get_animal_id_by_user_id(array('user_id'=>$user_id));
        $json_data = array('animal_detail'=>array(), 'tag_detail'=>array());
        if ($animal_id) {       
            $animal_ids = array_col_val($animal_id, 'animal_id');
            $animal_detail = $this->Animal_Model->get_animal_info_batch(array('animal_ids'=>$animal_ids));   
            $json_data['animal_detail'] = $animal_detail;
        }
        echo json_encode($json_data);
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
