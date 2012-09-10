<?php
// This page has been called via Ajax
// Get the Battle class
include $_SERVER['DOCUMENT_ROOT'] . '/libs/Core/Battle.class.php';

// New battle
$battle = new Core_Battle();
// Set the battle stats
$ships = $battle->getShipStats();

// Has the user submitted a battle?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Start the waves
	$battle->initiateWave();
	
	// Does the user want to show the debug information?
	if (isset($_POST['debug']) && $_POST['debug'] == '1') {
		$battle->debug();
	}
}