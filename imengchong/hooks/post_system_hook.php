<?php
/***************************************************************************
 * 
 * Copyright (c) 2011 Qingting.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

class post_system_hook{

    private $controller;

    public function post_system_hook() {
        $this->controller = &get_instance();
    }

    public function index() {
        $this->log_notice();

    }

    public function log_notice() {
        $this->controller->log_notice();
    }


}






/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
