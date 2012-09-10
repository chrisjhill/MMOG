$(document).ready(function() {
    // DOM is ready to be manipulated

    // The user has requested a battle to commence
    $("#battle_submit").click(function() {
        // Set the div as a loading indicator
        $("#battle_output .container").html('<img src="./assets/img/loading.gif" alt="Loading..." class="loading" />');
        
        // Send the Ajax request for the battle sequence
        $.ajax({
            url:  "./ajax/battle.php",
            type: "POST",
            data: {
                // Defending ships
                'defending[0]': $("input[name='defending[0]']").val(),
                'defending[1]': $("input[name='defending[1]']").val(),
                'defending[2]': $("input[name='defending[2]']").val(),
                'defending[3]': $("input[name='defending[3]']").val(),
                'defending[4]': $("input[name='defending[4]']").val(),
                'defending[5]': $("input[name='defending[5]']").val(),
                'defending[6]': $("input[name='defending[6]']").val(),
                'defending[7]': $("input[name='defending[7]']").val(),
                // Attacking ships
                'attacking[0]': $("input[name='attacking[0]']").val(),
                'attacking[1]': $("input[name='attacking[1]']").val(),
                'attacking[2]': $("input[name='attacking[2]']").val(),
                'attacking[3]': $("input[name='attacking[3]']").val(),
                'attacking[4]': $("input[name='attacking[4]']").val(),
                'attacking[5]': $("input[name='attacking[5]']").val(),
                'attacking[6]': $("input[name='attacking[6]']").val(),
                'attacking[7]': $("input[name='attacking[7]']").val(),
                // Supporting variables
                'waves':        $("#waves").val(),
                'asteroid':     $("#asteroid").val()
            },
            success: function(data) {
                // Place the HTML into the div
                $("#battle_output .container").html(data);
                
                // Select the first wave
                $("#wave_selector li:eq(0)").click();
            }
        });
    });
    
    // User has requested to see a particular battle wave
    $("#wave_selector li").live("click", function() {
        $(".active").removeClass("active");
        $(".wave_report").slideUp();
        $(this).addClass("active");
        $("#wave"+$(this).attr("rel")).slideDown();
    });
});