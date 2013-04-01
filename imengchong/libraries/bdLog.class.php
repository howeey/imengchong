<?php
/***************************************************************************
 * 
 * Copyright (c) 2010 Baidu.com, Inc. All Rights Reserved
 * $Id: bdLog.class.php,v 1.4 2010/01/06 04:05:17 duchuanying Exp $ 
 * 
 **************************************************************************/

/**
 * @file bdLog.class.php
 * @author zhujt(zhujianting@baidu.com)
 * @date 2010/04/08 10:31:44
 * @version $Revision: 1.4 $ 
 * @brief class for logging
 *  
 **/

class bdLog 
{
	const LOG_LEVEL_NONE    = 0x00;   /** 日志级别NONE    */
	const LOG_LEVEL_FATAL   = 0x01;   /** 日志级别FATAL   */
	const LOG_LEVEL_WARNING = 0x02;   /** 日志级别WARNING */
	const LOG_LEVEL_NOTICE  = 0x04;   /** 日志级别NOTICE  */
	const LOG_LEVEL_TRACE   = 0x08;   /** 日志级别TRACE   */
	const LOG_LEVEL_DEBUG   = 0x10;   /** 日志级别DEBUG   */
	const LOG_LEVEL_ALL     = 0xFF;   /** 日志级别ALL     */
	/**
	 * @brief 日志级别对应的含义 
	 *
	 */
	public static $arrLogLevels = array(
		self::LOG_LEVEL_NONE    => 'NONE',
		self::LOG_LEVEL_FATAL   => 'FATAL',
		self::LOG_LEVEL_WARNING => 'WARNING',
		self::LOG_LEVEL_NOTICE  => 'NOTICE',
		self::LOG_LEVEL_TRACE	=> 'TRACE',
		self::LOG_LEVEL_DEBUG   => 'DEBUG',
		self::LOG_LEVEL_ALL     => 'ALL',
	);
	/**
	 * @brief 日志级别 
	 */
	protected $intLevel;
	/**
	 * @brief 日志存储的位置
	 */
	protected $strLogFile;
	/** 
	 * @brief 自定义日志级别
	 */
	protected $arrSelfLogFiles;
	/**
	 * @brief 日志ID
	 */
	protected $intLogId;
	/**
	 * @brief 日志文件的最大大小
	 */
	protected $intMaxFileSize;


	/**
	 * @brief 保存类型实例，私有静态变量，实现单例模式
	 *
	 */
	private static $instance = null;

	/**
	 * @brief 私有构造函数，外部不能调用
	 * @param $arrLogConfig 配置项
	 *        $arrLogConfig(
	 *				'intLevel'        => 日志级别,
	 *				'strLogFile'      => 保存日志文件,
	 *				'arrSelfLogFiles' => 用户自定义日志级别,
	 *				'intMaxFileSize'  => 最大文件大小
	 *        )
	 * @return null
	 * @retval 
	 * @date 2010/04/08 15:30
	 *
	 */
	private function __construct($arrLogConfig)
	{
		$this->intLevel         = intval($arrLogConfig['intLevel']);
		$this->strLogFile		= $arrLogConfig['strLogFile'];
		$this->arrSelfLogFiles  = $arrLogConfig['arrSelfLogFiles'];
		// use framework logid as default
		$this->intLogId			= 0;
		$this->intMaxFileSize  = $arrLogConfig['intMaxFileSize'];
	}
	/**
	 * @brief 获取类实例，单例模式
	 *        第一次调用时必须给出配置参数
	 * @param $conf 配置参数,第一调用时必须设定值,默认是null
     *        $conf(
	 *				'intLevel'        => 日志级别,
	 *				'strLogFile'      => 保存日志文件,
	 *				'arrSelfLogFiles' => 用户自定义日志级别,
	 *				'intMaxFileSize'  => 最大文件大小
	 *				)
	 * @return 返回bdLog类实例
	 * @retval bdLog
	 */

