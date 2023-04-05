"use strict";

Number.prototype.formatMoney = function(c, d, t) {
    var n = this,
        c = isNaN((c = Math.abs(c))) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt((n = Math.abs(+n || 0).toFixed(c))) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return (
        s +
        (j ? i.substr(0, j) + t : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
        (c
            ? d +
              Math.abs(n - i)
                  .toFixed(c)
                  .slice(2)
            : "")
    );
};

jQuery(function($) {
    sln_init($);
    if (salon.has_stockholm_transition == "yes") {
        $("body").on(
            "click",
            'a[target!="_blank"]:not(.no_ajax):not(.no_link)',
            function() {
                setTimeout(function() {
                    sln_init(jQuery);
                }, 2000);
            }
        );
    }
});

function sln_init($) {
    if ($("#salon-step-services").length || $("#salon-step-secondary").length) {
        let request_args = window.location.search.split('&');
        if(! request_args.find(val => val.startsWith('submit_services'))
            && ! request_args.find(val => val.startsWith('save_selected'))
            && $("#salon-step-services").length
            && !$('.sln-icon--back').length){
            $('#salon-step-services input[type="checkbox"]').removeAttr('checked');
            $('.sln-service-variable-duration--counter--value').text(1);
            $('.sln-service-count-input').val(1);
            if(!$('.sln-checkbox.is-checked').length){
                $('#sln-step-submit').parent().hide();
            }
        }
        if($("#salon-step-services").length){
            $('.sln-service-variable-duration--counter--minus').addClass('sln-service-variable-duration--counter--button--disabled');
        }
        request_args = request_args.filter(item =>  !item.startsWith('save_selected'))
        window.history.replaceState({}, document.title, window.location.pathname + request_args.join('&'))
        sln_serviceTotal($);
    }
    if($('#salon-step-attendant').length){
        sln_attendantTotal($);
        // sln_stepAttendant($);
    }
    if ($("#salon-step-date").length) {
        sln_stepDate($);
    } else {
        if ($("#salon-step-details").length) {
            $("a.tec-link").on("click", function(e) {
                e.preventDefault();
                var href = $(this).attr("href");
                var locHref = window.location.href;
                var hrefGlue = href.indexOf("?") == -1 ? "?" : "&";
                var locHrefGlue = locHref.indexOf("?") == -1 ? "?" : "&";
                window.location.href =
                    href +
                    hrefGlue +
                    "redirect_to=" +
                    encodeURI(locHref + locHrefGlue + "sln_step_page=details");
            });
        }
        if ($('[data-salon-click="fb_login"]').length) {
            if (window.fbAsyncInit === undefined) {
                if (salon.fb_app_id !== undefined) {
                    sln_facebookInit();
                } else {
                    jQuery("[data-salon-click=fb_login]").remove();
                }
            } else {
                jQuery("[data-salon-click=fb_login]")
                    .off("click")
                    .on("click", function() {
                        FB.login(
                            function() {
                                sln_facebookLogin();
                            },
                            { scope: "email" }
                        );

                        return false;
                    });
            }
        }
        $('[data-salon-toggle="next"]').on("click", function(e) {
            var form = $(this).closest("form");
            $(
                "#sln-salon input.sln-invalid,#sln-salon textarea.sln-invalid,#sln-salon select.sln-invalid"
            ).removeClass("sln-invalid");
            if (form[0].checkValidity()) {
                let form_data = null;
                if(form.attr('enctype') == 'multipart/form-data'){
                    form_data = new FormData(form[0]);
                    let sln_data = $(this).data('salon-data').split('&');
                    for(let i = 0; i < sln_data.length; i++){
                        form_data.append(sln_data[i].split('=')[0], sln_data[i].split('=')[1]);
                    }
                }else{
                    form_data = form.serialize() + "&" + $(this).data("salon-data")
                }
                
                sln_loadStep(
                    $,
                    form_data
                );
            } else {
                $(
                    "#sln-salon input:invalid,#sln-salon textarea:invalid,#sln-salon select:invalid"
                )
                    .addClass("sln-invalid")
                    .attr("placeholder", salon.checkout_field_placeholder);
                $(
                    "#sln-salon input:invalid,#sln-salon textarea:invalid,#sln-salon select:invalid"
                )
                    .parent()
                    .addClass("sln-invalid-p")
                    .attr("data-invtext", salon.checkout_field_placeholder);
            }
            chooseAsistentForMe = undefined;
            return false;
        });
    }
    $('[data-salon-toggle="direct"]').on("click", function(e) {
        e.preventDefault();
        var form = $(this).closest("form");
        sln_loadStep($, form.serialize() + "&" + $(this).data("salon-data"));
        chooseAsistentForMe = '0';
        return false;
    });

    $('.sln-input-file input[type=file]').on('change', function(e){
        let file_list = $(this).parent().find('.sln-input-file--list');
        if(!file_list.children().length){
            $(this).parent().find('label:last-child').text(' ').addClass('sln-input-file--select')
        }
        $('.sln-file-errors').remove();
        $('.sln-file-progress-bar-wrapper').remove();

        var formData = new FormData();
        formData.append('action', 'salon')
        formData.append('method', 'UploadFile')
        formData.append('security', salon.ajax_nonce)
        formData.append('file', this.files[0])

        let file_name = this.files[0].name;
        this.files = undefined;
        this.value = ''

        var self = this
        file_list.append($('<li class="sln-file-progress-bar-wrapper"><div class="sln-file-progress-bar"><div class="sln-file-progress-bar--value"></div></div></li>'));

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                      var percentComplete = evt.loaded / evt.total;
                      percentComplete = parseInt(percentComplete * 100);
                      console.log(percentComplete);

                      $('.sln-file-progress-bar--value').css('width', percentComplete + '%')

                      if (percentComplete === 100) {

                      }

                    }
                  }, false);

                return xhr;
            },
            url: salon.ajax_url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(result) {
                $('.sln-file-progress-bar-wrapper').remove();
                if (result.success) {
                    let input_file = '<input type="hidden" name="'+ $(self).attr('name') +'" value="'+ result.file +'">';
                    file_list.append($('<li>' + file_name +  '<span class="sln-remove-file sln-icon-close">x</span></li>').append(input_file));
                    file_list.children().last().find('.sln-remove-file').on('click', function(e){
                        e.stopPropagation();
                        var self = this
                        $.post(salon.ajax_url, {action: 'salon', method: 'RemoveUploadedFile', security: salon.ajax_nonce, file: result.file}, function () {
                            $(self).closest('li').remove();
                        })
                    });
                } else {
                    file_list.append($('<li class="sln-file-errors">' + result.errors.join(',') +  '</li>'));
                }
            }
        });

    });

    // CHECKBOXES
    $("#sln-salon input:checkbox").each(function() {
        $(this).on("change", function() {
            if ($(this).is(":checked")) {
                $(this)
                    .parent()
                    .addClass("is-checked");
            } else {
                $(this)
                    .parent()
                    .removeClass("is-checked");
            }
            if(!$('.sln-checkbox.is-checked').length && $("#salon-step-services").length){
                $('#sln-step-submit').parent().hide();
            }else{
                $('#sln-step-submit').parent().show();
            }
        });
    });
    // RADIOBOXES
    $("#sln-salon input:radio").each(function() {
        $(this).on("click", function() {
            var name = jQuery(this).attr("name");
            jQuery(".is-checked").each(function() {
                if (
                    jQuery(this)
                        .find("input")
                        .attr("name") == name
                ) {
                    $(this).removeClass("is-checked");
                }
            });
            $(this)
                .parent()
                .toggleClass("is-checked");
        });
    });

    $('.sln-icon-sort').on('click', function(){
        let sorted_attendants = [];
        let $this = $(this);
        $this.addClass('active');
        if($this.hasClass('sln-icon-sort--down')){
            $this.closest('.row').find('.sln-icon-sort--up').removeClass('active');
            $this.closest('.sln-attendant-list').find('label').each(function(ind, attendant){
                if($(attendant).find('input').val() == 0 || $(attendant).find('input').val() == undefined){
                    return true;
                }
                if($(attendant).hasClass('DEC') || !$(attendant).hasClass('INC')){
                    return false;
                }
                $(attendant).addClass('DEC').removeClass('INC');
                sorted_attendants.push($(attendant).clone());
                $(attendant).remove();
            });
        }
        if($this.hasClass('sln-icon-sort--up')){
            $this.closest('.row').find('.sln-icon-sort--down').removeClass('active');
            $this.closest('.sln-attendant-list').find('label').each(function(ind, attendant){
                if($(attendant).find('input').val() == 0 || $(attendant).find('input').val() == undefined){
                    return true;
                }
                if($(attendant).hasClass('INC')){
                    return false;
                }
                $(attendant).addClass('INC').removeClass('DEC');
                sorted_attendants.push($(attendant).clone());
                $(attendant).remove();
            });
        }
        if(sorted_attendants.length){
            sorted_attendants = sorted_attendants.reverse();
            sorted_attendants.forEach(function(el){
                $this.closest('.sln-attendant-list').append(el);
                $(el).on("change", function() {
                    evalTot();
                }).on("click", function() {
                    var name = jQuery(this).attr("name");
                    jQuery(".is-checked").each(function() {
                        if (
                            jQuery(this)
                                .find("input")
                                .attr("name") == name
                        ) {
                            $(this).removeClass("is-checked");
                        }
                    });
                    $(this)
                        .parent()
                        .toggleClass("is-checked");
                });
            });
        }
    });

    $(".sln-edit-text").on("change", function() {
        var data =
            "key=" +
            $(this).attr("id") +
            "&value=" +
            $(this).val() +
            "&action=salon&method=SetCustomText&security=" +
            salon.ajax_nonce;
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {},
            error: function(data) {
                alert("error");
                console.log(data);
            },
        });
        return false;
    });

    $("div.editable").on("click", function() {
        var self = $(this);
        self.addClass("focus");
        var text = self.find(".text");
        var input = self.find("input");
        input.val(text.text().trim()).trigger("focus");
    });

    $("div.editable .input input").on("blur", function() {
        var self = $(this);
        var div = self.closest(".editable");
        div.removeClass("focus");
        var text = div.find(".text");
        text.html(self.val());
    });

    $("#sln_no_user_account")
        .on("change", function() {
            if ($(this).is(":checked")) {
                $("#sln_password")
                    .attr("disabled", "disabled")
                    .parent()
                    .css("display", "none");
                $("#sln_password_confirm")
                    .attr("disabled", "disabled")
                    .parent()
                    .css("display", "none");
                $(".sln-customer-fields").hide();
                $(this)
                    .closest("form")
                    .addClass("sln-guest-checkout-form");
            } else {
                $("#sln_password")
                    .attr("disabled", false)
                    .parent()
                    .css("display", "block");
                $("#sln_password_confirm")
                    .attr("disabled", false)
                    .parent()
                    .css("display", "block");
                $(".sln-customer-fields").show();
                $(this)
                    .closest("form")
                    .removeClass("sln-guest-checkout-form");
            }
        })
        .trigger("change");

    sln_createRatings(true, "star");

    if (typeof sln_createSelect2Full !== "undefined") {
        sln_createSelect2Full($);
    }
    sln_salonBookingCalendarInit();

    $(".sln-help-button").on("click", function() {
        window.Beacon("toggle");
        return false;
    });

    setTimeout(function() {
        $(".sln-service-list .sln-panel-heading").each(function() {
            $(this).replaceWith($(this).clone());
        });
    }, 0);

    var input = document.querySelector("#sln_phone");

    if (input && $("#sln_sms_prefix").length) {
        function getCountryCodeByDialCode(dialCode) {
            var countryData = window.intlTelInputGlobals.getCountryData();
            var countryCode = "";
            countryData.forEach(function(data) {
                if (data.dialCode == dialCode) {
                    countryCode = data.iso2;
                }
            });
            return countryCode;
        }

        var iti = window.intlTelInput(input, {
            initialCountry: getCountryCodeByDialCode(
                ($("#sln_sms_prefix").val() || "").replace("+", "")
            ),
            separateDialCode: true,
            autoHideDialCode: true,
            nationalMode: false,
        });

        input.addEventListener("countrychange", function() {
            if (iti.getSelectedCountryData().dialCode) {
                $("#sln_sms_prefix").val(
                    "+" + iti.getSelectedCountryData().dialCode
                );
            }
        });
        input.addEventListener("blur", function() {
            if (iti.getSelectedCountryData().dialCode) {
                $(input).val($(input).val().replace("+" + iti.getSelectedCountryData().dialCode, ""));
            }
        });
    }

    sln_google_maps_places_api_callback();

    $('input[name="sln[customer_timezone]"]').val(new window.Intl.DateTimeFormat().resolvedOptions().timeZone);

    $('.sln-service-variable-duration--counter--plus').on('click', function () {

        var checkbox = $(this).closest('.sln-service').find('input[type="checkbox"][name*="sln[services]"]');

        if ($(this).hasClass('sln-service-variable-duration--counter--button--disabled')  || checkbox.prop('disabled')) {
            return false;
        }

        var counter = $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--value');

        counter.html(+counter.text().trim() + 1);

        if (+counter.text().trim() > 1) {
            $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--minus').removeClass('sln-service-variable-duration--counter--button--disabled');
        }

        if (!checkbox.prop('checked')) {
            checkbox.prop('checked', true)
            checkServices();
        }

        $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-count-input').val(+counter.text().trim())

        evalTot()

        if (+counter.text().trim() >= +$(this).closest('.sln-service-variable-duration').data('unitsPerSession')) {
            $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--plus').addClass('sln-service-variable-duration--counter--button--disabled');
        }

        return false;
    })

    $('.sln-service-variable-duration--counter--minus').on('click', function () {

        var checkbox = $(this).closest('.sln-service').find('input[type="checkbox"][name*="sln[services]"]');

        if ($(this).hasClass('sln-service-variable-duration--counter--button--disabled') || checkbox.prop('disabled')) {
            return false;
        }

        var counter = $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--value');

        counter.html(+counter.text().trim() - 1);

        if (+counter.text().trim() < 2) {
            $(this).addClass('sln-service-variable-duration--counter--button--disabled');
        }

        if (!checkbox.prop('checked')) {
            checkbox.prop('checked', true)
            checkServices();
        }

        $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-count-input').val(+counter.text().trim())

        if (+counter.text().trim() < +$(this).closest('.sln-service-variable-duration').data('unitsPerSession')) {
            $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--plus').removeClass('sln-service-variable-duration--counter--button--disabled');
        }

        evalTot();

        return false;
    })

    $('.sln-service-variable-duration--counter--value').each(function () {
        $(this).html($(this).closest('.sln-service-variable-duration--counter').find('.sln-service-count-input').val())
        $(this).closest('.sln-service-variable-duration--counter').find('.sln-service-variable-duration--counter--minus').toggleClass('sln-service-variable-duration--counter--button--disabled', +$(this).text().trim() < 2)
    })

    var $checkboxes = $('.sln-service-list input[type="checkbox"]');
    var $totalbox = $("#services-total");
    evalTot();
    function evalTot() {
        var tot = 0;
        $checkboxes.each(function() {
            var count = $(this).closest('.sln-service').find('.sln-service-count-input').val() || 1;
            if ($(this).is(":checked")) {
                tot += $(this).data("price") * count;
            }
        });
        var decimals = parseFloat(tot) === parseFloat(parseInt(tot)) ? 0 : 2;
        $totalbox.text(
            $totalbox.data("symbol-left") +
                ($totalbox.data("symbol-left") !== "" ? " " : "") +
                tot.formatMoney(
                    decimals,
                    $totalbox.data("symbol-decimal"),
                    $totalbox.data("symbol-thousand")
                ) +
                ($totalbox.data("symbol-right") !== "" ? " " : "") +
                $totalbox.data("symbol-right")
        );
        $checkboxes.each(function() {
            var count = $(this).closest('.sln-service').find('.sln-service-count-input').val() || 1;
            var tot     = $(this).data("price") * count;
            var decimals = parseFloat(tot) === parseFloat(parseInt(tot)) ? 0 : 2;
            $(this).closest('.sln-service').find('.sln-service-price-value').text(
                $totalbox.data("symbol-left") +
                ($totalbox.data("symbol-left") !== "" ? " " : "") +
                tot.formatMoney(
                    decimals,
                    $totalbox.data("symbol-decimal"),
                    $totalbox.data("symbol-thousand")
                ) +
                ($totalbox.data("symbol-right") !== "" ? " " : "") +
                $totalbox.data("symbol-right")
            );
        });
        $checkboxes.each(function() {
            var count = $(this).closest('.sln-service').find('.sln-service-count-input').val() || 1;
            var tot     = $(this).data("duration") * count;
            var hours  = parseInt(tot / 60);
            var minutes = tot % 60;
            $(this).closest('.sln-service').find('.sln-service-duration > span').text(
                (hours < 10 ? '0' + hours : hours) + ":" + (minutes < 10 ? '0' + minutes : minutes)
            );
        });
    }

    function checkServices() {
        var form, data;
        if ($("#salon-step-services").length) {
            form = $("#salon-step-services");
            data =
                form.serialize() +
                "&action=salon&method=CheckServices&part=primaryServices&security=" +
                salon.ajax_nonce;
        } else if ($("#salon-step-secondary").length) {
            form = $("#salon-step-secondary");
            data =
                form.serialize() +
                "&action=salon&method=CheckServices&part=secondaryServices&security=" +
                salon.ajax_nonce;
        } else {
            return;
        }

        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (!data.success) {
                    var alertBox = $(
                        '<div class="sln-alert sln-alert--problem sln-service-error"></div>'
                    );
                    $.each(data.errors, function() {
                        alertBox.append("<p>").html(this);
                    });
                } else {
                    $(".sln-alert.sln-service-error").remove();
                    if (data.services)
                        $.each(data.services, function(index, value) {
                            var checkbox = $("#sln_services_" + index);
                            var errorsArea = $("#sln_services_" + index)
                                .closest(".sln-service")
                                .find(".errors-area");
                            if (value.status == -1) {
                                var alertBox = $(
                                    '<div class="sln-alert sln-alert-medium sln-alert--problem sln-service-error"><p>' +
                                        value.error +
                                        "</p></div>"
                                );
                                checkbox
                                    .attr("checked", false)
                                    .attr("disabled", "disabled")
                                    .trigger("change");
                                errorsArea.html(alertBox);
                            } else if (value.status == 0) {
                                checkbox
                                    .attr("checked", false)
                                    .attr("disabled", false)
                                    .trigger("change");
                            } else if (value.status == 1) {
                                checkbox
                                    .attr("checked", true)
                                    .trigger("change");
                            }
                        });
                    evalTot();
                }
            },
        });
    }
}
function sln_loadStep($, data) {
    var loadingMessage =
        '<div class="sln-loader-wrapper"><div class="sln-loader">Loading...</div></div>';
        let request_arr = {
            url: salon.ajax_url,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (typeof data.redirect != "undefined") {
                    window.location.href = data.redirect;
                } else {
                    $("#sln-salon").replaceWith(data.content);
                    salon.ajax_nonce = data.nonce;
                    $("html, body").animate(
                        {
                            scrollTop: $("#sln-salon").offset().top,
                        },
                        700
                    );
                    sln_init($);
                }
            },
            error: function(data) {
                alert("error");
                console.log(data);
            },
        };
    if(data instanceof FormData){
        data.append('action', 'salon');
        data.append('method', 'salonStep');
        data.append('security', salon.ajax_nonce);
        request_arr['processData'] = false;
        request_arr['contentType'] = false;
    }else{
        data += "&action=salon&method=salonStep&security=" + salon.ajax_nonce;
    }
    request_arr['data'] = data;
    $("#sln-notifications")
        .html(loadingMessage)
        .addClass("sln-notifications--active");
    $.ajax(request_arr);
}

