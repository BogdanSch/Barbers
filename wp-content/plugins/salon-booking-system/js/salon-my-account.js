// algolplus

"use strict";

var sln_myAccount = {
    cancelBooking: function (id) {
        if (!confirm(salon.confirm_cancellation_text)) {
            return;
        }

        jQuery.ajax({
            url: salon.ajax_url,
            data: {
                action: 'salon',
                method: 'cancelBooking',
                id: id
            },
            method: 'POST',
            dataType: 'json',
            success: function (data) {
                if (typeof data.redirect != 'undefined') {
                    window.location.href = data.redirect;
                } else if (data.success != 1) {
                    alert('error');
                    console.log(data);
                } else {
                    sln_myAccount.loadContent('cancelled');
                }
            },
            error: function (data) {
                alert('error');
                console.log(data);
            }
        });
    },

    loadContent: function (option) {
        jQuery.ajax({
            url: salon.ajax_url,
            data: {
                action: 'salon',
                method: 'myAccountDetails',
                option: option,
                customer_timezone: new window.Intl.DateTimeFormat().resolvedOptions().timeZone,
            },
            method: 'POST',
            dataType: 'json',
            success: function (data) {
                if (typeof data.redirect != 'undefined') {
                    window.location.href = data.redirect;
                } else {
                    jQuery('#sln-salon-my-account-content').html(data.content);
                    sln_createSelect2Full(jQuery);
                    sln_createRatings(true, 'circle');
                    jQuery("[data-toggle='tooltip']").tooltip();

                    if (sln_myAccount.feedback_id) {
                        sln_myAccount.showRateForm(sln_myAccount.feedback_id);
                    }
                    sln_myAccount.setActiveTab();
                    jQuery('.nav-tabs a').on('show.bs.tab', sln_myAccount.setActiveHash);
                    jQuery('#salon-my-account-profile-form input[name="action"]').val('salon');
                    jQuery('#salon-my-account-profile-form').on('submit', sln_myAccount.updateProfile);

                    var items = {intervals: {}};

                    sln_initDatePickersReschedule(jQuery, items);
                    sln_initTimePickersReschedule(jQuery, items);

                    var doingFunc = null;

                    var func = function () {
                        clearTimeout(doingFunc)
                        doingFunc = setTimeout(function () {
                            sln_updateDatepickerTimepickerSlots(jQuery, items.intervals);
                        }, 200);
                    }



                    jQuery('body').off('sln_date', func).on('sln_date', func);

                    jQuery('body').on('sln_date', function () {
                        setTimeout(function() {
                            jQuery(".datetimepicker-days table tr td.day").on("click", function() {
                                if (jQuery(this).hasClass("disabled")) {
                                    return;
                                }
                                const datetimepicker = jQuery(".sln_datepicker div").data(
                                    "datetimepicker"
                                );

                                const date = jQuery(this).attr("data-ymd");

                                const dateObj = jQuery.fn.datetimepicker.DPGlobal.parseDate(
                                    date,
                                    datetimepicker.format,
                                    datetimepicker.language,
                                    datetimepicker.formatType
                                );

                                const formattedDate = jQuery.fn.datetimepicker.DPGlobal.formatDate(
                                    dateObj,
                                    datetimepicker.format,
                                    datetimepicker.language,
                                    datetimepicker.formatType
                                );

                                jQuery("input[name='_sln_booking_date']").val(formattedDate);
                            });
                        });
                    });

                    function validate(obj) {
                        var form = jQuery(obj).closest('form');

                        var validatingMessage = '<div class="sln-alert sln-alert--wait">' + salon.txt_validating + '</div>';

                        form.find('.sln-notifications').addClass('sln-notifications--active').html(validatingMessage);

                        form.find('.sln-reschedule-form--save-button').addClass('disabled');

                        var data = form.serialize();

                        data += '&action=salon&method=rescheduleBookingCheckDate&security=' + salon.ajax_nonce;

                        jQuery.ajax({
                            url: salon.ajax_url,
                            data: data,
                            method: 'POST',
                            dataType: 'json',
                            success: function (data) {

                                items.intervals = data.intervals;

                                func();

                                if (!data.success) {

                                    /*var alertBox = jQuery('<div class="sln-alert sln-alert--problem"></div>');

                                    jQuery(data.errors).each(function (i, obj) {
                                        alertBox.append(jQuery('<p></p>').html(obj));
                                    });

                                    form.find('.sln-notifications').html('').append(alertBox);*/

                                    form.find('.sln-notifications').html('');

                                    form.find('input[name="_sln_booking_date"]').val(data.intervals.suggestedDate);

                                    var datetimepicker = form.find('.sln_datepicker div').data("datetimepicker");

                                    var suggestedDate = jQuery.fn.datetimepicker.DPGlobal.parseDate(
                                        data.intervals.suggestedDate,
                                        datetimepicker.format,
                                        datetimepicker.language,
                                        datetimepicker.formatType
                                    );

                                    datetimepicker.setUTCDate(suggestedDate);

                                    var timeValue = Object.values(data.intervals.times)[0] || "";
                                    var hours = parseInt(timeValue, 10) || 0;
                                    var datetimepicker = form.find(".sln_timepicker div").data(
                                        "datetimepicker"
                                    );
                                    datetimepicker.viewDate.setUTCHours(hours);
                                    var minutes =
                                        parseInt(
                                            timeValue.substr(timeValue.indexOf(":") + 1),
                                            10
                                        ) || 0;
                                    datetimepicker.viewDate.setUTCMinutes(minutes);
                                    form.find('input[name="_sln_booking_time"]').val(timeValue);

                                    sln_renderAvailableTimeslots(jQuery, data);
                                    jQuery("body").trigger("sln_date");
                                } else {

                                    form.find('input[name="_sln_booking_date"]').val(data.intervals.suggestedDate);
                                    form.find('input[name="_sln_booking_time"]').val(data.intervals.suggestedTime);

                                    form.find('.sln-reschedule-form--save-button').removeClass('disabled');
                                    form.find('.sln-notifications').html('').removeClass('sln-notifications--active');

                                    var timeValue = Object.values(data.intervals.times)[0] || "";
                                    var hours = parseInt(timeValue, 10) || 0;
                                    var datetimepicker = form.find(".sln_timepicker div").data(
                                        "datetimepicker"
                                    );
                                    datetimepicker.viewDate.setUTCHours(hours);
                                    var minutes =
                                        parseInt(
                                            timeValue.substr(timeValue.indexOf(":") + 1),
                                            10
                                        ) || 0;
                                    datetimepicker.viewDate.setUTCMinutes(minutes);
                                    sln_renderAvailableTimeslots(jQuery, data);
                                    jQuery("body").trigger("sln_date");
                                    form.find('input[name="_sln_booking_time"]').val(timeValue);
                                }
                            }
                        });
                    }

                    jQuery('.sln_datepicker div').on('changeDay', function () {
                        validate(this);
                    });

                    jQuery('.sln-reschedule-booking--button').on('click', function () {
                        const bookingTime = jQuery(this).closest('tr').find('.sln-booking-time').text();
                        const bookingDate = jQuery(this).closest('tr').find('.sln-booking-date').text();

                        var datetimepicker = jQuery('.sln_datepicker div').data("datetimepicker");

                        var suggestedDate = jQuery.fn.datetimepicker.DPGlobal.parseDate(
                            bookingDate,
                            datetimepicker.format,
                            datetimepicker.language,
                            datetimepicker.formatType
                        );

                        datetimepicker.setUTCDate(suggestedDate);

                        jQuery(this).closest('tr').find('.sln-reschedule-form').removeClass('hide');
                        jQuery(this).addClass('hide');

                        jQuery(this).closest('td').find('.sln-reschedule-form').find('input[name="_sln_booking_date"]').val(bookingDate);
                        jQuery(this).closest('td').find('.sln-reschedule-form').find('input[name="_sln_booking_time"]').val(bookingTime);

                        validate(jQuery(this).closest('td').find('.sln-reschedule-form'));
                        //jQuery("body").trigger("sln_date");
                        //jQuery(this).closest('tr').find('.sln_datepicker div').trigger('changeDay');

                    });

                    jQuery('.sln-reschedule-form--cancel-button').on('click', function () {
                        jQuery(this).closest('tr').find('.sln-reschedule-form').addClass('hide');
                        jQuery(this).closest('tr').find('.sln-reschedule-booking--button').removeClass('hide');
                        jQuery(this).closest('tr').find('.sln-notifications').html('');
                        jQuery(this).closest('tr').find('.sln-reschedule-form--save-button').removeClass('disabled');
                        jQuery(this).closest('tr').find('form').trigger('reset');
                    });

                    jQuery('.sln-reschedule-form--save-button').on('click', function () {

                        var self = this;

                        if (jQuery(self).hasClass('disabled')) {
                            return false;
                        }

                        var data = jQuery(self).closest('.sln-reschedule-form').serialize();

                        data += '&action=salon&method=rescheduleBooking&security=' + salon.ajax_nonce;

                        jQuery.ajax({
                            url: salon.ajax_url,
                            data: data,
                            method: 'POST',
                            dataType: 'json',
                            success: function (response) {

                                if (typeof response.redirect != 'undefined') {
                                    window.location.href = response.redirect;
                                }

                                jQuery(self).closest('tr').find('.sln-booking-date').html(response.booking_date);
                                jQuery(self).closest('tr').find('.sln-booking-time').html(response.booking_time);

                                if(response.booking_status == 'sln-b-pending'){
                                    let statusIcon = jQuery(self).closest('tr').find('.status .glyphicon').removeAttr('class').addClass('glyphicon');
                                    jQuery(statusIcon).parent().find('.glyphicon-class strong').text(response.booking_status_label.toUpperCase());
                                    jQuery(statusIcon).addClass('glyphicon-clock');
                                    jQuery(self).closest('tr').find('.sln-reschedule-booking--button').attr('style', 'display: none !important;');
                                    jQuery(document).scrollTop(0);
                                    alert('Booking is updated');
                                }

                                jQuery(self).closest('tr').find('input[name="_sln_booking_date"]').attr('value', response.booking_date);
                                jQuery(self).closest('tr').find('input[name="_sln_booking_time"]').attr('value', response.booking_time);

                                jQuery(self).closest('tr').find('.sln-reschedule-form--cancel-button').trigger('click');
                            },
                            error: function (data) {
                                alert('error');
                                console.log(data);
                            }
                        });
                    });

                    jQuery('.nav-tabs a').on('shown.bs.tab', function (e) {

                        if (jQuery(e.target).attr('data-target') !== '#profile') {
                            return true;
                        }

                        var input = document.querySelector("#sln_phone");

                        if (input && jQuery('#sln_sms_prefix').length && !!!jQuery(input).closest('.iti').length) {
                            function getCountryCodeByDialCode(dialCode) {
                                var countryData = window.intlTelInputGlobals.getCountryData();
                                var countryCode = '';
                                countryData.forEach(function(data) {
                                   if (data.dialCode == dialCode) {
                                       countryCode = data.iso2;
                                   }
                                });
                                return countryCode;
                            }

                            var iti = window.intlTelInput(input, {
                                initialCountry: getCountryCodeByDialCode((jQuery('#sln_sms_prefix').val() || '').replace('+', '')),
                                separateDialCode: true,
                                autoHideDialCode: true,
                                nationalMode: false,
                            });

                            input.addEventListener("countrychange", function() {
                                if (iti.getSelectedCountryData().dialCode) {
                                    jQuery('#sln_sms_prefix').val('+' + iti.getSelectedCountryData().dialCode);
                                }
                            });

                            input.addEventListener("blur", function() {
                                if (iti.getSelectedCountryData().dialCode) {
                                    jQuery(input).val(jQuery(input).val().replace("+" + iti.getSelectedCountryData().dialCode, ""));
                                }
                            });
                        }
                    });
                }
            },
            error: function (data) {
                alert('error');
                console.log(data);
            }
        });
    },

    loadNextHistoryPage: function () {
        var page = parseInt(jQuery('#sln-salon-my-account-history-content table tr:last').attr('data-page')) + 1;
        jQuery.ajax({
            url: salon.ajax_url,
            data: {
                action: 'salon',
                method: 'myAccountDetails',
                args: {
                    part: 'history',
                    page: page,
                }
            },
            method: 'POST',
            dataType: 'json',
            success: function (data) {
                if (typeof data.redirect != 'undefined') {
                    window.location.href = data.redirect;
                } else {
                    jQuery('#sln-salon-my-account-history-content').html(data.content);
                    if (jQuery('#sln-salon-my-account-history-content table tr:last').attr('data-end') == 1) {
                        jQuery('#next_history_page_btn').remove();
                    }
                    sln_createRatings(true, 'circle');
                    jQuery("[data-toggle='tooltip']").tooltip();
                }
            },
            error: function (data) {
                alert('error');
                console.log(data);
            }
        });
    },

    showRateForm: function (id) {
        sln_createRaty(jQuery("#ratingModal .rating"));
        jQuery("#ratingModal textarea").attr('id', id);
        jQuery("#ratingModal textarea").val('');

        jQuery("#ratingModal #step2").css('display', 'none');
        jQuery("#ratingModal").modal('show');
        jQuery("#ratingModal #step1").css('display', 'block');

        return false;
    },

    sendRate: function () {
        if (jQuery("#ratingModal .rating").raty('score') == undefined || jQuery("#ratingModal textarea").val() == '')
            return false;

        jQuery.ajax({
            url: salon.ajax_url,
            data: {
                action: 'salon',
                method: 'setBookingRating',
                id: jQuery("#ratingModal textarea").attr('id'),
                score: jQuery("#ratingModal .rating").raty('score'),
                comment: jQuery("#ratingModal textarea").val(),
            },
            method: 'POST',
            dataType: 'json',
            success: function (data) {
                if (typeof data.redirect != 'undefined') {
                    window.location.href = data.redirect;
                } else if (data.success != 1) {
                    alert('error');
                    console.log(data);
                } else {
                    jQuery("#ratingModal #step1").css('display', 'none');
                    jQuery("#ratingModal #step2").css('display', 'block');

                    jQuery('#ratingModal .close').delay(2000).queue(function () {
                        jQuery(this).trigger('click');
                        sln_myAccount.loadContent();
                        jQuery(this).dequeue();
                    });

                    sln_myAccount.feedback_id = false;
                }
            },
            error: function (data) {
                alert('error');
                console.log(data);
            }
        });
        return false;
    },

    setActiveHash: function (e) {
        window.location.hash = e.target.hash;
    },

    setActiveTab: function (hash) {
        var hash = hash ? hash : window.location.hash;
        if (hash)
            jQuery('.nav-tabs a[href="' + hash + '"]').tab('show');
    },


    updateProfile: function (e) {
        e.preventDefault();
        var form = e.target;
        var data = jQuery(form).serialize();
        var statusContainer = jQuery('#salon-my-account-profile-form .statusContainer');
        statusContainer.parent().hide();
        statusContainer.html('');
        data += "&method=UpdateProfile";
        jQuery.ajax({
            url: salon.ajax_url,
            data: data,
            method: 'POST',
            dataType: 'json',
            success: function (data) {
                statusContainer.parent().show();
                if (data.status === 'success') {
                    statusContainer.append('<div class="sln-alert alert-success">' + salonMyAccount_l10n.success + '</div>')
                    jQuery('#salon-my-account-profile-form .sln-account--last-update').html(data.last_update)
                } else {
                    data.errors.forEach(function (error) {
                        statusContainer.append('<div class="sln-alert sln-alert--problem">' + error + '</div>');
                    })
                }

            },
            error: function (data) {
                alert('error');
                console.log(data);
            }
        });
    },

    init: function () {
        if (jQuery('#sln-salon-my-account-content').length) {
            this.loadContent();
        } else {
            sln_createRatings(true, 'star');
        }
    }
};

