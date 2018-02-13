<?php

class Core_Autoloader {

    public static function load($class_name) {
    	if (class_exists($class_name, false) || interface_exists($class_name, false)) {
			return;
		}
		$class = strtolower($class_name);
		$code = strpos($class, 'controller') !== false ? -404 : null;
		$class_file = $class . '.php';
		$class_path = stream_resolve_include_path($class_file);
		if (!$class_path) {
			$class_file2 = str_replace('_', DIRECTORY_SEPARATOR, $class_file);
			$class_path = stream_resolve_include_path($class_file2);
			if (!$class_path) {
				throw new Core_Exception("Unable to locate class \"{$class_name}\" (looking for \"{$class_file}\" or \"{$class_file2}\")", $code);
			}
		}
		include $class_path;
		if (!class_exists($class, false)) {
			throw new Core_Exception("Class \"{$class_name}\" not found", $code);
		}
    }
}