function sln_updateDatepickerTimepickerSlots($, intervals) {
    $("[data-ymd]").addClass("disabled");
    $.each(intervals.dates, function(key, value) {
        $('.day[data-ymd="' + value + '"]').removeClass("disabled");
    });
    $(".day[data-ymd]").removeClass("full");
    $.each(intervals.fullDays, function(key, value) {
        console.log(value);
        $('.day[data-ymd="' + value + '"]').addClass("disabled full");
    });

    $.each(intervals.times, function(key, value) {
        $('.minute[data-ymd="' + value + '"]').removeClass("disabled");
    });
}

function sln_updateDebugDate( $, debugLog ){
    $('.day').removeAttr( 'title');
    if( debugLog ){
        function show_debug_day_info(){
            $( $( this ).find( '.sln-debug-day-info') ).show(0);
        }
        function hide_debug_day_info(){
            $( $( this ).find( '.sln-debug-day-info' ) ).hide(0);
        }
        $('.day').hover( show_debug_day_info, hide_debug_day_info );
        $( '.day' ).append( '<div class="sln-debug-day-info">The day out of the booking time range</div>');
        $( '.sln-debug-day-info' ).hide(0);
        $.each( debugLog, function(key, value) {
            if( value == 'free' ){
                $( '.day[data-ymd="' + key + '"] .sln-debug-day-info' ).remove();
                return;
            }
            $('.day[data-ymd="' + key + '"] .sln-debug-day-info').html( value ).hide(0);
        });
        // $( '.sln-debug-day-info' ).hide(0);
    }
}

