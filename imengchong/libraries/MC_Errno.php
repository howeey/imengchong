<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
class MC_Errno {

    //基本错误号
    const OK = 0;
    const MODEL_PARAM_ERROR = -1;
    const CONTROLLER_PARAM_ERROR = -2;
    const CONTROLLER_ERROR= -3;
    
    //宠物相关
    const CREATE_ANIMAL_FAILED = -101;
    const LIKE_ANIMAL_FAILED = -102;
    const ANIMAL_AVATAR_STORE_FAILED = -103;

    //照片相关
    const CREATE_PHOTO_FAILED = -201;
    const PHOTO_STORE_FAILED = -202;
    const MENG_PHOTO_FAILED = -203;

    //评论相关
    const ADD_COMMENT_FAILED = -301;

    //用户相关
    const FOCUS_USER_FAILED = -401;
    const USER_REGISTER_FAILED = -402;
    const USER_LOGIN_FAILED = -403;
}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
