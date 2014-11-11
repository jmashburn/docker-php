<?php

use Docker\DocketException;

abstract class Docker {

	const VERSION = '1.0.0';

	const API_VERSION = '1.14';

	public static $baseUrl = 'unix:///var/run/docker.sock';

	public static $timeout = '10';


	public static function getVersion() {
		return self::VERSION;
	}

	public static function getApiVersion() {
		return self::API_VERSION;
	}

	public static function setBaseUrl($baseUrl = null) {
		self::$baseUrl = $baseUrl;
	}

	public static function getBaseUrl() {
		return self::$baseUrl;
	}

	public static function setTimeout($timeout) {
		self::$timeout = $timeout;
	}

	public static function getTimeout() {
		return self::$timeout;
	}
}
