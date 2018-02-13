<?php

class Role {

	const ADMIN = 1;
	const USER = 2;
	private $role;

	private function __construct($role) {
		$this->role = (int) $role;
	}

	public static function i($role = null) {
		$role = $role ?: Account::o()->id_role;
		return new self($role);
	}

	public function can($action) {
		return in_array($action, self::permissions($this->role));
	}

	public function cannot($do_action) {
		return !$this->can($do_action);
	}

	public function is($role) {
		return $role == $this->role;
	}

	public function is_user() {
		return $this->is(self::USER);
	}

	public function is_admin() {
		return $this->is(self::ADMIN);
	}

	public function is_not($role) {
		return $role != $this->role;
	}

	public function is_not_user() {
		return $this->is_not(self::USER);
	}

	public function is_not_admin() {
		return $this->is_not(self::ADMIN);
	}

	private static function permissions($role) {
		$permissions = [
			self::ADMIN => [
				'view_users',
				'create_users',
				'update_users',
				'delete_users',
			],
			self::USER => [
				'view_users',
			],
		];
		return $permissions[$role];
	}

}