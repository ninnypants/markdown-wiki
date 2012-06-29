<?php
/*
* Class for resource handling
*/

class Resources {

	// vars for storing different resources
	private $registered_scripts = array();
	private $enqueued_header_scripts = array();
	private $enqueued_footer_scripts = array();
	private $registered_styles = array();
	private $enqueued_styles = array();

	function __construct(){

	}

	/*
	Register a script to be used with enqueue_script
	*/
	function register_script($name, $url){
		// check to see if the script exists
		if(array_key_exists($name, $this->registered_scripts) || in_array($url, $this->registered_scripts)){
			return false;
		}
		$this->registered_scripts[$name] = $url;
		return true;
	}

	/*
	Remove script from registered scripts and enqueued scripts
	*/
	function unregister_script($name){
		if(array_key_exists($name, $this->registered_scripts))
			unset($this->registered_scripts[$name]);

		if(array_key_exists($name, $this->enqueued_header_scripts))
			unset($this->enqueued_header_scripts[$name]);

		if(array_key_exists($name, $this->enqueued_footer_scripts))
			unset($this->enqueued_footer_scripts[$name]);
	}

	/*
	Add script to header or footer queue based on the value of $footer
	*/
	function enqueue_script($name, $url = false, $footer = false){
		if($url){
			if(array_key_exists($name, $this->enqueued_header_scripts) || array_key_exists($name, $this->enqueued_footer_scripts))
				return false;

			if(array_key_exists($name, $this->registered_scripts)){
				if($footer){
					$this->enqueued_footer_scripts[$name] = $this->registered_scripts[$name];
					return true;
				}

				$this->enqueued_header_scripts[$name] = $this->registered_scripts[$name];
			}else{
				if($footer){
					$this->enqueued_footer_scripts[$name] = $url;
					return true;
				}

				$this->enqueued_header_scripts[$name] = $url;
			}

		}else{
			if(!array_key_exists($name, $this->registered_scripts))
				return false;

			if($footer){
				$this->enqueued_footer_scripts[$name] = $this->registered_scripts[$name];
				return true;
			}

			$this->enqueued_header_scripts[$name] = $this->registered_scripts[$name];

		}

		return true;
	}

	/*
	Remove script from header and footer queues
	*/
	function unenqueue_script($name){
		if(array_key_exists($name, $this->enqueued_header_scripts))
			unset($this->enqueued_header_scripts[$name]);

		if(array_key_exists($name, $this->enqueued_footer_scripts))
			unset($this->enqueued_footer_scripts[$name]);
	}

	function register_style($name, $url){
		if(array_key_exists($name, $this->registered_styles) || in_array($name, $this->registered_styles))
			return false;

		$this->registered_styles[$name] = $url;
	}

	function unregister_style($name){
		if(isset($this->registered_styles[$name]))
			unset($this->registered_styles[$name]);

		if(isset($this->enqueued_styles[$name]))
			unset($this->enqueued_styles[$name]);
	}

	function enqueue_style($name, $url = false){

		if(array_key_exists($name, $this->registered_styles)){
			$this->enqueued_styles[$name] = $this->registered_styles[$name];
			return true;
		}

		if(!$url)
			return false;

		$this->enqueued_styles[$name] = $url;
		return true;
	}

	function unenqueue_style($name){
		if(isset($this->styles[$name]))
			unset($this->styles[$name]);
	}

	function print_styles(){

		foreach($this->enqueued_styles as $url){
			echo '<link rel="stylesheet" type="text/css" href="'.$url.'">';
		}

	}

	function print_header_scripts(){
		foreach($this->enqueued_header_scripts as $url){
			echo '<script type="text/javascript" src="'.$url.'"></script>';
		}
	}

	function print_footer_scripts(){
		foreach($this->enqueued_footer_scripts as $url){
			echo '<script type="text/javascript" src="'.$url.'"></script>';
		}
	}

}