	public static function getInstance($conf = null)
	{
		if( self::$instance === null )
		{
			if ($conf === null)
			{
				self::$instance = new bdLog($GLOBALS['LOG']);
			}else {
				self::$instance = new bdLog($conf);
			}
		}
		return self::$instance;
	}
	/**
	 * @brief 将一条日志打印到指定的日志文件上
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public function writeLog($intLevel, $str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		if( !($this->intLevel & $intLevel) || !isset(self::$arrLogLevels[$intLevel]) )
		{
			return false;
		}

		$strLevel = self::$arrLogLevels[$intLevel];

		$strLogFile = $this->strLogFile;
		if( ($intLevel & self::LOG_LEVEL_WARNING) || ($intLevel & self::LOG_LEVEL_FATAL) )
		{
			$strLogFile .= '.wf';
		}

		$trace = debug_backtrace();
		if( $depth >= count($trace) )
		{
			$depth = count($trace) - 1;
		}
		$file = basename($trace[$depth]['file']);
		$line = $trace[$depth]['line'];

		$strArgs = '';
		if( is_array($arrArgs) && count($arrArgs) > 0 )
		{
			foreach( $arrArgs as $key => $value )
			{
				$strArgs .= "{$key}[$value] ";
			}
		}

		$str = sprintf( "%s: %s [%s:%d] errno[%d] ip[%s] logid[%u] uri[%s] %s%s\n",
			$strLevel,
			date('m-d H:i:s:', time()),
			$file, $line, $errno,
			self::getClientIP(),
			$this->intLogId,
			isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
			$strArgs, $str);

		if($this->intMaxFileSize > 0)
		{
			clearstatcache();
			$arrFileStats = stat($strLogFile);
			if( is_array($arrFileStats) && floatval($arrFileStats['size']) > $this->intMaxFileSize )
			{
				unlink($strLogFile);
			}
		}
		return file_put_contents($strLogFile, $str, FILE_APPEND);
	}
	/**
	 * @brief 写自定义日志级别的日志内容
	 * @param 
	 *        $strKey  自定义标签必须包含在之前的配置项，否则写入失败
	 *        $str     日志内容
	 *        $arrArgs 参数信息
	 * @return 成功返回true
	 * @retval boolean
	 */
	public function writeSelfLog($strKey, $str, $arrArgs = null)
	{
		if( isset($this->arrSelfLogFiles[$strKey]) )
		{
			$strLogFile = $this->arrSelfLogFiles[$strKey];
		}
		else
		{
			return false;
		}

		$strArgs = '';
		if( is_array($arrArgs) && count($arrArgs) > 0 )
		{
			foreach( $arrArgs as $key => $value )
			{
				$strArgs .= "{$key}[$value] ";
			}
		}

		$str = sprintf( "%s: %s ip[%s] logId[%u] uri[%s] %s%s\n",
			$strKey,
			date('m-d H:i:s:', time()),
			self::getClientIP(),
			$this->intLogId,
			isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
			$strArgs, $str);

		if($this->intMaxFileSize > 0)
		{
			clearstatcache();
			$arrFileStats = stat($strLogFile);
			if( is_array($arrFileStats) && floatval($arrFileStats['size']) > $this->intMaxFileSize )
			{
				unlink($strLogFile);
			}
		}
		return file_put_contents($strLogFile, $str, FILE_APPEND);
	}
	/**
	 * @brief 静态方法 写入自定义级别日志内容
	 * @param 
	 *        $strKey  自定义标签必须包含在之前的配置项，否则写入失败
	 *        $str     日志内容
	 *        $arrArgs 参数信息
	 * @return 成功返回true
	 * @retval boolean
	 */	
	public static function selflog($strKey, $str, $arrArgs = null)
	{
		$log = bdLog::getInstance();
		return $log->writeSelfLog($strKey, $str, $arrArgs);
	}
	/**
	 * @brief 静态方法 写入DEBUG级别的日志内容
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public static function debug($str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		$log = bdLog::getInstance();
		return $log->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arrArgs, $depth + 1);
	}
	/**
	 * @brief 静态方法 写入TRACE级别的日志内容
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public static function trace($str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		$log = bdLog::getInstance();
		return $log->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arrArgs, $depth + 1);
	}
	/**
	 * @brief 静态方法 写入NOTICE级别的日志内容
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public static function notice($str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		$log = bdLog::getInstance();
		return $log->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arrArgs, $depth + 1);
	}
	/**
	 * @brief 静态方法 写入WARNING级别的日志内容
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public static function warning($str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		$log = bdLog::getInstance();
		return $log->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arrArgs, $depth + 1);
	}
	/**
	 * @brief 静态方法 写入FATAL级别的日志内容
	 * @param 
	 *        $intLevel 该条日志的级别，注意必须是包含在之前配置中
	 *        $str      日志内容
	 *        $errno    错误代码
	 *        $arrArgs  参数信息     默认null
	 *        $depth    打印回调信息的层数   默认全部打印
	 * @return 成功返回true 失败返回false
	 * @retval boolean
	 */
	public static function fatal($str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		$log = bdLog::getInstance();
		return $log->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arrArgs, $depth + 1);
	}
	/**
	 * @brief 静态方法 设置日志ID
	 * @param 
	 *        $intLogId 日志ID
	 * @return 
	 * @retval null
	 */
	public static function setLogId($intLogId)
	{
		bdLog::getInstance()->intLogId = $intLogId;
	}
	/**
	 * @brief 获得客户端IP
	 * @param 
	 * @return 返回客户端IP
	 * @retval string
	 */
	public static function getClientIP()
	{
		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif( isset($_SERVER['HTTP_CLIENTIP']) )
		{
			$ip = $_SERVER['HTTP_CLIENTIP'];
		}
		elseif( isset($_SERVER['REMOTE_ADDR']) )
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		elseif( getenv('HTTP_X_FORWARDED_FOR') )
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif( getenv('HTTP_CLIENTIP') )
		{
			$ip = getenv('HTTP_CLIENTIP');
		}
		elseif( getenv('REMOTE_ADDR') )
		{
			$ip = getenv('REMOTE_ADDR');
		}
		else
		{
			$ip = '127.0.0.1';
		}

		$pos = strpos($ip, ',');
		if( $pos > 0 )
		{
			$ip = substr($ip, 0, $pos);
		}

		if (!ip2long($ip)) {
			$ip = '127.0.0.0';
		}
		return trim($ip);
	}
}




/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
