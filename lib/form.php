<?php

class Form {

	const ERROR_TYPE_GENERAL = 'general';
	const ERROR_TYPE_REQUIRED = 'required';
	const ERROR_TYPE_EMAIL = 'email';
	const ERROR_TYPE_REGEX = 'regex';
	const ERROR_TYPE_IDENTICAL = 'identical';
	const ERROR_TYPE_CALLBACK = 'callback';
	const REGEX_VALID_NAME = "/^[\\p{L}\\s-']+$/iu";
	const REGEX_VALID_INSTITUTION_NAME = "/^[\\p{L}\\s-\.']+$/iu";
	const REGEX_VALID_ZIP = "/^[\\p{L}\\p{N}-]+$/i";
	const REGEX_VALID_PHONE = "/^[0-9-\+\.\(\)\\s]+$/";
	const REGEX_VALID_ADDRESS = "/^[\\p{L}\\p{N}\\s-'\.,]+$/iu";
	const FIELD_MAXLENGTH_NAME = 30;
	const FIELD_MAXLENGTH_FULL_NAME = 50;
	const FIELD_MAXLENGTH_INSTITUTION_NAME = 50;
	const FIELD_MAXLENGTH_ADDRESS = 100;
	const FIELD_MAXLENGTH_TOWN = 50;
	const FIELD_MAXLENGTH_ZIP = 10;
	const FIELD_MAXLENGTH_EMAIL = 50;
	const FIELD_MAXLENGTH_PHONE = 20;
	const FIELD_MAXLENGTH_SUBJECT = 100;
	const FIELD_NAME_NONCE = '_nid';
	const FIELD_NAME_TIMER = '_tid';
	const FIELD_NAME_JAR = '_jid';
	const FIELD_NAME_CAPTCHA = 'g-recaptcha-response';
	const OPTION_SHOW_AS_FORM_ERROR = 'show_as_form_error';
	const HONEYPOT_TIMER_SECONDS = 1;

	private $_request_raw = [];
	private $_request_fields = [];
	private $_form_errors = [];
	private $_default_whitelisted = [
		self::FIELD_NAME_NONCE,
		self::FIELD_NAME_TIMER,
		self::FIELD_NAME_JAR,
		self::FIELD_NAME_CAPTCHA,
	];

	public function __construct($request) {
		$this->_request_raw = $request;
	}

	public function whitelist_fields($allowed) {
		if ($allowed === true) {
			return;
		}
		$allowed = is_array($allowed) ? $allowed : [$allowed];
		$allowed = array_merge($allowed, $this->_default_whitelisted);
		$this->_request_raw = array_intersect_key($this->_request_raw, array_flip($allowed));
	}

	public function sanitize_fields($filters = []) {
		foreach ($this->_request_raw as $key => $value) {
			$default_filter = in_array($key, $this->_default_whitelisted) ? null : $filters['_default'];
			$this->set_field($key, $this->get_safe_field_value($key, $filters[$key] ?: $default_filter));
		}
	}

	private function get_safe_field_value($key, $filters) {
		$value = isset($_POST[$key]) ? $_POST[$key] : null;
		if ($value) {
			if (is_array($value)) {
				foreach ($value as &$v) {
					$v = $this->filter_value($v, $filters);
				}
			} else {
				$value = $this->filter_value($value, $filters);
			}
		}
		return $value;
	}

	private function filter_value($value, $filters) {
		$value = trim($value);
		if ($filters) {
			foreach ($filters as $filter => $options) {
				$value = filter_var($value, $filter, $options);
			}
		}
		return $value;
	}

	public function has_field($key) {
		return isset($this->_request_fields[$key]);
	}

	public function get_field($key) {
		return $this->_request_fields[$key];
	}

	public function set_field($key, $sanitized_value) {
		return $this->_request_fields[$key] = $sanitized_value;
	}

	public function get_field_or_default($key, $default, $use_default_if_empty = false) {
		$value = $this->get_field($key);
		$condition = $use_default_if_empty ? $value : isset($value);
		return $condition ? $value : $default;
	}

	public function get_fields() {
		return $this->_request_fields;
	}

	public function reset_fields() {
		$this->_request_fields = [];
	}

