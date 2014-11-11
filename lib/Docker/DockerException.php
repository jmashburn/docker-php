<?php

namespace Docker;

class DockerException extends \Exception {

	protected $messages = array();

	public function __construct($message = null, $code = null) {
		if (!empty($this->message[$code])) {
			$message = $this->message[$code];
		}
		parent::__construct($message, $code);
	}

}
