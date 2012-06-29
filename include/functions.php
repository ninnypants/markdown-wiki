<?php
##
## Theme functions
##

function the_content(){
	global $theme;
	$theme->the_content();
}

function the_title(){
	global $theme;
	$theme->the_title();
}

function edit_link(){
	echo '<a href="'.get_edit_url().'" class="edit">Edit</a>';
}

function list_pages(){
	global $theme;
	$theme->list_pages();
}

##
## Resource handling
##

function register_script($name, $url){
	global $resources;
	return $resources->register_script($name, $url);
}

function unregister_script($name){
	global $resources;
	$resources->unregister_script($name);
}

function enqueue_script($name, $url = false, $footer = false){
	global $resources;
	return $resources->enqueue_script($name, $url, $footer);
}

function unenqueue_script($name){
	global $resources;
	$resources->unenqueue_script($name);
}

function register_style($name, $url){
	global $resources;
	return $resources->register_style($name, $url);
}

function unregister_style($name){
	global $resources;
	$resources->unregister_style($name);
}

function enqueue_style($name, $url = false){
	global $resources;
	return $resources->enqueue_style($name, $url);
}

function unenqueue_style($name){
	global $resources;
	$resources->unenqueue_style($name);
}

function print_header_scripts(){
	global $resources;
	$resources->print_header_scripts();
}

function print_footer_scripts(){
	global $resources;
	$resources->print_footer_scripts();
}

function print_styles(){
	global $resources;
	$resources->print_styles();
}

##
## Helpers
##

function format_page_name($page, $include_index = false){
	if(strpos($page, DOC) === false){
		$page = DOC.'/'.trim($page, '/');
	}

	if($include_index){
		$page .= '/index.md';
	}
	return $page;
}

function page_exists($page){
	$page = format_page_name($page);

	// if folder exists and markdown file doesn't page hasn't been created yet
	if(file_exists($page) && !file_exists($page.'/index.md')){
		return false;
	}elseif(file_exists($page) && file_exists($page.'/index.md')){
		return true;
	}
	return false;
}

function get_base_url($path = ''){
	if(strpos($path, DOC) !== false){
		$path = str_replace(DOC, '', $path);
	}
	return URL.$path;
}

function get_action_url($path = '', $action = ''){
	$url = get_base_url($path);
	$url = rtrim($url, '/');
	return $url.'/'.$action.'/';
}

function get_edit_url($path = ''){
	return get_action_url($path, 'edit');
}

function get_save_url($path = ''){
	return get_action_url($path, 'save');
}

function get_login_url($path){
	return get_action_url($path, 'login');
}

function register_default_resources(){
	// rainbow syntaxes
	register_script('rainbow', get_base_url('/theme/js/rainbow/rainbow.min.js'));
	register_script('rainbow-c', get_base_url('/theme/js/rainbow/language/c.js'));
	register_script('rainbow-csharp', get_base_url('/theme/js/rainbow/language/csharp.js'));
	register_script('rainbow-css', get_base_url('/theme/js/rainbow/language/css.js'));
	register_script('rainbow-generic', get_base_url('/theme/js/rainbow/language/generic.js'));
	register_script('rainbow-html', get_base_url('/theme/js/rainbow/language/html.js'));
	register_script('rainbow-javascript', get_base_url('/theme/js/rainbow/language/javascript.js'));
	register_script('rainbow-lua', get_base_url('/theme/js/rainbow/language/lua.js'));
	register_script('rainbow-php', get_base_url('/theme/js/rainbow/language/php.js'));
	register_script('rainbow-python', get_base_url('/theme/js/rainbow/language/python.js'));
	register_script('rainbow-ruby', get_base_url('/theme/js/rainbow/language/ruby.js'));
	register_script('rainbow-scheme', get_base_url('/theme/js/rainbow/language/scheme.js'));
	register_script('rainbow-shell', get_base_url('/theme/js/rainbow/language/shell.js'));
	register_script('rainbow-smalltalk', get_base_url('/theme/js/rainbow/language/smalltalk.js'));

	// rainbow themes
	register_style('rainbow-all-hallows-eve', get_base_url('/theme/js/rainbow/themes/all-hallows-eve.css'));
	register_style('rainbow-blackboard', get_base_url('/theme/js/rainbow/themes/blackboard.css'));
	register_style('rainbow-espresso-libre', get_base_url('/theme/js/rainbow/themes/espresso-libre.css'));
	register_style('rainbow-github', get_base_url('/theme/js/rainbow/themes/github.css'));
	register_style('rainbow-obsidian', get_base_url('/theme/js/rainbow/themes/obsidian.css'));
	register_style('rainbow-solarized-dark', get_base_url('/theme/js/rainbow/themes/solarized-dark.css'));
	register_style('rainbow-solarized-light', get_base_url('/theme/js/rainbow/themes/solarized-light.css'));
	register_style('rainbow-sunburst', get_base_url('/theme/js/rainbow/themes/sunburst.css'));
	register_style('rainbow-tomorrow-night', get_base_url('/theme/js/rainbow/themes/tomorrow-night.css'));
	register_style('rainbow-tricolore', get_base_url('/theme/js/rainbow/themes/tricolore.css'));
	register_style('rainbow-twilight', get_base_url('/theme/js/rainbow/themes/twilight.css'));
	register_style('rainbow-zenburnesque', get_base_url('/theme/js/rainbow/themes/zenburnesque.css'));
}