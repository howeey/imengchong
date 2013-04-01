<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class Callback extends MC_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_Model');
    }

    public function sina() {
        echo 'callback/sina';
        $sina_app_key = $this->config->item('SINA_APP_KEY');
        $sina_app_secret = $this->config->item('SINA_APP_SECRET');
        $post = array (
            'client_id' => $sina_app_key,
            'client_secret' => $sina_app_secret,
            'grant_type' => 'authorization_code',
            'code' => $this->get_post('code'),
            'redirect_uri' => 'http://www.imengchong.com/callback/sina',
        );
        //获取access_token
        $query_string = http_build_query($post);
        $ch = curl_init("https://api.weibo.com/oauth2/access_token");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        $curl_res = curl_exec($ch);
        curl_close($ch);
        
        $sina_data = json_decode($curl_res, TRUE);
        if (isset($sina_data['uid'])) {
            //user_login
            //check if first login
            $res = $this->User_Model->get_user_id_by_sina_id(array('sina_id'=>$sina_data['uid']));         
            if (!$res) {
                //first login
                if (MC_Errno::OK != $this->_first_login($sina_data)) {
                    $this->log->warning('first login failed! sina_data:'.var_export($sina_data, TRUE));
                    return TRUE;
                }
            } 
            $res = $this->User_Model->get_user_id_by_sina_id(array('sina_id'=>$sina_data['uid']));         
            $login_data = array (
                    'user_id' => $res[0]['user_id'],
                    'access_token' => $sina_data['access_token'],
                    );
            $login_res = $this->User_Model->user_login($login_data);
            if (MC_Errno::OK != $login_res['errno']) {
                $this->log->warning('login failed! login_data:'.var_export($login_data, TRUE));
                return TRUE;
            }
            //set cookie
            $cookie = array(
                    'name'   => 'mc_user_id',
                    'value'  => $res[0]['user_id'],
                    'expire' => $sina_data['remind_in'],
                    'domain' => '.imengchong.com',
                    'path'   => '/',
                    );
            $this->input->set_cookie($cookie);
            $cookie = array(
                    'name'   => 'mc_user_session',
                    'value'  => $sina_data['access_token'],
                    'expire' => $sina_data['remind_in'],
                    'domain' => '.imengchong.com',
                    'path'   => '/',
                    );
            $this->input->set_cookie($cookie);
 
        }
        return TRUE;
    }

    public function _first_login($sina_data) {
        //get sina user info
        $sina_user_info_url = "https://api.weibo.com/2/users/show.json?access_token=".$sina_data['access_token']."&uid=".$sina_data['uid'];
        $ch = curl_init($sina_user_info_url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        $curl_res = curl_exec($ch);
        curl_close($ch);

        $sina_user_data = json_decode($curl_res, TRUE);
        
        //获取用户头像
        $user['sina_id'] = $sina_user_data['id'];
        $user['user_name'] = $sina_user_data['screen_name'];
        if (isset($sina_user_data['avatar_large']) && '' != isset($sina_user_data['avatar_large'])) {
            $avatar_binary = file_get_contents($sina_user_data['avatar_large']);
            $user['avatar_raw_name'] = 'avatar_'.time();
            $user['avatar_full_path'] = ROOT_PATH.'/uploads/'.$user['avatar_raw_name'].'.jpg'; 
            file_put_contents($user['avatar_full_path'], $avatar_binary);
        }
        $res = $this->User_Model->user_register($user);
        return $res['errno'];
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
