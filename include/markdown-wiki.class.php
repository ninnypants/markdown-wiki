<?php

class MarkdownWiki {
	// Wiki default configuration. All overridableindex
	protected $config = array(
		'doc_dir'      => '/tmp/',
		'default_page' => 'index',
		'new_page_text' => 'Start editing your new page',
		'markdown_ext' => 'md',
	);

	// An instance of the Markdown parser
	protected $parser;
	protected $base_url;

	// allowed actions
	protected $actions = array('edit', 'preview', 'save');

	public function __construct($config = false) {
		$this->init_wiki();
		if ($config) {
			$this->config = array_merge($this->config, $config);
		}
	}

	protected function init_wiki() {

	}

	public function wiki_link($link) {
		global $docIndex;

		$is_new = false;
		$wiki_url = $link;

		if (preg_match('/^\/?([a-z0-9-]+(\/[a-z0-9-]+)*)$/i', $link, $matches)) {
			$wiki_url = "{$this->base_url}{$matches[1]}";
			$is_new = !$this->is_markdown_file($link);
		} elseif ($link=='/') {
			$wiki_url = "{$this->base_url}{$this->config['default_page']}";
			$is_new = !$this->is_markdown_file($this->config['default_page']);
		}

		return array($is_new, $wiki_url);
	}

	public function handle_request($request = false, $server = false) {
		$action = $this->parse_request($request, $server);
		$action->model = $this->get_page_data($action);

		// If this is a new file, switch to edit mode
		if ($action->model->updated == 0 && $action->action == 'display') {
			$action->action = 'edit';
		}

		$action->response = $this->do_action($action);
		$output = $this->render_response($action->response);

		//echo '<pre>'; print_r($action); echo '</pre>';
	}

	##
	## Methods handling each action
	##

	public function do_action($action) {
		switch($action->action) {
			case 'edit':
				$response = $this->do_edit($action);
			break;

			case 'preview':
				$response = $this->do_preview($action);
			break;

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
		$response = array(
			'title'    => "Displaying: {$action->page}",
			'content'  => $this->render_document($action),
			'edit_form' => '',
			'options'  => array(
				'Edit' => $this->get_base_url(str_replace($this->config['doc_dir'], '', $action->page).'/edit/'),
			),
			'related'  => ''
		);

		return $response;
	}

	protected function do_edit($action) {
		$response = array(
			'title'    => "Editing: {$action->page}",
			'content'  => '',
			'edit_form' => $this->render_edit_form($action),
			'options'  => array(
				'Cancel' => $this->get_base_url(str_replace($this->config['doc_dir'], '', $action->page)),
			),
			'related'  => ''
		);

		return $response;
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
		// TODO: Implement some sort of versioning
		if (empty($action->model)) {
			// This is a new file
			echo "INFO: Saving a new file\n";
		} elseif ($action->model->updated == $action->post->updated) {
			// Check there isn't an editing conflict
			$action->model->content = $action->post->text;
			$this->set_page_data($action->model);
		} else {
			echo "WARN: Editing conflict!\n";
		}

		header('Location: '.$this->get_base_url(str_replace($this->config['doc_dir'], '', $action->page)));
	}

	##
	## Methods dealing with the model (plain old file system)
	##

	protected function get_page_data($action) {
		$data = (object) NULL;

		$data->file    = $this->format_page_name($action->page, true);
		$data->content = $this->get_content($data->file);
		$data->updated = $this->get_last_updated($data->file);

		return $data;
	}

	protected function set_page_data($model) {
		$directory = dirname($model->file);
		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		} elseif (!is_dir($directory)) {
			echo "ERROR: Cannot create {$model->file}\n";
		}

		file_put_contents($model->file, $model->content);
	}

	##
	## Methods for parsing the incoming request
	##

	public function parse_request($request = false, $server = false) {
		$action = (object) NULL;

		if (!$request) { $this->request = $_REQUEST; }
		if (!$server)  { $this->server  = $_SERVER;  }

		//echo "Request: "; print_r($this->request);
		//echo "Server : "; print_r($this->server);

		$action->method = $this->server['REQUEST_METHOD'];
		$action->page   = $this->get_page();
		$action->action = $this->get_action();
		$action->base   = $this->get_base_url();

		if ($action->method == 'POST') {
			$action->post = $this->get_post_details($this->request, $this->server);
		}

		// Take a copy of the action base for the wiki_link function
		$this->base_url = $action->base;

		return $action;
	}

	protected function get_content($filename) {
		if (file_exists($filename)){
			return file_get_contents($filename);
		}
		return $this->config['new_page_text'];
	}

	protected function get_last_updated($filename) {
		if (file_exists($filename)) {
			return filectime($filename);
		}
		return 0;
	}

	protected function get_page() {
		$page = '';

		$page = preg_replace('#^'.$this->config['base_path'].'#', '', $this->server['REQUEST_URI']);

		$page = trim($page, '/');

		// Determine the page name
		if(empty($page)){
			$page = $this->format_page_name('');
		} else {

			$page = rtrim(preg_replace('#'.implode('|', $this->actions).'/?$#', '', $page), '/');

			$page = $this->format_page_name($page);

		}
		if(!$this->page_exists($page) && !in_array($this->get_action(), $this->actions)){
			header('Location: '.$this->get_base_url(str_replace($this->config['doc_dir'], '', $page).'/edit/'));
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
			return $this->config['url'].$this->config['base_path'].$path;
	}

	protected function get_post_details($request, $server) {
		$post = (object) NULL;
		$post->text    = stripslashes($this->request['text']);
		$post->updated = $this->request['updated'];
		return $post;
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

	protected function render_document($action) {
		return Markdown(
			$action->model->content,
			array($this, 'wiki_link')
		);
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
		if(strpos($page, $this->config['doc_dir']) === false){
			$page = $this->config['doc_dir'].'/'.trim($page, '/');
		}

		if($include_index){
			$page .= '/index.md';
		}
		return $page;
	}

}