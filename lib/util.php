<?php

class Util {

	private static $__config;

	public static function is_dev() {
		return !self::is_env('production');
	}

	public static function is_prod() {
		return self::is_env('production');
	}

	public static function is_env($env) {
		return APP_ENV == $env;
	}

	public static function is_exception($object) {
		return $object instanceof Exception;
	}

	public static function is_me() {
		return in_array($_SERVER['REMOTE_ADDR'], Core_Config::i()->me);
	}

	public static function vardump($var) {
		if (self::is_me()) {
			var_dump($var);
		}
	}

	public static function printr($var) {
		if (self::is_me()) {
			print_r($var);
		}
	}

	public static function log($data, $file = null) {
		$file = PATH_ROOT . '/log/' . ($file ? $file : 'global') . '.txt';
		file_put_contents($file, $data . "\n\n", FILE_APPEND);
	}

	public static function to_controller_action_name($name) {
		$name = urldecode($name);
		$name = strtolower($name);
		$name = preg_replace('/[_\s]+/', '-', $name);
		return $name;
	}

	public static function to_action_method($name) {
		$name = self::to_controller_action_name($name);
		$name = str_replace('-', '_', $name);
		$name = Core_Controller::ACTION_METHOD_PREFIX . $name;
		return $name;
	}

	public static function to_class_name($name) {
		$name = self::to_controller_action_name($name);
		$name = str_replace('-', ' ', $name);
		$name = ucwords($name);
		$name = preg_replace('/[^A-Za-z0-9]/', '', $name);
		return $name;
	}

	public static function explode_to_key_value_pairs($string, $separator = null, $pair_separator = null) {
		$separator = $separator ? $separator : ($pair_separator === '&' ? '=' : '/');
		$string = trim($string, $separator);
		if (!$string) {
			return [];
		}
		$keys = $values = [];
		if ($pair_separator) {
			$pair_parts = explode($pair_separator, $string);
			foreach ($pair_parts as $m => $pair_part) {
				$parts = explode($separator, $pair_part);
				array_push($keys, $parts[0]);
				array_push($values, $parts[1]);
			}
		} else {
			$parts = explode($separator, $string);
			foreach ($parts as $n => $part) {
				if ($n % 2) {
					array_push($values, $part);
				} else {
					array_push($keys, $part);
				}
			}
			if (count($parts) % 2 != 0) {
				array_push($values, null);
			}
		}
		return array_combine($keys, $values);
	}

	public static function url($fragment = null, $full = false) {
		$url = $full ? rtrim(URL_BASE, '/') : '/';
		if (URL_BASE_FRAGMENT) {
			$url = rtrim($url, '/') . '/' . trim(URL_BASE_FRAGMENT, '/');
		}
		if ($fragment) {
			$url = rtrim($url, '/') . '/' . ltrim($fragment, '/');
		}
		return $url;
	}

