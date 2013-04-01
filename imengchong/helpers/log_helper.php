<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/***************************************************************************
 * 
 * Copyright (c) 2011 Qingting.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @brief 每个请求有全局唯一的log_id
 *
 * @return: log_id 
 * @retval:  
 * @param:   
 * @see 
 * @note 
 * @author liliangping
 * @date 2011/06/21 17:25:51
 **/
if ( ! function_exists('get_log_id'))
{
    function get_log_id() {
        static $log_id = 0;
        if (0 == $log_id) {
            $request_time = gettimeofday();
            $log_id = intval($request_time['sec'] * 100000 + $request_time['usec'] / 10) & 0x7FFFFFFF;
        }
        return $log_id;
    }
}






/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
