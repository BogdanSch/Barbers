"use strict";

var sln_customer_fields;
jQuery(function($) {
    if ($(".sln-booking-user-field").length) {
        sln_prepareToValidatingBooking($);
    }
    if ($("#sln_booking-details").length) {
        sln_adminDate($);
    }
    $("#calculate-total").on("click", sln_calculateTotal);
    $("#_sln_booking_amount,#_sln_booking_deposit").on("change", function() {
        var tot = $("#_sln_booking_amount").val();
        var bookingDeposit = $("#_sln_booking_deposit").val();
        $("#_sln_booking_remainedAmount").val(
            (+bookingDeposit > 0.0
                ? tot - bookingDeposit > 0.0
                    ? tot - bookingDeposit
                    : 0
                : 0
            ).toFixed(2)
        );
    });

    $("#_sln_booking_discounts_").on("select2:select", function(evt) {
        var element = evt.params.data.element;
        var $element = $(element);

        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
        sln_calculateTotal();
    });

    $("#_sln_booking_discounts_").on("select2:unselect", function(evt) {
        sln_calculateTotal();
    });

    sln_func_customBookingUser($);
    sln_manageAddNewService($);
    sln_manageCheckServices($);
    if (sln_isShowOnlyBookingElements($)) {
        sln_showOnlyBookingElements($);
    }

    sln_createServiceLineSelect2($);
    $('.sln-booking-service-line').each(function(){
        sln_bindServicesSelects(this);
        sln_bindAttendantSelects(this);
    });
    if(0 == $('.sln-booking-service-line').length){
        $('button[data-collection="addnewserviceline"]').trigger('click');
    }

    function moreDetails() {
        $("#collapseMoreDetails").on("hide.bs.collapse", function() {
            $("#collapseMoreDetails")
                .parent()
                .removeClass("sln-box__collapsewrp--open");
        });
        $("#collapseMoreDetails").on("show.bs.collapse", function() {
            $("#collapseMoreDetails")
                .parent()
                .addClass("sln-box__collapsewrp--open");
        });
    }
    if ($("#collapseMoreDetails").length) {
        moreDetails();
    }
    sln_selectValueFormatting($);
    sln_checkServicesAddedAlert()

    var input = document.querySelector("#_sln_booking_phone");

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
        initialCountry: getCountryCodeByDialCode(
            ($("#_sln_booking_sms_prefix").val() || "").replace("+", "")
        ),
        separateDialCode: true,
        autoHideDialCode: true,
        nationalMode: false,
    });

    input.addEventListener("countrychange", function() {
        if (iti.getSelectedCountryData().dialCode) {
            $('#_sln_booking_sms_prefix').val('+' + iti.getSelectedCountryData().dialCode);
        }
    });

    input.addEventListener("blur", function() {
        if (iti.getSelectedCountryData().dialCode) {
            $(input).val(
                $(input)
                    .val()
                    .replace("+" + iti.getSelectedCountryData().dialCode, "")
            );
        }
    });

    $('#_sln_booking_sms_prefix').on('change', function () {
        iti.setCountry(getCountryCodeByDialCode(($(this).val() || '').replace('+', '')));
    });
});

function sln_selectValueFormatting($) {
    $(
        ".sln-booking-service-line .select2-container--sln .select2-selection__rendered"
    ).each(function() {
        $(this).html(function() {
            if($(this).find('li').length){
                return;
            }
            var value = $(this).closest('.sln-select').find('select').val();
            var text  = $(this).closest('.sln-select').find('select option[value="'+ value +'"]').text()
            if (+value) {
                return (
                    "<span>" +
                        text.replace(/\, /g, "</span> <span>") +
                    " " +
                    "</span>"
                );
            } else {
                return (
                    "<span>" +
                        text.replace(/\, /g, "") +
                    " " +
                    "</span>"
                );
            }
        });
        if($(this).find('li').length){
            return;
        }
        $(this).attr('title', $(this).attr('title').replace(/^\, /g, ""))
    });
}

function sln_isShowOnlyBookingElements($) {
    return $("#salon-step-date").data("mode") === "sln_editor";
}

function sln_showOnlyBookingElements($) {
    $(".wp-toolbar").css("padding-top", "0");
    $("#adminmenuback").hide();
    $("#adminmenuwrap").hide();
    $("#wpcontent").css("margin-left", "0");
    $("#wpadminbar").hide();
    $("#wpbody-content").css("padding-bottom", "0");
    $("#screen-meta").hide();
    $("#screen-meta-links").hide();
    $(".wrap").css("margin-top", "0");
    $("#post").prevAll().hide();
    $("#poststuff").css("padding-top", "0");
    $("#post-body-content").css("margin-bottom", "0");
    $("#postbox-container-1").hide();
    $("#post-body").css("width", "100%");
    $("#message").addClass("sln-box sln-box--main").show();
    $("#message").find("p").css("font-size", "1.3em");
    $("#wpfooter").hide();
    if(window.location.search.split('&').find(el => el.endsWith('duplicate'))){
        $([document.documentElement, document.body])
            .animate({scrollTop: $('#_sln_booking_date').offset().top - 70}, 200);
    }
}

