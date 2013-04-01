<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class image extends MC_Controller {

    public function index() {
        echo 'cm/image';
    }

    public function photo() {
        $this->load->model('Photo_Model');
        $store_id = $this->get_post('p');
        $size = $this->get_post('s', 0);
        $data = $this->Photo_Model->get_photo_store(array('store_id'=>$store_id));
        $kstr = 'photo_resize';
        if ($size) {
            $kstr .= '_'.$size; 
        }
        if (isset($data[0][$kstr])) {
            header('Content-type: image/jpeg');
            echo $data[0][$kstr];
        }
    }

    public function avatar() {
        $this->load->model('User_Model');
        $store_id = $this->get_post('p');
        $size = $this->get_post('s', 'l');
        $data = $this->User_Model->get_avatar_store(array('portrait_id'=>$store_id));
        $kstr = 'avatar_resize';
        if ($size) {
            $kstr .= '_'.$size; 
        }
        if (isset($data[0][$kstr])) {
            header('Content-type: image/jpeg');
            echo $data[0][$kstr];
        }
    }

    public function animal_avatar() {
        $this->load->model('Animal_Model');
        $store_id = $this->get_post('p');
        $size = $this->get_post('s', 'l');
        $data = $this->Animal_Model->get_animal_avatar_store(array('animal_avatar_id'=>$store_id));
        $kstr = 'avatar_resize';
        if ($size) {
            $kstr .= '_'.$size; 
        }
        if (isset($data[0][$kstr])) {
            header('Content-type: image/jpeg');
            echo $data[0][$kstr];
        }
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
