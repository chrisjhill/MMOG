<?php
// We're in development, show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session and object buffer
session_start();

// Automatically set some session variables
// @todo Remove this when we have a login system.
$_SESSION['user_id']    = 1;
$_SESSION['round_id']   = 1;
$_SESSION['country_id'] = 1;

// Include autoloader and config
include $_SERVER['DOCUMENT_ROOT'] . '/libs/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/libs/autoloader.php';