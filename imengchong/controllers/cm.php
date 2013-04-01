<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class cm extends MC_Controller {

    public function __construct() {
        parent::__construct();
        //check is login
        if (!$this->is_user_login()) {
            $this->redirect('/');
        }
    }

    public function index() {
        echo 'cm/index';
    }

    public function create_animal_quick() {
        echo 'cm/create_animal_quick';
        //Post Interface
        //
        $this->load->model('Animal_Model');
        $type = $this->post('type', 0);
        $sex = $this->post('sex', 0);
        $animal_name = $this->post('animal_name', 0);
        if (!$type || !$sex || !$animal_name) {
            $this->log->warning("post param is invalid! type[$type] sex[$sex] animal_name[$animal_name]!");
            echo json_encode(array('errno'=> MC_Errno::CONTROLLER_PARAM_ERROR));
            return TRUE;
        }
        
        $post = array (
            'user_id' => $this->userinfo['user_id'],
            'type' => $type,
            'sex' => $sex,
            'animal_name' => $animal_name,
        );
        $model_res = $this->Animal_Model->create_animal(array ('create_type'=>'quick', 'animal_info' => $post));
        var_dump($model_res);
    }

    public function create_animal() {
        echo 'cm/create_animal';
        //Post Interface
        //
        $this->load->model('Animal_Model');
        $post = array (
            'user_id' => $this->userinfo['user_id'],
            'type' => Animal_Model::CAT,
            'sex' => Animal_Model::FEMALE,
            'animal_name' => '妙妙_create_animal_'.rand(),
        );
        $model_res = $this->Animal_Model->create_animal(array ('create_type'=>'base', 'animal_info' => $post));
        var_dump($model_res);
    }

    public function create_photo() {
        echo 'cm/create_photo';
        //Post Interface
        $this->load->model('Photo_Model');
        $this->load->model('Animal_Model');

        $photo_num = $this->post('photo_num', 0);
        $store_ids = $this->post('store_ids', 0);
        $description = $this->post('description', '');
        $tag_ids = $this->post('tag_ids', '');
        $source = $this->post('source', 0);
        $animal_id = $this->post('animal_id', 0);
        if (!$photo_num || !$store_ids || !$source) {
            $this->log->warning("post param is invalid! photo_num[$photo_num] store_ids[$store_ids] source[$source]!");
            echo json_encode(array('errno'=> MC_Errno::CONTROLLER_PARAM_ERROR));
            return TRUE;
        }
        if (Photo_Model::FROM_HOME == $source && !$animal_id) {
            $this->log->warning("post animal_id param is invalid! photo_num[$photo_num] store_ids[$store_ids] source[$source]! animal_id[$animal_id]");
            echo json_encode(array('errno'=> MC_Errno::CONTROLLER_PARAM_ERROR));
            return TRUE;
        }
        $post = array (
            'user_id' => $this->userinfo['user_id'],
            'animal_id' => $animal_id,
            'photo_num' => $photo_num,
            'store_ids' => '{"store_ids":['.$store_ids.']}',
            'tag_ids' => '{"tag_ids":['.$tag_ids.']}',
            'description' =>  $description,
            'source' => $source,
            'type' => 0, //需要获取
            'class' => 0, //需要获取
        );
        if (Photo_Model::FROM_HOME == $source && $animal_id) {
            $res = $this->Animal_Model->get_animal_info(array('animal_id'=>$animal_id)); 
            if ($res) {
                $post['type'] = $res['type'];
                $post['class'] = $res['class_id'];
            } else {
                $this->log->warning("get animalinfo failed! res:".var_export($res, TRUE));
                echo json_encode(array('errno'=> MC_Errno::CONTROLLER_ERROR));
                return TRUE;
            }
        }
        $model_res = $this->Photo_Model->create_photo($post);
        var_dump($model_res);
    }

    public function comment() {
        echo 'cm/comment';
        //Get Interface
        $this->load->model('Comment_Model');
        $post = array (
            'user_id' => $this->userinfo['user_id'],
            'content' => 'test_comment_'.rand(),
            'reffer_id' => 20, //photo_id or thing_id
        );
        $model_res = $this->Comment_Model->add_comment($post);
        var_dump($model_res);
    }

    public function animal_avatar_upload() {
        echo 'cm/animal_avatar_upload';

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['file_name'] = 'animal_avatar_'.rand();
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_res = $this->upload->do_upload('animal_avatar');
        if (!$upload_res) {
            $this->log->warning('upload file failed! error:'.var_export($this->upload->display_errors(),true).' data:'.var_export($this->upload->data(),TRUE));
            return TRUE;
        }
        $animal_avatar_data = $this->upload->data();

        $this->load->model('Animal_Model');
        $post = array (
                'animal_avatar_data' => $animal_avatar_data,
                );
        $model_res = $this->Animal_Model->animal_avatar_store($post);
        var_dump($model_res);
    }

    public function photo_upload() {
        echo 'cm/photo_upload';

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['file_name'] = 'photo_'.rand();
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_res = $this->upload->do_upload('photo');

        if (!$upload_res) {
            $this->log->warning('upload file failed! error:'.var_export($this->upload->display_errors(),true).' data:'.var_export($this->upload->data(),true));
            return TRUE;
        }
        $photo_data = $this->upload->data();

        $this->load->model('Photo_Model');
        $post = array (
                'photo_data' => $photo_data,
                );
        $model_res = $this->Photo_Model->photo_store($post);
        var_dump($model_res);
    }

    public function meng() {
        echo 'cm/meng';

        $this->load->model('Photo_Model');
        $post = array (
                'photo_id' => $this->get_post('photo_id', 0),
                'user_id' => $this->userinfo['user_id'],
                );
        if ($post['photo_id']) {
            $model_res = $this->Photo_Model->meng_photo($post);
            var_dump($model_res);
        }
    }

    public function like() {
        echo 'cm/like';

        $this->load->model('Animal_Model');
        $post = array (
                'animal_id' => $this->get_post('animal_id',0),
                'user_id' => $this->userinfo['user_id'],
                );
        if ($post['animal_id']) {
            $model_res = $this->Animal_Model->like_animal($post);
            var_dump($model_res);
        }
    }

    public function focus() {
        echo 'cm/focus_user';

        $this->load->model('User_Model');
        $post = array (
                'focus_user_id' => $this->userinfo['user_id'],
                'focused_user_id' => $this->get_post('focused_user_id', 0),
                );
        if ($post['focused_user_id']) {
            $model_res = $this->User_Model->focus_user($post);
            var_dump($model_res);
        }
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
