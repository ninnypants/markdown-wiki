<?php

class MarkdownWiki {

	// An instance of the Markdown parser
	protected $parser;
	protected $base_url;
	private $file;

	// allowed actions
	protected $actions = array('edit', 'preview', 'save', 'login', 'logout');

	public function __construct() {

	}

	public function handle_request($request = false, $server = false) {
		global $theme;
		$action = $this->parse_request($request, $server);

		$action->response = $this->do_action($action);

		$theme->get_template();
		//echo '<pre>'; print_r($action); echo '</pre>';
	}

	##
	## Methods handling each action
	##

	public function do_action($action) {
		switch($action->action) {

			// case 'preview':
			// 	$response = $this->do_preview($action);
			// break;

			case 'save':
				$response = $this->do_save($action);
			break;

			case 'history':
			case 'admin':
			case 'browse':
			default:
				$response = $this->do_display($action);
			break;
		}

		return $response;
	}

	protected function do_display($action) {
		global $user;
		// var_dump($user);
		if(!$user->is_logged_in() && VISIBILITY == 'private' && $action->action != 'login'){
			header('Location: '.get_login_url($action->page));
			exit;
		}elseif($user->is_logged_in() && $action->action == 'login'){
			header('Location: '.get_base_url($action->page));
			exit;
		}
		global $theme;
		$theme->load_page($action);
	}

	protected function do_edit($action) {
		global $user;
		if(!$user->is_logged_in()){
			if(VISIBILITY == 'private'){
				header('Location: '.get_login_url($action->page));
			}else{
				header('Location: '.get_base_url($action->page));
			}
			exit;
		}
	}

	protected function do_preview($action) {
		$response = array(
			'title'    => "Editing: {$action->page}",
			'content'  => $this->render_preview_document($action),
			'edit_form' => $this->render_edit_form($action),
			'options'  => array(
				'Cancel' => "{$action->base}{$action->page}"
			),
			'related'  => ''
		);

		return $response;
	}

	protected function do_save($action) {
		global $user;

		if(!$user->is_logged_in()){
			if(VISIBILITY == 'private'){
				header('Location: '.get_login_url($action->page));
			}else{
				header('Location: '.get_base_url($action->page));
			}
			exit;
		}

		$this->file = new File($this->format_page_name($action->page, true));
		if($_POST['updated'] == $this->file->time){
			$this->file->save($_POST['text']);
		}else{
			header('Location: '.$this->get_base_url(str_replace(DOC, '', rtrim($action->page, '/')).'/edit/'));
			exit;
		}

		header('Location: '.$this->get_base_url(str_replace(DOC, '', $action->page)));
		exit;
	}


	##
	## Methods for parsing the incoming request
	##

	public function parse_request() {
		$action = (object) NULL;

		$this->request = $_REQUEST;
		$this->server  = $_SERVER;

		//echo "Request: "; print_r($this->request);
		//echo "Server : "; print_r($this->server);

		$action->method = $this->server['REQUEST_METHOD'];
		$action->page   = $this->get_page();
		$action->action = $this->get_action();
		$action->base   = $this->get_base_url();

		// Take a copy of the action base for the wiki_link function
		$this->base_url = $action->base;

		return $action;
	}

	protected function get_page() {
		$page = '';

		$page = preg_replace('#^'.parse_url(URL, PHP_URL_PATH).'#', '', $this->server['REQUEST_URI']);

		$page = trim($page, '/');

		// Determine the page name
		if(empty($page)){
			$page = $this->format_page_name('');
		} else {

			$page = rtrim(preg_replace('#'.implode('|', $this->actions).'/?$#', '', $page), '/');

			$page = $this->format_page_name($page);

		}
		if(!$this->page_exists($page) && !in_array($this->get_action(), $this->actions)){
			header('Location: '.$this->get_base_url(str_replace(DOC, '', $page).'/edit/'));
		}

		return $page;
	}

	protected function get_action() {

		preg_match('#([^/]+)/?$#', $this->server['REQUEST_URI'], $matches);
		if(isset($matches[1])){
			$action = in_array($matches[1], $this->actions) ? $matches[1] : 'display';
		}else{
			$action = 'display';
		}

		return $action;
	}

	protected function get_base_url($path = '') {
		return URL.$path;
	}

	/*********

		RESPONSE RENDERERS

	*********/

	public function render_response($response) {
		if (!empty($this->config['layout'])) {
			// TODO: Use a custom template
		} else {
			$footer = array();

			if (!empty($response['options'])) {
				$footer[] = '<ul>';
				foreach($response['options'] as $label=>$link) {
					$footer[] = <<<HTML
<li><a href="{$link}">{$label}</a></li>
HTML;
				}
				$footer[] = '</ul>';
			}
			$response['footer'] = implode("\n", $footer);

			echo <<<PAGE
<html lang="en-US">
<head>
	<title>{$response['title']}</title>
	<link href="/theme/js/rainbow/github.css" rel="stylesheet">
</head>
<body>
	<div id="page">
		<div id="head"></div>
		<div id="content">
{$response['content']}
{$response['edit_form']}
		</div>
		<div id="related">
{$response['related']}
		</div>
		<div id="foot">
{$response['footer']}
		</div>
	</div>
	<script type="text/javascript" src="/theme/js/rainbow/rainbow.min.js"></script>
	<script type="text/javascript" src="/theme/js/rainbow/language/php.js"></script>
	<script type="text/javascript" src="/theme/js/rainbow/language/css.js"></script>
	<script type="text/javascript" src="/theme/js/rainbow/language/javascript.js"></script>
	<script type="text/javascript" src="/theme/js/rainbow/language/html.js"></script>
</body>
</html>
PAGE;

		}
	}

	protected function render_document($action){
		if(!$this->file){
			$this->file = new File($this->format_page_name($action->page, true));
		}
		return Markdown($this->file->data);
	}

	protected function render_preview_document($action) {
		return Markdown(
			$action->post->text,
			array($this, 'wiki_link')
		);
	}

	protected function render_edit_form($action) {
		if (!empty($action->post)) {
			$form = array(
				'raw'     => $action->post->text,
				'updated' => $action->post->updated
			);
		} else {
			$form = array(
				'raw'     => $action->model->content,
				'updated' => $action->model->updated
			);
		}

		return <<<HTML
<form action="{$this->get_base_url('/save/')}" method="post">
	<fieldset>
		<legend>Editing</legend>
		<label for="text">Content:</label><br>
		<textarea cols="78" rows="20" name="text" id="text">{$form['raw']}</textarea>
		<br>

		<input type="submit" name="preview" value="Preview">
		<input type="submit" name="save" value="Save">
		<input type="hidden" name="updated" value="{$form['updated']}">
	</fieldset>
</form>
HTML;

	}

	private function page_exists($page){
		$page = $this->format_page_name($page);

		// if folder exists and markdown file doesn't page hasn't been created yet
		if(file_exists($page) && !file_exists($page.'/index.md')){
			return false;
		}elseif(file_exists($page) && file_exists($page.'/index.md')){
			return true;
		}
		return false;
	}

	private function format_page_name($page, $include_index = false){
		if(empty($page)){
			$page = DOC;
		}elseif(strpos($page, DOC) === false){
			$page = DOC.'/'.trim($page, '/');
		}

		if($include_index){
			$page .= '/index.md';
		}

		return $page;
	}

}