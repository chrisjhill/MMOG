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
    	<!-- Start of the battle engine //-->
    	<div class="section">
    		<h2>Battle Engine</h2>
    		
    		<p class="intro">Developed as part of a <abbr title="Massively Multiplayer Online Game">MMOG</abbr>, this engine is capable of taking an attacking and defending fleet and battling them against each other. The engine is capable of freezing, stealing and destroying the opponents ships.</p>
    		
    		<hr />
    		
    		<h3>Battle input</h3>
    		
            <table id="battle_input">
                <tr>
                    <td>&nbsp;</td>
                    <th>Defending</th>
                    <th>Attacking</th>
                </tr>
    			<tr>
    	            <th>Attacking ship 1</th>
    	            <td><input type="text" name="defending[0]" class="battle_ship_count" value="700" /></td>
    	            <td><input type="text" name="attacking[0]" class="battle_ship_count" value="500" /></td>
    	        </tr>
            	        <tr>
    	            <th>Attacking ship 2</th>
    	            <td><input type="text" name="defending[1]" class="battle_ship_count" value="2000" /></td>
    	            <td><input type="text" name="attacking[1]" class="battle_ship_count" value="3000" /></td>
    	        </tr>
            	        <tr>
    	            <th>Attacking ship 3</th>
    	            <td><input type="text" name="defending[2]" class="battle_ship_count" value="1500" /></td>
    	            <td><input type="text" name="attacking[2]" class="battle_ship_count" value="1750" /></td>
    	        </tr>
            	        <tr>
    	            <th>Attacking ship 4</th>
    	            <td><input type="text" name="defending[3]" class="battle_ship_count" value="800" /></td>
    	            <td><input type="text" name="attacking[3]" class="battle_ship_count" value="400" /></td>
    	        </tr>
            	        <tr>
    	            <th>EMP freezing ship</th>
    	            <td><input type="text" name="defending[4]" class="battle_ship_count" value="5000" /></td>
    	            <td><input type="text" name="attacking[4]" class="battle_ship_count" value="4000" /></td>
    	        </tr>
            	        <tr>
    	            <th>Stealing ship</th>
    	            <td><input type="text" name="defending[5]" class="battle_ship_count" value="1000" /></td>
    	            <td><input type="text" name="attacking[5]" class="battle_ship_count" value="4000" /></td>
    	        </tr>
            	        <tr>
    	            <th>Salvage ship</th>
    	            <td><input type="text" name="defending[6]" class="battle_ship_count" value="500" /></td>
    	            <td><input type="text" name="attacking[6]" class="battle_ship_count" value="0" /></td>
    	        </tr>
            	        <tr>
    	            <th>Asteroid stealing ship</th>
    	            <td><input type="text" name="defending[7]" class="battle_ship_count" value="0" /></td>
    	            <td><input type="text" name="attacking[7]" class="battle_ship_count" value="2000" /></td>
    	        </tr>
                <tr>
                	<th>Asteroids</th>
                	<td colspan="2"><input type="text" name="asteroid" id="asteroid" class="battle_ship_count" value="100" /></td>
                </tr>
                <tr class="no-border">
                    <td>&nbsp;</td>
                    <td colspan="2">
                    	<input type="button" name="battle_submit" id="battle_submit" value="Start the battle sequence" />
                    	for
                    	<select name="waves" id="waves">
        					<option value="1">1 wave</option>
        					<option value="2">2 waves</option>
        					<option value="3" selected="selected">3 waves</option>
        					<option value="4">4 waves</option>
        					<option value="5">5 waves</option>
                    	</select>
                   	</td>
                </tr>
            </table>
            
            <hr />
            
    		<div id="battle_output">
    		    <h3>Battle output</h3>
    		    
    		     <div class="container">
    		         <p id="battle_output_introduction">Submit the form above<br />to see the output here</p>
    		     </div>
    		</div>
    	</div>
    	<!-- End of battle engine //-->
    </div>
</body>
</html>