function sln_addClassIfNarrow(element, narrowClass) {
    if (element.length > 0) {
        jQuery(window).on("load resize", function () {
            var elementWidth = element.width();
            if (elementWidth < 769) {
                element.addClass(narrowClass);
            } else {
                element.removeClass(narrowClass);
            }
        });
    }
}

function sln_initDatePickersReschedule($, data) {
    $(".sln_datepicker div").each(function () {
        $(this).attr("readonly", "readonly");
        if ($(this).hasClass("started")) {
            return;
        } else {
            $(this)
                .addClass("started")
                .datetimepicker({
                    format: $(this).data("format"),
                    weekStart: $(this).data("weekstart"),
                    minuteStep: 60,
                    minView: 2,
                    maxView: 4,
                    language: $(this).data("locale"),
                })
                .on("changeMonth", function () {
                    $("body").trigger("sln_date");
                })
                .on("changeYear", function () {
                    $("body").trigger("sln_date");
                })
                .on("hide", function () {
                    if ($(this).is(":focus")) ;
                    $(this).trigger("blur");
                });
            $("body").trigger("sln_date");
        }
    });
    var elementExists = document.getElementById("sln-salon");
    if (elementExists) {
        setTimeout(function () {
            $(".datetimepicker.sln-datetimepicker").wrap(
                "<div class='sln-salon-bs-wrap'></div>"
            );
        }, 50);
    }
}

