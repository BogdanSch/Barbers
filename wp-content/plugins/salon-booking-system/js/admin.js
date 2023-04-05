"use strict";
if (jQuery("#toplevel_page_salon").hasClass("wp-menu-open")) {
    jQuery("#wpbody-content .wrap").addClass("sln-bootstrap");
    jQuery("#wpbody-content .wrap").attr("id", "sln-salon--admin");
}

jQuery(function ($) {
    if (window.frameElement) {
        $("html").addClass("in-iframe");
    }
    $("#booking-accept, #booking-refuse").on("click", function () {
        $("#_sln_booking_status").val($(this).data("status"));
        $("#save-post").trigger("click");
    });

    $(".sln-toolbox-trigger").on("click", function (event) {
        $(this).parent().toggleClass("open");
        event.preventDefault();
    });
    $(".sln-toolbox-trigger-mob").on("click", function (event) {
        $(this).parent().find(".sln-toolbox").toggleClass("open");
        event.preventDefault();
    });
    $(".sln-box-info-trigger button").on("click", function (event) {
        $(this).parent().parent().parent().toggleClass("sln-box--info-visible");
        event.preventDefault();
    });
    $(".sln-box-info-content:after").on("click", function (event) {
        event.preventDefault();
    });
    if ($(".sln-admin-sidebar").length) {
        $(".sln-admin-sidebar").affix({
            offset: {
                top: $(".sln-admin-sidebar").offset().top - 96,
            },
        });
    }
    $('.sln-notice__dismiss').on('click', function(){
        $(this).closest('.sln-notice__wrapper').hide();
        document.cookie = 'sln-notice__dismiss=1';
    });
    $("[data-action=change-service-type]").on("change", function () {
        var $this = $(this);
        var $target = $($this.attr("data-target"));
        var $exclusive = $("#exclusive_service");
        if ($this.is(":checked")) {
            $target.removeClass("hide");
            $exclusive.addClass("hide");
            $("#_sln_service_exclusive").val(0);
        } else {
            $target.addClass("hide");
            $exclusive.removeClass("hide");
        }
    });

    $("[data-action=change-secondary-service-mode]").on("change", function () {
        var $this = $(this);
        var $target = $($this.attr("data-target"));
        if ($this.val() === "service") {
            $target.removeClass("hide");
        } else {
            $target.addClass("hide");
        }
    });
    $(".sln-radiobox__wrapper--bd").each(function () {
        var inputTrigger = $(this).find('input[type="radio"]');
        if (inputTrigger.prop("checked")) {
            $(this).addClass("sln-radiobox__wrapper--checked");
        }
        inputTrigger.on("change", function () {
            $(".sln-radiobox__wrapper--bd").removeClass(
                "sln-radiobox__wrapper--checked"
            );
            $(this)
                .parent()
                .parent()
                .addClass("sln-radiobox__wrapper--checked");
        });
    });
    function premiumVersionBanner() {
        $(".sln-admin-banner--trigger, .sln-admin-banner--close").on(
            "click",
            function (event) {
                $(".sln-admin-banner").toggleClass("sln-admin-banner--inview");
                event.preventDefault();
            }
        );
    }
    if ($("#sln-salon--admin.sln-calendar--wrapper--loading").length) {
        $(".sln-calendar--wrapper--sub").css("opacity", "1");
        $(".sln-calendar--wrapper").removeClass(
            "sln-calendar--wrapper--loading sln-calendar--wrapper"
        );
    }
    if ($(".sln-calendar--wrapper").length) {
        $(".sln-calendar--wrapper--sub").css("opacity", "1");
        $(".sln-calendar--wrapper").removeClass(
            "sln-calendar--wrapper--loading"
        );
    }
    if ($(window).width() < 1024) {
        premiumVersionBanner();
    }

    if ($("#import-customers-drag").length > 0) {
        sln_initImporter($("#import-customers-drag"), "Customers");
    }
    if ($("#import-services-drag").length > 0) {
        sln_initImporter($("#import-services-drag"), "Services");
    }
    if ($("#import-assistants-drag").length > 0) {
        sln_initImporter($("#import-assistants-drag"), "Assistants");
    }

    $("#_sln_service_price")
        .on("sln_add_error_tip", function (e, element, error_type) {
            var offset = element.position();

            if (element.parent().find(".sln_error_tip").length === 0) {
                element.after(
                    '<div class="sln_error_tip ' +
                        error_type +
                        '">' +
                        salon_admin[error_type] +
                        "</div>"
                );
                element
                    .parent()
                    .find(".sln_error_tip")
                    .css(
                        "left",
                        offset.left +
                            element.width() -
                            element.width() / 2 -
                            $(".sln_error_tip").width() / 2
                    )
                    .css("top", offset.top + element.height())
                    .fadeIn("100");
            }
        })
        .on("sln_remove_error_tip", function (e, element, error_type) {
            element
                .parent()
                .find(".sln_error_tip." + error_type)
                .fadeOut("100", function () {
                    $(this).remove();
                });
        })
        .on("blur", function () {
            $(".sln_error_tip").fadeOut("100", function () {
                $(this).remove();
            });
        })
        .on("change", function () {
            var regex = new RegExp(
                "[^-0-9%\\" + salon_admin.mon_decimal_point + "]+",
                "gi"
            );
            var value = $(this).val();
            var newvalue = value.replace(regex, "");

            if (value !== newvalue) {
                $(this).val(newvalue);
            }
        })
        .on("keyup", function () {
            var regex, error;
            regex = new RegExp(
                "[^-0-9%\\" + salon_admin.mon_decimal_point + "]+",
                "gi"
            );
            error = "i18n_mon_decimal_error";
            var value = $(this).val();
            var newvalue = value.replace(regex, "");

            if (value !== newvalue) {
                $("#_sln_service_price").triggerHandler("sln_add_error_tip", [
                    $(this),
                    error,
                ]);
            } else {
                $("#_sln_service_price").triggerHandler(
                    "sln_remove_error_tip",
                    [$(this), error]
                );
            }
        });

    $("#salon_settings_sms_provider").on("change", function () {
        $("#salon_settings_whatsapp_enabled").prop("checked", false);
        $(".enabled-whatsapp-checkbox").toggleClass(
            "hide",
            $(this).val() !== "twilio"
        );
    });
    function settingsPanel() {
        $(".sln-box--haspanel").each(function () {
            var trigger = $(this).find(".sln-box__paneltitle"),
                target = $(this).find(".sln-box__panelcollapse");
            trigger.on("click", function () {
                $(".sln-box--haspanel .sln-box__paneltitle").removeClass(
                    "sln-box__paneltitle--open"
                );
                $(".sln-box--haspanel .sln-box__panelcollapse.in").collapse(
                    "hide"
                );
                target.collapse("toggle");
            });
            target.on("hidden.bs.collapse", function () {
                var parentID = $(this).parent().attr("id"),
                    navbarLink = $("a[href$='#" + parentID + "']").parent();
                navbarLink.removeClass("active");
                trigger.removeClass("sln-box__paneltitle--open");
                $(this).parent().removeClass("sln-box--haspanel--open");
            });
            target.on("show.bs.collapse", function () {
                var parentID = $(this).parent().attr("id"),
                    navbarLink = $("a[href$='#" + parentID + "']").parent(),
                    x = $("a[href$='#" + parentID + "']").attr(
                        "data-initialOffset"
                    );
                $(".sln-inpage_navbar_inner").scrollLeft(x - 10);
                $("#sln-inpage_navbar li").removeClass("active");
                navbarLink.addClass("active");
                console.log(parentID + " " + navbarLink);
                trigger.addClass("sln-box__paneltitle--open");
                $(this).parent().addClass("sln-box--haspanel--open");
            });
        });
        $('#salon_settings_enable_booking_tax_calculation').on('change', function(){
            $(this).closest('.row').next().toggleClass('hide', $(this).val());
        });
        setTimeout(function () {
            if (window.location.hash) {
                $(
                    "#" +
                        window.location.hash.replace("#", "") +
                        " .sln-box__paneltitle"
                ).trigger("click");
                $([document.documentElement, document.body]).animate(
                    {
                        scrollTop: $(
                            "#" + window.location.hash.replace("#", "")
                        ).offset().top,
                    },
                    2000
                );
            }
        }, 0);
    }
    if ($(".sln-box--haspanel").length) {
        settingsPanel();
    }
    $("#salon_settings_attendant_enabled").on("change", function () {
        // !$(this).prop("checked") &&
        //     $("#salon_settings_only_from_backend_attendant_enabled").prop(
        //         "checked",
        //         false
        //     );
        $(
            ".only-from-backend-attendant-enable-checkbox, .assistant-selections-options"
        ).toggleClass("hide", !$(this).prop("checked"));
        $(this)
            .closest(".row")
            .toggleClass("sln-box--appeared", $(this).prop("checked"));
    });

    $(".sln-booking-holiday-rules").on(
        "change",
        ".sln-from-date .sln-input",
        function () {
            $(this)
                .closest(".row")
                .find(".sln-to-date .sln-input")
                .val($(this).val());
        }
    );

    $("#_sln_attendant_email").select2({
        containerCssClass: "sln-select-rendered",
        dropdownCssClass: "sln-select-dropdown",
        theme: "sln",
        width: "100%",
        placeholder: $("#_sln_attendant_email").data("placeholder"),
        tags: true,
        allowClear: true,
        language: {
            noResults: function () {
                return $("#_sln_attendant_email").data("nomatches");
            },
        },

        ajax: {
            url:
                salon.ajax_url +
                "&action=salon&method=SearchAssistantStaffMember&security=" +
                salon.ajax_nonce,
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    s: params.term,
                };
            },
            minimumInputLength: 3,
            processResults: function (data, page) {
                let selected = $("#_sln_attendant_email :first-child");
                if(selected.data('staff-member-id') == ""){
                    data.result.forEach(function(el){
                        if(selected.val() == el.id){
                            selected.data('staff-member-id', el.staff_member_id);
                            return false;
                        }
                    });
                }
                return {
                    results: data.result,
                };
            },
        },
    });

    $("#_sln_attendant_email").on("change", function () {
        var selected_option = $("#_sln_attendant_email").select2("data")[0];
        var user_id =
            typeof selected_option !== "undefined"
                ? selected_option["staff_member_id"] ||
                  $(selected_option.element).data("staff-member-id")
                : "";
        $('[name="_sln_attendant_staff_member_id"]').val(user_id);
        $(
            '[name="_sln_attendant_limit_staff_member_to_assigned_bookings_only"]'
        ).prop("checked", false);
        if (+user_id) {
            $(".sln-staff-member-assigned-bookings-only").removeClass("hide");
        } else {
            $(".sln-staff-member-assigned-bookings-only").addClass("hide");
        }
    });

    var input = document.querySelector("#sln_customer_meta__sln_phone");

    if (input && $("#sln_customer_meta__sln_sms_prefix").length) {
        function getCountryCodeByDialCode(dialCode) {
            var countryData = window.intlTelInputGlobals.getCountryData();
            var countryCode = "";
            countryData.forEach(function (data) {
                if (data.dialCode == dialCode) {
                    countryCode = data.iso2;
                }
            });
            return countryCode;
        }

        var iti = window.intlTelInput(input, {
            initialCountry: getCountryCodeByDialCode(
                ($("#sln_customer_meta__sln_sms_prefix").val() || "").replace(
                    "+",
                    ""
                )
            ),
            separateDialCode: true,
            autoHideDialCode: true,
            nationalMode: false,
        });

        input.addEventListener("countrychange", function () {
            if (iti.getSelectedCountryData().dialCode) {
                $("#sln_customer_meta__sln_sms_prefix").val(
                    "+" + iti.getSelectedCountryData().dialCode
                );
            }
        });

        input.addEventListener("blur", function () {
            if (iti.getSelectedCountryData().dialCode) {
                $("#sln_customer_meta__sln_phone").val(
                    $("#sln_customer_meta__sln_phone")
                        .val()
                        .replace(
                            "+" + iti.getSelectedCountryData().dialCode,
                            ""
                        )
                );
            }
        });
    }

    var input = document.querySelector("#_sln_attendant_phone");

    if (input && $("#_sln_attendant_sms_prefix").length) {
        function getCountryCodeByDialCode(dialCode) {
            var countryData = window.intlTelInputGlobals.getCountryData();
            var countryCode = "";
            countryData.forEach(function (data) {
                if (data.dialCode == dialCode) {
                    countryCode = data.iso2;
                }
            });
            return countryCode;
        }

        var iti = window.intlTelInput(input, {
            initialCountry: getCountryCodeByDialCode(
                ($("#_sln_attendant_sms_prefix").val() || "").replace("+", "")
            ),
            separateDialCode: true,
            autoHideDialCode: true,
            nationalMode: false,
        });

        input.addEventListener("countrychange", function () {
            if (iti.getSelectedCountryData().dialCode) {
                $("#_sln_attendant_sms_prefix").val(
                    "+" + iti.getSelectedCountryData().dialCode
                );
            }
        });

        input.addEventListener("blur", function () {
            if (iti.getSelectedCountryData().dialCode) {
                $("#_sln_attendant_phone").val(
                    $("#_sln_attendant_phone")
                        .val()
                        .replace(
                            "+" + iti.getSelectedCountryData().dialCode,
                            ""
                        )
                );
            }
        });
    }

    $("#sln-booking-editor-modal").on("shown.bs.modal", function (e) {
        $(this)
            .find("iframe")
            .on("load", function () {
                $(this).contents().find("body").addClass("inmodal");
            });
    });

    $('.sln-booking-confirmation .sln-booking-confirmation-success, .sln-booking-confirmation .sln-booking-confirmation-error').on('click', function () {

        var self = $(this);

        if (self.closest('.sln-booking-confirmation-disabled').length) {
            return false;
        }

        self.closest('.sln-booking-confirmation').find('.sln-booking-confirmation-alert-loading').html(self.attr('title')).addClass(self.data('class'));
        self.closest('.sln-booking-confirmation').addClass('loading');

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "salon",
                method: "setBookingStatus",
                status: self.data('status'),
                booking_id: self.data('booking-id'),
            },
            cache: false,
            dataType: "json",
            success: function(response) {
                self.closest('tr').find('.booking_status').html(response.status);
                self.closest('td').html('');
            },
        });

		return false;
	});

    $('#_sln_booking_status').on('change', function () {

        var default_status =  $(this).closest('.row').find('.sln-set-default-booking-status--block-labels').data('defaultStatus');

        if ($(this).val() !== default_status) {
            $('.sln-set-default-booking-status--label-set').removeClass('hide');
            $(this).closest('.row').find('.select2-selection__rendered').removeClass('sln-booking-default-status');
        } else {
            $('.sln-set-default-booking-status--label-set').addClass('hide');
            $(this).closest('.row').find('.select2-selection__rendered').addClass('sln-booking-default-status');
        }

    }).trigger('change');

    $('.sln-set-default-booking-status--label-set').on('click', function () {

        if ($(this).closest('.sln-set-default-booking-status--block-label-disabled').length) {
            return false;
        }

        var status = $('#_sln_booking_status').val();
        var self     = $(this);

        self.addClass('hide');
        self.closest('.sln-set-default-booking-status--block-labels').find('.sln-set-default-booking-status--alert-loading').removeClass('hide');

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "salon",
                method: "setDefaultBookingStatus",
                status: status,
            },
            cache: false,
            dataType: "json",
            success: function(response) {
                self.closest('.sln-set-default-booking-status--block-labels').data('defaultStatus', status);
                var done_label = self.closest('.sln-set-default-booking-status--block-labels').find('.sln-set-default-booking-status--label-done');
                done_label.removeClass('hide');
                setTimeout(function () {
                    done_label.addClass('hide');
                }, 3000);
                $('#_sln_booking_status').trigger('change');
                self.closest('.sln-set-default-booking-status--block-labels').find('.sln-set-default-booking-status--alert-loading').addClass('hide');
            },
        });
        return false;
    });

    $('.generate-onesignal-app').on('click', function (e) {
        $(this).addClass('loading')
        var self = this
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "salon",
                method: "GenerateOnesignalApp",
            },
            cache: false,
            dataType: "json",
            success: function(response) {
                $(self).removeClass('loading')
                $('#salon_settings_onesignal_app_id').val(response.app_id)
            },
        });
        return false;
    });

});

