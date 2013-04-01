<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
class Testmodel extends MC_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('test_model');
        $this->load->model('Animal_Model');
    }

    public function index() {
        echo 'testmodel controller index';
    }

    public function test_model() {
        $this->test_model->test();
    }

    public function test_animal() {
        $ret = $this->Animal_Model->get_animal_class(array('class_type'=>2));
        var_dump($ret);
    }
}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
