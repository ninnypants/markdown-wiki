<?php
/*
Handle reads and writes as well as gather data on a file
*/

class File {

	public $time = 0;
	public $data = '';
	private $file = '';

	/*
	Set all vars
	*/
	public function __construct($file){
		$this->file = $file;
		if(file_exists($this->file)){
			$this->data = file_get_contents($this->file);
			$this->time = filectime($this->file);
		}
	}

	/*
	Save data and create directory/file if it doesn't exist
	*/
	public function save($data = false){
		if($data !== false)
			$this->data = $data;

		if(!file_exists($file))
			mkdir(dirname($file), 775, true);

		file_put_contents($this->file, $this->data);
		$this->time = filectime($this->file);

	}
}