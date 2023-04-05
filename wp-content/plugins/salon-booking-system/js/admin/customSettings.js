"use strict";

jQuery(function ($) {
    if ($('.sln-panel').length) {
        sln_initSlnPanel($);
    }

    sln_settingsLogo($);
    sln_settingsPayment($);
    sln_settingsCheckout($);
    sln_settingsGeneral($);
});

function sln_settingsLogo($) {
    $("[data-action=select-logo]").on("click", function() {
        $("#" + $(this).attr("data-target")).trigger("click");
    });

    $("[data-action=select-file-logo]").on("change", function() {
        $(this)
            .closest("form")
            .find("input:first")
            .trigger("click");
    });

    $("[data-action=delete-logo]").on("click", function() {
        $("#" + $(this).attr("data-target-reset")).val("");
        $("#" + $(this).attr("data-target-show")).removeClass("hide");
        $("#" + $(this).attr("data-target-remove")).remove();
    });
}

function sln_settingsPayment($) {

    $('input.sln-pay_method-radio').on('change', function () {
        $('.payment-mode-data').hide().removeClass('sln-box--fadein');
        $('#payment-mode-' + $(this).data('method')).show().addClass('sln-box--fadein');
    });

    $('#salon_settings_pay_enabled').on('change', function(){
        if($(this).is(':checked') && !$('#salon_settings_pay_offset_enabled').is(':checked')){
            $('#sln-create_booking_after_pay').removeClass('hide');
        }else{
            $('#sln-create_booking_after_pay').addClass('hide');
        }
        $('#salon_settings_create_booking_after_pay').removeAttr('checked');
    });

    $('#salon_settings_pay_offset_enabled').on('change', function(){
        if(!$(this).is(':checked') && $('#salon_settings_pay_enabled').is(':checked')){
            $('#sln-create_booking_after_pay').removeClass('hide');
        }else{
            $('#sln-create_booking_after_pay').addClass('hide');
        }
        $('#salon_settings_create_booking_after_pay').removeAttr('checked');
    });

    $("#salon_settings_pay_method")
        .on("change", function() {
            $(".payment-mode-data").hide();
            $("#payment-mode-" + $(this).val()).show();
        })
        .trigger("change");

    $("input.sln-pay_method-radio").each(function() {
        if ($(this).is(":checked")) {
            $("#payment-mode-" + $(this).data("method"))
                .show()
                .addClass("sln-box--fadein");
        }
    });

    $("#salon_settings_pay_deposit")
        .on("change", function() {
            var current = $(this).val();
            var expected = $("#salon_settings_pay_deposit_fixed_amount").data(
                "relate-to"
            );
            $("#salon_settings_pay_deposit_fixed_amount").attr(
                "disabled",
                current === expected ? false : "disabled"
            );
        })
        .trigger("change");
}

function sln_settingsCheckout($) {
    $("#salon_settings_enabled_force_guest_checkout")
        .on("change", function() {
            if ($(this).is(":checked")) {
                $("#salon_settings_enabled_guest_checkout")
                    .attr("checked", "checked")
                    .trigger("change");
            }
        })
        .trigger("change");
    // $("#salon_settings_primary_services_count").on("change", function() {
    //     if (+$(this).val()) {
    //         $("#salon_settings_is_services_count_primary_services")
    //             .closest(".row")
    //             .removeClass("hide");
    //     } else {
    //         $("#salon_settings_is_services_count_primary_services")
    //             .closest(".row")
    //             .addClass("hide");
    //         $("#salon_settings_is_services_count_primary_services").prop(
    //             "checked",
    //             false
    //         );
    //     }
    // });
    $("#salon_settings_secondary_services_count").on("change", function() {
        if (+$(this).val()) {
            $("#salon_settings_is_secondary_services_selection_required")
                .closest(".row")
                .removeClass("hide");
        } else {
            $("#salon_settings_is_secondary_services_selection_required")
                .closest(".row")
                .addClass("hide");
            $("#salon_settings_is_secondary_services_selection_required").prop(
                "checked",
                false
            );
        }
    });
}