function sln_stepDate($) {
    var isValid;
    var items = { intervals: $("#salon-step-date").data("intervals"), debugDate: $('#salon-step-date').data('debug') };
    var updateFunc = function() {
        sln_updateDatepickerTimepickerSlots($, items.intervals);
        sln_updateDebugDate( $, items.debugDate );
    };
    var debounce = function(fn, delay) {
        var inDebounce;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(inDebounce);
            inDebounce = setTimeout(function() {
                return fn.apply(context, args);
            }, delay);
        };
    };

    $( document ).ready(function(){
        var oldMousePosition = [0, false];
        $( '#sln-debug-sticky-panel .sln-debug-move' ).mousedown(function(e){
            oldMousePosition[0] = e.clientY;
            oldMousePosition[1] = true;
        });
        $( 'body' ).mousemove( function(e){
            if( true === oldMousePosition[1] ){
                var heightElem = $( '#sln-debug-div' ).height();
                $( '#sln-debug-div' ).animate({height: heightElem + oldMousePosition[0] - e.clientY,}, 0);
                oldMousePosition[0] = e.clientY;
            }
        });
        $( 'body' ).mouseup( function(e){
            oldMousePosition[1] = false;
        });

        $( '#sln-debug-sticky-panel #disable-debug-table' ).click(function(){
            if( confirm( 'Debug table will be disable.' ) ){
                $( "input[name='sln[debug]']").val(0);
                $( '#sln-debug-div' ).hide();
                validate( this, false );
                delete items.debugDate;
            }
        });
        var oldOpenDebugPopup = null;
        $( '#sln-debug-table' ).each( function( iter, elem ){
            elem = $( elem );
            $( elem.find( '.sln-debug-time' ) ).click(function(e){
                if( oldOpenDebugPopup ){
                    oldOpenDebugPopup.hide();
                    oldOpenDebugPopup = null;
                }
                $( window ).click( function( closeEvent){
                    if( e.timeStamp != closeEvent.timeStamp ){
                        oldOpenDebugPopup.hide()
                        $( window ).off('click')
                    }
                } );
                var popup = oldOpenDebugPopup = $( $( this ).parent().find( '.sln-debug-popup' ) );
                var mousePosition = [e.clientX, $(this).position().top + $( '#sln-debug-div' ).scrollTop()];
                if( mousePosition[0] + popup.width() > $( window ).width() ){
                    mousePosition[0] -= popup.width() - ($( window ).width() - mousePosition[0]);
                }
                popup.show().css( {'top': mousePosition[1], 'left': mousePosition[0]} );
            });

            $( '.sln-debug-popup' ).hide();
        } );
    });
    var func = debounce(updateFunc, 200);
    func();
    $("body").on("sln_date", func);
    $("body").on("sln_date", function() {
        setTimeout(function() {
            $(".datetimepicker-days table tr td.day").on("click", function() {
                if ($(this).hasClass("disabled")) {
                    return;
                }
                var datetimepicker = $(".sln_datepicker div").data(
                    "datetimepicker"
                );
                datetimepicker = (datetimepicker === undefined) ? $('.sln_datepicker input').data('datetimepicker') : datetimepicker;

                var date = $(this).attr("data-ymd");

                var dateObj = $.fn.datetimepicker.DPGlobal.parseDate(
                    date,
                    datetimepicker.format,
                    datetimepicker.language,
                    datetimepicker.formatType
                );

                var formattedDate = $.fn.datetimepicker.DPGlobal.formatDate(
                    dateObj,
                    datetimepicker.format,
                    datetimepicker.language,
                    datetimepicker.formatType
                );
                var dateString = dateObj.toLocaleDateString(
                    datetimepicker.language.replace("_", "-"),
                    {
                        weekday: "long",
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        timeZone: "UTC",
                    }
                );
                $("input[name='sln[date]']").val(formattedDate);
                dateString = dateString + ' |' + $('#sln_timepicker_viewdate').text().split('|')[1];
                $("#sln_timepicker_viewdate").text(dateString);
            });
        });
    });

    function validate(obj, autosubmit) {
        var form = $(obj).closest("form");
        var validatingMessage =
            '<div class="sln-alert sln-alert--wait">' +
            salon.txt_validating +
            "</div>";
        var data = form.serialize();
        data += "&action=salon&method=checkDate&security=" + salon.ajax_nonce;
        $("#sln-notifications")
            .addClass("sln-notifications--active")
            .append(validatingMessage);
        $( "#sln-debug-notifications" ).addClass("sln-notifications--active" ).html( validatingMessage );
        $( "#sln-debug-div" ).css( 'overflow-y', 'hidden').scrollTop(0);
        $("#sln-salon").addClass("sln-salon--loading");
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                $(".sln-alert").remove();
                if (!data.success) {
                    var alertBox = $(
                        '<div class="sln-alert sln-alert--problem"></div>'
                    );
                    $(data.errors).each(function() {
                        alertBox.append("<p>").html(this);
                    });
                    $("#sln-notifications")
                        .html("")
                        .append(alertBox);
                    $( "#sln-debug-notifications").html("").append( alertBox );
                    isValid = false;
                } else {
                    $("#sln-step-submit").attr("disabled", false);
                    $("#sln-notifications")
                        .html("")
                        .removeClass("sln-notifications--active");
                    $( "#sln-debug-notifications" ).html("").removeClass( "sln-notifications--active" );
                    $( "#sln-debug-div" ).css( 'overflow-y', 'scroll' );
                    $("#sln-salon").removeClass("sln-salon--loading");
                    isValid = true;
                    if (autosubmit) submit();
                }
                bindIntervals(data.intervals);
                if ( data.debug ){
                    bindDebugTimeLog( data.debug.times );
                }
                var timeValue = Object.values(data.intervals.times)[0] || "";
                var hours = parseInt(timeValue, 10) || 0;
                var datetimepicker = $(".sln_timepicker div").data(
                    "datetimepicker"
                );
                datetimepicker.viewDate.setUTCHours(hours);
                var minutes =
                    parseInt(
                        timeValue.substr(timeValue.indexOf(":") + 1),
                        10
                    ) || 0;
                datetimepicker.viewDate.setUTCMinutes(minutes);
                sln_renderAvailableTimeslots($, data);
                $("body").trigger("sln_date");
                $("input[name='sln[time]']").val(timeValue);
            },
        });
    }

    $('#close-debug-table').click(function(){
        $( '#sln-debug-div' ).hide();
    });

    function bindIntervals(intervals) {
        items.intervals = intervals;
        $("#salon-step-date").data("intervals", intervals);
        func();
        // putOptions($("#sln_date"), intervals.suggestedDate);
        // putOptions($("#sln_time"), intervals.suggestedTime);

        if (!Object.keys(intervals.dates).length) {
            $("#sln-step-submit").attr("disabled", true);
            $("#sln_time").attr("disabled", true);
        } else {
            $("#sln-step-submit").attr("disabled", false);
            $("#sln_time").attr("disabled", false);
        }
    }

    function bindDebugTimeLog( debugLog ){
        $( '.sln-debug-time-slote').each( function( iter, element ){
            var time = $( $( element).find('.sln-debug-time p' ) ).text();
            var timeSlot = $( element );
            $( timeSlot.find('.sln-debug-time p' ) ).attr( 'title', '');
            $( timeSlot.find( '.sln-debug-time' ) ).removeClass( 'sln-debug--failed' );
            $( timeSlot.find( '.sln-debug-popup' ) ).html('');
            var firstFaild = '';
            for( const [ruleName, ruleValue] of Object.entries( debugLog[time] ) ){
                if( false === ruleValue ){
                    if( '' === firstFaild ){
                        firstFaild = ruleName;
                    }
                    $( '<p>' + ruleName + '</p>' ).appendTo( timeSlot.find( '.sln-debug-popup' ) ).addClass( 'sln-debug--failed' );
                } else{
                    $( '<p>' + ruleName + '</p>' ).appendTo( timeSlot.find( '.sln-debug-popup' ) );
                }
            }
            if( '' !== firstFaild ){
                $( timeSlot.find( '.sln-debug-time p' ) ).attr( 'title', firstFaild );
                $( timeSlot.find( '.sln-debug-time' ) ).addClass( 'sln-debug--failed' );
            }
        })
    }

    if (!Object.keys(items.intervals.dates).length) {
        $("#sln-step-submit").attr("disabled", true);
        $("#sln_time").attr("disabled", true);
    } else {
        $("#sln-step-submit").attr("disabled", false);
        $("#sln_time").attr("disabled", false);
    }

    function putOptions(selectElem, value) {
        selectElem.val(value);
    }

    function submit() {
        if ($("#sln-step-submit").data("salon-toggle").length)
            sln_loadStep(
                $,
                $("#salon-step-date").serialize() +
                    "&" +
                    $("#sln-step-submit").data("salon-data")
            );
        else $("#sln-step-submit").trigger("click");
    }

    $(".sln_datepicker div").on("changeDay", function() {
        validate(this, false);
    });
    $("#salon-step-date").on("submit", function() {
        if (!isValid) {
            validate(this, true);
        } else {
            submit();
        }
        return false;
    });

    function dateStepResize() {
        if ($("#sln-salon.sln-step-date").length) {
            var offset = $("#sln-salon.sln-step-date").offset(),
                newOffsetLeft = offset.left - 18,
                elWidth = $("#sln-salon.sln-step-date").outerWidth(),
                wWidth = $(window).width(),
                wHeight = $(window).height();
            if (wWidth < wHeight) {
                if (elWidth <= 340) {
                    $("#sln-salon.sln-step-date").attr(
                        "style",
                        "min-width: calc(100vw - 36px); transform: translateX(-" +
                            newOffsetLeft +
                            "px);"
                    );
                }
            } else {
                $("#sln-salon.sln-step-date").attr("style", "");
            }
        }
    }
    dateStepResize();
    $(window).resize(function() {
        dateStepResize();
    });
    $("#sln_time")
        .css("position", "absolute")
        .css("opacity", "0");
    function sln_timeScroll() {
        var dateTable = $(".datetimepicker-days"),
            timeTable = $("#sln_time"),
            //originalHeight = timeTable.outerHeight(true),
            originalHeight = timeTable.prop("scrollHeight"),
            otherHeight = $(".datetimepicker-days").outerHeight(true),
            timeTableHeight =
                otherHeight -
                $("#sln_timepicker_viewdate").outerHeight(true) -
                30;
        if (originalHeight > timeTableHeight) {
            timeTable
                .css("max-height", timeTableHeight)
                .addClass("is_scrollable")
                .css("position", "relative")
                .css("opacity", "1");
        } else {
            timeTable.css("position", "relative").css("opacity", "1");
        }
    }
    $(window).bind("load", function() {
        setTimeout(function() {
            sln_timeScroll();
        }, 200);
    });
    $(window).resize(function() {
        setTimeout(function() {
            sln_timeScroll();
        }, 200);
    });
    $(document).ajaxComplete(function(event, request, settings) {
        setTimeout(function() {
            sln_timeScroll();
        }, 200);
    });
    setTimeout(function() {
        sln_timeScroll();
    }, 200);
    //$("#sln_timepicker_viewdate").on("click", function() {
    //    sln_timeScroll();
    //});
    if($('.cloned-data').length){
        $('#save-post').attr('disabled', 'disabled');
    }
    sln_initDatepickers($, items);
    sln_initTimepickers($, items);

    if (!$('input[name="sln[customer_timezone]"]').val() && $('input[name="sln[customer_timezone]"]').length) {
        $('input[name="sln[customer_timezone]"]').val(new window.Intl.DateTimeFormat().resolvedOptions().timeZone);
        validate($(".sln_datepicker div"), false);
    }
}

