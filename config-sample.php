<?php

// base url for the wiki
define('URL', 'http://wiki-url.com');

// directory constants
define('ABSPATH', dirname(__FILE__));
define('INC', ABSPATH.'/include');
define('DOC', ABSPATH.'/pages');


// theme
define('THEME', ABSPATH.'/theme/default');

/*
Visibility
private = must be logged in to view content
public = content is visible to everyone, must be logged in to edit
*/
define('VISIBILITY', 'public');