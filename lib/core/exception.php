<?php

class Core_Exception extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return parent::__toString();
    }

    public static function handler($ex) {
		try {
			Core_Cache::_disable();
			$request = [
				'controller' => Core_Controller::CONTROLLER_ERROR,
				'params'     => [
					'_exception' => $ex,
				],
			];
			Core_Application::i()->bootstrap($request);
		} catch (Exception $ex) {
			throw $ex;
		}
	}
}