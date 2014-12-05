<?php

namespace Docker;

class Container extends AbstractApiHandler {

	protected $idField = "Id";

	public static function find($params = array()) {
		return self::getCall('containers/json', $params);
	}

	public static function create($params = array()) {
		return self::postCall('containers/create', $params);
	}

	public static function inspect($name,  $params = array()) {
		return self::getCall('containers/'.$name.'/json', $params);
	}

	public static function listProcesses($name, $params = array()) {
		return self::getCall('containers/'.$name.'/top', $params);
	}

	public static function logs($name, $params = array()) {
		return self::getCall('containers/'.$name.'/logs', $params);
	}

	public static function changes($name, $params = array()) {
		return self::getCall('containers/'.$name.'/changes', $params);
	}

	public static function export($name, $params = array()) {
		return self::getCall('containers/'.$name.'/export', $params);
	}

	public static function resize() {
	}

	public static function start($name, $params = array()) {
		return self::postCall('containers/'.$name.'/start', $params);
	}

	public static function stop($name, $params = array()) {
		return self::postCall('containers/'.$name.'/stop', $params);
	}

	public static function restart($name, $params = array()) {
		return self::postCall('containers/'.$name.'/restart', $params);
	}

	public static function kill($name, $params = array()) {
		return self::postCall('containers/'.$name.'/kill', $params);
	}

	public static function pause($name, $params = array()) {
		return self::postCall('containers/'.$name.'/pause', $params);
	}

	public static function unpause($name, $params = array()) {
		return self::postCall('containers/'.$name.'/unpause', $params);
	}

	public static function attach($name, $params = array()) {
		return self::postCall('containers/'.$name.'/attach', $params);
	}

	public static function wait($name, $params = array()) {
		return self::postCall('containers/'.$name.'/wait', $params);
	}

	public static function remove($name, $params = array()) {
		return self::deleteCall('containers/'.$name, $params);
	}

	public static function copy($name, $params = array()) {
		return self::postCall('containers/'.$name.'/restart', $params);
	}
}