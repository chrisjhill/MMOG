<?php
// Global configurations
include $_SERVER['DOCUMENT_ROOT'] . '/libs/global.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Language" content="en-gb" />
    <title>PHP Battlescript</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <!-- CSS //-->
    <link type="text/css" href="./assets/css/styles.css" rel="stylesheet" media="screen" />

    <!-- Scripts //-->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="./assets/js/scripts.js"></script>
</head>
<body>
    <div id="container">
    	<div class="section">
    		<h2>Battle Engine</h2>
    		
            <div class="intro">
        		<p>
                    Developed as part of a <abbr title="Massively Multiplayer Online Game">MMOG</abbr>, this engine is
                    capable of taking an attacking and defending fleet and battling them against each other. The engine
                    is capable of freezing, stealing and destroying the opponents ships.
                </p>

                <p><input type="button" name="battle_submit" id="battle_submit" value="Start the battle sequence" /></p>
            </div>
            
            <hr />
            
    		<div id="battle_output">
    		    <h3>Battle output</h3>
    		    
    		     <div class="container">
    		         <p id="battle_output_introduction">
                        Click the button above<br />
                        to see the output here
                    </p>
    		     </div>
    		</div>
    	</div>
    </div>
</body>
</html>