<?php

class Account {

	public static function o($account = null) {
		if ($account) {
			$account->password = null;
			Session::i()->account = $account;
		}
		return Session::i()->account;
	}

	private static function create_session($account, $owner = null) {
		if ($owner) {
			$account->session_owner = $owner;
		}
        session_regenerate_id();
        self::o($account);
	}

	private static function destroy_session() {
		Session::destroy();
		session_regenerate_id();
	}

	public static function login($email, $password) {
		$account = Db_Account::i()->get('*', 'where email = ?', $email, true);
		if (__e($account)) {
			return $account;
		} else if (!$account || !$account->active || !password_verify($password, $account->password)) {
			return false;
		}
        self::create_session($account);
        return true;
	}

	public static function masquerade($account_id) {
		$account = Db_Account::i()->get_by_id($account_id);
		if (__e($account)) {
			return $account;
		} else if (!$account) {
			return false;
		}
		$owner = self::o();
		self::destroy_session();
        self::create_session($account, $owner);
        return true;
	}

	public static function is_masqueraded() {
		return self::o()->session_owner != null;
	}

	public static function logout() {
		$owner = self::o()->session_owner;
		self::destroy_session();
		if ($owner) {
			self::create_session($owner);
		}
	}

	public static function reload() {
		if (!self::o()) {
			return new Core_Exception(__('no_auth_session_found'));
		}
		$account = Db_Account::i()->get_by_id(self::o()->id);
		if (__e($account)) {
			return $account;
		} else if (!$account) {
			return false;
		}
		self::create_session($account, self::o()->session_owner);
		return true;
	}

	public static function authenticated() {
		return Session::i()->account->id ? true : false;
	}

}