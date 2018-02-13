<?php

class Cache_Storage_File {

	private $_dir;
	private $_config;

	public function __construct($service_config) {
		$config = Core_Config::i()->cache->storage->file;
		if (!$config->path) {
			throw new Core_Exception("Undefined option \"path\" for cache storage \"" . get_class($this) . "\"");
		}
		$this->_dir = rtrim($config->path, DIRECTORY_SEPARATOR);
		if (!is_dir($this->_dir)) {
			throw new Core_Exception("Cache storage directory \"{$this->_dir}\" not found");
		} else if (!is_writable($this->_dir)) {
			throw new Core_Exception("Cache storage directory \"{$this->_dir}\" not writable");
		}
		$this->_config = $service_config;
		if (!$this->_config->key_prefix) {
			throw new Core_Exception("Undefined option \"key_prefix\" for service used with cache storage \"" . get_class($this) . "\"");
		}
	}

	public function get($key) {
		$file = $this->get_file_path($key);
		if (is_file($file)) {
			return file_get_contents($file);
		}
		return false;
	}

	public function set($key, $content, $lifetime = null) {
		$file = $this->get_file_path($key);
		file_put_contents($file, $content);
		if ($lifetime) {
			touch($file . '_exp_' . $lifetime);
		}
	}

	public function delete($key) {
		$file = $this->is_file($key) ? $key : $this->get_file_path($key);
		foreach (glob("{$file}_exp_*") as $metafile) {
			unlink($metafile);
		}
		if (is_file($file)) {
			unlink($file);
		}
	}

	public function clear() {
		$files = glob($this->_dir . "/{$this->_config->key_prefix}*");
		foreach ($files as $file) {
		    $this->delete($file);
		}
	}

	public function clear_special() {
		$files = glob($this->_dir . "/{$this->_config->key_prefix}*");
		foreach ($files as $file) {
			if (strpos($file, '_exp_') === false && (!$this->get_exp($file) || $this->expired($file))) {
		    	$this->delete($file);
			}
		}
	}

	public function expired($key) {
		$file = $this->is_file($key) ? $key : $this->get_file_path($key);
		if (!is_file($file)) {
			return;
		}
		$lifetime = (int) ($this->get_exp($file) ?: $this->_config->lifetime);
		if (!$lifetime) {
			return false;
		}
		$changed = @filemtime($file);
		return $changed && $changed < time() - $lifetime;
	}

	public function get_exp($file) {
		foreach (glob("{$file}_exp_*") as $metafile) {
			return str_replace("{$file}_exp_", '', $metafile);
		}
	}

	private function get_file_path($key) {
		return $this->_dir . DIRECTORY_SEPARATOR . $this->to_key($key);
	}

	private function is_file($key) {
		return strpos($key, $this->_config->key_prefix) !== false;
	}

	private function to_key($key) {
		return $this->_config->key_prefix . $key;
	}

}