function sln_validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}

function sln_prepareToValidatingBooking($) {
    var form = $(".sln-booking-user-field").closest("form");
    $(form).on("submit", sln_validateBooking);
}

function sln_validateBooking() {
    var $ = jQuery;
    $(".sln-invalid").removeClass("sln-invalid");
    $(".sln-error").remove();
    var hasErrors = false;

    var toValidate = ['select[data-selection="service-selected"]'];
    sln_customer_fields =
        sln_customer_fields !== undefined
            ? sln_customer_fields
            : jQuery("#salon-step-date")
                  .attr("data-customer_fields")
                  .split(",");
    var fields = $("#salon-step-date")
        .attr("data-required_user_fields")
        .split(",");
    $.each(fields, function(k, val) {
        if (val !== "")
            toValidate.push(
                (sln_customer_fields.indexOf(val) !== -1
                    ? "#_sln_"
                    : "#_sln_booking_") + val
            );
    });

    $.each(toValidate, function(k, val) {
        if (val == "#_sln_booking_email" || val == "#_sln_email") {
        } else if (val == 'select[data-selection="service-selected"]') {
            if (!$(".sln-booking-service-line").length) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if ($(val).attr("type") === "checkbox") {
            if (!$(val).is(":checked")) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if ($(val).prop("tagName") === "SELECT") {
            if (!$(val).find("option:selected").length) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if (!$(val).val()) {
            $(val)
                .addClass("sln-invalid")
                .parent()
                .append(
                    '<div class="sln-error error">This field is required</div>'
                );
            if (!hasErrors) $(val).trigger("focus");
            hasErrors = true;
        }
    });
    return !hasErrors;
}

function sln_func_customBookingUser($) {
    $("#sln-update-user-field").select2({
        containerCssClass: "sln-select-rendered",
        dropdownCssClass: "sln-select-dropdown",
        theme: "sln",
        width: "100%",
        placeholder: $("#sln-update-user-field").data("placeholder"),
        language: {
            noResults: function() {
                return $("#sln-update-user-field").data("nomatches");
            },
        },

        ajax: {
            url:
                salon.ajax_url +
                "&action=salon&method=SearchUser&security=" +
                salon.ajax_nonce,
            dataType: "json",
            delay: 250,
            data: function(params) {
                return {
                    s: params.term,
                };
            },
            minimumInputLength: 3,
            processResults: function(data, page) {
                return {
                    results: data.result,
                };
            },
        },
    });

    $("#sln-update-user-field").on("select2:select", function() {
        var message = '<div class="alert alert-loading">Loading</div>';

        var data =
            "&action=salon&method=UpdateUser&s=" +
            $("#sln-update-user-field").val() +
            "&security=" +
            salon.ajax_nonce;
        $("#sln-update-user-message").html(message).fadeIn(500);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                sln_customer_fields =
                    sln_customer_fields !== undefined
                        ? sln_customer_fields
                        : jQuery("#salon-step-date")
                              .attr("data-customer_fields")
                              .split(",");
                if (!data.success) {
                    var alertBox = $('<div class="alert alert-danger"></div>');
                    $(data.errors).each(function() {
                        alertBox.append("<p>").html(this);
                    });
                    $("#sln-update-user-message").html(alertBox).fadeIn(500);
                } else {
                    var alertBox = $(
                        '<div class="alert alert-success">' +
                            data.message +
                            "</div>"
                    );
                    $("#sln-update-user-message").html(alertBox).fadeIn(500);
                    $.each(data.result, function(key, value) {
                        if (key == "id") $("#post_author").val(value);
                        else if(key == 'admin_url'){
                            $('a.sln-customer-url--icon').removeClass('hide').attr('href', value);
                        }
                        else {
                            var el = $(
                                (sln_customer_fields.indexOf(key) === -1
                                    ? "#_sln_booking_"
                                    : "#_sln_") + key
                            );
                            el.is(":checkbox")
                                ? el.prop("checked", value)
                                : el.val(value);
                            if (el.is("select")) {
                                el.trigger("change");
                            }
                        }
                    });
                    $('[name="_sln_booking_createuser"]').attr(
                        "checked",
                        false
                    );
                    if ($("#_sln_booking_sms_prefix").val() == "") {
                        $("#_sln_booking_sms_prefix").val(
                            $("#_sln_booking_default_sms_prefix").val()
                        );
                    }
                    $('#_sln_booking_sms_prefix').trigger('change');
                }
            },
        });
        setTimeout(function() {
            $("#sln-update-user-message").fadeOut(500);
        }, 3000);
        return false;
    });
    $('.sln-booking__customer button[data-collection="reset"]').on(
        "click",
        function(e) {
            e.stopPropagation();
            e.preventDefault();
            $("#sln-update-user-field").val(null).trigger("change");
            $(".sln-booking__customer :input").val("");
            $("#sln-update-user-message").html("");

            $('#_sln_booking_sms_prefix').val($('#_sln_booking_default_sms_prefix').val());
            $('#_sln_booking_sms_prefix').trigger('change');
        }
    );
}

