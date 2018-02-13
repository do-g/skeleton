<?php

class Session {

	private static $_instance;
	private $namespace;
	private $data;

	private function __construct($namespace = null) {
		$_SESSION[SESSION_NAMESPACE] = $_SESSION[SESSION_NAMESPACE] ?: new stdClass;
		if ($namespace) {
			$this->namespace = $namespace;
			$_SESSION[SESSION_NAMESPACE]->$namespace = $_SESSION[SESSION_NAMESPACE]->$namespace ?: new stdClass;
			$this->data =& $_SESSION[SESSION_NAMESPACE]->$namespace;
		} else {
			$this->data =& $_SESSION[SESSION_NAMESPACE];
		}
	}

	public static function &i($namespace = null) {
		self::$_instance = new self($namespace);
		return self::$_instance->data;
	}

	public static function destroy($namespace = null) {
		if ($namespace) {
			unset($_SESSION[SESSION_NAMESPACE]->$namespace);
		} else {
			unset($_SESSION[SESSION_NAMESPACE]);
		}
		self::$_instance = null;
	}

}