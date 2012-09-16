<?php
// Include autoloader
include $_SERVER['DOCUMENT_ROOT'] . '/libs/global.php';

// New battle
$battle = new Model_Battle();
$battle->initiateWave();

// Build the battle report
$battleReport = new Model_BattleReport();
echo $battleReport->output($battle->getBattleId(), 1);