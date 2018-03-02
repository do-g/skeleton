<?php

$root_path = realpath(dirname(__FILE__) . '/../../');
require_once '../config/config.php';
set_include_path(
	get_include_path() .
	PATH_SEPARATOR . PATH_LIB .
	PATH_SEPARATOR . PATH_CONTROLLERS
);
require_once PATH_LIB_CORE . DIRECTORY_SEPARATOR . 'autoloader.php';
spl_autoload_register('Core_Autoloader::load', true);
set_exception_handler('Core_Exception::handler');
@include_once(PATH_VENDOR . '/autoload.php');
Core_Router::i()->add('/', [
	'controller' => 'test',
	'action' => 'test',
	'token' => 'test1234',
], [
	'subdomain' => 'test',
]);
Core_Router::i()->add('/google', [
	'redirect' => 'https://www.google.com',
]);
Core_Application::i()->bootstrap();