<?php
// Include autoloader
include $_SERVER['DOCUMENT_ROOT'] . '/libs/global.php';

// New battle
$battle = new Model_Battle_Fight(1);
$battle->initiateWave();

// Build the battle report
$battleReport = new Model_Battle_Report();
echo $battleReport->output($battle->getBattleId(), 1);