<?php

class Cache_Object extends Core_Cache {

	const KEY_PREFIX = '_obj_';

	protected function __construct() {
		$config = Core_Config::i()->cache->service->object;
		if (!$config || !$config->enabled) {
			$this->disable();
		} else {
			$config->key_prefix = self::KEY_PREFIX;
			parent::__construct($config);
		}
	}

	protected function to_key($key) {
		return md5($key);
	}

	public function get($key) {
		return unserialize(parent::get($key));
	}

	public function set($key, $content, $lifetime = null) {
		return parent::set($key, serialize($content), $lifetime);
	}

}