function sln_calculateTotal() {
    var loading =
        '<img src="' +
        salon.loading +
        '" alt="loading .." width="16" height="16" /> ';
    var form = jQuery("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CalcBookingTotal&security=" +
        salon.ajax_nonce;
    jQuery(".sln-calc-total-loading").html(loading);
    jQuery.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            jQuery(".sln-calc-total-loading").html("");
            jQuery("#_sln_booking_amount").val(data.total);
            jQuery("#_sln_booking_deposit").val(data.deposit);
            jQuery("#sln-duration").val(data.duration);
            jQuery(".sln-booking-discounts").remove();
            jQuery("#calculate-total").parent().after(data.discounts);

            jQuery('select[name="_sln_booking[services][]"][disabled]').each(
                function(i, e) {
                    var value = jQuery(e).val();
                    if (typeof data.services[value] !== "undefined") {
                        jQuery(e)
                            .data("select2")
                            .$selection.find(".select2-selection__rendered")
                            .html(data.services[value])
                            .attr("title", data.services[value]);
                    }
                }
            );

            jQuery("#_sln_booking_deposit").trigger("change"); //recalc amount to be paid
        },
    });
    return false;
}

function sln_calculateTotalDuration() {
    var $ = jQuery;
    var duration = 0;
    $(".sln-booking-service-line select[data-duration]").each(function() {
        duration += parseInt($(this).data("duration"));
    });
    var i = duration % 60;
    var h = (duration - i) / 60;
    if (i < 10) {
        i = "0" + i;
    }
    if (h < 10) {
        h = "0" + h;
    }

    $("#sln-duration").val(h + ":" + i);
}

function sln_adminDate($) {
    var items = $("#salon-step-date").data("intervals");
    var doingFunc = false;

    var func = function() {
        if (doingFunc) return;
        setTimeout(function() {
            doingFunc = true;
            $("[data-ymd]").removeClass("disabled");
            $("[data-ymd]").addClass("red");
            $.each(items.dates, function(key, value) {
                $('.day[data-ymd="' + value + '"]').removeClass("red");
            });
            $(".day[data-ymd]").removeClass("full");
            $.each(items.fullDays, function(key, value) {
                console.log(value);
                $('.day[data-ymd="' + value + '"]').addClass("red full");
            });

            $.each(items.times, function(key, value) {
                $('.minute[data-ymd="' + value + '"]').removeClass("red");
            });
            doingFunc = false;
        }, 200);
        return true;
    };
    func();
    $("body").on("sln_date", func);
    var firstValidate = true;

    function validate(obj) {
        var form = $(obj).closest("form");
        var validatingMessage =
            '<div class="alert alert-loading">' +
            salon.txt_validating +
            "</div>";
        var data = form.serialize();
        data += "&action=salon&method=checkDate&security=" + salon.ajax_nonce;
        $("#sln-notifications").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (firstValidate) {
                    $("#sln-notifications").html("").fadeIn(500);
                    firstValidate = false;
                } else if (!data.success) {
                    var alertBox = $('<div class="alert alert-danger"></div>');
                    $(data.errors).each(function() {
                        alertBox.append("<p>").html(this);
                    });
                    $("#sln-notifications")
                        .html("")
                        .append(alertBox)
                        .fadeIn(500);
                } else {
                    $("#sln-notifications")
                        .html("")
                        .append(
                            '<div class="alert alert-success">' +
                                $("#sln-notifications").data("valid-message") +
                                "</div>"
                        )
                        .fadeIn(500);
                    setTimeout(function() {
                        $("#sln-notifications .alert-success").fadeOut(500);
                    }, 3000);
                }
                bindIntervals(data.intervals);
                sln_checkServices($);
            },
        });
    }

    function bindIntervals(intervals) {
        items = intervals;
        func();
    }

    function putOptions(selectElem, value) {
        selectElem.val(value);
    }

    $("#_sln_booking_date, #_sln_booking_time").on("change", function() {
        $('.cloned-data').removeClass('cloned-data');
        $('#sln-booking-cloned-notice').hide();
        $('#save-post').removeAttr('disabled');
        validate(this);
    });
    validate($("#_sln_booking_date"));
    sln_initDatepickers($);
    sln_initTimepickers($);
    sln_initResendNotification();
    sln_initResendPaymentSubmit();
}

