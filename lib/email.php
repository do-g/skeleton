<?php

class Email {

	protected $from;
	protected $to;
	protected $subject;
	protected $body;
	protected $template;
	protected $vars;
	protected $headers = [];
	private $tpl;

	public function __construct($template = null) {
		$this->from = new stdClass();
		$this->from->name = Core_Config::i()->email->from->name;
		$this->from->email = Core_Config::i()->email->from->email;
		$this->to = [];
		$this->template = $template;
		$this->vars = [];
	}

	public function from($sender = null, $name = null) {
		if ($sender) {
			if (is_array($sender)) {
				$this->from->email = $sender['email'] ?: $sender[0];
				$this->from->name = $sender['name'] ?: $sender[1];
			} else if (is_object($sender)) {
				$this->from->email = $sender->email;
				$this->from->name = $sender->name;
			} else {
				$this->from->email = $sender;
				$this->from->name = $name;
			}
		}
		return $this->from;
	}

	protected function prepare_from() {
		$from = $this->from();
		if (!$from->email) {
			return new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun expeditor'));
		}
		return $from;
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

	protected function prepare_to() {
		$to = $this->to();
		if (!$to) {
			return new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun recipient'));
		}
		return $to;
	}

	public function subject($value = null) {
		if ($value) {
			$this->subject = $value;
		}
		return $this->subject;
	}

	protected function prepare_subject() {
		if ($this->subject()) {
			$subject = $this->subject();
		} else if ($this->template) {
			$template = $this->prepare_template();
			if (__e($template)) {
				return $template;
			}
			$subject = $template->subject;
		}
		if (!$subject) {
			return new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun subiect'));
		}
		$subject = Util::swap_placeholders($subject, $this->vars);
		return $subject;
	}

	public function body($value = null) {
		if ($value) {
			$this->body = $value;
		}
		return $this->body;
	}

	protected function prepare_body() {
		if ($this->body()) {
			$body = $this->body();
		} else if ($this->template) {
			$template = $this->prepare_template();
			if (__e($template)) {
				return $template;
			}
			$body = $template->body;
		}
		$body = Util::swap_placeholders($body, $this->vars);
		return $body;
	}

	public function vars($vars = null) {
		if ($vars) {
			$this->vars = $vars;
		}
		return $this->vars;
	}

	protected function headers($data = []) {
		if ($data) {
			$this->headers = $data;
		}
		return $this->headers;
	}

	protected function prepare_template() {
		if ($this->tpl) {
			$tpl_path = PATH_EMAILS . '/' . $this->template . '.tpl';
			if (!is_file($tpl_path)) {
				return new Core_Exception(__('Mesajul email a eșuat. Nu am găsit fișierul template "${tpl}"', $this->template));
			} else if (!is_readable($tpl_path)) {
				return new Core_Exception(__('Mesajul email a eșuat. Fișierul template "${tpl}" nu poate fi citit', $this->template));
			}
			$this->tpl = file_get_contents($tpl_path);
		}
		return $this->tpl;
	}

	protected function prepare_data() {
		$data = new stdClass();
		$data->from = $this->prepare_from();
		if (__e($data->from)) {
			return $data->from;
		}
		$data->to = $this->prepare_to();
		if (__e($data->to)) {
			return $data->to;
		}
		$data->subject = $this->prepare_subject();
		if (__e($data->subject)) {
			return $data->subject;
		}
		$data->body = $this->prepare_body();
		if (__e($data->body)) {
			return $data->body;
		}
		return $data;
	}

	public function send() {
		$data = $this->prepare_data();
		if (__e($data)) {
			return $data;
		}
		$data->body = "<body>{$data->body}</body>";
		$data->subject = '=?UTF-8?B?' . base64_encode($data->subject) . '?=';
		$data->to = implode(', ', $data->to);
		$data->from->name = $data->from->name ? '=?UTF-8?B?' . base64_encode($data->from->name) . '?= ' : '';
		$headers = array_merge([
			"From: {$data->from->name}<{$data->from->email}>\r\n",
			"Reply-To: {$data->from->email}\r\n",
			"MIME-Version: 1.0\r\n",
			"Content-Type: text/html; charset=UTF-8\r\n",
		], $this->headers());
		$headers  = implode("\r\n", $headers);
		$result = mail($data->to, $data->subject, $data->body, $headers);
		if (!$result) {
			return new Core_Exception(__('Mesajul email nu poate fi trimis'));
		}
		return true;
	}

}