var sln_importRows;
function sln_initImporter($item, mode) {
    var $importArea = $item;

    $importArea[0].ondragover = function () {
        $importArea.addClass("hover");
        return false;
    };

    $importArea[0].ondragleave = function () {
        $importArea.removeClass("hover");
        return false;
    };

    $importArea[0].ondrop = function (event) {
        event.preventDefault();
        $importArea.removeClass("hover").addClass("drop");

        var file = event.dataTransfer.files[0];

        $importArea.file = file;

        $importArea.find(".text").html(file.name);
        importShowFileInfo();
    };

    jQuery(
        "[data-action=sln_import][data-target=" + $importArea.attr("id") + "]"
    ).on("click", function () {
        var $importBtn = jQuery(this);
        $importBtn.button("loading");
        if (!$importArea.file) {
            $importBtn.button("reset");
            return false;
        }
        $importArea
            .find(".progress-bar")
            .attr("aria-valuenow", 0)
            .css("width", "0%");
        importShowInfo();

        var data = new FormData();

        data.append("action", "salon");
        data.append("method", "import" + mode);
        data.append("step", "start");
        data.append("file", $importArea.file);

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: data,
            cache: false,
            dataType: "json",
            processData: false, //(Don't process the files)
            contentType: false,
            success: function (response) {
                $importBtn.button("reset");
                if (response.success) {
                    console.log(response);
                    sln_importRows = response.data.rows;

                    var $modal = jQuery("#import-matching-modal");

                    var $modalBtn = $modal.find(
                        "[data-action=sln_import_matching]"
                    );
                    $modalBtn.button("reset");

                    $modal.find("table tbody").html(response.data.matching);
                    jQuery("#wpwrap").css("z-index", "auto");
                    $modal.modal({
                        keyboard: false,
                        backdrop: true,
                    });
                    sln_createSelect2Full(jQuery);
                    sln_validImportMatching();
                    $modal
                        .find("[data-action=sln_import_matching_select]")
                        .on("change", sln_changeImportMatching);

                    jQuery("[data-action=sln_import_matching]")
                        .off("click")
                        .on("click", function () {
                            if (!sln_validImportMatching()) {
                                return false;
                            }
                            $modalBtn.button("loading");

                            jQuery.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "salon",
                                    method: "import" + mode,
                                    step: "matching",
                                    form: $modal.closest("form").serialize(),
                                },
                                cache: false,
                                dataType: "json",
                                success: function (response) {
                                    console.log(response);
                                    $modal.modal("hide");
                                    if (response.success) {
                                        importShowPB();
                                        importProgressPB(
                                            response.data.total,
                                            response.data.left
                                        );
                                    } else {
                                        importShowError();
                                    }
                                },
                                error: function () {
                                    $modal.modal("hide");
                                    importShowError();
                                },
                            });
                        });
                } else {
                    importShowError();
                }
            },
            error: function () {
                $importBtn.button("reset");
                importShowError();
            },
        });

        $importArea.file = false;

        return false;
    });

    function importProgressPB(total, left) {
        total = parseInt(total);
        left = parseInt(left);

        var value = ((total - left) / total) * 100;
        $importArea
            .find(".progress-bar")
            .attr("aria-valuenow", value)
            .css("width", value + "%");

        if (left != 0) {
            jQuery.ajax({
                url: ajaxurl,
                type: "GET",
                data: {
                    action: "salon",
                    method: "import" + mode,
                    step: "process",
                },
                cache: false,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        console.log(response);
                        importProgressPB(
                            response.data.total,
                            response.data.left
                        );
                    } else {
                        importShowError();
                    }
                },
                error: function () {
                    importShowError();
                },
            });
        } else {
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "salon",
                    method: "import" + mode,
                    step: "finish",
                },
                cache: false,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        importShowSuccess();
                    } else {
                        importShowError();
                    }
                },
                error: function () {
                    importShowError();
                },
            });
        }
    }

    function importShowPB() {
        $importArea.find(".info, .alert").addClass("hide");
        $importArea.find(".progress").removeClass("hide");
    }

    function importShowFileInfo() {
        $importArea.find(".alert, .progress").addClass("hide");
        $importArea.find(".info").removeClass("hide");
    }

    function importShowInfo() {
        $importArea
            .find(".text")
            .html($importArea.find(".text").attr("placeholder"));
        $importArea.find(".alert, .progress").addClass("hide");
        $importArea.find(".info").removeClass("hide");
    }

    function importShowSuccess() {
        $importArea.find(".info, .alert, .progress").addClass("hide");
        $importArea.find(".alert-success").removeClass("hide");
    }

    function importShowError() {
        $importArea.find(".info, .alert, .progress").addClass("hide");
        $importArea.find(".alert-danger").removeClass("hide");
    }
}

