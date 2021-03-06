<?php

class Error_Controller extends Base_Controller {

	public function action_index() {
		$this->_view->exception = $this->_params['_exception'];
		if ($this->_client_accepts_json()) {
			$this->_respond_json($this->ajax_error($this->_view->exception->getMessage()), true);
		}
		switch ($this->_view->exception->getCode()) {
			case Exception_404::SYS_CODE:
				$this->_view->is_autoloader_error = true;
			case Exception_404::CODE:
				header("HTTP/1.0 404 Not Found");
				$this->_render('404');
				break;
			case Exception_Forbidden::CODE:
				header('HTTP/1.0 403 Forbidden');
				$this->_render('forbidden');
				break;
		}
	}

	public function action_500() {

	}

}