"use strict";

jQuery(function ($) {
    var url = location.search;
    if (url.indexOf("post_type=sln_service") > -1) {
        sln_initServiceManagement($);
    }
    if (url.indexOf("taxonomy=sln_service_category") > -1) {
        sln_initServiceCategoryManagement($);
    }
    if (url.indexOf("post_type=sln_attendant") > -1) {
        sln_initAttendantManagement($);
    }
    sln_dataAttendant($);
    function attendantsListSkills() {
        $(".sln-service__collapse").each(function() {
            var parent = $(this),
                trigger = $(this).next(".sln-service__collapsetrigger");
            console.log(trigger.text());
            trigger.on("click", function(e) {
                parent.toggleClass("open");
                parent.toggleClass("closed");
                $(this).toggleClass("less");
                console.log(trigger.text());
                e.preventDefault();
            });
        });
        $("#_sln_attendant_services").on("select2:opening", function(e) {
            $(this)
                .parent()
                .removeClass("closed")
                .addClass("open");
        });
    }
    if ($(".sln-service__collapse").length) {
        attendantsListSkills();
    }

    if($('#_sln_service_multiple_attendants_for_service').is(':checked')){
        $('#_sln_service_variable_price_enabled').prop('checked', 0).trigger('change');
    }

    $('#_sln_service_variable_price_enabled').on('change', function () {
        if($('#_sln_service_multiple_attendants_for_service').is(':checked')){
            $(this).prop('checked', 0);
        }
        $(this).closest('.sln-variable-price').find('.sln-variable-price-attendants').toggleClass('hide', !$(this).is(':checked'));
    }).trigger('change');

    $('#_sln_service_multiple_attendants_for_service').on('change', function(){
        $(this).closest('.row').find('.sln-multiple-count-attendants').toggleClass('hide', !$(this).prop('checked'));
        if($('#_sln_service_variable_price_enabled').is(':checked') && $(this).is(':checked')){
            $('#_sln_service_variable_price_enabled').prop('checked', 0).trigger('change');
        }
    }).trigger('change');

});

function sln_initServiceManagement($) {
    $("tbody").sortable({
        start: function(event, ui) {
            $(ui.item).data("startindex", ui.item.index());
        },
        stop: function(event, ui) {
            var $item = ui.item;
            var startIndex = $item.data("startindex") + 1;
            var newIndex = $item.index() + 1;
            if (newIndex != startIndex) {
                var i = 1,
                    pos = [];
                $("tr").map(function() {
                    var post = $(this)[0].id;
                    if (post.indexOf("post-") > -1) {
                        post = post.split("post-")[1];
                        pos.push(post + "_" + i);
                        i++;
                    }
                });
                jQuery
                    .ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: "json",
                        data: {
                            action: "sln_service",
                            method: "save_position",
                            data: "positions=" + pos,
                        },
                    })
                    .done(function(msg) {
                        console.log(msg);
                    });
            }
        },
    });
    $("tbody").disableSelection();
}
function sln_initServiceCategoryManagement($) {
    $("tbody").sortable({
        start: function(event, ui) {
            $(ui.item).data("startindex", ui.item.index());
        },
        stop: function(event, ui) {
            var $item = ui.item;
            var startIndex = $item.data("startindex") + 1;
            var newIndex = $item.index() + 1;
            if (newIndex != startIndex) {
                var i = 1,
                    pos = [];
                $("tr").map(function() {
                    var post = $(this)[0].id;
                    if (post.indexOf("tag-") > -1) {
                        post = post.split("tag-")[1];
                        pos.push(post);
                        i++;
                    }
                });
                //var post_id = ui.item[0].id;
                jQuery
                    .ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: "json",
                        data: {
                            action: "sln_service",
                            method: "save_cat_position",
                            data: "positions=" + pos,
                        },
                    })
                    .done(function(msg) {
                        console.log(msg);
                    });
            }
        },
    });
    $("tbody").disableSelection();
}

function sln_initAttendantManagement($) {
    $("tbody").sortable({
        start: function(event, ui) {
            $(ui.item).data("startindex", ui.item.index());
        },
        stop: function(event, ui) {
            var $item = ui.item;
            var startIndex = $item.data("startindex") + 1;
            var newIndex = $item.index() + 1;
            if (newIndex != startIndex) {
                var i = 1,
                    pos = [];
                $("tr").map(function() {
                    var post = $(this)[0].id;
                    if (post.indexOf("post-") > -1) {
                        post = post.split("post-")[1];
                        pos.push(post + "_" + i);
                        i++;
                    }
                });
                jQuery
                    .ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: "json",
                        data: {
                            action: "sln_attendant",
                            method: "save_position",
                            data: "positions=" + pos,
                        },
                    })
                    .done(function(msg) {
                        console.log(msg);
                    });
            }
        },
    });
    $("tbody").disableSelection();
}

function sln_dataAttendant($) {
    $("select[data-attendant]").each(function() {
        var serviceVal = $(this).attr("data-service");
        var attendantVal = $(this).val();
        var selectHtml = "";
        if (jQuery.inArray(attendantVal, ["", "0"]) !== false) {
            selectHtml += '<option value="" selected >n.d.</option>';
        }
        $.each(servicesData[serviceVal].attendants, function(index, value) {
            selectHtml +=
                '<option value="' +
                value +
                '" ' +
                (value == attendantVal ? "selected" : "") +
                " >" +
                attendantsData[value] +
                "</option>";
        });
        $(this)
            .html(selectHtml)
            .trigger("change");
    });
}
