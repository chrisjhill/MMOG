<?php
// Autoload any classes we have not previously included
function autoloader($class) {
	include $_SERVER['DOCUMENT_ROOT'] . '/libs/' . str_replace('_', '/', $class) . '.class.php';
}

// And register our autoloader
spl_autoload_register('autoloader');