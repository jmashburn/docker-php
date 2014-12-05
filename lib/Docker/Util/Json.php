<?php

namespace Docker\Util;


class Json {

	public static function encode(array $array = array()) {
		$json = json_encode($array, JSON_UNESCAPED_SLASHES);
		$json = str_replace('[]', '{}', $json);
		return $json;
	}

	public static function decode($json = null) {
		return json_decode($json, true);
	}
	
}