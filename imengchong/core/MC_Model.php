<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
class MC_Model extends CI_Model {
    
    const JSON_NULL = '{}';
    
    public function __construct() {
        parent::__construct();
        $this->load->library('MC_Orm');
        $this->load->library('MC_Errno');
    }

}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