function sln_initTimePickersReschedule($, data) {
    $(".sln_timepicker div").each(function () {
        $(this).attr("readonly", "readonly");
        if ($(this).hasClass("started")) {
            return;
        } else {
            var picker = $(this)
                .addClass("started")
                .datetimepicker({
                    format: $(this).data("format"),
                    minuteStep: $(this).data("interval"),
                    minView: 0,
                    maxView: 0,
                    startView: 0,
                    showMeridian: $(this).data("meridian") ? true : false,
                })
                .on("show", function () {
                    $("body").trigger("sln_date");
                })
                .on("place", function () {
                    sln_renderAvailableTimeslots($, data);

                    $("body").trigger("sln_date");
                })
                .on("changeMinute", function () {
                    setTimeout(function () {
                        sln_renderAvailableTimeslots($, data);

                        $("body").trigger("sln_date");

                        $('.sln-reschedule-form--save-button').removeClass('disabled');
                        $('.sln-notifications').html('').removeClass('sln-notifications--active');
                    }, 5);
                })
                .on("hide", function () {
                    if ($(this).is(":focus")) ;
                    $(this).blur();
                })

                .data("datetimepicker").picker;
            picker.addClass("timepicker");

            picker
                .find(".datetimepicker-minutes")
                .prepend(
                    $(
                        '<div class="sln-datetimepicker-minutes-wrapper-table"></div>'
                    ).append(picker.find(".datetimepicker-minutes table"))
                );

            sln_renderAvailableTimeslots($, data);
        }
    });
}

jQuery(function () {
    sln_myAccount.init();
    sln_addClassIfNarrow(jQuery('#sln-salon-my-account'), 'mobile-version');
});