function sln_manageAddNewService($) {
    function getNewBookingServiceLineString(serviceId, attendantId) {
        var line = lineItem;
        line = line.replace(/__service_id__/g, serviceId);
        line = line.replace(/__attendant_id__/g, (!Array.isArray(attendantId) ? attendantId : attendantId.join(';')));
        line = line.replace(
            /__service_title__/g,
            servicesData[serviceId].title
        );
        if(!Array.isArray(attendantId)){
            line = line.replace(/__attendant_name__/g, attendantsData[attendantId]);
        }else{
            let attendantsName = '';
            attendantId.forEach(function(attId){
                attendantsName += attendantsData[attId] + ' ';
            });
            line = line.replace(/__attendant_name__/g, attendantsName);
        }
        line = line.replace(
            /__service_price__/g,
            servicesData[serviceId].price
        );
        line = line.replace(
            /__service_duration__/g,
            servicesData[serviceId].duration
        );
        line = line.replace(
            /__service_break_duration__/g,
            servicesData[serviceId].break_duration
        );
        return line;
    }

    $('button[data-collection="addnewserviceline"]').on("click", function(e) {
        e.stopPropagation();
        e.preventDefault();
        var serviceVal = 0;
        if(serviceVal === undefined){
            return;
        }
        $('#save-post').attr('disabled', true);
        var attendantVal = 0;

        $(".sln-booking-service-line label.time").html("");

        var line = getNewBookingServiceLineString(serviceVal, attendantVal);
        $(line).find('select[data-selection="attendant-selected"]').val(attendantVal);
        $(
            ".sln-booking-service-line.sln-booking-service-line-last-added"
        ).removeClass("sln-booking-service-line-last-added");
        line = $(line).addClass("sln-booking-service-line-last-added").get(0);
        $(".sln-booking-service-action").before(line);


        sln_createServiceLineSelect2($);
        sln_bindRemoveBookingsServices();
        sln_bindServicesSelects(line);
        sln_bindAttendantSelects(line);
        $('#sln_booking_services').find('select').each(function(){
            if(0 !== $(this).val().length && $(this).val() != '0'){
                $(this).next().find('.select2-selection').removeClass('select2-selection--single-warning');
            }else{
                $(this).next().find('.select2-selection').addClass('select2-selection--single-warning');
            }
        });
        $(this)
            .removeClass("sln-btn--blink")
        sln_selectValueFormatting($);
        return false;
    });
}
sln_checkServicesAddedAlert();
function sln_checkServices($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckServices&part=allServices&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (!data.success) {
                var alertBox = $('<div class="alert alert-danger"></div>');
                $.each(data.errors, function() {
                    alertBox.append("<p>").html(this);
                });
            } else {
                $("#sln_booking_services").find(".alert").remove();
                sln_processServices($, data.services);
                sln_checkServicesAddedAlert()
            }
        },
    });
}

function sln_checkServices_on_preselection($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckServices&part=allServices&all_services=true&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (data.services) {
                var options_ids = Object.keys(data.services).filter(function(
                    i
                ) {
                    return data.services[i];
                });
                var options = options_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  options_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                var error_ids = Object.keys(data.services).filter(function(i) {
                    return data.services[i].errors.length;
                });
                var elems = error_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  error_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                if (elems)
                    elems
                        .html(function() {
                            $(this).find('.sln-select__wrn').remove()
                            return (
                                $(this).html() +
                                " " +
                                "<span class='sln-select__wrn'>" +
                                sln_customBookingUser.not_available_string +
                                "</span>"
                            );
                        })
                        .parent()
                        .addClass("select2-results__option--unavailable");
            }
        },
    });
}

function sln_checkAttendants_on_preselection($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckAttendants&all_attendants=true&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (data.attendants) {
                var error_ids = Object.keys(data.attendants).filter(function(
                    i
                ) {
                    return data.attendants[i].errors.length;
                });
                var elems = error_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  error_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                if (elems)
                    elems
                        .html(function() {
                            $(this).find('.sln-attendant-wrn').remove()
                            return (
                                $(this).html() + " <span class=\"sln-attendant-wrn\">" + sln_customBookingUser.not_available_string + "</span>"
                            );
                        })
                        .parent()
                        .css({ backgroundColor: "#ffa203", color: "#fff" });
            }
        },
    });
}

