<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// get config files
require_once 'config.php';
require_once ABSPATH.'/users.php';

// get classes
require_once INC.'/user.class.php';
require_once INC.'/resources.class.php';
require_once INC.'/theme.class.php';
require_once INC.'/file-system.class.php';
require_once INC.'/markdown.class.php';
require_once INC.'/markdown-wiki.class.php';

$wiki = new MarkdownWiki();
$wiki->handle_request();