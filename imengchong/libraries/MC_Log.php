<?php
require_once (dirname(__FILE__).'/bdLog.class.php'); 

class MC_Log extends CI_Log{ 

    protected $obj_logger = null;

    public function __construct() {

        parent::__construct();

        $log_conf = array (
            // 日志级别配置，0x07 = LOG_LEVEL_FATAL|LOG_LEVEL_WARNING|LOG_LEVEL_NOTICE
           'intLevel' => bdLog::LOG_LEVEL_DEBUG|bdLog::LOG_LEVEL_FATAL|bdLog::LOG_LEVEL_WARNING|bdLog::LOG_LEVEL_NOTICE|bdLog::LOG_LEVEL_TRACE,
            // 日志文件路径，wf日志为test.log.wf
            'strLogFile' => ROOT_PATH.'/logs/imengchong.log',
            // 0表示无限
            'intMaxFileSize' => 0,
            // 特殊日志路径，根据需要配置
            'arrSelfLogFiles' => array('wujiajia'=>ROOT_PATH.'/qingting/logs/wujiajia.log'),
            );

        $this->obj_logger = bdLog::getInstance($log_conf);
    }

    public function fatal($msg, $errno = 0, $arrArgs = null){
        return $this->obj_logger->fatal($msg, $errno, $arrArgs, 1);
    }

    public function warning($msg, $errno = 0, $arrArgs = null){
        return $this->obj_logger->warning($msg, $errno, $arrArgs, 1);
    }

    public function debug($msg, $errno = 0, $arrArgs = null){
        return $this->obj_logger->debug($msg, $errno, $arrArgs, 1);
    }

    public function notice($msg, $errno = 0, $arrArgs = null){
        return $this->obj_logger->notice($msg, $errno, $arrArgs, 1);
    }

    public function trace($msg, $errno = 0, $arrArgs = null) {
        return $this->obj_logger->trace($msg, $errno, $arrArgs, 1);
    }

    public function set_log_id($log_id) {
        $this->obj_logger->setLogId($log_id);
    }
    
    public function myself_log($strKey, $str, $arrArgs = null){
        $this->obj_logger->selflog($strKey, $str, $arrArgs = null);
    }
}
?>
