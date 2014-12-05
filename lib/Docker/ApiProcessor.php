<?php

namespace Docker;


class ApiProcessor {

	const USER_AGENT = 'Docker-API';

	const PROTOCOL_VERSION = '1.1';

	const CHUNK_SIZE = 8192;

	private $defaults = array();

	private $options = array();

	public function __construct($options = null) {
		if (!is_array($options)) {
			$options = array($options);
		}
		$this->options = array_merge($this->defaults, $options);
	}

	public static function apiEndpoint($endpoint = '') {
		$version = \Docker::API_VERSION;
		#$baseUrl = \Docker::getBaseUrl();
		return "/v$version/" . $endpoint ;
	}

	public function request($method, $endpoint, $params, $auth = array()) {
		if (!is_array($params)) {
			$params = array($params);
		}
		if (empty($params['headers'])) {
			$params['headers'] = array('Connection' => 'Close');
		}

		if(!array_key_exists('Connection', $params['headers'])) {
			$params['headers'] = array('Connection' => 'Close');
		}
		return $this->_requestRaw($method, $endpoint, $params, $auth);
	}

	public static function encode($array, $prefix = null) {
		if (!is_array($array)) {
			return $array;
		}

		$result = array();
		foreach ($array as $key => $value) {
			if (is_null($value)) {
				continue;
			}
			if ($prefix && $key && !is_int($key)) {
				$key = $prefix."[" . $key ."]";
			} elseif ($prefix) {
				$key = $prefix."[]";
			}

			if (is_array($value)) {
				$result[] = self::encode($value, $key, true);
			} else {
				$result[] = urlencode($key) ."=".urlencode($value);
			}
		}
		return (count($result)>0)?implode('&', $result):$result;
	}

	private function _requestRaw($method, $endpoint, $params, $auth) {
		if (!is_array($params)) {
			$params = array();
		}

		$headers = array();
		if (isset($params['headers']) && is_array($params['headers'])) {
			$headers = $params['headers'];
			unset($params['headers']);
		}
		list($response, $headers, $options) = $this->_socketRequest($method, $endpoint, $headers, $params, $auth);
		return array($response, $headers, $options);
	}

	private function _socketRequest($method, $endpoint, $headers, $params, $auth) {
		$method = strtoupper($method);
		$options = array();

		$authStr = self::encode($auth);

		#$url = self::apiUrl($url)."?$authStr";
		$endpoint = self::apiEndpoint($endpoint);
		if ($method == 'GET') {
			#$endpoint .="?$authStr";
			if (count($params) > 0) {
				$encoded = self::encode($params);
				$endpoint = "$endpoint?$encoded";
			}
		} elseif ($method == 'PUT') {
			$params = array_merge($auth, $params);
			#$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
			#$options[CURLOPT_POSTFIELDS] = self::encode($params);
		} elseif ($method == 'POST') {
			$params = array_merge($auth, $params);
			if (!empty($params['body'])) {
				$body = $params['body'];
				unset($params['body']);
			}
			if (count($params) > 0) {
				$encoded = self::encode($params);
				$endpoint = "$endpoint?$encoded";
			}
		} elseif ($method == 'DELETE') {
			#$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			#$endpoint ."?$authStr";
			if (count($params) > 0) {
				$encoded = self::encode($params);
				$endpoint = "$endpoint?$encoded";
			}

		} else {
			throw new DockerException('Unrecognized method %method');
		}



		if (!empty($body)) {
			$headers['Content-Length'] = strlen($body);
		}

		if (($socket = stream_socket_client(\Docker::getBaseUrl(), $errno, $errorMessage, \Docker::getTimeout())) === false) {
			throw new DockerException(sprintf('Failed to connect: %s', $errorMessage), $errno);
		}
		#die($this->__getRequestToString($method, $endpoint, $headers, $params));
		fwrite($socket, $this->__getRequestToString($method, $endpoint, $headers, $params));

		if (!empty($body) && isset($body)) {
			if (is_array($body)) {

			} elseif (is_object($body) && $body instanceof \Docker\Util\Object) {

			} else {

			}

			// Chunck
		  	$bodyResource = fopen('php://temp', 'r+');
	        if ($body !== '') {
	            fwrite($bodyResource, $body);
	            fseek($bodyResource, 0);
	        }

	        while(!feof($bodyResource)) {
	        	$write = fwrite($socket, fread($bodyResource, self::CHUNK_SIZE));
	        }
	        fclose($bodyResource);

	        // Chunck
		}





		// Do some stuff with post/get/delete body

		stream_set_timeout($socket, \Docker::getTimeout());
		do { 
			list($headers, $options) = $this->__getResponseHeaders($socket);
		} while($options['status_code'] === 100);

		//Check timeout
        $metadata = stream_get_meta_data($socket);

        if ($metadata['timed_out']) {
            throw new DockerException('Timed out while reading socket');
        }
        $contents = $this->__getResponseContent($headers, $socket);        
		if (is_resource($socket)) {
            fclose($socket);
        }

		return array($contents, $headers, $options);
	}


