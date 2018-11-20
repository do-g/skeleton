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
		$this->_config = $service_config;
		if (!$this->_config->key_prefix) {
			throw new Core_Exception("Undefined option \"key_prefix\" for service used with cache storage \"" . get_class($this) . "\"");
		}
	}

	public function get($key) {
		$file = $this->get_file_path($key);
		if (is_file($file)) {
			$contents = @file_get_contents($file);
			if ($contents === false) {
				throw new Core_Exception("Unable to read cache file \"{$file}\"");
			}
			return $contents;
		}
		return false;
	}

	public function set($key, $content, $lifetime = null) {
		$this->create_dir(dirname($key));
		$file = $this->get_file_path($key);
		if (@file_put_contents($file, $content) === false) {
			throw new Core_Exception("Unable to write cache file \"{$file}\"");
		}
		$lifetime = $lifetime ?: $this->_config->lifetime;
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
		$files = glob($this->_dir . "/{$this->_config->key_prefix}/*");
		foreach ($files as $file) {
		    $this->delete($file);
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

	private function create_dir($suffix = null) {
		$dir_path = $this->get_file_path($suffix);
		if (is_dir($dir_path)) {
			return;
		}
		$result = @mkdir($dir_path, 0755, true);
		if (!$result) {
			throw new Core_Exception("Unable to create cache storage directory \"{$dir_path}\"");
		}
	}

	private function get_dir_path() {
		return $this->_dir . DIRECTORY_SEPARATOR . $this->_config->key_prefix;
	}

	private function get_file_path($key) {
		return $this->get_dir_path() . DIRECTORY_SEPARATOR . $key;
	}

	private function is_file($key) {
		return strpos($key, DIRECTORY_SEPARATOR) !== false;
	}

}