function sln_after_selectService($, select){
    let service_id = $(select).val();
    let attendant_select = $(select).closest('.sln-row').find('select[data-selection="attendant-selected"]:not(.hide)');
    let alert = $(select).closest('.sln-row').find('.sln-alert.sln-alert--multiple');
    $(alert).text(servicesData[service_id].countMultipleAttendants + ' ' + $(alert).attr('data-alert'))
    if(servicesData[service_id].isMultipleAttendants){
        $(attendant_select).attr('multiple', true);
        $(attendant_select).select2({
            tags: "true",
            theme: "sln",
            width: "100%",
            placeholder: function () {
                $(this).data("placeholder");
            },
        }).val([]).trigger('change');
        $(alert).removeClass('hide')
    }else{

        $(attendant_select).removeAttr('multiple');
        $(attendant_select).select2({
            theme: "sln",
            tags: "true",
            width: "100%",
            templateResult: function (state) {
                if (!state.id) return state.text;
                return $(
                    '<span data-value="' + state.id + '">' + state.text + "</span>"
                );
            },
            placeholder: function () {
                $(this).data("placeholder");
            },
        });
        $(alert).addClass('hide');
    }
    let save_is_disabled = false;
    $('#sln_booking_services').find('select').each(function(){
        if(0 !== $(this).val().length && ($(this).val() != '0' || $(this).hasClass('hide'))){
            $(this).next().find('.select2-selection').removeClass('select2-selection--single-warning');
            save_is_disabled = save_is_disabled || false;
        }else{
            $(this).next().find('.select2-selection').addClass('select2-selection--single-warning');
            save_is_disabled = save_is_disabled || true;
        }
    });
    if(save_is_disabled){
        $('#save-post').attr('disabled', true);
    }else{
        $('#save-post').removeAttr('disabled');
    }
    sln_selectValueFormatting($);
    sln_checkServicesAddedAlert()
}

function sln_processServices($, services) {
    if (!services) return;
    $('.select2-selection__rendered').removeClass('danger');
    $.each(services, function(index, value) {
        var serviceItem = $("#_sln_booking_service_" + index);
        if (value.status == -1) {
            if(value.attendantErrorsCount){
                serviceItem.closest('.sln-select').next().find('.select2-selection__rendered').addClass('danger');
            }
            if(value.serviceErrorCount){
                serviceItem.closest('.sln-select').find('.select2-selection__rendered').addClass('danger');
            }
            $.each(value.errors, function(index, value) {
                var alertBox = $(
                    '<div class="row col-xs-12 col-sm-12 col-md-12"><div class="' +
                        ($("#salon-step-date").attr("data-m_attendant_enabled")
                            ? "col-md-offset-2 col-md-6"
                            : "col-md-8") +
                        '"><p class="alert alert-danger">' +
                        value +
                        "</p></div></div>"
                );
                serviceItem.parent().parent().next().after(alertBox);
            });
        }
        serviceItem
            .parent()
            .parent()
            .find("label.time:first")
            .html(value.startsAt);
        serviceItem
            .parent()
            .parent()
            .find("label.time:last")
            .html(value.endsAt);
    });
}

