<?php

class Theme {

	private $markdown = '';
	private $file = null;

	public function __construct(){

	}

	/*
	Find page template file to be included
	*/
	public function get_template(){

	}

	/*
	Load and process content
	*/
	private function load_data(){

	}

	/*
	Display page content
	*/
	public function the_content(){

	}

	public function the_raw_data(){
		echo $this->markdown;
	}

	/*
	Create list of all wiki pages
	*/
	private function get_page_list(){

	}

	/*
	Display page list
	*/
	public function list_pages(){

	}

	/*
	Determine the current page
	*/
	public function get_page(){

	}

	/*
	Load header template
	*/
	public function get_header(){

	}

	/*
	Load footer template
	*/
	public function get_footer(){

	}

	/*
	Output the login form
	*/
	public function login_form(){

	}

	/*
	Output edit form
	*/
	public function edit_form(){
		?>
		<form action="<?php echo $this->get_base_url('/save/') ?>" method="post">
			<fieldset>
				<legend>Editing</legend>
				<label for="text">Content:</label><br>
				<textarea cols="78" rows="20" name="text" id="text"><?php echo $this->file->data; ?></textarea>
				<br>

				<input type="submit" name="preview" value="Preview">
				<input type="submit" name="save" value="Save">
				<input type="hidden" name="updated" value="<?php echo $this->file->time; ?>">
			</fieldset>
		</form>
		<?php
	}

}