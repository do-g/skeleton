<?php

class Exception_Forbidden extends Core_Exception {

	const CODE = -403;

	public function __construct($message, Exception $previous = null) {
        parent::__construct($message, self::CODE, $previous);
    }

}