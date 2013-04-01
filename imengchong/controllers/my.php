<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class my extends MC_Controller {

    const VAL_COMMENT_NUM = 2;
    const VAL_FOCUS_USER_LIST_NUM = 8;

    public function __construct() {
        parent::__construct();
        $this->load->model('Photo_Model');
        $this->load->model('Animal_Model');
        $this->load->model('User_Model');
    }

    public function index() {
        echo 'my/index';
    }

    public function collection() {
        echo 'my/collection';
        //照片信息
        $source = $this->get('source', 0);
        
        $post = array (
            'user_id' => $this->userinfo['user_id'],
        );
        if ($source) {
            $post['source'] = $source;
        }
        $photo_id = $this->Photo_Model->get_user_collection_photo_id($post);        
        if (!$photo_id) {
            $this->log->warning('get_user_collection_photo_id return false! input:'.var_export($post,TRUE));
        }

        $photo_ids = array_col_val($photo_id, 'photo_id');
        $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);
       
        $page_var = $this->_get_left_bar_info();
        $page_var['photo_detail'] = $photo_detail;
        foreach ($page_var as $k => $v) {
            $this->set_page_var($k, $v);
        }
        var_dump($page_var);
    }

    public function animal() {
        echo 'my/animal';
        //照片信息
        $post = array(
                'user_id' => $this->userinfo['user_id'],
                );
        $animal_id = $this->get('animal_id', 0);
        if ($animal_id) {
            $post['animal_id'] = $animal_id;
        }
        $photo_id = $this->Photo_Model->get_user_animal_photo_id($post);        
        if (!$photo_id) {
            $this->log->warning('get_user_animal_photo_id return false! input:'.var_export($post,TRUE));
        }
        $photo_ids = array_col_val($photo_id, 'photo_id');
        $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);

        $page_var = $this->_get_left_bar_info();
        $page_var['photo_detail'] = $photo_detail;
        foreach ($page_var as $k => $v) {
            $this->set_page_var($k, $v);
        }
        var_dump($page_var);
    }

    public function master() {
        echo 'my/master';
        //照片信息
        $post = array (
            'user_id' => $this->userinfo['user_id'],
        );
        $user_id = $this->get('user_id', 0);
        if ($user_id) {
            $post['user_id'] = $user_id;
        }
        $photo_id = $this->Photo_Model->get_user_master_photo_id($post);        
        if (!$photo_id) {
            $this->log->warning('get_user_animal_photo_id return false! input:'.var_export($post,TRUE));
        }
        $photo_ids = array_col_val($photo_id, 'photo_id');
        $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);
        
        $page_var = $this->_get_left_bar_info();
        $page_var['photo_detail'] = $photo_detail;
        foreach ($page_var as $k => $v) {
            $this->set_page_var($k, $v);
        }
        var_dump($page_var);

    }

    public function focus() {
        echo 'my/focus';
        //照片信息
        $post = array (
            'user_id' => $this->userinfo['user_id'],
        );
        $photo_id = $this->Photo_Model->get_user_focus_photo_id($post);        
        $photo_ids = array_col_val($photo_id, 'photo_id');
        $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);

        $page_var = $this->_get_left_bar_info();
        $page_var['photo_detail'] = $photo_detail;
        foreach ($page_var as $k => $v) {
            $this->set_page_var($k, $v);
        }
        var_dump($page_var);
    }
    

    public function _get_left_bar_info() {
        //个人宠物id
        $user_animal = $this->Animal_Model->get_animal_id_by_user_id(array('user_id'=>$this->userinfo['user_id']));

        //用户信息
        $user_detail = $this->User_Model->get_user_info(array('user_id'=>$this->userinfo['user_id']));

        //用户关注列表
        $user_focus_list = $this->User_Model->get_user_focus_list(array('user_id'=>$this->userinfo['user_id']));
        $user_focus_list_ids = array_col_val($user_focus_list, 'focused_user_id');
        $user_focus_list_detail = $this->User_Model->get_user_info_batch(array('user_ids'=>$user_focus_list_ids));

        //喜欢的萌宠列表
        $animal_liked_list = $this->Animal_Model->get_user_like_animal_list(array('user_id'=>$this->userinfo['user_id'])); 
        $animal_liked_list_ids = array_col_val($animal_liked_list, 'animal_id');
        $animal_liked_detail = $this->Animal_Model->get_animal_info_batch(array('animal_ids'=>$animal_liked_list_ids));

        return array (
            'user_animal' => $user_animal,
            'user_detail' => $user_detail,
            'user_focus_list_detail' => $user_focus_list_detail,
            'animal_liked_detail' => $animal_liked_detail,
        );
    }
}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