function sln_changeServices($, selected){
    let service_data = servicesData[$(selected).val()];
    if(service_data == undefined){
        let attendant_select = $(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]');
        attendant_select.data('service', 0);
        if(!attendant_select.find('option[value="0"]').length)
            attendant_select.append('<option value=0>' + attendantsData[0] + '</option>');
        attendant_select.val(0).trigger('change');
        return;
    }
    if(typeof service_data.isAttendantsEnabled !== 'undefined' && !service_data.isAttendantsEnabled){
        let attendant_select = $(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]');
        attendant_select.addClass('hide');
        attendant_select.siblings('.select2').addClass('hide');
        attendant_select.closest('.sln-select').find('.sln-no-attendant-required').removeClass('hide');
    } else {
        let attendant_select = $(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]');
        attendant_select.removeClass('hide');
        attendant_select.siblings('.select2').removeClass('hide');
        attendant_select.closest('.sln-select').find('.sln-no-attendant-required').addClass('hide');
    }
    if (!+$(selected).val()) {
        $(selected).closest('.sln-row').find('.time').html('')
    }
    let s_id = $(selected).val();
    let is_exist = 0;
    $(selected).closest('#sln_booking_services').find('select[data-selection="service-selected"]').each(function(index, element){
        if($(element).val() == s_id){
            is_exist += 1;
        }
    });
    if(is_exist == 2){
        selected
        setTimeout(function(){
            $(selected).data('select2').open();
        }, 1);

        let attendant_select = $(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]:not(.hide)');
        attendant_select.data('service', 0);
        attendant_select.find('option').not('option[value="0"]').remove()
        return false;
    }

    $(selected).find("select2-selection__rendered").html(function(){
        return "<span>" + $(selected).text().replace(/\, /g, "</span><span>") + " </span>";
    });
    let attendant_select = $(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]:not(.hide)');
    attendant_select.find('option').remove();
    attendant_select.data('service', s_id);
    attendant_select.attr('name', '_sln_booking[attendants][' + s_id + ']' + (service_data.isMultipleAttendants ? '[]': ''));
    let html = !service_data.isMultipleAttendants ? '<option value="' + 0 + '">' + attendantsData[0] + "</option>" : '';
    attendant_select.append(html);
    $('form input[name="_sln_booking_service_select"]').remove()
    $('form').append('<input name="_sln_booking_service_select" value="'+ s_id +'" type="hidden">')
    sln_checkAttendants_on_preselection($);
    let inputs = $(selected).parent();
    inputs.find('input').remove();
    inputs.append('<input type="hidden" name="_sln_booking[service]['+ s_id +']" id="_sln_booking_service_'+ s_id +'" value="'+ s_id +'" class="sln-input sln-input--text">'+
    '<input type="hidden" name="_sln_booking[price]['+ s_id +']" id="_sln_booking_price_'+ s_id +'" value="'+ service_data.price +'" class="sln-input sln-input--text">'+
    '<input type="hidden" name="_sln_booking[duration]['+ s_id +']" id="_sln_booking_duration_'+ s_id +'" value="'+ service_data.duration +'" class="sln-input sln-input--text">'+
    '<input type="hidden" name="_sln_booking[break_duration]['+ s_id +']" id="_sln_booking_break_duration_'+ s_id +'" value="'+ service_data.break_duration +'" class="sln-input sln-input--text"></input>'
    );
    if(!attendant_select.length){
        sln_checkServices($);
        $(selected).closest('.sln-row').find('.sln-alert--onremove').removeClass('hide');
    }
    sln_calculateTotal();
    sln_calculateTotalDuration()
}

function sln_manageCheckServices($) {
    if (typeof servicesData == "string") {
        servicesData = JSON.parse(servicesData);
    }
    if (typeof attendantsData == "string") {
        attendantsData = JSON.parse(attendantsData);
    }

    sln_bindRemoveBookingsServices();
}

