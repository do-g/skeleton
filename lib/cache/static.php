<?php

class Cache_Static extends Core_Cache {

	const KEY_PREFIX = 'static';

	protected function __construct() {
		$config = Core_Config::i()->cache->service->static;
		if (!$config || !$config->enabled) {
			$this->disable();
		} else {
			$config->storage = Core_Cache::Cache_Storage_File;
			$config->key_prefix = self::KEY_PREFIX;
			parent::__construct($config);
		}
	}

	protected function to_key($key) {
		$key = urldecode($key);
		$key = trim($key, '/');
		$key = $key ?: 'index';
		return "{$key}.html";
	}

}