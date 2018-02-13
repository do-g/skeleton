<?php

class Label {

	private static $_instance;
    private $labels;

	private function __construct() {
        $this->load_labels();
	}

	public static function i() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function get($label, $placeholders = []) {
    	$index = array_search($label, $this->labels->orig);
    	$trans = $index !== false ? $this->labels->trans[$index] : $label;
        $trans = str_replace('\n', "\n", $trans);
		return Util::swap_placeholders($trans, $placeholders);
    }

    private function load_labels() {
        $this->labels = new stdClass();
        $this->labels->orig = [];
        $this->labels->trans = [];
        if (!file_exists(PATH_LABELS)) {
            return false;
        }
        if (($handle = fopen(PATH_LABELS, 'r')) === FALSE) {
        	return false;
        }
        while (($data = fgetcsv($handle, null, ',')) !== FALSE) {
            array_push($this->labels->orig, $data[0]);
            array_push($this->labels->trans, $data[1]);
        }
        fclose($handle);
    }
}