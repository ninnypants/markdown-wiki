<?php

class MarkdownWiki {
	// Wiki default configuration. All overridableindex
	protected $config = array(
		'doc_dir'      => '/tmp/',
		'default_page' => 'index',
		'new_page_text' => 'Start editing your new page',
		'markdown_ext' => array('markdown', 'md'),
	);

	// An instance of the Markdown parser
	protected $parser;
	protected $base_url;

	public function __construct($config=false) {
		$this->init_wiki();
		if ($config) {
			$this->set_config($config);
		}
	}

	protected function init_wiki() {
		$baseDir = dirname(__FILE__) . '/';

		// Including the markdown parser
		//echo "BaseDir: {$baseDir}\n";
		require_once $baseDir . 'markdown.php';
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

	public function is_markdown_file($link) {
		$filename = "{$this->config['doc_dir']}{$link}.{$this->config['markdown_ext']}";
		return file_exists($filename);
	}

	public function set_config($config) {
		$this->config = array_merge($this->config, $config);
	}

	public function handle_request($request=false, $server=false) {
		$action           = $this->parse_request($request, $server);
		$action->model    = $this->get_model_data($action);

		// If this is a new file, switch to edit mode
		if ($action->model->updated==0 && $action->action=='display') {
			$action->action = 'edit';
		}

		$action->response = $this->do_action($action);
		$output           = $this->render_response($action->response);

		//echo '<pre>'; print_r($action); echo '</pre>';
	}

	##
	## Methods handling each action
	##

	public function do_action($action) {

		switch($action->action) {
			case 'UNKNOWN': # Default to display
			case 'display':
				$response = $this->do_display($action);
				break;
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
				$response = array(
					'messages' => array(
						"Action {$action->action} not implemented."
					)
				);
				print_r($action);
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
				'Edit' => "{$action->base}{$action->page}?action=edit&amp;id={$action->page}"
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
				'Cancel' => "{$action->base}{$action->page}"
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
		} elseif ($action->model->updated==$action->post->updated) {
			// Check there isn't an editing conflict
			$action->model->content = $action->post->text;
			$this->set_model_data($action->model);
		} else {
			echo "WARN: Editing conflict!\n";
		}

		return $this->do_display($action);
	}

	##
	## Methods dealing with the model (plain old file system)
	##

	protected function get_model_data($action) {
		$data = (object) NULL;

		$data->file    = $this->get_filename($action->page);
		$data->content = $this->get_content($data->file);
		$data->updated = $this->get_last_updated($data->file);

		return $data;
	}

	protected function set_model_data($model) {
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

	public function parse_request($request=false, $server=false) {
		$action = (object) NULL;

		if (!$request) { $request = $_REQUEST; }
		if (!$server)  { $server  = $_SERVER;  }

		//echo "Request: "; print_r($request);
		//echo "Server : "; print_r($server);

		$action->method = $this->get_method($request, $server);
		$action->page   = $this->get_page($request, $server);
		$action->action = $this->get_action($request, $server);
		$action->base   = $this->get_base_url($request, $server);

		if ($action->method=='POST') {
			$action->post = $this->get_post_details($request, $server);
		}

		// Take a copy of the action base for the wiki_link function
		$this->base_url = $action->base;

		return $action;
	}

	protected function get_filename($page) {
		return "{$this->config['doc_dir']}{$page}.{$this->config['markdown_ext']}";
	}

	protected function get_content($filename) {
		if (file_exists($filename)) {
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

	protected function get_method($request, $server) {
		if (!empty($server['REQUEST_METHOD'])) {
			return $server['REQUEST_METHOD'];
		}
		return 'UNKNOWN';
	}

	protected function get_page($request, $server) {
		$page = '';

		// Determine the page name
		if (!empty($server['PATH_INFO'])) {
			//echo "Path info detected\n";
			// If we are using PATH_INFO then that's the page name
			$page = substr($server['PATH_INFO'], 1);

		} elseif (!empty($request['id'])) {
			$page = $request['id'];

		} else {
			// TODO: Keep checking
			//echo "WARN: Could not find a pagename\n";
		}

		// Check whether a default Page is being requested
		if ($page=='' || preg_match('/\/$/', $page)) {
			$page .= $this->config['default_page'];
		}

		return $page;
	}

	protected function get_action($request, $server) {
		if ($server['REQUEST_METHOD']=='POST') {
			if (!empty($request['preview'])) {
				return 'preview';
			} elseif (!empty($request['save'])) {
				return 'save';
			}
		} elseif (!empty($request['action'])) {
			return $request['action'];
		} elseif (!empty($server['PATH_INFO'])) {
			return 'display';
		}

		// TODO: handle version history etc.

		return 'UNKNOWN';
	}

	protected function get_base_url($request, $server) {
		if (!empty($this->config['base_url'])) {
			return $this->config['base_url'];
		}
		/**
			PATH_INFO $_SERVER keys
    [SERVER_NAME] => localhost
    [DOCUMENT_ROOT] => /home/user/sites/default/htdocs
    [SCRIPT_FILENAME] => /home/user/sites/default/htdocs/index-sample.php
    [REQUEST_METHOD] => GET
    [QUERY_STRING] =>
    [REQUEST_URI] => /index-sample.php
    [SCRIPT_NAME] => /index-sample.php
    [PHP_SELF] => /index-sample.php
		**/

		$scriptName = $server['SCRIPT_NAME'];
		$requestUrl = $server['REQUEST_URI'];
		$phpSelf    = $server['PHP_SELF'];

		if ($requestUrl==$scriptName) {
			// PATH_INFO based
		} elseif(strpos($requestUrl, $scriptName)===0) {
			// Query string based
		} else {
			// Maybe mod_rewrite based?
			// Perhaps we need a config entry here
		}

		return '/index-sample.php/'; // PATH-INFO base
	}

	protected function get_post_details($request, $server) {
		$post = (object) NULL;
		$post->text    = stripslashes($request['text']);
		$post->updated = $request['updated'];
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
<form action="{$action->base}{$action->page}" method="post">
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


}


if (!empty($_SERVER['REQUEST_URI'])) {
	# Dealing with a web request
	$wiki = new MarkdownWiki();
	$wiki->handle_request();
	//print_r($wiki);
}

?>