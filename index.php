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
    'url' => 'http://ninnypants.com',
    'base_path' => '/app/wiki',

);


# And off we go...
require_once $app_root.'/markdown.php';
require_once $app_root . '/markdown-wiki.php';
$wiki = new MarkdownWiki($config);
$wiki->handle_request();
