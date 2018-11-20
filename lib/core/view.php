<?php

class Core_View {

	const DIR_SHARED = 'shared';
	protected $_layout;
	protected $_name;
	protected $_dir;
	private   $_content;
	private   $_body_css_class = [];
	private   $_js_vars = [];
	private   $_meta = [
		'robots' => 'index,follow',
	];
	const TEMPLATE_SUFFIX = '.phtml';

	public function __construct() {}

	public function render() {
		if (!$this->_content) {
			if ($this->_name) {
		  		$this->_content = $this->get_partial(null, '.');
		  	} else if ($this->_name !== false) {
				throw new Core_Exception('No view has been set');
			}
		}
		if ($this->_layout) {
			$layout_name = $this->_layout . self::TEMPLATE_SUFFIX;
			$layout_dir = PATH_LAYOUTS;
			$layout = $layout_dir . DIRECTORY_SEPARATOR . $layout_name;
			if (!is_file($layout)) {
				throw new Core_Exception("Layout \"{$layout_name}\" not found in path \"{$layout_dir}\"");
			}
			$output = $this->get_output($layout);
		} else if ($this->_layout === false) {
			$output = $this->_content;
		} else {
			throw new Core_Exception('No layout has been set');
		}
		Cache_Output::i()->set(Util::get_uri(), $output);
		if (!Util::get_query_string()) {
			Cache_Static::i()->set(Util::get_uri(), $output);
		}
		echo $output;
		exit;
	}

	public function get_partial($name = null, $directory = null, $data = null) {
		if ($data === null && $directory && !is_string($directory)) {
			$data = $directory;
			$directory = null;
		}
		$name = $name ?: $this->_name;
		$template_name = $name . self::TEMPLATE_SUFFIX;
		$template_dir = PATH_VIEWS . DIRECTORY_SEPARATOR . ($directory ? ($directory == '.' ? $this->_dir : $directory) : self::DIR_SHARED);
		$template = $template_dir . DIRECTORY_SEPARATOR . $template_name;
		if (!is_file($template)) {
			throw new Core_Exception("View \"{$template_name}\" not found in path \"{$template_dir}\"");
		}
		return $this->get_output($template, $data);
	}

	private function partial($name = null, $directory = null, $data = null) {
  		echo $this->get_partial($name, $directory, $data);
	}

	private function get_output($file_path, $local = null) {
		ob_start();
  		include $file_path;
  		return ob_get_clean();
	}

	public function layout($name = null) {
		if (func_num_args()) {
			$this->_layout = $name;
		}
		return $this->_layout;
	}

	public function template($name = null, $directory = null) {
		if (func_num_args()) {
			$this->_dir = $directory ? $directory : Core_Controller::DEFAULT_CONTROLLER;
			$this->_name = $name;
		}
		return $this->_name;
	}

	public function content($content = null) {
		if (func_num_args()) {
			$this->_content = $content;
		}
		return $this->_content;
	}

	public function meta($prop, $value = null) {
		if ($value) {
			$this->_meta[$prop] = $value;
		}
		return $this->_meta[$prop];
	}

	public function meta_title($title = null, $suffix = null) {
		$seo_title = $this->meta('title', $title);
		if ($suffix) {
			if ($seo_title) {
				$seo_title .= ' - ';
			}
			$seo_title .= $suffix;
		}
		return $seo_title;
	}

	public function meta_description($description = null) {
		return $this->meta('description', $description);
	}

	public function page_css_class($css_class = null) {
		if ($css_class) {
			array_push($this->_body_css_class, $css_class);
		}
		return implode(' ', $this->_body_css_class);
	}

	public function add_js_var($name, $value) {
		$this->_js_vars[$name] = json_encode($value);
	}

}