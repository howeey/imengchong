<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

if (!function_exists('array_col_val')) {

    function array_col_val($arr, $col_name) {
        if (!is_array($arr)) {
            return array();
        }
        $result = array();
        foreach ($arr as $v) {
            $result[] = $v[$col_name];
        }
        return $result;
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
