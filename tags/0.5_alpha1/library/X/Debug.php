<?php

class X_Debug {

	const INFO = "[III] ";
	const WARNING = "[WWW] ";
	const ERROR = "[EEE] ";
	const FATAL = "[FFF] ";
	
	const LVL_NONE = -1;
	const LVL_FATAL = 0;
	const LVL_ERROR = 1;
	const LVL_WARNING = 2;
	const LVL_INFO = 3;
	
	private static $logFile = null;
	private static $logLevel = -1;
	
	/**
	 * Prevent constuction
	 */
	private function __construct() {}
	
	/**
	 * Init debug system
	 * @param string $logPath path for debug log
	 * @param int $logLevel level of debug is allowed from
	 */
	static public function init($logPath, $logLevel = self::LVL_FATAL) {
		if ( !is_null(self::$logFile) ) {
			fclose(self::$logFile);
		}
		self::$logFile = fopen($logPath, 'ab');
		self::$logLevel = $logLevel;
		self::forcedInfo("------------------------------------------------------");
		self::forcedInfo("Debug log enabled, level: {$logLevel}");
	}
	
	/**
	 * Add a formatted message to debug log
	 * @param string $message
	 * @param string $type
	 * @param string $func
	 * @param string $line
	 */
	static protected function _($message, $type = '', $func = '', $line = '') {
		if ($func != '') {
			if ( $line != '' ) {
				$func = "({$func}:{$line}) ";
			} else {
				$func = "({$func}) ";
			}
		}
		$time = date("[d/m/Y H:i:s]");
		if ( self::$logFile ) {
			fwrite(self::$logFile, "{$time} {$type}{$func}{$message}\n");
		}
	}
	
	/**
	 * Add info message in debug log
	 * @param string $message message to log
	 * @param int $traceBack n of steps for debug traces
	 */
	static public function i($message, $traceBack = 1) {
		if ( self::$logLevel < self::LVL_INFO ) return;
		$traces = debug_backtrace();
		$traces = $traces[$traceBack];
		$func = $traces['function'];
		if ( @$traces['class'] ) {
			$func = "{$traces['class']}::{$func}";
		}
		$line = @$traces['line'];
		self::_($message, self::INFO, $func, $line);
	}
	/**
	 * Add warning message in debug log
	 * @param string $message message to log
	 * @param int $traceBack n of steps for debug traces
	 */
	static public function w($message, $traceBack = 1) {
		if ( self::$logLevel < self::LVL_WARNING ) return;
		$traces = debug_backtrace();
		$traces = $traces[$traceBack];
		$func = $traces['function'];
		if ( @$traces['class'] ) {
			$func = "{$traces['class']}::{$func}";
		}
		$line = @$traces['line'];
		self::_($message, self::WARNING, $func, $line);
	}
	
	/**
	 * Add error message in debug log
	 * @param string $message message to log
	 * @param int $traceBack n of steps for debug traces
	 */
	static public function e($message, $traceBack = 1) {
		if ( self::$logLevel < self::LVL_ERROR ) return;
		$traces = debug_backtrace();
		$traces = $traces[$traceBack];
		$func = $traces['function'];
		if ( @$traces['class'] ) {
			$func = "{$traces['class']}::{$func}";
		}
		$line = @$traces['line'];
		self::_($message, self::ERROR, $func, $line);
	}
	
	/**
	 * Add fatal message in debug log
	 * @param string $message message to log
	 * @param int $traceBack n of steps for debug traces
	 */
	static public function f($message, $traceBack = 1) {
		if ( self::$logLevel < self::LVL_FATAL ) return;
		$traces = debug_backtrace();
		$traces = $traces[$traceBack];
		$func = $traces['function'];
		if ( @$traces['class'] ) {
			$func = "{$traces['class']}::{$func}";
		}
		$line = @$traces['line'];
		self::_($message, self::FATAL, $func, $line);
	}

	/**
	 * Add a info to the log, even if debug_level don't allow info logs
	 * @param string $message message for the log
	 * @param int $traceBack n of steps for debug traces
	 */
	static public function forcedInfo($message, $traceBack = 1) {
		$traces = debug_backtrace();
		$traces = $traces[$traceBack];
		$func = $traces['function'];
		if ( @$traces['class'] ) {
			$func = "{$traces['class']}::{$func}";
		}
		$line = @$traces['line'];
		self::_($message, self::INFO, $func, $line);
	}
}


