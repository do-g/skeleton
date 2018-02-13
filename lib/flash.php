<?php
class Flash {

	private $namespace;
	private $keys;

	private function __construct($namespace = null) {
		$this->namespace = $namespace;
		$this->keys = explode('/', $this->namespace);
	}

	public static function i($namespace = null) {
		return new self($namespace);
	}

	public function get() {
		$storage =& $_SESSION;
		$key = 'flash';
		$value = $storage[$key];
		foreach ($this->keys as $k) {
			if (isset($value[$k])) {
				$storage =& $storage[$key];
				$key = $k;
				$value = $storage[$key];
			} else {
				return null;
			}
		}
		unset($storage[$key]);
		return $value;
	}

	public function set($value) {
		$storage =& $_SESSION;
		$key = 'flash';
		foreach ($this->keys as $k) {
			$storage =& $storage[$key];
			$key = $k;
		}
		$storage[$key] = $value;
	}

}