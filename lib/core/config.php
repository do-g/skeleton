<?php

class Core_Config {

	private static $_instance;

	private function __construct() {}

	public static function i() {
		if (!self::$_instance) {
			self::$_instance = self::load();
		}
		return Util::deepclone(self::$_instance);
	}

	private static function load() {
		global $__config;
		return Util::array_to_obj($__config);
	}

}