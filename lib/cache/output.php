<?php

class Cache_Output extends Core_Cache {

	const KEY_PREFIX = '_out_';
	private $_key;

	protected function __construct() {
		$config = Core_Config::i()->cache->service->output;
		if (!$config || !$config->enabled) {
			$this->disable();
		} else {
			$config->key_prefix = self::KEY_PREFIX;
			parent::__construct($config);
		}
	}

	protected function to_key($key) {
		$this->_key = md5($key);
		return $this->get_key();
	}

	public function get_key() {
		return $this->_key;
	}

}