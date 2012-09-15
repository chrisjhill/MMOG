<?php
// Include autoloader
include $_SERVER['DOCUMENT_ROOT'] . '/libs/global.php';

// New battle
$battle = new Core_Battle();
$battle->initiateWave();

// Build the battle report
echo Core_BattleReport::output($battle->getBattleId(), 1);