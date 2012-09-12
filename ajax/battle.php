<?php
// This page has been called via Ajax
// Get the Battle class
include $_SERVER['DOCUMENT_ROOT'] . '/libs/Core/Battle.class.php';

// New battle
$battle = new Core_Battle();
$battle->initiateWave();