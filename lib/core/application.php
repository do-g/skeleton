<?php

class Core_Application {

	private static $_instance;
	private $_view;

	private function __construct() {}

	public static function i() {
		if (!self::$_instance) {
			session_start();
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function bootstrap($request = null) {
		if (!$request) {
			$uri = Util::get_uri(true);
			$this->send_cached_output($uri);
			$subdomain = Util::get_subdomain();
			$request = Core_Router::i()->route($uri, $subdomain);
		}
		$this->do_request($request);
		$this->_view->render();
	}

	public function do_request($request) {
		$this->_view = new Core_View();
		$controller = Core_Controller::factory($request, $this->_view);
	}

	private function send_cached_output($uri) {
		$cache = Cache_Output::i()->get($uri);
		if ($cache !== false) {
			header('X-Cache: ' . Cache_Output::i()->get_key());
			echo $cache;
			exit;
		}
	}

}