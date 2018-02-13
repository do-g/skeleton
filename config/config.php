<?php

date_default_timezone_set('Europe/Bucharest');

define('PATH_ROOT', realpath(dirname(__FILE__) . '/..'));
define('PATH_CONFIG', PATH_ROOT . DIRECTORY_SEPARATOR . 'config');
define('PATH_LIB', PATH_ROOT . DIRECTORY_SEPARATOR . 'lib');
define('PATH_LIB_CORE', PATH_LIB . DIRECTORY_SEPARATOR . 'core');
define('PATH_CONTROLLERS', PATH_ROOT . DIRECTORY_SEPARATOR . 'controllers');
define('PATH_VIEWS', PATH_ROOT . DIRECTORY_SEPARATOR . 'views');
define('PATH_LAYOUTS', PATH_VIEWS . DIRECTORY_SEPARATOR . '_layouts');
define('PATH_LABELS', PATH_CONFIG . DIRECTORY_SEPARATOR . 'labels.csv');
define('PATH_EMAILS', PATH_ROOT . DIRECTORY_SEPARATOR . 'email');
define('PATH_VENDOR', PATH_ROOT . DIRECTORY_SEPARATOR . 'vendor');
define('PATH_TEST', PATH_ROOT . DIRECTORY_SEPARATOR . 'test');
define('URL_BASE', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST']);
define('URL_BASE_FRAGMENT', '');
define('LANG_ACTIVE', 'en');

if (function_exists('apache_getenv')) {
	$env = apache_getenv('LB_ENV');
} else {
	$env = getenv('LB_ENV');
}
$env = $env ?: 'env';
$env_config = PATH_CONFIG . DIRECTORY_SEPARATOR . "config.{$env}.php";
if (!is_file($env_config)) {
	throw new Exception("Config file {$env_config} not found");
}
if (!is_readable($env_config)) {
	throw new Exception("Config file {$env_config} not readable");
}
include $env_config;
return;