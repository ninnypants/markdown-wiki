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
		require THEME.'/index.php';
	}

	/*
	Load page in to be handled
	*/
	public function load_page($action){
		$this->file = new File(format_page_name($action->page, true));
		$this->markdown = Markdown($this->file->data);
		$this->action = $action->action;
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
		switch($this->action){
			case 'login':
				$this->login_form();
			break;

			case 'edit':
				$this->edit_form();
			break;

			default:
				echo $this->markdown;
			break;
		}
	}

	/*
	Output title of the current page
	*/
	public function the_title(){

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
		?>
		<form action="" method="post">
			<fieldset>
				<legend>Log In</legend>
				<p><label for="username">Username: <input type="text" name="username" id="username"></label></p>
				<p><label for="password">Password: <input type="password" name="password" id="password"></label></p>
				<input type="hidden" name="login" value="1">
				<input type="submit" value="Log In">
			</fieldset>
		</form>
		<?php
	}

	/*
	Output edit form
	*/
	public function edit_form(){
		?>
		<form action="<?php echo get_base_url('/save/') ?>" method="post">
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