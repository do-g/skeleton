<?php

class Core_Controller {

	const DEFAULT_CONTROLLER = 'index';
	const DEFAULT_ACTION = 'index';
	const CONTROLLER_ERROR = 'error';
	const CLASS_SUFIX = '_Controller';
	const ACTION_METHOD_PREFIX = 'action_';

	protected $_name;
	protected $_action;
	protected $_params;
	protected $_raw_params;
	protected $_view;
	protected $_static_actions = [];

	protected function __construct($data, $view) {
		$this->_name = Util::to_controller_action_name($data['controller'] ?: self::DEFAULT_CONTROLLER);
		$this->_action = Util::to_controller_action_name($data['action'] ?: self::DEFAULT_ACTION);
		$this->_params = $data['params'];
		$this->_raw_params = $data['raw_params'];
		$this->_view = $view;
		$this->_view->page_css_class("req-{$this->_name}-{$this->_action}");
		$this->_render($this->_action);
		$this->_before();
		if ($this->_is_ajax()) {
			$this->_layout(false);
		}
		if (!$this->_is_static()) {
			$method_name = Util::to_action_method($this->_action);
			if (!method_exists($this, $method_name)) {
				throw new Core_Exception("Method \"{$method_name}\" not found in controller \"" . get_class($this) . "\"", -404);
			}
			$action_result = call_user_func([$this, $method_name]);
		}
		$this->_after();
		if ($this->_client_accepts_json()) {
			$this->_respond_json($action_result);
		}
	}

	public static function factory($data, $view) {
		$controller_class_name = self::to_controller_class($data['controller']);
		return new $controller_class_name($data, $view);
	}

	protected function _before() {}

	protected function _after() {}

	final protected function _layout($name) {
		$this->_view->layout($name);
	}

	final protected function _render($name, $directory = null) {
		$this->_view->template($name, $directory ? $directory : $this->_name);
	}

	final protected function _respond($response, $immediate = false) {
		if ($immediate) {
			echo $response;
			exit;
		}
		$this->_view->content($response);
	}

	protected function _respond_json($response = null, $immediate = false) {
		$this->_content_type_json();
		if ($response) {
			$json = json_encode($response);
			$this->_respond($json, $immediate);
		}
	}

	protected function _content_type_json($charset = 'utf-8') {
		Util::content_type('application/json', $charset);
	}

	final protected function _forward($request) {
		if (!is_array($request)) {
			$request = [
				'action' => $request,
			];
		}
		if (!$request['controller']) {
			$request['controller'] = $this->_name;
		}
		if (!$request['action']) {
			$request['action'] = self::DEFAULT_ACTION;
		}
		Core_Application::i()->do_request($request);
	}

	private static function to_controller_class($name) {
		$name = Util::to_class_name($name);
		return $name . self::CLASS_SUFIX;
	}

	protected function _redirect($url) {
		Util::redirect($url);
	}

	protected function _param($name, $type = 'string', $callable = null) {
		$value = $this->_params[$name];
		if ($value === null) {
			return $value;
		}
		$value = trim(urldecode($value));
		switch ($type) {
			case 'int':
				return (int) Util::sanitize_int($value);
			case 'bool':
				return (bool) $value;
			case 'callback':
				return Util::sanitize_callback($value, $callable);
			default:
				return Util::sanitize_string($value);
		}
	}

	protected function _is_ajax() {
		return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || $this->_param('ajax');
	}

	protected function _client_accepts($type) {
		$client_accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
		$client_accepts = array_map('trim', $client_accepts);
		switch ($type) {
			case 'json':
				return in_array('application/json', $client_accepts) || $this->_param('json');
		}
	}

	protected function _client_accepts_json() {
		return $this->_client_accepts('json');
	}

	protected function _force_ajax() {
		if (!$this->_is_ajax()) {
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		}
		if (!$this->_client_accepts('json')) {
			$_SERVER['HTTP_ACCEPT'] .= ', application/json';
		}
	}

	protected function _is_static() {
		return in_array($this->_action, $this->_static_actions);
	}

	protected function _static($actions = null) {
		if ($actions) {
			$actions = is_array($actions) ? $actions : [$actions];
			$this->_static_actions = array_merge($this->_static_actions, $actions);
		}
		return $this->_static_actions;
	}

}