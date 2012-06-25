<?php
/*
* Class for resource handling
*/

class Resources {

	// vars for storing different resources
	private $header_scripts = array();
	private $footer_scripts = array();
	private $styles = array();

	function __construct(){
		// enqueue default scripts
		$this->enqueue_script('jquery', '');
		$this->enqueue_script('sort', '');
		$this->enqueue_script('panic', '/resources/js/panic.js');
		// enqueue default styles
		$this->enqueue_style('normalize', '/resources/css/normalize.css');
		$this->enqueue_style('panic', '/resources/css/panic.css');
	}

	function enqueue_script($name, $url, $footer = false){
		// check to see if the script exists
		if(array_key_exists($name, $this->header_scripts) || array_key_exists($name, $this->footer_scripts) || in_array($url, $this->header_scripts) || in_array($url, $this->footer_scripts)){
			return false;
		}
		if(!$footer){
			$this->header_scripts[$name] = $url;
		}else{
			$this->footer_scripts[$name] = $url;
		}
		return true;
	}

	function remove_script($name){
		if(isset($this->header_scripts[$name])){
			unset($this->header_scripts[$name]);
		}elseif(isset($this->footer_scripts[$name])){
			unset($this->footer_scripts[$name]);
		}
	}

	function enqueue_style($name, $url){
		if(array_key_exists($name, $this->styles) || in_array($url, $this->styles)){
			return false;
		}
		$this->styles[$name] = $url;
		return true;
	}

	function remove_style($name){
		if(isset($this->styles[$name])){
			unset($this->styles[$name]);
		}
	}

	function print_styles(){

	}

	function print_header_scripts(){

	}

	function print_footer_scripts(){

	}

}