function sln_serviceTotal($) {
    var $checkboxes = $('.sln-service-list input[type="checkbox"]');
    var $totalbox = $("#services-total");
    function evalTot() {
        var tot = 0;
        $checkboxes.each(function() {
            var count = $(this).closest('.sln-service').find('.sln-service-count-input').val() || 1;
            if ($(this).is(":checked")) {
                tot += $(this).data("price") * count;
            }
        });
        var decimals = parseFloat(tot) === parseFloat(parseInt(tot)) ? 0 : 2;
        $totalbox.text(
            $totalbox.data("symbol-left") +
                ($totalbox.data("symbol-left") !== "" ? " " : "") +
                tot.formatMoney(
                    decimals,
                    $totalbox.data("symbol-decimal"),
                    $totalbox.data("symbol-thousand")
                ) +
                ($totalbox.data("symbol-right") !== "" ? " " : "") +
                $totalbox.data("symbol-right")
        );
    }

    function checkServices($) {
        var form, data;
        if ($("#salon-step-services").length) {
            form = $("#salon-step-services");
            data =
                form.serialize() +
                "&action=salon&method=CheckServices&part=primaryServices&security=" +
                salon.ajax_nonce;
        } else if ($("#salon-step-secondary").length) {
            form = $("#salon-step-secondary");
            data =
                form.serialize() +
                "&action=salon&method=CheckServices&part=secondaryServices&security=" +
                salon.ajax_nonce;
        } else {
            return;
        }

        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (!data.success) {
                    var alertBox = $(
                        '<div class="sln-alert sln-alert--problem sln-service-error"></div>'
                    );
                    $.each(data.errors, function() {
                        alertBox.append("<p>").html(this);
                    });
                } else {
                    $(".sln-alert.sln-service-error").remove();
                    if (data.services)
                        $.each(data.services, function(index, value) {
                            var checkbox = $("#sln_services_" + index);
                            var errorsArea = $("#sln_services_" + index)
                                .closest(".sln-service")
                                .find(".errors-area");
                            if (value.status == -1) {
                                var alertBox = $(
                                    '<div class="sln-alert sln-alert-medium sln-alert--problem sln-service-error"><p>' +
                                        value.error +
                                        "</p></div>"
                                );
                                checkbox
                                    .attr("checked", false)
                                    .attr("disabled", "disabled")
                                    .trigger("change");
                                errorsArea.html(alertBox);
                            } else if (value.status == 0) {
                                checkbox
                                    .attr("checked", false)
                                    .attr("disabled", false)
                                    .trigger("change");
                            } else if (value.status == 1) {
                                checkbox
                                    .attr("checked", true)
                                    .trigger("change");
                            }
                        });
                    evalTot();
                }
            },
        });
    }

    $checkboxes.on("click", function() {
        checkServices($);
    });
    checkServices($);
    evalTot();
}

