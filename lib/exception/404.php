<?php

class Exception_404 extends Core_Exception {

	const CODE = 404;
	const SYS_CODE = -404;

	public function __construct($message, Exception $previous = null) {
        parent::__construct($message, self::CODE, $previous);
    }

}