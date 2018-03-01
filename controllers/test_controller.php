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

	public function action_test() {
		$param = $this->_param('token');
		echo 'now you are on subdomain "test" with token "' . $param . '"';
	}

	public function action_util_get_end_date() {
		require_once PATH_TEST . '/util.php';
		test_get_end_date();
	}

	public function action_util_get_end_date_reverse() {
		require_once PATH_TEST . '/util.php';
		test_get_end_date_reverse();
	}

	public function action_util_get_work_end_date_weekend() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_weekend();
	}

	public function action_util_get_work_end_date_weekend_reverse() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_weekend_reverse();
	}

	public function action_util_get_work_end_date_saturday() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_saturday();
	}

	public function action_util_get_work_end_date_saturday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_saturday_reverse();
	}

	public function action_util_get_work_end_date_sunday() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_sunday();
	}

	public function action_util_get_work_end_date_sunday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_get_work_end_date_sunday_reverse();
	}

	public function action_util_count_days() {
		require_once PATH_TEST . '/util.php';
		test_count_days();
	}

	public function action_util_count_days_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_days_reverse();
	}

	public function action_util_count_work_days_date_weekend() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_weekend();
	}

	public function action_util_count_work_days_date_weekend_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_weekend_reverse();
	}

	public function action_util_count_work_days_days_weekend() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_weekend();
	}

	public function action_util_count_work_days_days_weekend_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_weekend_reverse();
	}

	public function action_util_count_work_days_date_saturday() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_saturday();
	}

	public function action_util_count_work_days_date_saturday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_saturday_reverse();
	}

	public function action_util_count_work_days_days_saturday() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_saturday();
	}

	public function action_util_count_work_days_days_saturday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_saturday_reverse();
	}

	public function action_util_count_work_days_date_sunday() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_sunday();
	}

	public function action_util_count_work_days_date_sunday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_date_sunday_reverse();
	}

	public function action_util_count_work_days_days_sunday() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_sunday();
	}

	public function action_util_count_work_days_days_sunday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_work_days_days_sunday_reverse();
	}

	public function action_util_count_free_days_date_weekend() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_weekend();
	}

	public function action_util_count_free_days_date_weekend_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_weekend_reverse();
	}

	public function action_util_count_free_days_days_weekend() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_weekend();
	}

	public function action_util_count_free_days_days_weekend_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_weekend_reverse();
	}

	public function action_util_count_free_days_date_saturday() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_saturday();
	}

	public function action_util_count_free_days_date_saturday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_saturday_reverse();
	}

	public function action_util_count_free_days_days_saturday() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_saturday();
	}

	public function action_util_count_free_days_days_saturday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_saturday_reverse();
	}

	public function action_util_count_free_days_date_sunday() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_sunday();
	}

	public function action_util_count_free_days_date_sunday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_date_sunday_reverse();
	}

	public function action_util_count_free_days_days_sunday() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_sunday();
	}

	public function action_util_count_free_days_days_sunday_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_free_days_days_sunday_reverse();
	}

	public function action_util_count_week_days_date() {
		require_once PATH_TEST . '/util.php';
		test_count_week_days_date();
	}

	public function action_util_count_week_days_date_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_week_days_date_reverse();
	}

	public function action_util_count_week_days_days() {
		require_once PATH_TEST . '/util.php';
		test_count_week_days_days();
	}

	public function action_util_count_week_days_days_reverse() {
		require_once PATH_TEST . '/util.php';
		test_count_week_days_days_reverse();
	}

}