var chooseAsistentForMe
function sln_stepAttendant($){
    if(chooseAsistentForMe != undefined){
        return;
    }
    chooseAsistentForMe = $('#sln_attendant_0').length;
    if(1+chooseAsistentForMe == $('input[name="sln[attendant]"]').length - $('.sln-alert.sln-alert--problem.sln-service-error').length){
        $('input[name="sln[attendant]"]').each(function(){
            if(0 != $(this).val()){
                $(this).trigger('click');
                var form = $(this).closest("form");
                $("#sln-salon input.sln-invalid,#sln-salon textarea.sln-invalid,#sln-salon select.sln-invalid"
                ).removeClass("sln-invalid");
                if (form[0].checkValidity()) {
                    sln_loadStep(
                        $,
                        form.serialize() + "&" + $('#sln-step-submit').data("salon-data")
                    );
                } else {
                    $(
                        "#sln-salon input:invalid,#sln-salon textarea:invalid,#sln-salon select:invalid"
                    )
                        .addClass("sln-invalid")
                        .attr("placeholder", salon.checkout_field_placeholder);
                    $(
                        "#sln-salon input:invalid,#sln-salon textarea:invalid,#sln-salon select:invalid"
                    )
                        .parent()
                        .addClass("sln-invalid-p")
                        .attr("data-invtext", salon.checkout_field_placeholder);
                }
                return false;
            }
        });
    }
}