	private function __getResponseContent($headers, $socket) {
		$maxlength = -1;
		$contents = '';
		if (!empty($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked') {
			$contents = '';
        	while (!feof($socket)) {
        		$tmpSize = "";
        		while (($read = (string) fread($socket, 1)) != "\n") {
        			$tmpSize .= $read;
        			if (feof($socket)) {
        				return null;
        			}
        		}

        		$size = hexdec(trim($tmpSize));
        		if ($size > 0) {
        			$part = (string)fread($socket, $size);
        		} else {
        			$part = '';
        		}

        		while ((string) fread($socket, 1) != "\n") {
        			if (feof($socket)) {
        				return null;
        			}
        		}

    			if ($part == null) {
        			break;
        		}
        		$contents .= $part;

        		if ($maxlength > -1 && strlen($contents) > $maxlength) {
                	return substr($contents, 0, $maxlength);
            	}
        	}	
        } elseif (!empty($headers['Content-Type']) && $headers['Content-Type'] == 'application/vnd.docker.raw-stream') {

        } else {
        	if (!empty($headers['Content-Length'])) {
				$contents = (string) stream_get_contents($socket, $headers['Content-Length']);
			}
		}
		return $contents;
	}


	private function __getResponseHeaders($socket) {
			$headers = array();
	        while (($line = fgets($socket)) !== false) {
	            if (rtrim($line) === '') {
	                break;
	            }
	            $headers[] = trim($line);
	        }
	        $parts = explode(' ', array_shift($headers), 3);
	        $options = ['status_code' => $parts[1]];
	        $options['protocol_version'] = substr($parts[0], -3);
	        if (isset($parts[2])) {
	            $options['reason_phrase'] = $parts[2];
	        }
	        // Set the size on the stream if it was returned in the response
	        $responseHeaders = [];
	        foreach ($headers as $header) {
	            $headerParts = explode(':', $header, 2);
	            $responseHeaders[trim($headerParts[0])] = isset($headerParts[1])
	                ? trim($headerParts[1])
	                : '';
	        }
	        return array($responseHeaders, $options);
	}

	private function __getRequestToString($method, $endpoint, $headers = array(), $params = array()) {
		$endpoint = $endpoint;
		$message = vsprintf("%s %s HTTP/%s\r\n", array(strtoupper($method), $endpoint, self::PROTOCOL_VERSION));
		foreach ($headers as $key => $value) {
			$message .= $key . ': '. $value ."\r\n";
		}
		$message .= 'Content-type: application/json'."\r\n";
		$message .= 'User-Agent: ' . self::USER_AGENT . ' ' . \Docker::API_VERSION."\r\n";
		$message .= "\r\n";
		return $message;
	}
}
