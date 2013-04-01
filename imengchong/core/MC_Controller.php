<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
class MC_Controller extends CI_Controller{
   
    public $userinfo =  array();    /** userinfo */
    public $param_get = array();    /** raw $_GET */
    public $param_post = array();   /** raw $_POST */

    protected $log_id = 0;            /**< 请求id      */
    protected $request_uri = '';      /**< 请求URI       */
    protected $request_ip = '';       /**< 请求IP       */
    protected $c = '';                /**< class        */
    protected $m = '';                /**< method       */
    protected $page_var = array();    /**< 模板变量数组       */
    protected $log_var = array();     /**< 日志变量数组       */
    protected $referer = '';          /**< 请求referer       */
    protected $agent = '';            /**< 请求agent       */
    protected $errno = 0;             /**< 错误号       */
    protected $debug = 0;             /**< 是否调试      */

    public function __construct(){
        parent::__construct();

        // 记录执行时间
        $this->benchmark->mark('request_process_start');

        $this->load();
        $this->init();

        $this->c = $this->router->class;
        $this->m = $this->router->method;

        // log相关变量
        $this->log_id = get_log_id();
        $this->log->set_log_id($this->log_id);
        $this->request_uri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : '';
        $this->request_ip = $this->input->ip_address();
        $this->referer = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '';
        $this->agent = $this->input->user_agent();

        // 获取userinfo
        $this->get_userinfo();
        
        $this->set_log_var('user_id', $this->userinfo['user_id']);
        $this->set_log_var('user_name', $this->userinfo['user_name']);
        $this->set_log_var('referer', $this->referer);
        $this->set_log_var('agent', $this->agent);

        //$this->load_ui();
        //$this->init_ui();
    }

    protected function get_userinfo() {
        //判断用户是否登陆
        $this->load->model('User_Model');
        $cookie_user_id = $this->cookie('mc_user_id', 0);
        $cookie_user_session = $this->cookie('mc_user_session', 0);
        $is_login = FALSE;
        if ($cookie_user_id && $cookie_user_session) {
            if ($this->User_Model->check_login(array('user_id' => $cookie_user_id, 'user_session' => $cookie_user_session))) {
                $is_login = TRUE;
            }
        } 
        if ($is_login) {
            $this->userinfo['user_id'] = $cookie_user_id;
            $res = $this->User_Model->get_user_info(array('user_id'=>$cookie_user_id));
            $this->userinfo['user_name'] = $res['user_name'];
        } else {
            $this->userinfo['user_id'] = 0;
            $this->userinfo['user_name'] = '';
        }
    }

    protected function is_user_login() {
        return (isset($this->userinfo['user_id']) && $this->userinfo['user_id'] > 0) ? TRUE : FALSE;
    }

    private function load(){
        $this->load->library('log');
    }

    private function init(){
        $debug = $this->get('debug');
        if (isset($debug)) {
            $debug_conf = $this->config->item('DEBUG');
            if(1 == $debug_conf){
                $this->debug = 1;
            }
        }

        $this->param_get = array_merge($_GET,array());
        $this->param_set = array_merge($_POST,array());
    }

    protected function set_page_var($key, $val) {
        $this->page_var[$key] = $val;
    }

    protected function set_log_var($key, $val) {
        $this->log_var[$key] = $val;
    }
    
    protected function set_errno($errno) {
        $this->errno = $errno;
    }

    protected function view($view) {
        // 全局页面变量 $uiCore
//        $uiCore = array(
//                'domain' => 'http://www.qingting.com:8081',
//                'staticDomain' => 'http://www.qingting.com:8081',
//                'photoDomain' => 'http://www.qingting.com:8081',
//                'portraitURL' => 'http://www.qingting.com:8081/portrait',
//                'userID' => $this->userinfo['uid'],
//                'userName' => $this->userinfo['uname'],
//                'userPortrait' => $this->userinfo['porid'],
//                'is_login' => $this->userinfo['is_login']
//                );
        $ui_core = array();
        if (1 == $this->debug) {
            echo '<p style="color:red;font-weight:bold;font-size:18px">data：</p>';
            var_dump($this->page_var);
            echo '<p style="color:red;font-weight:bold;fonot-size:18px">uiCore：</p>';
            var_dump($ui_core);
        } else {
            $this->load->view('header');
            $this->load->view($view, array('data' => $this->page_var,'ui_core' => $ui_core));
            $this->load->view('footer');
        }
    }

