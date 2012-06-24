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
require_once $app_root . '/markdown-wiki.php';
// var_dump($_SERVER['REQUEST_URI']);
if (!empty($_SERVER['REQUEST_URI'])) {
    # Dealing with a web request
    $wiki = new MarkdownWiki($config);
    $wiki->handle_request();
    //print_r($wiki);
}
?>