function sln_changeImportMatching() {
    var $select = jQuery(this);
    var field = $select.val();
    var col = $select.attr("data-col");

    $select
        .closest("table")
        .find("tr.import_matching")
        .each(function (index, v) {
            var $cell = jQuery(this).find("td[data-col=" + col + "] span");

            var text;
            if (
                sln_importRows[index] !== undefined &&
                sln_importRows[index][field] !== undefined
            ) {
                $cell
                    .addClass("pull-left")
                    .removeClass("half-opacity")
                    .html(sln_importRows[index][field]);
            } else {
                $cell
                    .removeClass("pull-left")
                    .addClass("half-opacity")
                    .html($cell.closest("td").attr("placeholder"));
            }
        });

    sln_validImportMatching();
}

function sln_validImportMatching() {
    var $modal = jQuery("#import-matching-modal");

    var valid = true;
    $modal.find("select").each(function () {
        if (jQuery(this).prop("required") && jQuery(this).val() == "") {
            valid = false;
        }
    });

    if (valid) {
        $modal.find(".alert").addClass("hide");
        $modal
            .find("[data-action=sln_import_matching]")
            .prop("disabled", false);
    } else {
        $modal.find(".alert").removeClass("hide");
        $modal
            .find("[data-action=sln_import_matching]")
            .prop("disabled", "disabled");
    }

    return valid;
}
