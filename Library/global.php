<?php
// We're in development, show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// We want to work with UTF8
header('Content-type: text/html; charset=UTF-8');

// Include autoloader and config
include $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config.php';
include $_SERVER['DOCUMENT_ROOT'] . DS . '..' . DS . 'Library' . DS . 'autoloader.php';

// Start the database sessions
$session = new Core_Session();
session_set_save_handler(
	array($session, 'open'),	// Open database connection
	array($session, 'close'),	// Close database connection
	array($session, 'read'),	// Read the session from database
	array($session, 'write'),	// Write session data to database
	array($session, 'destroy'),	// Destroy the session in the database
	array($session, 'gc')		// Garbage collection, removing old sessions
);
// We now need to start the session
session_start();