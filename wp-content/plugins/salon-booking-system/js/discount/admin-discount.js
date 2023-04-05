"use strict";

var sln_discount_rule_html;
jQuery(function ($) {
    var $rule_wrapper  = $('.sln_discount_rule[data-rule-id=__new_discount_rule__]').wrap('<p></p>').closest('p');
    sln_discount_rule_html = $rule_wrapper.html();
    $rule_wrapper.remove();

    sln_bindDiscountTypeChange($);
    sln_bindDiscountRuleModeChange($);
    sln_bindDiscountRuleRemove($);
    sln_bindDiscountRuleAdd($);
});

function sln_bindDiscountTypeChange($) {
    $('[data-type=discount-type]').off('change').on('change', function() {
        $('.sln_discount_type').addClass('hide');
        $('.sln_discount_type--'+$(this).val()).removeClass('hide');
    });
}

function sln_bindDiscountRuleModeChange($) {
    $('[data-type=discount-rule-mode]').off('change').on('change', function() {
        var $rule = $(this).closest('.sln_discount_rule');
        $rule.find('.sln_discount_rule_mode_details').addClass('hide');
        $rule.find('.sln_discount_rule_mode_details--'+$(this).val()).removeClass('hide');
    }).trigger('change');
}

function sln_bindDiscountRuleAdd($) {
    $('[data-action=add-discount-rule]').off('click').on('click', function() {

        var id = 0;
        if ($('.sln_discount_rule').length > 0) {
            id = parseInt($('.sln_discount_rule:last').attr('data-rule-id')) + 1;
        }
        var rule_html = sln_discount_rule_html.replace(/__new_discount_rule__/g, id).replace(/hide/, ''); // remove only first 'hide'

        $('#sln_discount_rules').append($(rule_html));

        sln_bindDiscountRuleModeChange($);
        sln_bindDiscountRuleRemove($);

        sln_createSelect2Full($);
        sln_initDatepickers($);
    });
}

function sln_bindDiscountRuleRemove($) {
    $('[data-action=remove-discount-rule]').off('click').on('click', function() {
        $(this).closest('.sln_discount_rule').remove();
    });
}