	public function field_is_empty($field_name) {
		$field = $this->get_field($field_name);
		return $field === null || (is_string($field) && $field === '')
			|| (is_array($field) && !count($field));
	}

	public function validate_nonce($id, $error_message = null) {
		$nonce = $this->get_field(self::FIELD_NAME_NONCE);
		if (!$nonce || !Util::check_nonce($id, $nonce)) {
			$error_message = $error_messages ? $error_messages : __('Eroare la validarea formularului');
			$this->set_form_error($error_message);
			return false;
		}
		return true;
	}

	public function validate_honeypot($error_message = null) {
		if ($this->get_field(self::FIELD_NAME_JAR)) {
			$error_message = $error_messages ? $error_messages : __('Execuție suspectă sau neautorizată [1]');
			$this->set_form_error($error_message);
			return false;
		}
		$timer = $this->get_field(self::FIELD_NAME_TIMER);
		if (!$timer || microtime(true) - $timer < self::HONEYPOT_TIMER_SECONDS) {
			$error_message = $error_messages ? $error_messages : __('Execuție suspectă sau neautorizată [2]');
			$this->set_form_error($error_message);
			return false;
		}
	}

	public function validate_required_fields($fields, $error_messages = null, $options = []) {
		$fields = is_array($fields) ? $fields : [$fields];
		$error_messages = is_array($error_messages) ? $error_messages : [$error_messages];
		foreach ($fields as $n => $field_name) {
			if (is_array($field_name)) {
				$present = false;
				foreach ($field_name as $fld_nm) {
					if (!$this->field_is_empty($fld_nm)) {
						$present = true;
						break;
					}
				}
				if (!$present) {
					$error_message = $error_messages[$n] ? $error_messages[$n] : __('At least one field of [${list}] is required', implode(', ', $field_name));
					if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
						$this->set_form_error($error_message);
					} else {
						foreach ($field_name as $fld_nm) {
							$this->set_field_error($fld_nm, $error_message, self::ERROR_TYPE_REQUIRED);
						}
					}
				}
			} else {
				if ($this->field_is_empty($field_name)) {
					$error_message = $error_messages[$n] ? $error_messages[$n] : __('This field is required');
					if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
						$this->set_form_error($error_message);
					} else {
						$this->set_field_error($field_name, $error_message, self::ERROR_TYPE_REQUIRED);
					}
				}
			}
		}
	}

	public function validate_email_fields($fields, $error_messages = null, $options = []) {
		$fields = is_array($fields) ? $fields : [$fields];
		$error_messages = is_array($error_messages) ? $error_messages : [$error_messages];
		foreach ($fields as $n => $field_name) {
			$field_value = $this->get_field($field_name);
			if (!$this->field_is_empty($field_name) && $this->field_is_valid($field_name) && !Util::is_email($field_value)) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : __('Invalid email address');
				if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
					$this->set_form_error($error_message);
				} else {
					$this->set_field_error($field_name, $error_message, self::ERROR_TYPE_EMAIL);
				}
			}
		}
	}

	public function validate_regex_fields($fields, $regex_patterns, $error_messages = null, $options = []) {
		$fields = is_array($fields) ? $fields : [$fields];
		$regex_patterns = is_array($regex_patterns) ? $regex_patterns : [$regex_patterns];
		$error_messages = is_array($error_messages) ? $error_messages : [$error_messages];
		foreach ($fields as $n => $field_name) {
			$field_value = $this->get_field($field_name);
			if (!$this->field_is_empty($field_name) && $this->field_is_valid($field_name)) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : __('Value does not match constraints');
				$regex_pattern = $regex_patterns[$n];
				if (!preg_match($regex_pattern, $field_value)) {
					if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
						$this->set_form_error($error_message);
					} else {
						$this->set_field_error($field_name, $error_message, self::ERROR_TYPE_REGEX);
					}
				}
			}
		}
	}

	public function validate_identical_fields($fields, $match_fields, $error_messages = null, $options = []) {
		$fields = is_array($fields) ? $fields : [$fields];
		$match_fields = is_array($match_fields) ? $match_fields : [$match_fields];
		$error_messages = is_array($error_messages) ? $error_messages : [$error_messages];
		foreach ($fields as $n => $field_name) {
			$field_value = $this->get_field($field_name);
			$match_field_name = $match_fields[$n];
			$match_field_value = $this->get_field($match_field_name);
			if (!$this->field_is_empty($field_name) && $this->field_is_valid($field_name) && $field_value !== $match_field_value) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : __('Field does not match "${field}"', $field_name);
				if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
					$this->set_form_error($error_message);
				} else {
					$this->set_field_error($match_field_name, $error_message, self::ERROR_TYPE_IDENTICAL);
				}
			}
		}
	}

	public function validate_callback_fields($fields, $callbacks, $error_messages = null, $options = []) {
		$fields = is_array($fields) ? $fields : [$fields];
		$callbacks = !is_callable($callbacks) ? $callbacks : [$callbacks];
		$error_messages = is_array($error_messages) ? $error_messages : [$error_messages];
		foreach ($fields as $n => $field_name) {
			$field_value = $this->get_field($field_name);
			$callback = $callbacks[$n];
			if (!$this->field_is_empty($field_name) && $this->field_is_valid($field_name) && !call_user_func($callback, $field_value, $this->get_fields())) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : __('Field does not pass validation');
				if ($options[$n][self::OPTION_SHOW_AS_FORM_ERROR]) {
					$this->set_form_error($error_message);
				} else {
					$this->set_field_error($field_name, $error_message, self::ERROR_TYPE_CALLBACK);
				}
			}
		}
	}

	public function validate_callback($callbacks) {
		$callbacks = !is_callable($callbacks) ? $callbacks : [$callbacks];
		foreach ($callbacks as $callback) {
			$result = call_user_func($callback, $this);
			if (is_array($result)) {
				foreach ($result as $field_name => $error_data) {
					$error_message = is_array($error_data) ? $error_data[0] : $error_data;
					$options = is_array($error_data) ? $error_data[1] : [];
					if (!$this->field_is_empty($field_name) && $this->field_is_valid($field_name)) {
						if ($options[self::OPTION_SHOW_AS_FORM_ERROR]) {
							$this->set_form_error($error_message);
						} else {
							$this->set_field_error($field_name, $error_message, self::ERROR_TYPE_CALLBACK);
						}
					}
				}
			}
		}
	}

	public function validate_captcha($secret_key, $error_message = null) {
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = [
			'secret'   => $secret_key,
			'response' => $this->get_field(self::FIELD_NAME_CAPTCHA),
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		];
		$options = [
		    'http' => [
		    	'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => http_build_query($data),
		    ],
		];
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result !== false) {
			$result = json_decode($result);
			if (json_last_error() == JSON_ERROR_NONE) {
				if ($result->success) {
					return true;
				}
			}
		}
		$error_message = $error_message ? $error_message : __('Unable to verify that you are human');
		$this->set_form_error($error_message);
	}

	public function field_is_valid($field_name) {
		return !$this->get_field_errors($field_name);
	}

	public function set_field_error($field_name, $message, $type = self::ERROR_TYPE_GENERAL) {
		$this->_form_errors[$field_name][$type] = $message;
	}

	public function get_field_errors($field_name, $type = null) {
		return $type ? $this->_form_errors[$field_name][$type] : $this->_form_errors[$field_name];
	}

	public function is_valid() {
		return !$this->get_all_errors();
	}

	public function set_form_error($message, $type = self::ERROR_TYPE_GENERAL) {
		$this->set_field_error('form', $message, $type);
	}

	public function get_form_errors($type = null) {
		return $this->get_field_errors('form', $type);
	}

	public function get_all_errors() {
		return $this->_form_errors;
	}

	public function is_submit($key) {
		return Util::is_post($key) || Util::is_get($key);
	}

	public function is_post($key) {
		return Util::is_post($key);
	}

	public function is_get($key) {
		return Util::is_get($key);
	}

	public static function get_regex_min_length($length) {
		return '/^.{' . $length . ',}$/';
	}

	public static function get_regex_max_length($length) {
		return '/^.{0,' . $length . '}$/';
	}

	public static function get_regex_exact_length($length) {
		return '/^.{' . $length . '}$/';
	}

	public static function get_regex_min_max_length($min_length, $max_length) {
		return '/^.{' . $min_length . ',' . $max_length . '}$/';
	}

}