function sln_bindRemoveBookingsServices() {
    function sln_bindRemoveBookingsServicesFunction() {
        sln_calculateTotal();
        if (jQuery('select[data-selection="service-selected"]').length) {
            sln_checkServices(jQuery);
        }
        let save_is_disabled = false;
        $('#sln_booking_services').find('select').each(function(){
            if(0 !== $(this).val().length && ($(this).val() != '0' || $(this).hasClass('hide'))){
                $(this).next().find('.select2-selection').removeClass('select2-selection--single-warning');
                save_is_disabled = save_is_disabled || false;
            }else{
                $(this).next().find('.select2-selection').addClass('select2-selection--single-warning');
                save_is_disabled = save_is_disabled || true;
            }
        });
        if(save_is_disabled){
            $('#save-post').attr('disabled', true);
        }else{
            $('#save-post').removeAttr('disabled');
        }
        sln_checkServicesAddedAlert()
        return false;
    }

    sln_bindRemove();
    jQuery('button[data-collection="remove"]')
        .off("click", sln_bindRemoveBookingsServicesFunction)
        .on("click", sln_bindRemoveBookingsServicesFunction);
}
function sln_bindServicesSelects(line){
    $ = jQuery;
    $(line).find('select[data-selection="service-selected"]').on("select2:open", function() {
        $(this).closest('.sln-row').find('.sln-alert--onremove').addClass('hide');
        sln_checkServices_on_preselection($);
    });
    $(line).find('select[data-selection="service-selected"]').on("change", function(){
        sln_changeServices($, this);
    });
    sln_checkRequiredAssistant($(line).find('select[data-selection="service-selected"]'))
    $(line).find('select[data-selection="service-selected"]').on('select2:closing', function(){
        sln_after_selectService($, this);
    });
    $(line).find('.sln-booking-service--move-line').on('mousedown', function(event){
        let element = $(this).closest('.sln-booking-service-line');
        $(element).toggleClass('sln-booking-service-line--move', true);
        let pos = event.pageY;
        $(element).parent().on('mousemove', function(e){
            if(element.position().top > element.parent().position().top
                && element.position().top + element.height() < element.parent().position().top + element.parent().height()){
                $(element).animate({'top': ((e.pageY - pos) - 3) + 'px'}, 0);
                pos = event.pageY;
            }else{
                element.parent().off('mousemove');
                element.removeAttr('style');
                element.toggleClass('sln-booking-service-line--move', false);
            }
        });
    });
    $(line).parent().off('mouseup').on('mouseup', function(){
        let element = $('#sln_booking-details .sln-booking-service-line.sln-booking-service-line--move');
        element.parent().off('mousemove');
        element.parent().find('.sln-booking-service-line').each(function(iter, elem){
            if($(elem).hasClass('sln-booking-service-line--move')){
                return true;
            }
            if(iter == 0 && element.position().top < $(elem).position().top + $(elem).height()){
                $(element).insertBefore($(elem));
                $(element.next()).insertBefore($(elem));
                let time = element.find('.time:first').text();
                if(element.find('.time').length){
                    swap_time(element, $(elem));
                }
                return false;
            }else if(element.position().top > $(elem).position().top && element.position().top < $(elem).position().top + $(elem).height()){
                $(element.next()).insertAfter($(elem));
                $(element).insertAfter($(elem));
                if(element.find('.time').length){
                    swap_time(element, $(elem));
                }
                return false;
            }else if(iter + 1 == element.parent().find('.sln-booking-service-line').length && element.position().top > $(elem).position().top){
                $(element.next()).insertAfter($(elem));
                $(element).insertAfter($(elem));
                if(element.find('.time').length){
                    swap_time(element, $(elem));
                }
                return false;
            }

        });
        setTimeout(function(){
            $(element).removeAttr('style');
            $(element).toggleClass('sln-booking-service-line--move', false);
        }, 2);

    });
    function swap_time(first_elem, second_elem){
        let time = first_elem.find('.time:first').text();
        first_elem.find('.time:first').text(second_elem.find('.time:first').text());
        second_elem.find('.time:first').text(time);
        time = first_elem.find('.time:last').text();
        first_elem.find('.time:last').text(second_elem.find('.time:last').text());
        second_elem.find('.time:last').text(time);
    }
}

function sln_bindAttendantSelects(line) {
    $ = jQuery;
    function bindChangeAttendantSelectsFunction() {
       // sln_checkServices(jQuery);
        sln_calculateTotal();
        let save_is_disabled = false;
        $('#sln_booking_services').find('select').each(function(){
            if(0 !== $(this).val().length && $(this).val() != '0'){
                $(this).next().find('.select2-selection').removeClass('select2-selection--single-warning');
                save_is_disabled = save_is_disabled || false;
            }else{
                $(this).next().find('.select2-selection').addClass('select2-selection--single-warning');
                save_is_disabled = save_is_disabled || true;
            }
        });
        var service_data = servicesData[$(this).closest('.sln-row').find('[data-selection="attendant-selected"]').data('service')]
        if (+service_data.isMultipleAttendants) {
            if (Array.isArray($(this).val()) && +service_data.countMultipleAttendants === $(this).val().filter(item => item).length) {
                sln_checkServices($);
                $(this).closest('.sln-row').find('.sln-alert--onremove').removeClass('hide');
            } else {
                $(this).closest('.sln-row').find('.sln-alert--onremove').addClass('hide');
            }
        } else {
            if (+$(this).val()) {
                sln_checkServices($);
                $(this).closest('.sln-row').find('.sln-alert--onremove').removeClass('hide');
            } else {
                $(this).closest('.sln-row').find('.sln-alert--onremove').addClass('hide');
            }
        }
        if(save_is_disabled){
            $('#save-post').attr('disabled', true);
        }else{
            $('#save-post').removeAttr('disabled');
        }
        sln_checkServicesAddedAlert()
    }

    jQuery(line).find("select[data-attendant]")
        .off("change", bindChangeAttendantSelectsFunction)
        .on("change", bindChangeAttendantSelectsFunction);
    $(line).find('select[data-selection="attendant-selected"]').on('select2:open', function(){
        $('.select2-results__option').addClass('select2-results__option--stl');
        $('form input[name="_sln_booking_service_select"]').remove()
        $('form').append('<input name="_sln_booking_service_select" value="'+ $(this).data('service') +'" type="hidden">')
        sln_checkAttendants_on_preselection($);
        let service = servicesData[$(this).data('service')];
        if(service == undefined){
            service = {'attendants': [0]}
        }
        if($(this).find('option').not('option[value="0"]').length != service.attendants.length){
            let $this = $(this);
            if(service.attendants != undefined){
                $.each(service.attendants, function(index, val){
                    if($this.find('option[value="' + val + '"]').length){
                        return true;
                    }
                    let html = '<option value="' + val + '">' + attendantsData[val] + "</option>";
                    $this.append(html);
                });
            }
        }
    }).on('change', function(){
        let serviceVal = $(this).data('service');
        let attendantVal = $(this).val();
        let alert = $(this).closest('.sln-select').find('.sln-alert.sln-alert--multiple');
        $(alert).text(servicesData[serviceVal].countMultipleAttendants + ' ' + $(alert).attr('data-alert'))
        if(
            Array.isArray(attendantVal) &&
            servicesData[serviceVal]['isMultipleAttendants'] &&
            servicesData[serviceVal]['countMultipleAttendants'] !== attendantVal.length
        ){
            $(alert).removeClass('hide');
        }else{
            $(alert).addClass('hide');
        }
    })
}

