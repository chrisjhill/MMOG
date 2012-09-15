<?php
// Include autoloader
include $_SERVER['DOCUMENT_ROOT'] . '/libs/global.php';

// New battle
$battle = new Core_Battle();
$battle->initiateWave();