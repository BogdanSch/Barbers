"use strict";

jQuery(function ($) {
    sln_createSelect2Full($);
});

function sln_createSelect2Full($) {
    $(".sln-select-wrapper select").select2({
        tags: "true",
        width: "100%",
    });
    $(".sln-select-wrapper select")
        .select2({
            tags: "true",
            width: "100%",
        })
        .on("focus", function () {
            $(this).select2("open");
        });
    $(".sln-select select").each(function () {
        $(this)
            .select2({
                containerCssClass:
                    "sln-select-rendered " +
                    ($(this).attr("data-containerCssClass")
                        ? $(this).attr("data-containerCssClass")
                        : ""),
                dropdownCssClass: "sln-select-dropdown",
                theme: "sln",
                width: "100%",
                templateResult: function (state) {
                    if (!state.id) return state.text;
                    return $(
                        '<span data-value="' +
                            state.id +
                            '">' +
                            state.text +
                            "</span>"
                    );
                },
                placeholder: $(this).data("placeholder"),
            })
            .on("focus", function () {
                $(this).select2("open");
            });
    });

    sln_createSelect2();
    sln_createSelect2NoSearch();
    sln_createServicesActionLineSelect2($);
}

function sln_createSelect2() {
    jQuery(".sln-select-wrapper select").select2({
        tags: "true",
        width: "100%",
    });
}

function sln_createServiceLineSelect2($) {
    jQuery('.sln-booking-service-line select[data-selection="service-selected"]').select2({
        tags: "true",
        width: "100%",
        theme: "sln",
        dropdownCssClass: 'select2-results__option--stl',
        templateResult: function (state) {
            if (!state.id) return state.text;
            return $(
                '<span data-value="' + state.id + '"><span>' + 
                state.text.replace(/\, /g, "</span><span>") +
                " " +
                "</span>" + "</span>"
            );
        },
        placeholder: function () {
            $(this).data("placeholder");
        },
    });
    jQuery('.sln-booking-service-line select[data-selection="attendant-selected"]:not(.hide)').select2({
        tags: "true",
        width: "100%",
        theme: "sln",
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
}

function sln_createSelect2NoSearch() {
    jQuery(".sln-select-wrapper-no-search select").select2({
        tags: "true",
        width: "100%",
        minimumResultsForSearch: Infinity,
    });
}

function sln_createServicesActionLineSelect2($) {
    if (jQuery(".sln-booking-service-action select").length) {
        jQuery(".sln-booking-service-action select")
            .select2({
                containerCssClass:
                    "sln-select-rendered " +
                    ($(this).attr("data-containerCssClass")
                        ? $(this).attr("data-containerCssClass")
                        : ""),
                dropdownCssClass: "sln-select-dropdown",
                theme: "sln",
                width: "100%",
                dropdownAutoWidth: "true",
                templateResult: function (state) {
                    if (!state.id) return state.text;
                    return $(
                        '<span data-value="' +
                            state.id +
                            '">' +
                            state.text +
                            "</span>"
                    );
                },
                placeholder: $(this).data("placeholder"),
            })
            .on("focus", function () {
                $(this).select2("open");
            });
        //  alert("dd");
    }
}
