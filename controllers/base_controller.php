<?php

class Base_Controller extends Core_Controller {

	protected function _before() {
		$this->_layout('main');
		//$this->check_auth();
	}

	protected function authorize($action_or_condition, $ajax = false, $message = null) {
		if (is_bool($action_or_condition)) {
			if (!$action_or_condition) {
				$this->not_authorized($ajax, $message);
			}
		} else {
			if (Role::i()->cannot($action_or_condition)) {
				$this->not_authorized($ajax, $message);
			}
		}
	}

	protected function not_authorized($ajax = false, $message = null) {
		if ($ajax) {
			$response = $this->ajax_error($message ?: __('access_denied_verbose'));
			header('HTTP/1.0 403 Forbidden');
			$this->_respond_json($response, true);
		} else {
			throw new Exception_Forbidden($message ?: __('access_denied_verbose'));
		}
	}

	/**
	 * Returns ajax data as json response
	 * @param $data array
	 * @return array
	 */
	protected function ajax_success($data = null) {
		return [
			// this is the status code expected by the AJAX sender
			// 1 means the request succeeded
			// it does not necessarily mean the intended action succeeded
			// the latter needs to be determined from the response data
			'status' => 1,
			// this is the response data expected by the AJAX sender
			'data'   => $data,
		];
	}

	/**
	 * Returns ajax error message as json response
	 * @param $message string
	 * @param $error string
	 * @return array
	 */
	protected function ajax_error($message, $error = null) {
		return [
			// this is the status code expected by the AJAX sender
			// 0 means the request has failed
			'status'  => 0,
			// this is the public message shown to the user
			'message' => $message,
			// this is the private debug message that will be logged to console
			'error'   => $error,
		];
	}

	/**
	 * Checks if we have a login session
	 * Allows certain routes to run without session
	 * Redirects to login screen if check fails
	 */
	private function check_auth() {
		// list of $controller => ($actions) that are strictly public
		// they will not be available to logged in users
		$public_strict = [
			'account' => ['signin'],
		];
		// list of $controller => ($actions) that do not require a login session
		// to target all actions use $controller => true
		// they will be available to all users regardless of login
		$public = array_merge_recursive([
			'error' => true,
			'test' => true,
		], $public_strict);
		// list of $controller => ($actions) for which the referer url is ignored
		// login process keeps track of where the user came from
		// upon successful login user is redirected back to the attempted location
		// sometimes this feature is not desired
		$ignore_continue_to = [
			'account' => ['signout'],
		];
		if (!$this->in_list($public) && !Account::authenticated()) {
			if (!$this->in_list($ignore_continue_to)) {
				Flash::i('login/continue')->set($_SERVER['REQUEST_URI']);
			}
			$this->_redirect('/account/signin');
		} else if ($this->in_list($public_strict) && Account::authenticated()) {
			$this->_redirect('/');
		}
	}

	/**
	 * Checks if current controller/action is inside a list of handled routes
	 * @param $list array
	 * @return boolean
	 */
	private function in_list($list) {
		return $list[$this->_name] === true || (is_array($list[$this->_name]) && in_array($this->_action, $list[$this->_name]));
	}

}