function sln_attendantTotal($) {
    var $checkboxes = $('input[name*="sln[attendants]"], input[name*="sln[attendant]"]');
    var $totalbox = $("#services-total");
    function evalTot() {
        var tot = 0;
        $checkboxes.each(function() {
            if ($(this).is(":checked")) {
                $(this).closest('.sln-attendant').find('.sln-service-price').each(function () {
                    tot += $(this).data('price');
                });
            }
        });
        var decimals = parseFloat(tot) === parseFloat(parseInt(tot)) ? 0 : 2;
        $totalbox.text(
            $totalbox.data("symbol-left") +
                ($totalbox.data("symbol-left") !== "" ? " " : "") +
                tot.formatMoney(
                    decimals,
                    $totalbox.data("symbol-decimal"),
                    $totalbox.data("symbol-thousand")
                ) +
                ($totalbox.data("symbol-right") !== "" ? " " : "") +
                $totalbox.data("symbol-right")
        );
    }

    $checkboxes.on("change", function() {
        evalTot();
    });
    evalTot();
}

function sln_initDatepickers($, data) {
    $(".sln_datepicker div").each(function() {
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
                .on("changeMonth", function() {
                    $("body").trigger("sln_date");
                })
                .on("changeYear", function() {
                    $("body").trigger("sln_date");
                })
                .on("hide", function() {
                    if ($(this).is(":focus"));
                    $(this).trigger("blur");
                });
            $("body").trigger("sln_date");

            var datetimepicker = $(this).data("datetimepicker");

            var suggestedDate = $.fn.datetimepicker.DPGlobal.parseDate(
                data.intervals.suggestedDate,
                datetimepicker.format,
                datetimepicker.language,
                datetimepicker.formatType
            );

            datetimepicker.setUTCDate(suggestedDate);

            var dateString = suggestedDate.toLocaleDateString(
                datetimepicker.language.replace("_", "-"),
                {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    timeZone: "UTC",
                }
            );
            $("#sln_timepicker_viewdate").text(dateString + ' | ' + data.intervals.suggestedTime);

            $("input[name='sln[date]']").val(data.intervals.suggestedDate);
        }
    });
    var elementExists = document.getElementById("sln-salon");
    if (elementExists) {
        setTimeout(function() {
            $(".datetimepicker.sln-datetimepicker").wrap(
                "<div class='sln-salon-bs-wrap'></div>"
            );
        }, 50);
    }

    if (document.dir === "rtl") {
        swapNodes($('.datetimepicker-days .table-condensed .prev'), $('.datetimepicker-days .table-condensed .next'));
    }

    function swapNodes(a, b) {
        var aNext = $('<div>').insertAfter(a);
        a.insertAfter(b);
        b.insertBefore(aNext);
        // remove marker div
        aNext.remove();
    }
}

