<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
class Index extends MC_Controller {

    const VAL_COMMENT_NUM = 2;
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sina_app_key = $this->config->item('SINA_APP_KEY');
        echo '<html>';
        echo '<head>';
        echo '<meta property="wb:webmaster" content="bbb76f4135f7fec5" />';
        echo '</head>';
        echo '<body>';
        echo '<a href="https://api.weibo.com/oauth2/authorize?client_id='.$sina_app_key.'&redirect_uri=http://www.imengchong.com/callback/sina"> 登陆 </a>';
        echo '</body>';
        echo '</html>';
    }

    public function newest() {
        //echo 'index/newest';
        $this->load->model('Photo_Model'); 
        $this->load->model('Animal_Model'); 
        //tag参数
        $tag_id = $this->get('tag_id', 0);
        $type_id = $this->get('type_id', 0);
        $class_id = $this->get('class_id', 0);
        $photo_detail = array();
        if ($tag_id) {
            $photo_id = $this->Photo_Model->get_newest_photo_id_by_tag_id(array('tag_id' => $tag_id));
            if (!$photo_id) {
                $this->log->warning('get_newest_photo_id_tag_id failed! input:'.var_export($input,TRUE));
                return TRUE;
            }
            $photo_ids = array_col_val($photo_id, 'reffer_id');
            $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);
        } elseif ($type_id) {
            $photo_id = $this->Photo_Model->get_newest_photo_id(array('type_id' => $type_id));
            if (!$photo_id) {
                $this->log->warning('get_newest_photo_id failed! input:'.var_export($input,TRUE));
                return TRUE;
            }
            $photo_ids = array_col_val($photo_id, 'photo_id');
            $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);
        } elseif ($class_id) {
            //TODO
        } else {
            $photo_id = $this->Photo_Model->get_newest_photo_id(array());
            if (!$photo_id) {
                $this->log->warning('get_newest_photo_id failed! input:'.var_export($input,TRUE));
                return TRUE;
            }
            $photo_ids = array_col_val($photo_id, 'photo_id');
            $photo_detail = $this->_get_photo_detail_by_photo_ids($photo_ids, self::VAL_COMMENT_NUM);
        }
        
        // Assign data and load view
        $this->set_page_var('photo_detail', $photo_detail);

        //分类类型
        $photo_class = array();
        $photo_class['cat'] = $this->Animal_Model->get_animal_class(array('class_type'=>Animal_Model::CAT, 'order_by'=>'animal_count')); 
        $photo_class['dog'] = $this->Animal_Model->get_animal_class(array('class_type'=>Animal_Model::DOG, 'order_by'=>'animal_count')); 
        $this->set_page_var('photo_class', $photo_class);

        $this->view('flow');
    }
}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