function sln_initResendNotification() {
    var $ = jQuery;
    $("#resend-notification-submit").on("click", function() {
        var data =
            "post_id=" +
            $("#post_ID").val() +
            "&emailto=" +
            $("#resend-notification").val() +
            "&message=" +
            $("#resend-notification-text").val() +
            "&action=salon&method=ResendNotification&security=" +
            salon.ajax_nonce +
            "&" +
            $.param(salonCustomBookingUser.resend_notification_params);
        var validatingMessage =
            '<img src="' +
            salon.loading +
            '" alt="loading .." width="16" height="16" /> ';
        $("#resend-notification-message").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (data.success)
                    $("#resend-notification-message").html(
                        '<div class="alert alert-success">' +
                            data.success +
                            "</div>"
                    );
                else if (data.error)
                    $("#resend-notification-message").html(
                        '<div class="alert alert-danger">' +
                            data.error +
                            "</div>"
                    );
            },
        });
        return false;
    });
}

function sln_initResendPaymentSubmit() {
    var $ = jQuery;
    $("#resend-payment-submit").on("click", function() {
        var data =
            "post_id=" +
            $("#post_ID").val() +
            "&emailto=" +
            $("#resend-payment").val() +
            "&action=salon&method=ResendPaymentNotification&security=" +
            salon.ajax_nonce +
            "&" +
            $.param(salonCustomBookingUser.resend_payment_params);
        var validatingMessage =
            '<img src="' +
            salon.loading +
            '" alt="loading .." width="16" height="16" /> ';
        $("#resend-payment-message").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (data.success)
                    $("#resend-payment-message").html(
                        '<div class="alert alert-success">' +
                            data.success +
                            "</div>"
                    );
                else if (data.error)
                    $("#resend-payment-message").html(
                        '<div class="alert alert-danger">' +
                            data.error +
                            "</div>"
                    );
            },
        });
        return false;
    });
}

function sln_checkServicesAddedAlert() {
    var found = false
    jQuery(".sln-booking-service-line").each(function () {
        var service_id       = +jQuery(this).find('select[name^="_sln_booking[services]"]').val();
        var service_data     = servicesData[service_id]
        var attendant_select = jQuery(this).find('select[name^="_sln_booking[attendants]"]');
        if (+service_data.isMultipleAttendants) {
            if (service_id && (Array.isArray(attendant_select.val()) && +service_data.countMultipleAttendants === attendant_select.val().filter(item => item).length || attendant_select.hasClass('hide') || attendant_select.length === 0)) {
                found = true
            }
        } else {
            if (service_id && (+attendant_select.val() || attendant_select.hasClass('hide') || attendant_select.length === 0)) {
                found = true
            }
        }
    })
    if (!found) {
        jQuery("#sln-alert-noservices").fadeIn();
    } else {
        jQuery("#sln-alert-noservices").fadeOut();
    }
}

function sln_checkRequiredAssistant(selected) {
    let service_data = servicesData[jQuery(selected).val()];
    if(typeof service_data.isAttendantsEnabled !== 'undefined' && !service_data.isAttendantsEnabled){
        let attendant_select = jQuery(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]');
        attendant_select.addClass('hide');
        attendant_select.siblings('.select2').addClass('hide');
        attendant_select.closest('.sln-select').find('.sln-no-attendant-required').removeClass('hide');
    } else {
        let attendant_select = jQuery(selected).closest('.sln-row').find('select[data-selection="attendant-selected"]');
        attendant_select.removeClass('hide');
        attendant_select.siblings('.select2').removeClass('hide');
        attendant_select.closest('.sln-select').find('.sln-no-attendant-required').addClass('hide');
    }
}