function sln_initTimepickers($, data) {
    $(".sln_timepicker div").each(function() {
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
                .on("show", function() {
                    $("body").trigger("sln_date");
                })
                .on("place", function() {
                    sln_renderAvailableTimeslots($, data);

                    $("body").trigger("sln_date");
                })
                .on("changeMinute", function() {
                    setTimeout(function() {
                        sln_renderAvailableTimeslots($, data);

                        $("body").trigger("sln_date");
                    }, 5);
                })
                .on("hide", function() {
                    if ($(this).is(":focus"));
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

            function convertTo24HrsFormat(time) {
                const slicedTime = time.split(/(pm|am)/gm)[0];

                let [hours, minutes] = slicedTime.split(':');

                if (hours === '12') {
                    hours = '00';
                }

                let updateHourAndMin;

                function addition(hoursOrMin) {
                    updateHourAndMin =
                        hoursOrMin.length < 2
                            ? (hoursOrMin = `${0}${hoursOrMin}`)
                            : hoursOrMin;

                    return updateHourAndMin;
                }

                if (time.endsWith('pm')) {
                    hours = parseInt(hours, 10) + 12;
                }

                return `${addition(hours)}:${addition(minutes)}`;
            }

            var suggestedTime = convertTo24HrsFormat(data.intervals.suggestedTime);
            var hours = parseInt(suggestedTime, 10) || 0;
            var datetimepicker = $(this).data("datetimepicker");
            datetimepicker.viewDate.setUTCHours(hours);
            var minutes =
                parseInt(
                    suggestedTime.substr(suggestedTime.indexOf(":") + 1),
                    10
                ) || 0;
            datetimepicker.viewDate.setUTCMinutes(minutes);

            sln_renderAvailableTimeslots($, data);

            $("body").trigger("sln_date");

            picker.find("tr td").addClass("disabled");
        }
    });
}
/* ========================================================================
 * Bootstrap: transition.js v3.2.0
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+(function($) {
    "use strict";

    // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
    // ============================================================

    function transitionEnd() {
        var el = document.createElement("bootstrap");

        var transEndEventNames = {
            WebkitTransition: "webkitTransitionEnd",
            MozTransition: "transitionend",
            OTransition: "oTransitionEnd otransitionend",
            transition: "transitionend",
        };

        for (var name in transEndEventNames) {
            if (el.style[name] !== undefined) {
                return { end: transEndEventNames[name] };
            }
        }

        return false; // explicit for ie8 (  ._.)
    }

    // http://blog.alexmaccaw.com/css-transitions
    $.fn.emulateTransitionEnd = function(duration) {
        var called = false;
        var $el = this;
        $(this).one("bsTransitionEnd", function() {
            called = true;
        });
        var callback = function() {
            if (!called) $($el).trigger($.support.transition.end);
        };
        setTimeout(callback, duration);
        return this;
    };

    $(function() {
        $.support.transition = transitionEnd();

        if (!$.support.transition) return;

        $.event.special.bsTransitionEnd = {
            bindType: $.support.transition.end,
            delegateType: $.support.transition.end,
            handle: function(e) {
                if ($(e.target).is(this))
                    return e.handleObj.handler.apply(this, arguments);
            },
        };
    });
})(jQuery);

/* ========================================================================
 * Bootstrap: collapse.js v3.2.0
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+(function($) {
    "use strict";

    // COLLAPSE PUBLIC CLASS DEFINITION
    // ================================

    var Collapse = function(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, Collapse.DEFAULTS, options);
        this.transitioning = null;

        if (this.options.parent) this.$parent = $(this.options.parent);
        if (this.options.toggle) this.toggle();
    };

    Collapse.VERSION = "3.2.0";

    Collapse.DEFAULTS = {
        toggle: true,
    };

    Collapse.prototype.dimension = function() {
        var hasWidth = this.$element.hasClass("width");
        return hasWidth ? "width" : "height";
    };

    Collapse.prototype.show = function() {
        if (this.transitioning || this.$element.hasClass("in")) return;

        var startEvent = $.Event("show.bs.collapse");
        this.$element.trigger(startEvent);
        if (startEvent.isDefaultPrevented()) return;

        var actives = this.$parent && this.$parent.find("> .panel > .in");

        if (actives && actives.length) {
            var hasData = actives.data("bs.collapse");
            if (hasData && hasData.transitioning) return;
            Plugin.call(actives, "hide");
            hasData || actives.data("bs.collapse", null);
        }

        var dimension = this.dimension();

        this.$element
            .removeClass("collapse")
            .addClass("collapsing")
            [dimension](0);

        this.transitioning = 1;

        var complete = function() {
            this.$element
                .removeClass("collapsing")
                .addClass("collapse in")
                [dimension]("");
            this.transitioning = 0;
            this.$element.trigger("shown.bs.collapse");
        };

        if (!$.support.transition) return complete.call(this);

        var scrollSize = $.camelCase(["scroll", dimension].join("-"));

        this.$element
            .one("bsTransitionEnd", $.proxy(complete, this))
            .emulateTransitionEnd(350)
            [dimension](this.$element[0][scrollSize]);
    };

    Collapse.prototype.hide = function() {
        if (this.transitioning || !this.$element.hasClass("in")) return;

        var startEvent = $.Event("hide.bs.collapse");
        this.$element.trigger(startEvent);
        if (startEvent.isDefaultPrevented()) return;

        var dimension = this.dimension();

        this.$element[dimension](this.$element[dimension]())[0].offsetHeight;

        this.$element
            .addClass("collapsing")
            .removeClass("collapse")
            .removeClass("in");

        this.transitioning = 1;

        var complete = function() {
            this.transitioning = 0;
            this.$element
                .trigger("hidden.bs.collapse")
                .removeClass("collapsing")
                .addClass("collapse");
        };

        if (!$.support.transition) return complete.call(this);

        this.$element[dimension](0)
            .one("bsTransitionEnd", $.proxy(complete, this))
            .emulateTransitionEnd(350);
    };

    Collapse.prototype.toggle = function() {
        this[this.$element.hasClass("in") ? "hide" : "show"]();
    };

    // COLLAPSE PLUGIN DEFINITION
    // ==========================

    function Plugin(option) {
        return this.each(function() {
            var $this = $(this);
            var data = $this.data("bs.collapse");
            var options = $.extend(
                {},
                Collapse.DEFAULTS,
                $this.data(),
                typeof option == "object" && option
            );

            if (!data && options.toggle && option == "show") option = !option;
            if (!data)
                $this.data("bs.collapse", (data = new Collapse(this, options)));
            if (typeof option == "string") data[option]();
        });
    }

    var old = $.fn.collapse;

    $.fn.collapse = Plugin;
    $.fn.collapse.Constructor = Collapse;

    // COLLAPSE NO CONFLICT
    // ====================

    $.fn.collapse.noConflict = function() {
        $.fn.collapse = old;
        return this;
    };

    // COLLAPSE DATA-API
    // =================

    $(document).on(
        "click.bs.collapse.data-api",
        '[data-toggle="collapse"]',
        function(e) {
            var href;
            var $this = $(this);
            var target =
                $this.attr("data-target") ||
                e.preventDefault() ||
                ((href = $this.attr("href")) &&
                    href.replace(/.*(?=#[^\s]+$)/, "")); // strip for ie7
            var $target = $(target);
            var data = $target.data("bs.collapse");
            var option = data ? "toggle" : $this.data();
            var parent = $this.attr("data-parent");
            var $parent = parent && $(parent);

            if (!data || !data.transitioning) {
                if ($parent)
                    $parent
                        .find(
                            '[data-toggle="collapse"][data-parent="' +
                                parent +
                                '"]'
                        )
                        .not($this)
                        .addClass("collapsed");
                $this[$target.hasClass("in") ? "addClass" : "removeClass"](
                    "collapsed"
                );
            }

            Plugin.call($target, option);
        }
    );
})(jQuery);

function sln_facebookInit() {
    window.fbAsyncInit = function() {
        FB.init({
            appId: salon.fb_app_id,
            cookie: true,
            xfbml: true,
            version: "v2.8",
        });
        FB.AppEvents.logPageView();

        jQuery("[data-salon-click=fb_login]")
            .off("click")
            .on("click", function() {
                FB.login(
                    function() {
                        sln_facebookLogin();
                    },
                    { scope: "email" }
                );

                return false;
            });
    };

    (function(d, s, id) {
        var js,
            fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;

        var locale =
            typeof salon.fb_locale !== "undefined" ? salon.fb_locale : "en_US";

        js.src = "//connect.facebook.net/" + locale + "/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    })(document, "script", "facebook-jssdk");
}

function sln_facebookLogin() {
    var auth = FB.getAuthResponse();

    if (!auth) {
        return;
    }

    var $form = jQuery("#salon-step-details");

    if ($form.length) {
        $form.append(
            '<input type="hidden" name="fb_access_token" value="' +
                auth.accessToken +
                '" />'
        );
        $form.find("[name=submit_details]").trigger("click");
        return;
    }

    jQuery.ajax({
        url: salon.ajax_url,
        data: {
            accessToken: auth.accessToken,
            action: "salon",
            method: "FacebookLogin",
            security: salon.ajax_nonce,
        },
        method: "POST",
        dataType: "json",
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert("error");
                console.log(response);
            }
        },
        error: function(data) {
            alert("error");
            console.log(data);
        },
    });
}

function sln_salonBookingCalendarInit() {
    if (jQuery("#sln-salon-booking-calendar-shortcode").length === 0) {
        return;
    }
    sln_salonBookingCalendarInitTooltip();

    setInterval(function() {
        jQuery.ajax({
            url: salon.ajax_url,
            data: {
                action: "salon",
                method: "salonCalendar",
                security: salon.ajax_nonce,
                attrs: JSON.parse(
                    jQuery(
                        "#sln-salon-booking-calendar-shortcode .booking-main"
                    ).attr("data-attrs")
                ),
            },

            method: "POST",
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    jQuery(
                        "#sln-salon-booking-calendar-shortcode > .wrapper"
                    ).html(data.content);
                    sln_salonBookingCalendarInitTooltip();
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.errors) {
                    // TODO: display errors
                }
            },
            error: function(data) {
                alert("error");
                console.log(data);
            },
        });
    }, 10 * 1000);
}

function sln_salonBookingCalendarInitTooltip() {
    jQuery('[data-toggle="tooltip"]').tooltip();
}

function sln_createRatings(readOnly, view) {
    jQuery("[name=sln-rating]").each(function() {
        if (jQuery(this).val()) {
            sln_createRaty(jQuery(this), readOnly, view);
        }
    });
}

function sln_createRaty($rating, readOnly, view) {
    readOnly = readOnly == undefined ? false : readOnly;
    view = view == undefined ? "star" : view;

    var starOnClass = "glyphicon";
    var starOffClass = "glyphicon";

    if (view === "circle") {
        starOnClass += " sln-rate-service-on";
        starOffClass += " sln-rate-service-off";
    } else {
        starOnClass += " glyphicon-star";
        starOffClass += " glyphicon-star-empty";
    }

    var $ratyElem = $rating.parent().find(".rating");
    $ratyElem.raty({
        score: jQuery($rating).val(),
        space: false,
        path: salon.images_folder,
        readOnly: readOnly,
        starType: "i",
        starOff: starOffClass,
        starOn: starOnClass,
    });
    $ratyElem.css("display", "block");
}

function sln_renderAvailableTimeslots($, data) {
    $(".datetimepicker-minutes table tr td").html("");

    //for active timeslot to stay
    var datetimepicker = $(".sln_timepicker div").data("datetimepicker");
    var DtHours = datetimepicker.viewDate.getUTCHours();
    DtHours = DtHours >= 10 ? DtHours : "0" + DtHours;
    var DtMinutes = datetimepicker.viewDate.getUTCMinutes();
    DtMinutes = DtMinutes >= 10 ? DtMinutes : "0" + DtMinutes;
    var DtTime = DtHours + ":" + DtMinutes;
    let dateString = $('#sln_timepicker_viewdate').text().split('|')[0] + ' | ' + DtTime;
    $("#sln_timepicker_viewdate").text(dateString);

    var html = [];

    var date = datetimepicker.getDate();

    $.each(data.intervals.workTimes, function(value) {
        var hours = parseInt(value, 10) || 0;
        var minutes = parseInt(value.substr(value.indexOf(":") + 1), 10) || 0;

        date.setUTCHours(hours);
        date.setUTCMinutes(minutes);

        html.push(
            '<span data-ymd="' +
                value +
                '" class="minute disabled' +
                (value === DtTime ? " active" : "") +
                '">' +
                $.fn.datetimepicker.DPGlobal.formatDate(
                    date,
                    datetimepicker.format,
                    datetimepicker.language,
                    datetimepicker.formatType
                ) +
                "</span>"
        );
    });

    $(".datetimepicker-minutes table tr td").html(html.join(""));

    $(".datetimepicker-minutes table tr td .minute").on("click", function() {
        var datetimepicker = $(".sln_timepicker div").data("datetimepicker");

        var time = $(this).attr("data-ymd");

        var hours = parseInt(time, 10) || 0;
        var minutes = parseInt(time.substr(time.indexOf(":") + 1), 10) || 0;

        datetimepicker.element.on('changeDate', function() {
            datetimepicker.viewDate.setUTCHours(hours);
            datetimepicker.viewDate.setUTCMinutes(minutes);
        });
        let dateString = $('#sln_timepicker_viewdate').text().split('|')[0] + ' | ' + time;
        $("#sln_timepicker_viewdate").text(dateString);


        $('#sln-booking-cloned-notice').hide();
        $("input[name='sln[time]']").val(time);
        //for reschedule timepicker
        $("input[name='_sln_booking_time']").val(time);
    });
    setTimeout(() => {
        $(".datetimepicker-days table tr th.next").on("click", function() {
            $("body").trigger("sln_date");
        });
        $(".datetimepicker-days table tr th.prev").on("click", function() {
            $("body").trigger("sln_date");
        });
    }, 0);
}
jQuery(function($) {
    $(function() {
        if ($(".sln-customcolors").length) {
            $("body").addClass("sln-salon-page-customcolors");
        }
    });
});
// DIVI THEME ACCORDION FIX SNIPPET
jQuery(function($) {
    if ($("body.theme-Divi").length || $("body.et_divi_theme").length) {
        $(".sln-panel-heading").off("click");
    }
});
// DIVI THEME ACCORDION FIX SNIPPET // END

function sln_applyTipsAmount() {
    var $ = jQuery;
    var amount = $("#sln_tips").val();

    var data =
        "sln[tips]=" +
        amount +
        "&action=salon&method=applyTipsAmount&security=" +
        salon.ajax_nonce;

    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            $("#sln_tips_status")
                .find(".sln-alert")
                .remove();
            var alertBox;
            if (data.success) {
                $("#sln_tips_value").html(data.tips);
                $(".sln-total-price").html(data.total);
                alertBox = $(
                    '<div class="sln-alert sln-alert--paddingleft sln-alert--success"></div>'
                );
            } else {
                alertBox = $(
                    '<div class="sln-alert sln-alert--paddingleft sln-alert--problem"></div>'
                );
            }
            $(data.errors).each(function() {
                alertBox.append("<p>").html(this);
            });
            $("#sln_tips_status")
                .html("")
                .append(alertBox);
        },
        error: function(data) {
            alert("error");
            console.log(data);
        },
    });

    return false;
}

function sln_google_maps_places_api_callback() {
    if (
        typeof google == "object" &&
        typeof google.maps == "object" &&
        typeof google.maps.places == "object"
    ) {
        var address_inputs = [
            "_sln_booking_address",
            "sln_address",
            "salon_settings_gen_address",
            "sln_customer_meta__sln_address",
        ];
        address_inputs.forEach((address_input) => {
            var address_input_obj = document.getElementById(address_input);
            if (
                !!address_input_obj &&
                address_input_obj instanceof HTMLInputElement &&
                address_input_obj.type == "text"
            ) {
                new google.maps.places.Autocomplete(
                    document.getElementById(address_input)
                );
            }
        });
    }
}
