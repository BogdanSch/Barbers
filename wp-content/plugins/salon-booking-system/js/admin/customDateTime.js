"use strict";

function sln_initDatepickers($) {
    $('.sln_datepicker input').each(function () {
        var $this = $(this);
        $this.on('focusin', function() {
            if ($($this).hasClass('started') || $($this).attr('id').indexOf('__new__') > 0) {
                return;
            } else {
                var picker = $($this)
                    .addClass('started')
                    .datetimepicker({
                        format: $($this).data('format'),
                        weekStart: $($this).data('weekstart'),
                        minuteStep: 60,
                        autoclose: true,
                        minView: 2,
                        maxView: 4,
                        todayBtn: true,
                        language: $($this).data('locale')
                    })
                    .on('show', function () {
                        $('body').trigger('sln_date');
                    })
                    .on('place', function () {
                        $('body').trigger('sln_date');
                    })
                    .on('changeMonth', function () {
                        $('body').trigger('sln_date');
                    })
                    .on('changeYear', function () {
                        $('body').trigger('sln_date');
                    })
                    .data('datetimepicker').picker;

                picker.addClass($($this).data('popup-class'));
            }
        });
    });
}

function sln_initTimepickers($) {
    $('.sln_timepicker input').each(function () {
        var $this = $(this);
        $this.on('focusin', function() {
            if ($($this).hasClass('started') || $($this).attr('id').indexOf('__new__') > 0) {
                return;
            } else {
                var picker = $($this)
                    .addClass('started')
                    .datetimepicker({
                        format: $($this).data('format'),
                        minuteStep: $($this).closest('.sln-booking-holiday-rules-wrapper').length ? $($this).data('interval') : 5,
                        autoclose: true,
                        minView: $($this).data('interval') == 60 ? 1 : 0,
                        maxView: 1,
                        startView: 1,
                        showMeridian: $($this).data('meridian') ? true : false,
                        minuteStep: $this.attr('data-interval'),
                    })
                    .on('show', function () {
                        $('body').trigger('sln_date');
                    })
                    .on('place', function () {
                        $('body').trigger('sln_date');
                    })

                    .data('datetimepicker').picker;
                picker.addClass('timepicker').addClass($($this).data('popup-class'));
            }
        });
    });
}

jQuery(function($){
    sln_initDatepickers($);
    sln_initTimepickers($);
});
