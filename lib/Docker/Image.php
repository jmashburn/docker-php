<?php

namespace Docker;

class Image extends AbstractApiHandler {

	protected $idField = "Id";

	public static function find($params = array()) {
		$images = self::getCall('images/json', $params);
		
		if (!is_array($images->toArray())) {
			return [];
		}

		foreach ($images as $image) {
			foreach ($image['RepoTags'] as $repoTag) {
				list($repository, $tag) = explode(":", $repoTag);
				$tagImage = clone $image;
				$tagImage->Repository = $repository;
				$tagImage->Tag = $tag;
				$img[] = $tagImage;
			}
		}
		return new Util\Object($img);
	}

	public static function create($params = array()) {
		return self::postCall('images/create', $params);
	}

	public static function inspect($name) {
		return self::getCall("images/". $name ."/json");
	}

	public static function history($name, $params = array()) {
		return self::getCall("images/" . $name ."/history", $params);
	}

	public static function push() {

	}

	public static function tag() {

	}

	public static function remove() {

	}

	public static function search($params = array()) {
		return self::getCall("images/search", $params);
	}
}