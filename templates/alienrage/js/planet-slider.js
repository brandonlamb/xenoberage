//<![CDATA[ 
$(window).load(function(){
var sliders = $("#planet-slider .slider");

sliders.each(function() {
    var max = document.getElementById("sliderMax").value;
    var value = Number($(this).text(), 10),
        availableTotal = max;

    $(this).empty().slider({
        value: 0,
        min: 0,
        max: $(this).siblings('.value').attr("max"),
        range: "max",
        step: 1,
        animate: 100,
        disabled: (function(curMax) {
            if (curMax < 1) {
                return 1;
            }
            return 0;
        })($(this).siblings('.percentage-value').attr("max")),
        slide: function(event, ui) {
            // Update text box to current value and call .change()
            $(this).siblings('.percentage-value').attr("value", ui.value);
            $(this).siblings('.percentage-value').trigger('change');
        }
    });
});
$(".slider").each(function() {
    var disabled = Number($(this).slider("option", "disabled"));
    if(disabled == 1) {
        $(this).siblings('.percentage-value').attr('disabled', 'disabled');    
    }
});

$(".percentage-value").change(function() {
    var thisAmount = Number($(this).prop("value"));
    var totalMax = Number(document.getElementById("sliderMax").value);
    var indMin = Number($(this).attr("min"));
    var indMax = Number($(this).attr("max"));
    var total = 0;

    //Get the values of all other text boxes
    $('.percentage-value').not(this).each(function() {
        total += Number($(this).prop("value"));
    });

    //Find the remaining from our total sliders max
    var remaining = totalMax - total;

    if(remaining < 0) {
      remaining = 0;   
    }
    //if we are under our minimums, go for it! Otherwise, reduce the number.
    if (thisAmount >= indMin && thisAmount < indMax && thisAmount < totalMax && thisAmount < remaining) {
        $(this).siblings(".slider").slider("option", "value", thisAmount);
        //total += thisAmount;
    }
    else {
        //var setMax = ((indMax + totalMax) - Math.abs(indMax - totalMax)) / 2;
        var setMax = Math.min(indMax, totalMax, remaining);
        $(this).siblings(".slider").slider("option", "value", setMax);
        $(this).prop("value", setMax);
        //total += (thisAmount - setMax);
    }

    //above was getting buggy, so lets just reset total and get it again
    total = 0;
    //Get the values of all text boxes
    $('.percentage-value').each(function() {
        total += Number($(this).prop("value"));
    });
    //Find our new remaining number after updating total for this value
    remaining = totalMax - total;
    if(remaining < 0) {
      remaining = 0;
    }
    //Set each slider to the current point and update their max values.
    $('.percentage-value').each(function() {
        var sliderVal = Number($(this).prop("value"));
        var sliderMin = Number($(this).attr("min"));
        var sliderMax = Number($(this).attr("max"));
        var setNewMax = (((sliderMax + totalMax) - Math.abs(sliderMax - totalMax)) / 2);
        var newMax = sliderVal + remaining;

        if(newMax < setNewMax) {
            $(this).siblings('.slider').slider("option", "max", newMax);
            $(this).siblings('span').text('/' + (newMax));
        }
        else {
            $(this).siblings('.slider').slider("option", "max", setNewMax);
        }
        $(this).prop("max", setNewMax);        
        $(this).siblings(".slider").slider("option", "value", sliderVal);
    });
    $('#sliderTotal').attr("value", total);
});
});//]]>  