    protected function json_return($info_array, $errmsg, $errno) {
        $ret_array = array (
            'data' => $info_array, 
            'info' => $errmsg,
            'status' => intval($errno),
        );
        echo json_encode($ret_array);
    }

    public function log_notice() {
        $this->benchmark->mark('request_process_end');
        $elapsed = $this->benchmark->elapsed_time('request_process_start', 'request_process_end') * 1000;
        $this->set_log_var('ptime', $elapsed . 'ms');
        return $this->log->notice('', $this->errno, $this->log_var);
    }

    protected function redirect($uri = '', $method = 'location', $http_response_code = 302) {
        switch($method) {
            case 'refresh'  : 
                header("Refresh:0;url=".$uri);
                break;
            default         : 
                header("Location: ".$uri, TRUE, $http_response_code);
                break;
        }
        exit;
    }

    protected function get($key, $default = null) {
        $value = $this->input->get($key);
        if (false === $value) {
            $value = $default;
        }
        return $value;
    }

    protected function post($key, $default = null) {
        $value = $this->input->post($key);
        if (false === $value) {
            $value = $default;
        }
        return $value;
    }

    protected function get_post($key, $default = null) {
        $value = $this->input->get_post($key);
        if (false === $value) {
            $value = $default;
        }
        return $value;
    }

    protected function cookie($key, $default = null) {
        $value = $this->input->cookie($key);
        if (false === $value) {
            $value = $default;
        }
        return $value;
    }

    protected function _get_photo_detail_by_photo_ids($photo_ids, $comment_num=2) {
        $this->load->model('Photo_Model'); 
        $this->load->model('Animal_Model');
        $this->load->model('User_Model');
        $this->load->model('Comment_Model');

        //获取图片详细信息
        $photo_detail = $this->Photo_Model->get_photo_detail(array('photo_ids'=>$photo_ids));
        if (!$photo_detail) {
            return TRUE;
        }

        //获取宠物名
        $animal_ids = array_col_val($photo_detail, 'animal_id');
        $animal_ids = array_unique($animal_ids);
        $animal_detail = $this->Animal_Model->get_animal_info_batch(array('animal_ids'=>$animal_ids));
        if ($animal_detail) {
            foreach ($photo_detail as $k => $v) {
                if ($v['animal_id'] > 0) {
                    $photo_detail[$k]['animal_name'] = $animal_detail[$v['animal_id']]['animal_name'];
                }
            }
        }

        //获取用户名
        $user_ids = array_col_val($photo_detail, 'user_id');
        $user_ids = array_unique($user_ids);
        $user_detail = $this->User_Model->get_user_info_batch(array('user_ids'=>$user_ids));
        if ($user_detail) {
            foreach ($photo_detail as $k => $v) {
                $photo_detail[$k]['user_name'] = $user_detail[$v['user_id']]['user_name'];
            }
        }

        //获取评论数
        $comment_status = $this->Comment_Model->get_comment_status(array('reffer_ids' => $photo_ids));
        if ($comment_status) {
            foreach ($photo_detail as $k => $v) {
                $photo_detail[$k]['comment_count'] = isset($comment_status[$v['photo_id']]['comment_count']) ? $comment_status[$v['photo_id']]['comment_count'] : 0;
            }
        }

        //获取评论
        foreach ($photo_detail as $k => $v) {
            $photo_comment = $this->Comment_Model->get_comment(array('reffer_id'=>$v['photo_id'], 'num'=>$comment_num));    
            $photo_detail[$k]['comment'] = array();
            if ($photo_comment) {
                $photo_detail[$k]['comment'] = $photo_comment;
            }
        }
        return $photo_detail;
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
