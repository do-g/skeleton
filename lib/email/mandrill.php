<?php

class Email_Mandrill extends Email {

	private $service;

	public function __construct($template = null) {
		parent::__construct($template);
		$this->service = new Mandrill(Core_Config::i()->email->mandrill_api_key);
	}

	public function send() {
		$body = $this->prepare_body();
		if ($body === false) {
			return false;
		}
		$subject = $this->prepare_subject();
		if ($subject === false) {
			return false;
		}
		if (!$this->to()) {
			return self::handle_exception(new Core_Exception(__('Mesajul email a eșuat. Nu a fost specificat niciun recipient')));
		}
		try {
		    $message = array(
		        'html' => $body,
		        'subject' => $subject,
		        'from_email' => $this->from()->email,
		        'from_name' => $this->from()->name,
		        'to' => array_map(function($i) {
					return ['email' => $i];
				}, $this->to()),
		        'headers' => [
		        	'Reply-To' => $this->from()->email,
		        ],
		        'important' => false,
		        'track_opens' => false,
		        'track_clicks' => false,
		        'auto_text' => false,
		        'auto_html' => false,
		        'inline_css' => false,
		        'url_strip_qs' => false,
		        'preserve_recipients' => false,
		        'tags' => Core_Config::i()->email->mandrill_tags,
		    );
		    $result = $this->service->messages->send($message, false);
		    foreach ($result as $recipient) {
		    	if ($recipient['status'] != 'sent') {
		    		return self::handle_exception(new Core_Exception(__('Mesajul email nu poate fi trimis')));
		    	}
		    }
		    return true;
		} catch(Mandrill_Error $e) {
			return self::handle_exception($e);
		}
	}

}