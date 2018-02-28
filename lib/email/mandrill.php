<?php

class Email_Mandrill extends Email {

	const STATUS_SENT = 'sent';
	private $service;

	public function __construct($template = null) {
		if (!Core_Config::i()->email->mandrill_api_key) {
			throw new Core_Exception(__('Serviciul Mandrill nu poate fi instanțiat. Cheia de autentificare lipsește'));
		}
		parent::__construct($template);
		$this->service = new Mandrill(Core_Config::i()->email->mandrill_api_key);
	}

	public function send($params = []) {
		$data = $this->prepare_data();
		if (__e($data)) {
			return $data;
		}
		try {
		    $message = array_merge([
		        'html' => $data->body,
		        'subject' => $data->subject,
		        'from_email' => $data->from->email,
		        'from_name' => $data->from->name,
		        'to' => array_map(function($i) {
					return ['email' => $i];
				}, $data->to),
		        'headers' => [
		        	'Reply-To' => $data->from->email,
		        ],
		        'important' => false,
		        'track_opens' => false,
		        'track_clicks' => false,
		        'auto_text' => false,
		        'auto_html' => false,
		        'inline_css' => false,
		        'url_strip_qs' => false,
		        'preserve_recipients' => false,
		        'tags' => Core_Config::i()->email->mandrill_tags ?: [],
		    ], $params);
		    $result = $this->service->messages->send($message, false);
		    foreach ($result as $recipient) {
		    	if ($recipient['status'] != self::STATUS_SENT) {
		    		return new Core_Exception(__('Mesajul email nu poate fi trimis'));
		    	}
		    }
		    return true;
		} catch (Mandrill_Error $e) {
			return $e;
		}
	}

}