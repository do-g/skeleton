<?php

abstract class Core_Cache {

	private static $_instances = [];
	protected $_storage;
	protected static $__disabled = false;
	protected $_disabled = false;

	protected function __construct($config) {
		if (!Core_Config::i()->cache || !Core_Config::i()->cache->enabled) {
			$this->disable();
		} else {
			if (!$config->storage) {
				throw new Core_Exception("Undefined option \"storage\" for cache service \"" . get_class($this) . "\"");
			}
			if ($config->lifetime == -1) {
				$config->lifetime = 0;
				register_shutdown_function([$this, 'clear_session']);
			}
			$storage_class_name = 'Cache_Storage_' . $config->storage;
			$this->_storage = new $storage_class_name($config);
		}
	}

	final public static function i(...$arguments) {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class(...$arguments);
        }
        return self::$_instances[$class];
	}

	public function get($key) {
		if ($this->is_disabled()) {
			return false;
		}
		if ($this->expired($key) === true) {
			$this->delete($key);
			return false;
		}
		return $this->_storage->get($this->to_key($key));
	}

	public function set($key, $content, $lifetime = null) {
		if ($this->is_disabled()) {
			return false;
		}
		return $this->_storage->set($this->to_key($key), $content, $lifetime);
	}

	public function delete($key) {
		if ($this->is_disabled()) {
			return false;
		}
		return $this->_storage->delete($this->to_key($key));
	}

	public function expired($key) {
		return $this->_storage->expired($this->to_key($key));
	}

	public function clear() {
		if ($this->is_disabled()) {
			return false;
		}
		return $this->_storage->clear();
	}

	public function clear_session() {
		return $this->_storage->clear_special();
	}

	public function disable() {
		$this->_disabled = true;
	}

	public static function _disable() {
		self::$__disabled = true;
	}

	public function enable() {
		$this->_disabled = false;
	}

	public static function _enable() {
		self::$__disabled = false;
	}

	protected function to_key($key) {
		return $key;
	}

	protected function is_disabled() {
		return self::$__disabled || $this->_disabled;
	}

}