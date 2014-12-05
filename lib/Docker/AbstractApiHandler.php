<?php

namespace Docker;

abstract class AbstractApiHandler extends Util\Object {

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
		// if (isset($params['body'])) {
		// 	if ($params['body'] instanceof self::className()) {

		// 	} elseif (is_array($params['body'])) {

		// 	}
		// }
	}

	protected static function retrieveCall($params, $apiAuth = null) {
		$class = self::className();
		$instance = new $class($params);
		$url = $instance->getInstanceUrl();

		$processor = new ApiProcessor();
		list($response, $code) = $processor->request('get', $url, $params); 
		return self::processResponse($response);
	}

	protected static function processResponse($response, $options) {
		try {
			if (!empty($options['status_code']) && in_array($options['status_code'], array(404, 409, 500))) {
				throw new \Docker\DockerException($options['reason_phrase'], $options['status_code']);
				#$response = Util\Json::encode(array('status' => $response));
			}	
			$jsonArray = Util\Json::decode($response);
			$class = self::className();
			if (!empty($jsonArray[0])) {
				$instance = new Util\Object($jsonArray, $class);
			} else {
				$instance = new $class($jsonArray);
			}
			return $instance;
		} catch (\Docker\DockerException $e) {
			throw new DockerException($e->getMessage());
		}
	}

	protected static function postCall($class, $params = array(), $apiAuth = null) {
		self::validate('POST', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		#$url = self::getBaseUrl($class);
		list($response, $headers, $options) = $processor->request('POST', $class, $params);
		return self::processResponse($response, $options);	
	}

	protected static function getCall($endpoint, $params = array(), $apiAuth = null) {
		self::validate('GET', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		#$url = self::getBaseUrl($class);
		list($response, $headers, $options) = $processor->request('GET', $endpoint, $params);
		return self::processResponse($response, $options);	
	}

	protected static function deleteCall($class, $params = array(), $apiAuth = null) {
		self::validate('DELETE', $params, $apiAuth);
		$processor = new ApiProcessor($apiAuth);
		#$url = self::getBaseUrl($class);
		list($response, $headers, $options) = $processor->request('DELETE', $class, $params); 
		return self::processResponse($response, $options);	
	}

	protected static function putCall($class, $params = array(), $apiAuth = null) {
		self::validate('PUT', $params, $apiAuth);
		$classInstant = self::className();
		$instance = new $class(self::className());
		#$url = $instance->getInstanceUrl();
		$processor = new ApiProcessor($apiAuth);
		list($response, $headers, $options) = $processor->request('PUT', $class, $params); 
		return self::processResponse($response, $options);	
	}

}
