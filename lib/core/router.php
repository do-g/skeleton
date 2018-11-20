<?php

class Core_Router {

	const CONSTRAIN_DIGIT = '\d';
	const CONSTRAIN_DIGITS = '\d+';
	const CONSTRAIN_LETTER = '[a-zA-Z]';
	const CONSTRAIN_LETTERS = '[a-zA-Z]+';
	const CONSTRAIN_LETTERS_NUMBERS = '[a-zA-Z0-9]+';
	const CONSTRAIN_FRAGMENT = '[a-zA-Z0-9-]+';
	const CONSTRAIN_ALPHANUM = '[\w-]+';
	private static $_instance;
	private $_routes = [];

	private function __construct() {}

	public static function i() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function add($pattern, $data = [], $constraints = []) {
		$route = new stdClass();
		$route->pattern = '/' . ltrim($pattern, '/');
		$route->data = $data;
		$route->constraints = $constraints;
		array_push($this->_routes, $route);
	}

	private function add_default() {
		$this->add(':controller/:action/*', [
			'controller' => Core_Controller::DEFAULT_CONTROLLER,
			'action'     => Core_Controller::DEFAULT_ACTION,
		]);
	}

	public function route($uri, $subdomain = null) {
		$uri_parts = explode('?', $uri);
		$uri = $uri_parts[0];
		$query_string = $uri_parts[1];
		$this->add_default();
		foreach ($this->_routes as $r) {
			$vars = [];
			$pattern = preg_replace_callback('~:([^/]+)~', function($matches) use (&$vars, $r) {
				$param_name = array_pop($matches);
				array_push($vars, $param_name);
				$regex = $r->constraints[$param_name] ? $r->constraints[$param_name] : '[^/]+';
				if (isset($r->data[$param_name])) {
					return "?(?:({$regex})*)";
				}
				return "(?:({$regex}))";
			}, $r->pattern);
			$pattern = preg_replace_callback('~(/\*)~', function($matches) use (&$vars) {
				array_push($vars, 'params');
				return '/?(.*)';
			}, $pattern);
			if (!$this->valid_on_subdomain($r, $subdomain)) {
				continue;
			}
			if (preg_match("~^{$pattern}$~i", $uri, $matches)) {
				if ($r->data['redirect']) {
					Util::redirect($r->data['redirect'], $r->data['status']);
				}
				array_shift($matches);
				$data = [];
				if ($vars) {
					foreach ($vars as $k => $v) {
						$data[$v] = $matches[$k];
					}
				}
				return $this->build($data, $r->data, $query_string);
			}
		}
		throw new Core_Exception("No route matches request \"{$uri}\"");
	}

	private function build($url_data, $route_defaults, $query_string) {
		$raw_params = $url_data['params'];
		// params that are defined along with the route like /:name
		$route_params = array_diff_key($url_data, ['params' => null]);
		// params that are set in the url like ?key=value
		$query_params = Util::explode_to_key_value_pairs($query_string, '=', '&');
		// params that are set in the url like /key/value
		// but are not defined along with the route like /:name
		$url_params = Util::explode_to_key_value_pairs($url_data['params']);
		// params that are set in the url like /key/value take precedence
		// over params that are set in the url like ?key=value
		$url_params = array_merge($query_params, $url_params);
		// params that are defined along with the route as data
		$params = $route_defaults;
		if ($route_params) {
			// params that are defined along with the route like /:name take precedence
			// over params that are defined along with the route as data
			// but only if they are not empty
			foreach ($route_params as $k => $v) {
				if (trim($v) != '' || !isset($params[$k])) {
					$params[$k] = $v;
				}
			}
		}
		if ($url_params) {
			// params that are set in the url take precedence
			// over params that are defined along with the route
			// but only if they are not empty
			foreach ($url_params as $k => $v) {
				if (trim($v) != '' || !isset($params[$k])) {
					$params[$k] = $v;
				}
			}
		}
		$special = ['controller', 'action', 'view'];
		$route = [
			'controller' => $params['controller'] ? $params['controller'] : Core_Controller::DEFAULT_CONTROLLER,
			'action'     => $params['action'] ? $params['action'] : Core_Controller::DEFAULT_ACTION,
			'view'       => $params['view'],
			'params'     => array_diff_key($params, array_flip($special)),
			'raw_params' => $raw_params,
		];
		return $route;
	}

	private function valid_on_subdomain($route, $subdomain) {
		$route_subdomain = $route->constraints['subdomain'];
		if (!$route_subdomain) {
			return true;
		}
		return (is_array($route_subdomain) && in_array($subdomain, $route_subdomain)) || (!is_array($route_subdomain) && preg_match("/{$route_subdomain}/", $subdomain));
	}

}