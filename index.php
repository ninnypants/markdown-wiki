<?php
// get config files
require_once 'config.php';
require_once ABSPATH.'/users.php';

// get classes
require_once INC.'/user.class.php';
$user = new User();

// log in if form is being submitted
if(isset($_POST['login'])){
	$user = new User($_POST['username'], $_POST['password']);
}

require_once INC.'/resources.class.php';
$resources = new Resources();

require_once INC.'/theme.class.php';
$theme = new Theme();

require_once INC.'/file-system.class.php';
require_once INC.'/markdown.class.php';
require_once INC.'/markdown-wiki.class.php';
require_once INC.'/functions.php';

register_default_resources();

if(file_exists(THEME.'/functions.php'))
	include THEME.'/functions.php';

$wiki = new MarkdownWiki();
$wiki->handle_request();