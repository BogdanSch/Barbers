"use strict";

function sln_customSliderRange($, $elements) {
    // TIME RANGE //
    $($elements).each(function() {
        var labelFrom = $(this)
                .parent()
                .parent()
                .find(".col-time .slider-time-from"),
            labelTo = $(this)
                .parent()
                .parent()
                .find(".col-time .slider-time-to"),
            inputFrom = $(this)
                .parent()
                .parent()
                .find(".col-time .slider-time-input-from"),
            inputTo = $(this)
                .parent()
                .parent()
                .find(".col-time .slider-time-input-to"),
            orarioFrom = inputFrom.val(),
            oreFrom = orarioFrom.substr(0, orarioFrom.indexOf(":")),
            oreInMinutiFrom = parseInt(Math.floor(oreFrom * 60)),
            minutiFrom = parseInt(
                orarioFrom.substr(orarioFrom.indexOf(":") + 1)
            ),
            totaleMinutiFrom = oreInMinutiFrom + minutiFrom,
            orarioTo = inputTo.val(),
            oreTo = orarioTo.substr(0, orarioTo.indexOf(":")),
            oreInMinutiTo = parseInt(Math.floor(oreTo * 60)),
            minutiTo = parseInt(orarioTo.substr(orarioTo.indexOf(":") + 1)),
            totaleMinutiTo = oreInMinutiTo + minutiTo;

        labelFrom.html(inputFrom.val());
        labelTo.html(inputTo.val());
        console.log(inputFrom.val());
        $(this).slider({
            range: true,
            min: 0,
            minRange: 200,
            max: 1440,
            step: 5,
            values: [totaleMinutiFrom, totaleMinutiTo],
            slide: function(e, ui) {
                var hours1 = Math.floor(ui.values[0] / 60);
                var minutes1 = ui.values[0] - hours1 * 60;
                if (ui.values[1] - ui.values[0] < 15) {
                    // minimum range = 60 minutes
                    return false;
                } else {
                    if (hours1.length == 1) hours1 = "0" + hours1;
                    if (minutes1.length == 1) minutes1 = "0" + minutes1;
                    if (minutes1 == 0) minutes1 = "00";
                    if (minutes1 == 5) minutes1 = "05";
                    if (hours1 >= 12) {
                        if (hours1 == 12) {
                            hours1 = hours1;
                            minutes1 = minutes1 + "";
                        } else {
                            hours1 = hours1;
                            minutes1 = minutes1 + "";
                        }
                    } else {
                        hours1 = hours1;
                        minutes1 = minutes1 + "";
                    }
                    if (hours1 == 0) {
                        hours1 = 0;
                        minutes1 = minutes1;
                    }

                    $(this)
                        .parent()
                        .parent()
                        .find(".col-time .slider-time-from")
                        .html(hours1 + ":" + minutes1);
                    $(this)
                        .parent()
                        .parent()
                        .find(".col-time .slider-time-input-from")
                        .val(hours1 + ":" + minutes1);
                    $(this)
                        .find(".ui-slider-range + .ui-slider-handle")
                        .html("<span>" + hours1 + ":" + minutes1 + "</span>");
                    var hours2 = Math.floor(ui.values[1] / 60);
                    var minutes2 = ui.values[1] - hours2 * 60;

                    if (hours2.length == 1) hours2 = "0" + hours2;
                    if (minutes2.length == 1) minutes2 = "0" + minutes2;
                    if (minutes2 == 0) minutes2 = "00";
                    if (minutes2 == 5) minutes2 = "05";
                    if (hours2 >= 12) {
                        if (hours2 == 12) {
                            hours2 = hours2;
                            minutes2 = minutes2 + "";
                        } else {
                            hours2 = hours2;
                            minutes2 = minutes2 + "";
                        }
                    } else {
                        hours2 = hours2;
                        minutes2 = minutes2 + "";
                    }

                    $(this)
                        .parent()
                        .parent()
                        .find(".col-time .slider-time-to")
                        .html(hours2 + ":" + minutes2);
                    $(this)
                        .parent()
                        .parent()
                        .find(".col-time .slider-time-input-to")
                        .val(hours2 + ":" + minutes2);
                    $(this)
                        .find(
                            ".ui-slider-range + .ui-slider-handle + .ui-slider-handle"
                        )
                        .html("<span>" + hours2 + ":" + minutes2 + "</span>");
                }
            },
        });
        $(this)
            .find(".ui-slider-range + .ui-slider-handle")
            .html("<span>" + inputFrom.val() + "</span>");
        $(this)
            .find(".ui-slider-range + .ui-slider-handle + .ui-slider-handle")
            .html("<span>" + inputTo.val() + "</span>");
    });
}

jQuery(function($) {
    sln_customSliderRange($, $(".slider-range"));
});
