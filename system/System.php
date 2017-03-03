<?php

namespace App\System;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/Config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/UserConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/libraries/Session.php';

use App\System\Library\Session;

class System
{
	public static function get_config($name = NULL)
	{
		global $config;
		if ($name)
		{
			return $config[$name];
		}
		return $config;
	}
	public static function get_user_config($name = NULL)
	{
		global $userconfig;
		if ($name)
		{
			return $userconfig[$name];
		}
		return $userconfig;
	}
	public static function input_get($attr)
	{
		return isset($_GET[$attr]) ? $_GET[$attr] : FALSE;
	}
	public static function input_post($attr)
	{
		return isset($_POST[$attr]) ? $_POST[$attr] : FALSE;
	}
	public static function get_uid()
	{
		static $i = 0;
		$m = microtime(true);
		return sprintf("%8x%05x%02x", floor($m), ($m-floor($m)) * 1000000, $i++);
	}
	public static function redirect($location = NULL, $data = NULL)
	{
		if (!$location)
		{
			$location = self::current_uri();
		}
		if ($data === NULL)
		{
			$data = $_GET;
		}
		if (is_array($data) and !empty($data))
		{
			$location .= '?' . http_build_query($data);
		}
		header('Location: ' . $location);
		exit;
	}
	public static function current_uri()
	{
		return $_SERVER['PHP_SELF'];
	}
	public static function show_404()
	{
		self::redirect('/system/error/404.php', TRUE, 404);
	}
	/*
	public static function generate_uniqid()
	{
		return uniqid() . sprintf('%02x', rand(1, 255));
	}
	 * 
	 */
	public static function alert($type, $msg, $auto_close)
	{
		$title = ucfirst($type);
		$classes = "alert alert-$type ";
		if ($auto_close) $classes .= 'auto-close-msg';
		return <<<EOF
		<div class="$classes">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<div class="inner-msg">
				<strong> $title ! </strong> $msg
			</div>
		</div>
EOF;
	}
	public static function put_msg($type, $msg, $auto_close = TRUE)
	{
		if ($msg instanceof \Exception)
		{
			$msg = self::get_exception_msg($msg);
		}
		$str = self::alert($type, $msg, $auto_close);
		Session::set('msg', $str);
	}
	public static function get_msg()
	{
		$r = Session::get('msg');
		Session::remove('msg');
		return $r;
	}
	public static function get_exception_msg($ExceptionObject)
	{
		$html =
			'<pre><strong>#Message: </strong><em>#' . $ExceptionObject->getCode() . ': ' . $ExceptionObject->getMessage() . '</em>' . PHP_EOL .
			'=> ' . $ExceptionObject->getFile() . '(' . $ExceptionObject->getLine() . ')' .
			PHP_EOL . $ExceptionObject->getTraceAsString() .
			'</pre>';
		return $html;
	}
}

?>