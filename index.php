<?php
error_reporting(E_ALL);
# The directory containing the php5-markdown wiki code
$app_root = dirname(__FILE__);

$config = array(
	# Directory to store the markdown pages
	'doc_dir'      => $app_root . 'pages/',

	# Default page name
	'default_page' => 'index'

);


# And off we go...
require_once $app_root . 'markdown-wiki.php';

?>
