<?php

class Db {

	private static $_instance;
	private $db;

	private function __construct() {
		try {
			$this->db = new PDO('mysql:host=' . Core_Config::i()->db->host . ';dbname=' . Core_Config::i()->db->name, Core_Config::i()->db->user, Core_Config::i()->db->pass, [
			    PDO::ATTR_PERSISTENT => true,
			    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
	   			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			]);
		} catch (PDOException $e) {
			throw $e;
		}
	}

	public static function i() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function o() {
		return self::i()->get();
	}

	public function get() {
		return $this->db;
	}

}