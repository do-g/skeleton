<?php

class Account {

	private static $exception;

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
        return true;
	}

	private static function destroy_session() {
		Session::destroy();
		session_regenerate_id();
	}

	public static function login($email, $password) {
		$account = Db_Account::i()->get('*', 'where email = ?', $email, true);
		if ($account === false) {
			return self::handle_exception();
		} else if (!$account || !$account->active || !password_verify($password, $account->password)) {
			return null;
		}
        return self::create_session($account);
	}

	public static function masquerade($account_id) {
		$account = Db_Account::i()->get_by_id($account_id);
		if (!$account) {
			return $account;
		}
		$owner = self::o();
		self::destroy_session();
        return self::create_session($account, $owner);
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
			return self::handle_exception(new Core_Exception(__('no_auth_session_found')));
		}
		$account = Db_Account::i()->get_by_id(self::o()->id);
		if (!$account) {
			return $account;
		}
		return self::create_session($account, self::o()->session_owner);
	}

	public static function authenticated() {
		return Session::i()->account->id ? true : false;
	}

	public static function exception() {
		return self::$exception;
	}

	public static function error() {
		return self::exception() ? self::exception()->getMessage() : null;
	}

	private static function handle_exception($ex = null) {
		$ex = $ex ?: Db_Account::exception();
		self::$exception = $ex;
		return false;
	}

	private static function handle_result($result, $ex = null) {
		if ($result === false) {
			return self::handle_exception($ex);
		}
		return $result;
	}

}