function sln_settingsGeneral($) {
    if(!window.location.search.endsWith('salon-settings') && !window.location.search.endsWith('tab=general')){
        return;
    }
    $("#salon_settings_m_attendant_enabled")
        .on("change", function() {
            if ($(this).is(":checked")) {
                $("#salon_settings_attendant_enabled")
                    .attr("checked", "checked")
                    .trigger("change");
            }
        })
        .trigger("change");

    $("#salon_settings_follow_up_interval")
        .on("change", function() {
            $("#salon_settings_follow_up_interval_custom_hint").css(
                "display",
                $(this).val() === "custom" ? "" : "none"
            );
            $("#salon_settings_follow_up_interval_hint").css(
                "display",
                $(this).val() !== "custom" ? "" : "none"
            );
        })
        .trigger("change");

    $("#salon_settings_sms_provider")
        .on("change", function() {
            $(".sms-provider-data")
                .hide()
                .removeClass("sln-box--fadein");
            if (
                $("#sms-provider-" + $(this).val())
                    .html()
                    .trim() !== ""
            ) {
                $("#sms-provider-" + $(this).val())
                    .show()
                    .addClass("sln-box--fadein");
            } else {
                $("#sms-provider-default")
                    .show()
                    .addClass("sln-box--fadein");
            }
        })
        .trigger("change");

    $("#salon_settings_google_maps_api_key").on("change", function() {
        var successCallback = function() {
            var service = new google.maps.places.AutocompleteService();

            service.getQueryPredictions({ input: "pizza near Syd" }, function(
                predictions,
                status
            ) {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    $('#salon_settings_google_maps_api_key_valid').val('1');
                } else if (status === google.maps.places.PlacesServiceStatus.REQUEST_DENIED) {
                    $('#salon_settings_google_maps_api_key_valid').val('0');
                } else {
                    $('#salon_settings_google_maps_api_key_valid').val('0');
                }
            });
        };

        var errorCallback = function() {
            $("#salon_settings_google_maps_api_key_valid").val("0");
        };

        document
            .querySelectorAll('script[src*="maps.google"]')
            .forEach((script) => {
                script.remove();
            });

        if (!$(this).val()) {
            return;
        }

        if (typeof google === "object") {
            google.maps = false;
        }

        window.gm_authFailure = errorCallback;

        var scriptTag = document.createElement("script");
        scriptTag.src =
            "https://maps.googleapis.com/maps/api/js?key=" +
            $(this).val() +
            "&libraries=places&language=en";

        scriptTag.onload = successCallback;
        scriptTag.onreadystatechange = successCallback;
        scriptTag.async = true;
        scriptTag.defer = true;

        document.body.appendChild(scriptTag);
    });

    var input = document.querySelector("#salon_settings_sms_prefix");

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
        initialCountry: getCountryCodeByDialCode(($(input).val() || '').replace('+', '')),
    });

    input.addEventListener("countrychange", function() {
        if (iti.getSelectedCountryData().dialCode) {
            $(input).val('+' + iti.getSelectedCountryData().dialCode);
        }
    });
}


function sln_initSlnPanel($) {
    $('.sln-panel .collapse').on('shown.bs.collapse', function () {
        $(this).parent().find('.sln-paneltrigger').addClass('sln-btn--active');
        $(this).parent().addClass('sln-panel--active');
    }).on('hide.bs.collapse', function () {
        $(this).parent().find('.sln-paneltrigger').removeClass('sln-btn--active');
        $(this).parent().removeClass('sln-panel--active');
    });
    $('.sln-panel--oncheck .sln-panel-heading input:checkbox').on('change', function () {
        if ($(this).is(':checked')) {
            $(this).parent().parent().parent().find('.sln-paneltrigger').removeClass('sln-btn--disabled');
        } else {
            $(this).parent().parent().parent().find('.sln-paneltrigger').addClass('sln-btn--disabled');
            $(this).parent().parent().parent().find('.collapse').collapse('hide');
        }
    });
    $(".sln-panel--oncheck .sln-panel-heading input").each(function() {
        if ($(this).is(":checked")) {
            $(this)
                .parent()
                .parent()
                .parent()
                .find(".sln-paneltrigger")
                .removeClass("sln-btn--disabled");
        } else {
            $(this)
                .parent()
                .parent()
                .parent()
                .find(".sln-paneltrigger")
                .addClass("sln-btn--disabled");
        }
    });
}
