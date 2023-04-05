"use strict";

function sln_serviceBreakSliderRange($, $elements) {

    function initSliderRange($elements) {

        var value = getMinutesDuration($('#_sln_service_duration').val());

        // TIME RANGE //
        $($elements).each(function() {

            var from = +$(this).closest('.sln-slider').find('.slider-time-input-from').val();
            var to   = +$(this).closest('.sln-slider').find('.slider-time-input-to').val();

            var min = 0;
            var max = value + to - from;

            var step = +$(this).closest('.sln-slider').find('.slider-time-input-step').val();

            $(this).closest('.sln-slider').find('.slider-time-min-value').html(min);
            $(this).closest('.sln-slider').find('.slider-time-max-value').html(max);

            $(this).closest('.sln-slider').find('.slider-time-from-value').html(from);
            $(this).closest('.sln-slider').find('.slider-time-to-value').html(to);

            $(this).dragslider({
                range: true,
                min: min,
                minRange: 0,
                max: max,
                step: step,
                values: [from, to],
                rangeDrag: true,
                rangeDragStep: 1,
                slide: function(e, ui) {

                    if (ui.values[1] - ui.values[0] < step) {
                        return false;
                    }

                    $(this).closest('.sln-slider').find('.slider-time-from-value').html(ui.values[0]);
                    $(this).closest('.sln-slider').find('.slider-time-to-value').html(ui.values[1]);

                    $(this).closest('.sln-slider').find('.slider-time-input-from').val(ui.values[0]);
                    $(this).closest('.sln-slider').find('.slider-time-input-to').val(ui.values[1]);

                    var value = getMinutesDuration($('#_sln_service_duration').val());

                    var max = value + ui.values[1] - ui.values[0];
                    $(this).dragslider( "option", "max", max );
                    $(this).closest('.sln-slider').find('.slider-time-max-value').html(max);

                    var duration = ui.values[1] - ui.values[0];

                    $(this).closest('.sln-slider').find('.slider-time-input-break-duration').val(getDuration(duration));

                    $(this).dragslider( "option", "values", ui.values )
                },
            });

            $(this).find('.ui-slider-range').append('<div class="slider-time-break">'+ sln_SliderDragRange.break_string +'</div>');
            $(this).find('.ui-slider-handle').eq(0).append('<div class="slider-time-range-min slider-time-range-value">'+ $(this).closest('.sln-slider').find('.slider-time-from-wrapper').html() +'</div>');
            $(this).find('.ui-slider-handle').eq(1).append('<div class="slider-time-range-max slider-time-range-value">'+ $(this).closest('.sln-slider').find('.slider-time-to-wrapper').html() +'</div>');
        });
    }

    $('#_sln_service_duration').on('change', function () {

        var value = getMinutesDuration($(this).val());

        $($elements).each(function() {
            var values = $(this).dragslider( "option", "values" );
            var max    = value + values[1] - values[0];
            $(this).dragslider( "option", "max", max );
            $(this).closest('.sln-slider').find('.slider-time-max-value').html(max);
        });
    });

    $('#_sln_service_break_duration_enabled').on('change', function () {

        $('.sln-slider-break-duration-wrapper').toggleClass('hide', !$(this).prop('checked'));

        if ($(this).prop('checked')) {
            if ( ! getMinutesDuration($('.sln-slider-break-duration-wrapper .slider-time-input-break-duration').val()) ) {
                $('.sln-slider-break-duration-wrapper .slider-time-input-break-duration').val(getDuration(+$('.sln-slider-break-duration-wrapper .slider-time-input-step').val()));
                $('.sln-slider-break-duration-wrapper .slider-time-input-from').val(parseInt(+$('.sln-slider-break-duration-wrapper .slider-time-input-step').val() / 2));
                $('.sln-slider-break-duration-wrapper .slider-time-input-to').val(parseInt(+$('.sln-slider-break-duration-wrapper .slider-time-input-step').val() / 2) + +$('.sln-slider-break-duration-wrapper .slider-time-input-step').val());
            }
            initSliderRange($elements);
        } else {
            $('.sln-slider-break-duration-wrapper .slider-time-input-break-duration').val("00:00");
            $('.sln-slider-break-duration-wrapper .slider-time-input-from').val(0);
            $('.sln-slider-break-duration-wrapper .slider-time-input-to').val(0);

            $($elements).each(function() {
                if ($(this).hasClass('ui-slider')) {
                    $(this).dragslider( "destroy" );
                }
            });
        }
    }).trigger('change');

    function getMinutesDuration(duration) {
        var tmp = duration.split(':');
        return parseInt(tmp[0]) * 60 + parseInt(tmp[1]);
    }

    function getDuration(minutes) {
        var tmp = parseInt(minutes / 60) > 9 ? parseInt(minutes / 60) : "0" + parseInt(minutes / 60);
        tmp    += ":" + ((minutes % 60) > 9 ? (minutes % 60) : "0" + (minutes % 60));
        return tmp;
    }
}

jQuery(function($) {
    sln_serviceBreakSliderRange($, $(".service-break-slider-range"));
});
