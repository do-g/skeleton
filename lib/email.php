<?php

class Email {

	protected $from;
	protected $to;
	protected $subject;
	protected $body;
	protected $template;
	protected $vars;
	private $tpl;
	private static $exception;

	public function __construct($template = null) {
		$this->from = new stdClass();
		$this->from->name = Core_Config::i()->email->from->name;
		$this->from->email = Core_Config::i()->email->from->email;
		$this->to = [];
		$this->template = $template;
		$this->vars = [];
	}

	public function from($value = null) {
		if ($value) {
			if (is_array($value)) {
				$this->from->name = $value['name'] ?: $value[0];
				$this->from->email = $value['email'] ?: $value[1];
			} else if (is_object($value)) {
				$this->from->name = $value->name;
				$this->from->email = $value->email;
			} else {
				$this->from->email = $value;
			}
		}
		return $this->from;
	}

	public function to($value = null) {
		if (Core_Config::i()->email->to) {
			$this->to = [Core_Config::i()->email->to];
		} else if ($value) {
			if (is_array($value)) {
				$this->to = array_merge($this->to, $value);
			} else {
				array_push($this->to, $value);
			}
		}
		return $this->to;
	}

	public function subject($value = null) {
		if ($value) {
			$this->subject = $value;
		}
		return $this->subject;
	}

	public function body($value = null) {
		if ($value) {
			$this->body = $value;
		}
		return $this->body;
	}

	public function vars($vars = null) {
		if ($vars) {
			$this->vars = $vars;
		}
		return $this->vars;
	}

	protected function get_template() {
		if ($this->tpl) {
			$tpl_path = PATH_EMAILS . '/' . $this->template . '.tpl';
			if (!is_file($tpl_path)) {
				return self::handle_exception(new Core_Exception(__('Mesajul email a eșuat. Nu am găsit fișierul template "${tpl}"', $this->template)));
			} else if (!is_readable($tpl_path)) {
				return self::handle_exception(new Core_Exception(__('Mesajul email a eșuat. Fișierul template "${tpl}" nu poate fi citit', $this->template)));
			}
			$this->tpl = file_get_contents($tpl_path);
		}
		return $this->tpl;
	}

	protected function prepare_body() {
		if ($this->body()) {
			$body = $this->body();
		} else if ($this->template) {
			$template = $this->get_template();
			if ($template === false) {
				return false;
			}
			$body = $template->body;
		}
		$body = Util::swap_placeholders($body, $this->vars);
		return $body;
	}

	protected function prepare_subject() {
		if ($this->subject()) {
			$subject = $this->subject();
		} else if ($this->template) {
			$template = $this->get_template();
			if ($template === false) {
				return false;
			}
			$subject = $template->subject;
		}
		if (!$subject) {
			return self::handle_exception(new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun subiect')));
		}
		$subject = Util::swap_placeholders($subject, $this->vars);
		return $subject;
	}

	public function send() {
		$body = $this->prepare_body();
		if ($body === false) {
			return false;
		}
		$body = "<body>{$body}</body>";
		$subject = $this->prepare_subject();
		if ($subject === false) {
			return false;
		}
		$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
		if (!$this->to()) {
			return self::handle_exception(new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun recipient')));
		}
		$to = implode(', ', $this->to());
		$from_name = '=?UTF-8?B?'.base64_encode($this->from()->name).'?=';
		$headers  = "From: {$from_name} <{$this->from()->email}>\r\n";
		$headers .= "Reply-To: {$this->from()->email}\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$result = mail($to, $subject, $body, $headers);
		if (!$result) {
			return self::handle_exception(new Core_Exception(__('Mesajul email nu poate fi trimis')));
		}
		return true;
	}

	public static function exception() {
		return self::$exception;
	}

	public static function error() {
		return self::exception() ? self::exception()->getMessage() : null;
	}

	protected static function handle_exception($ex) {
		self::$exception = $ex;
		return false;
	}

}