	public static function get_url() {
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	public static function get_uri($ignore_base_fragment = false, $ignore_query = false) {
		$url = $_SERVER['REQUEST_URI'];
		if ($ignore_query) {
			$url = parse_url($url, PHP_URL_PATH);
		}
		$uri = '/' . trim($url, '/');
		if ($ignore_base_fragment) {
			$uri = str_replace('/' . trim(URL_BASE_FRAGMENT, '/'), '/', $uri);
		}
		return $uri;
	}

	public static function get_host_name($url = null, $exclude_subdomain = false) {
		$url = $url ?: self::get_url();
		$parts = parse_url($url);
		$host = $parts['host'];
		if ($exclude_subdomain) {
			$parts = explode('.', $host);
			if (count($parts) > 2) {
				$parts = array_slice($parts, -2);
			}
			$host = implode('.', $parts);
		}
		return $host;
	}

	public static function get_subdomain($url = null) {
		$url = $url ?: self::get_url();
		$parts = parse_url($url);
		$host = $parts['host'];
		$parts = explode('.', $host);
		$parts = array_slice($parts, 0, count($parts) - 2);
		$subdomain = implode('.', $parts);
		return $subdomain;
	}

	public static function get_query_string($url = null) {
		$url = $url ?: self::get_url();
		return parse_url($url, PHP_URL_QUERY);
	}

	public static function get_ip() {
		if ($_SERVER['HTTP_CLIENT_IP']) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ($_SERVER['HTTP_X_FORWARDED']) {
			return $_SERVER['HTTP_X_FORWARDED'];
		} else if ($_SERVER['HTTP_FORWARDED_FOR']) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		} else if ($_SERVER['HTTP_FORWARDED']) {
			return $_SERVER['HTTP_FORWARDED'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	public static function image($relative_path, $full = false) {
		return self::url('/images/' . trim($relative_path, '/'), $full);
	}

	public static function css($relative_path, $full = false) {
		return self::url('/css/' . trim($relative_path, '/'), $full);
	}

	public static function js($relative_path, $full = false) {
		return self::url('/js/' . trim($relative_path, '/'), $full);
	}

	public static function get_file_output($file_path, $local_data = null) {
		ob_start();
  		include $file_path;
  		return ob_get_clean();
	}

	public static function array_to_obj($data) {
		if (!is_scalar($data)) {
			$obj = new stdClass();
			$arr = [];
			foreach ($data as $key => $value) {
				if (is_int($key)) {
					$arr[$key] = self::array_to_obj($value);
				} else {
					$obj->$key = self::array_to_obj($value);
				}
			}
		} else {
			$obj = $data;
		}
		return $arr ?: $obj;
	}

	public static function obj_to_array($data) {
		if (!is_scalar($data)) {
			$obj = [];
			foreach ($data as $key => $value) {
				$obj[$key] = self::obj_to_array($value);
			}
		} else {
			$obj = $data;
		}
		return $obj;
	}

	public static function merge_objects($obj1, $obj2) {
		if (!is_object($obj1)) {
			$obj1 = $obj2;
		} else if (is_object($obj2)) {
			foreach ($obj2 as $key => $value) {
				$obj1->$key = self::merge_objects($obj1->$key, $value);
			}
		}
		return $obj1;
	}

	public static function deepclone($obj) {
		return unserialize(serialize($obj));
	}

	public static function redirect($url, $status = 302) {
		$url = filter_var($url, FILTER_SANITIZE_URL);
		header("Location: {$url}", true, $status);
		exit;
	}

	public static function is_post($key = null) {
		return self::is_http_request('post', $key);
	}

	public static function is_get($key = null) {
		return self::is_http_request('get', $key);
	}

	public static function is_http_request($type, $key = null) {
		$type = strtoupper($type);
		if (strtoupper($_SERVER['REQUEST_METHOD']) != $type) {
			return false;
		}
		if ($key) {
			if (is_array($key)) {
				foreach ($key as $k) {
					if (self::is_http_request($type, $k)) {
						return true;
					}
				}
				return false;
			} else {
				return $type == 'POST' ? isset($_POST[$key]) : isset($_GET[$key]);
			}
		} else {
			return true;
		}
	}

	public static function is_email($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public static function md5_24($value) {
		$alphabet = 'g2byCzwqcD8kmlxr4ua0oEsnvj6tef953BAdpi1h7';
		$raw = md5($value, true);
		$result = '';
  		$length = strlen(self::dec_to_base($alphabet, 2147483647));
  		foreach (str_split($raw, 4) as $dword) {
    		$dword = ord($dword[0]) + ord($dword[1]) * 256 + ord($dword[2]) * 65536 + ord($dword[3]) * 16777216;
    		$result .= str_pad(self::dec_to_base($alphabet, $dword), $length, $alphabet[0], STR_PAD_LEFT);
  		}
  		return $result;
	}

	private static function dec_to_base($alphabet, $dword) {
	    $rem = (int) fmod($dword, strlen($alphabet));
	    if ($dword < strlen($alphabet)) {
	      return $alphabet[$rem];
	    } else {
	      return self::dec_to_base($alphabet, ($dword - $rem) / strlen($alphabet)) . $alphabet[$rem];
	    }
	}

	public static function convert_memory($value, $from_unit, $to_unit) {
		switch (strtoupper($from_unit)) {
			case 'KB':
				$value *= 1000;
				break;
			case 'MB':
				$value *= pow(1000, 2);
				break;
			case 'GB':
				$value *= pow(1000, 3);
				break;
		}
		switch (strtoupper($to_unit)) {
			case 'KB':
				$value /= 1000;
				break;
			case 'MB':
				$value /= pow(1000, 2);
				break;
			case 'GB':
				$value /= pow(1000, 3);
				break;
		}
		return $value;
	}

	public static function to_memory_size($value, $unit = 'B', $precision = 2) {
		$value = self::convert_memory($value, $unit, 'b');
		$gb = $value / pow(1000, 3);
		if ($gb >= 1) {
			return number_format($gb, $precision) . ' GB';
		}
		$mb = $value / pow(1000, 2);
		if ($mb >= 1) {
			return number_format($mb, $precision) . ' MB';
		}
		$kb = $value / 1000;
		if ($kb >= 1) {
			return number_format($kb, $precision) . ' KB';
		}
		return $value . ' B';
	}

	public static function transliterate($value) {
		return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
	}

	public static function encrypt($value) {
		$cipher_algo = 'aes-256-ctr';
		$hash_algo = 'sha256';
		$iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
		$iv = mcrypt_create_iv($iv_num_bytes, MCRYPT_DEV_URANDOM);
		$keyhash = openssl_digest(ENCRYPT_PASS, $hash_algo, true);
        $opts =  OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($value, $cipher_algo, $keyhash, OPENSSL_RAW_DATA, $iv);
        $res = $iv . $encrypted;
		return self::base64url_encode($res);
	}

	public static function decrypt($value) {
		$cipher_algo = 'aes-256-ctr';
		$hash_algo = 'sha256';
		$iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
		$raw = self::base64url_decode($value);
		$iv = substr($raw, 0, $iv_num_bytes);
        $raw = substr($raw, $iv_num_bytes);
		$keyhash = openssl_digest(ENCRYPT_PASS, $hash_algo, true);
        return openssl_decrypt($raw, $cipher_algo, $keyhash, OPENSSL_RAW_DATA, $iv);
	}

	public static function get_ext($file_name) {
		$info = pathinfo($file_name);
		return $info['extension'];
	}

	public static function remove_ext($file_name, $target_ext = null) {
		$parts = explode('.', $file_name);
		// if file has an extension
		if (count($parts) > 1) {
			$last = end($parts);
			// if we have a valid extension
			if (strlen($last) <= 4 && (!$target_ext || $last == $target_ext)) {
				array_pop($parts);
			}
		}
		return implode('.', $parts);
	}

	public static function change_ext($file_name, $new_ext) {
		return self::remove_ext($file_name) . '.' . $new_ext;
	}

	public static function base64url_encode($value) {
    	return str_replace(['+', '/'], ['-', '_'], base64_encode($value));
	}

	public static function base64url_decode($value) {
	    return base64_decode(str_replace(['-', '_'], ['+', '/'], $value));
	}

	public static function swap_placeholders($string, $placeholders = null) {
		if ($placeholders !== null) {
			if (is_array($placeholders)) {
				foreach ($placeholders as $key => $value) {
					if (is_array($value) || is_object($value)) {
						foreach ($value as $k => $v) {
							$string = str_replace('${' . $key . '.' . $k . '}', $v, $string);
						}
					} else {
						$string = str_replace('${' . $key . '}', $value, $string);
					}
				}
			} else {
				$string = preg_replace('/\$\{([^\}]+)\}/i', $placeholders, $string);
			}
		}
		return $string;
	}

	public static function active_lang() {
		return LANG_ACTIVE;
	}

	public static function content_type($type, $charset = 'utf-8') {
		header("Content-Type: {$type}; charset={$charset}");
	}

	public static function no_cache() {
		header("Expires: on, 01 Jan 1970 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	/***** ASSETS *****/

	public static function img($path) {
		$prefix = rtrim(URL_BASE_FRAGMENT_IMAGES, '/');
		$path = ltrim($path, '/');
		return "{$prefix}/{$path}";
	}

	public static function jpg($path) {
		return self::img("{$path}.jpg");
	}

	public static function png($path) {
		return self::img("{$path}.png");
	}

	public static function svg($path) {
		return self::img("{$path}.svg");
	}

	public static function svgi($path, $class = null, $return = true) {
		$path = PATH_ROOT . '/public' . self::svg($path);
		$contents = file_get_contents($path);
		if ($class) {
			$contents = str_replace('<svg ', '<svg class="' . $class . '" ', $contents);
		}
		if ($return) {
			return $contents;
		}
		echo $contents;
	}

	/***** DATES & DAYS *****/

	public static function get_end_date($start, $days, $format = 'Y-m-d') {
		$days = (int) $days;
		$forward  = $days > 0;
		$backward = $days < 0;
		$add_days = $days + ($forward ? -1 : 1);
		$add_days = $backward ? $add_days : "+{$add_days}";
		$start = $start instanceof DateTime ? $start : new DateTime($start);
		$date = clone $start;
		$date->modify("{$add_days} days");
		return $format === null ? $date : $date->format($format);
	}

	public static function get_work_end_date($start, $days, $free_days = [6, 7], $format = 'Y-m-d') {
		$backward = $days < 0;
		$date = self::get_end_date($start, $days, null);
		$free = self::count_free_days($start, $days, $free_days);
		if ($free) {
			$date->modify(($backward ? '-' : '+') . "1 days");
			return self::get_work_end_date($date, $backward ? -$free : $free, $free_days, $format);
		}
		return $format === null ? $date : $date->format($format);
	}

	public static function count_days($start, $end) {
		$start_date = $start instanceof DateTime ? $start : new DateTime($start);
		$end_date = $end instanceof DateTime ? $end : new DateTime($end);
		$days = $start_date->diff($end_date)->format('%a');
		$backward = $days < 0;
		$days += $backward ? -1 : 1;
		return $days;
	}

	public static function count_work_days($start, $days_or_end, $free_days = [6, 7]) {
		if (!is_array($free_days)) {
			$free_days = [$free_days];
		}
		if (is_numeric($days_or_end)) {
			$end = $start instanceof DateTime ? clone $start : new DateTime($start);
			$days = abs($days_or_end + ($days_or_end > 0 ? -1 : 1));
			$end->modify(($days_or_end < 0 ? '-' : '+') . "{$days} days");
		} else {
			$end = $days_or_end;
		}
		$days = self::count_days($start, $end);
		$backward = $days < 0;
		$free = self::count_free_days($start, $days_or_end, $free_days);
		$days += $backward ? $free : -$free;
		return $days;
	}

	public static function count_free_days($start, $days_or_end, $free_days = [6, 7]) {
		if (!is_array($free_days)) {
			$free_days = [$free_days];
		}
		$free = 0;
		foreach ($free_days as $d) {
			$free += self::count_week_days($start, $days_or_end, $d);
		}
		return $free;
	}

	public static function count_week_days($start, $days_or_end, $week_day) {
		$week_day = $week_day ?: 7;
		$start_date = $start instanceof DateTime ? clone $start : new DateTime($start);
		if (is_numeric($days_or_end)) {
			$num_days = $days_or_end;
			if ($num_days < 0) {
				$nd = $num_days + 1;
				$start_date->modify("{$nd} days");
				$num_days = abs($num_days);
			}
		} else {
			$end_date = $days_or_end instanceof DateTime ? $days_or_end : new DateTime($days_or_end);
			$s = clone $start_date;
			$e = clone $end_date;
			$start_date = min($s, $e);
			$end_date = max($s, $e);
			$num_days = $start_date->diff($end_date)->format('%a');
			$num_days++;
		}
		$first_week_day = (int) $start_date->format('w');
		$first_week_day = $first_week_day ?: 7;
		if ($week_day == $first_week_day) {
			$day_margin = 6 - $week_day;
			$margin = $first_week_day + $day_margin;
		} else {
			$margin = $first_week_day - $week_day - 1;
		}
		if ($margin < 0) {
			$margin += (7 * (ceil(abs($margin / 7))));
		}
		return floor(($num_days + $margin) / 7);
	}

/***** SANITIZE & FILTER *****/

	public static function sanitize_string($value) {
		return filter_var($value, FILTER_SANITIZE_STRING);
	}

	public static function sanitize_int($value) {
		return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	public static function sanitize_callback($value, $callable) {
		return filter_var($value, FILTER_CALLBACK, ['options' => $callable]);
	}

	public static function sanitize_date($value) {
		return preg_replace('([^0-9-: ])', '', $value);
	}

	public static function sanitize_time($value) {
		return preg_replace('([^0-9:])', '', $value);
	}

/***** FLASH MESSAGES *****/

	public static function set_flash_notice($message, $namespace = null) {
		$namespace = $namespace ?: 'shared/' . uniqid();
		Flash::i("notice/{$namespace}")->set($message);
	}

	public static function get_flash_notices($namespace = 'shared') {
		return Flash::i("notice/{$namespace}")->get();
	}

	public static function set_flash_error($message, $namespace = null) {
		$namespace = $namespace ?: 'shared/' . uniqid();
		Flash::i("error/{$namespace}")->set($message);
	}

	public static function get_flash_errors($namespace = 'shared') {
		return Flash::i("error/{$namespace}")->get();
	}

/***** SECURITY *****/

	public static function create_nonce($id) {
		$nonce = uniqid(rand(), true) . hash('sha256', microtime(true));
		if (Account::authenticated()) {
			$nonce .= Account::o()->session_uid;
		}
		Flash::i("nonce/{$id}")->set($nonce);
		return $nonce;
	}

	public static function check_nonce($id, $value) {
		return Flash::i("nonce/{$id}")->get() === $value;
	}

}

function __($label, $placeholders = []) {
	return Label::i()->get($label, $placeholders);
}

function __e($object = null) {
	return $object ? Util::is_exception($object) : Core_Application::i()->is_exception();
}

function isme() {
	return Util::is_me();
}

function vardump(...$var) {
	return Util::vardump(...$var);
}

function printr(...$var) {
	return Util::printr(...$var);
}

function tolog($data, $file = null) {
	return Util::log($data, $file);
}

function img($path) {
	return Util::img($path);
}

function jpg($path) {
	return Util::jpg($path);
}

function png($path) {
	return Util::png($path);
}

function svg($path) {
	return Util::svg($path);
}

function svgi($path, $class = null) {
	return Util::svgi($path, $class);
}