<?php

namespace Docker;

abstract class AbstractionApiHandler extends Util\Object {

	public static function name($class = null) {
		if (!$class) {
			$class = self::className();
		}
		if (strrchr($class, '\\')) {
			$class = substr(strrchr($class,'\\'), 1);
		}
		return $class;
	}

	public static function className() {
		return get_called_class();
	}

	public function getInstanceUrl($base = null) {
		$id = $this->{$this->idField};
		if (!$id) {
			throw new DockerException(sprintf('Could not create URL for %s. No ID found for object', get_class($this)));
		}
		if (isset($base)) {
			$base = lcfirst($base);
			return self::getBaseUrl($base."/".$id);
		}
		return self::getBaseUrl(self::name(self::className())."/".$id);
	}

	public static function getBaseUrl($base = null) {
		if (!$base) {
			throw new DockerException('No name found for this Docker API call.');
		}
		return lcfirst($base);
	}

	private static function validate($method, $params=null, $apiAuth=null) {
		if (isset($params) && !is_array($params)) {
			throw new DockerException('You must pass an array as the first argument to the Docker API call');
		}
		if (isset($apiAuth) && !is_array($apiAuth)) {
			throw new DockerException('You must pass an array of authentication parameters');
		}
	}

	protected static function retrieveCall($params, $apiAuth = null) {
		$class = self::className();
		$instance = new $class($params);
		$url = $instance->getInstanceUrl();
		$processor = new ApiProcessor($apiAuth);
		list($response, $code) = $processor->request('get', $url, $params); 
		return self::processResponse($response);
	}

	protected static function processResponse($response) {
		try {
			// Some Response are empty. Create Empty XML for those times
			if (!$response) {
				$response = "<empty/>";
			}
			$xmlArray = Util\Xml::toArray(Util\Xml::build($response));
			if (!empty($xmlArray['error'])) {
				throw new DockerException($xmlArray['error']['message'], $xmlArray['error']['code']);
			}
			$class = self::className();
			$instance = new $class($xmlArray);
			return $instance;
		} catch (\Docker\Util\XmlException $e) {
			throw new DockerException($e->getMessage());
		}
	}

	protected static function postCall($class, $params = null, $apiAuth = null) {
		self::validate('post', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		$url = self::getBaseUrl($class);
		list($response, $code) = $processor->request('post', $url, $params); 
		return self::processResponse($response);
	}

	protected static function getCall($class, $params = null, $apiAuth = null) {
		self::validate('get', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		$url = self::getBaseUrl($class);
		list($response, $code) = $processor->request('get', $url, $params);
		return self::processResponse($response);
	}

	protected static function deleteCall($class, $params = null, $apiAuth = null) {
		self::validate('delete', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		$url = self::getBaseUrl($class);
		list($response, $code) = $processor->request('delete', $url, $params); 
		return self::processResponse($response);
	}

	protected static function putCall($class, $params = null, $apiAuth = null) {
		self::validate('put', $params, $apiAuth);
		$class = self::className();
		$instance = new $class($params);
		$url = $instance->getInstanceUrl();
		$processor = new ApiProcessor($apiAuth);
		list($response, $code) = $processor->request('put', $url, $params); 
		return self::processResponse($response);	
	}
