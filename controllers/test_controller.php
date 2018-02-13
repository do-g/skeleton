<?php

class Test_Controller extends Core_Controller {

	protected function _before() {
		$this->_layout(false);
		if ($this->_action != 'index') {
			$this->_render(false);
		}
	}

	public function action_index() {

	}

	public function action_sample() {
		require_once PATH_TEST . '/sample.php';
		test_sample();
	}

}