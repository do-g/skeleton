<?php

class Db_Expr {

	private $data;

	public function __construct($expr) {
		$this->data = $expr;
	}

	public function get() {
		return $this->data;
	}

	public function __toString() {
		return $this->get();
	}
}