$(document).ready(function() {
    // DOM is ready to be manipulated

    // The user has requested a battle to commence
    $("#battle_submit").click(function() {
        // Set the div as a loading indicator
        $("#battle_output .container").html('<img src="./assets/img/loading.gif" alt="Loading..." class="loading" />');
        
        // Send the Ajax request for the battle sequence
        $.ajax({
            url:  "./ajax/battle.php",
            success: function(data) {
                // Place the HTML into the div
                $("#battle_output .container").html(data);
            }
        });
    });
});