<?php

namespace Docker;


class ApiConnection {

	const USER_AGENT = 'Docker-API';

	const PROTOCOL_VERSION = '1.1';

	private $defaults = array();

	private $options = array();

	public function __construct($options = null) {
		if (!is_array($options)) {
			$options = array($options);
		}
		$this->options = array_merge($this->defaults, $options);
	}

	public static function apiUrl($method = '', $opts = array()) {
		$version = \Docker::VERSION;
		$apiVersion = \Docker::API_VERSION;

		$baseUrl = \Docker::getBaseUrl();

		// if (!is_string($method)) {
		// 	throw new DockerException(sprintf('Expected a string, got: %s', $url));
		// }

		// if (!is_array($opts)) {
		// 	throw new DockerException(sprintf('Expected an array, got: %s', $opts));
		// }

		if (strpos($baseUrl, 'unix\:') !== 0) {
			return $baseUrl;	
		} else {
			$baseUrl = parse_url($baseUrl);
			if (in_array($uri['schema'], array('https', 'tcp'))) {

			} else {

			}
		}
	}

	public function request($method = null, $url, $opts = array()) {
		#$request = $this->__buildRequest();

		$opts = array('');
		try {
			$this->__requestRaw($method, $url, $opts);
		} catch (DockerException $e) {
			print_r($e);
			die('Made it here');
		}

		#return $this->__requestRaw($method, $url, $opts);
	}

	private function __requestRaw($method, $url, $opts) {
		if (!is_array($opts)) {
			$opts = array();
		}
		$opts = array('Content-Type' => 'application/json');

		if (($socket = stream_socket_client('unix:///var/run/docker.sock', $errno, $errorMessage, 10)) === false) {
			throw new DockerException(sprintf('Failed to connect: %s', $errorMessage), $errno);
		}

		if (!$socket) {
		    echo "$errorMessage ($errno)<br />\n";
		} else {
		    fwrite($socket, "GET /images/json HTTP/1.0\r\n");
		    while (!feof($socket)) {
		        echo fgets($socket, 1024);
		    }
		    fclose($socket);
		}
		die('asd');




		fwrite($socket, $this->__getRequestToString($method, $url, $opts));

		$line = fgets($socket);
		print_r($line);
		die();

		do {
			$headers = array();
			while (($line = fgets($socket)) !== false) {
				if (rtrim($line) === '') {
					break;
				}
				print_r($line);
				$headers[] = trim($line);
			}       
			die('sdf');    

			$parts = explode(' ', array_shift($headers), 3);
			print_r($parts);
		
        } while($parts[1] == 100);
        //Check timeout
        $metadata = stream_get_meta_data($socket);

        print_r($metadata);

	} 	

	private function __buildRequest($method, $opts) {

	}

	private function __getRequestToString($method, $url, $headers = array()) {
		$message = vsprintf('%s %s HTTP/%s', array(strtoupper($method), "/v".\Docker::API_VERSION.$url, self::PROTOCOL_VERSION));
		// foreach ($headers as $key => $value) {
		// 	$message .= $key . ': '. implode(', ', $values) ."\r\n";
		// }
		$message .= "\r\n".'Content-type: application/json'."\r\n";
		$message .= 'User-Agent: ' . self::USER_AGENT . ' ' . \Docker::API_VERSION;
		$message .= "\r\n";
		return $message;
	}
}
