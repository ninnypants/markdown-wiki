<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
# The directory containing the php5-markdown wiki code
$app_root = dirname(__FILE__);
// var_dump($app_root);
$config = array(
	# Directory to store the markdown pages
	'doc_dir'      => $app_root . '/pages',

	# Default page name
	'default_page' => 'index',
    'url' => 'http://wiki.35thsouth.com',
    'base_path' => '',

);


# And off we go...
require_once $app_root.'/include/markdown.class.php';
require_once $app_root . '/include/markdown-wiki.class.php';
$wiki = new MarkdownWiki($config);
$wiki->handle_request();
