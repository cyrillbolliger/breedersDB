global.moment = require('moment');
global.QueryBuilder = require('jQuery-QueryBuilder');

require('../../assets/queryBuilder-validate');
require('jQuery-QueryBuilder/src/plugins/change-filters/plugin');

/**
 * handles all the queries stuff
 */
function QueriesWhereBuilderModule(General) {
    "use strict";

    /**
     * having our class always accessible can get handy
     */
    var self = this;

    /**
     * make the general module accessible
     */
    this.General = General;

    /**
     * gets called on startup
     */
    this.init = function () {
        self.$query_where_builder = $('#query_where_builder');

        // stop here if we are on a page without query builder
        if (0 === self.$query_where_builder.length) {
            return;
        }

        self.autoAddUtilitiesSwitch = true;

        self.autoAddUtilities();
        self.instantiateWhereBuilder();
        self.onViewSelectorChange();
        self.bindSubmitEvents();
        self.bindValidatorEvents();
    };

    /**
     * Listen to view selector changes and update filters
     */
    this.onViewSelectorChange = function () {
        $('.view-selector').on('change', function () {
            self.updateFilters();
        });
    };

    /**
     * Instantiate the query where builder
     */
    this.instantiateWhereBuilder = function () {
        var filters = self.getFilters();

        var icons = {
            add_group: 'fa fa-plus-square',
            add_rule: 'fa fa-plus-circle',
            remove_group: 'fa fa-minus-square',
            remove_rule: 'fa fa-minus-circle',
            error: 'fa fa-exclamation-triangle'
        };

        var operators = [
            {type: 'equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean']},
            {type: 'not_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean']},
            {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime']},
            {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime']},
            {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime']},
            {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime']},
            {type: 'begins_with', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'not_begins_with', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'contains', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'not_contains', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'ends_with', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'not_ends_with', nb_inputs: 1, multiple: false, apply_to: ['string']},
            {type: 'is_empty', nb_inputs: 0, multiple: false, apply_to: ['string']},
            {type: 'is_not_empty', nb_inputs: 0, multiple: false, apply_to: ['string']},
            {type: 'is_null', nb_inputs: 0, multiple: false, apply_to: ['number', 'datetime', 'boolean']},
            {type: 'is_not_null', nb_inputs: 0, multiple: false, apply_to: ['number', 'datetime', 'boolean']}
        ];

        // handles a rare edge case, with an unknown source, where
        // the query builder contains filter values that are not
        // available (because the fields are not checked)
        if (this.hasInvalidRuleProperties(filters, query_where_builder_rules)) {
            alert(trans.invalid_query_builder_rules);
            query_where_builder_rules = {
                condition: "AND",
                rules: [],
                valid: true
            };
        }

        this.$query_where_builder.queryBuilder({
            icons: icons,
            filters: filters,
            rules: query_where_builder_rules,
            operators: operators,
            allow_empty: true
        });
    };

    /**
     * Returns true if the ruleset contains elements that are not available
     * (= not in the filters)
     *
     * @param filters
     * @param ruleset
     * @returns {boolean}
     */
    this.hasInvalidRuleProperties = function (filters, ruleset) {
        var invalid;
        if (ruleset.hasOwnProperty('rules')) {
            for (var i = 0; i < ruleset.rules.length; i++) {
                invalid = this.hasInvalidRuleProperties(filters, ruleset.rules[i]);
                if (invalid) {
                    return invalid;
                }
            }
        } else {
            if (!ruleset.hasOwnProperty('id')) {
                return false;
            }

            return !this.inValues(filters, ruleset.id);
        }
    };

    /**
     * Set the filters of the query where builder
     */
    this.getFilters = function () {
        var $checked = $('.view-selector:checked');
        var filters = [];
        var tmp;

        $checked.each(function () {
            tmp = $(this).attr('name');
            $.each(query_where_builder_filters[tmp], function (key, val) {
                filters.push(val);
            });
        });

        return filters;
    };

    /**
     * Update filters of query builder
     */
    this.updateFilters = function () {

        // turn off autoAddUtilities for better performance
        self.autoAddUtilitiesSwitch = false;

        var newFilters = this.getFilters();
        var oldFilters = this.$query_where_builder[0].queryBuilder.filters;

        newFilters.forEach(function (nel) {
            if (!self.inValues(oldFilters, nel.id)) {
                self.$query_where_builder.queryBuilder('addFilter', nel);
            }
        });

        // remove old filters after adding new ones to prevent an empty filter list
        oldFilters.forEach(function (oel) {
            if (!self.inValues(newFilters, oel.id)) {
                self.$query_where_builder.queryBuilder('removeFilter', oel.id, true);
            }
        });

        // turn on autoAddUtilities again
        self.autoAddUtilitiesSwitch = true;

        // manually trigger a change
        self.addUtilities();
    };

    /**
     * test if value can be found in in an items.id of the array
     *
     * @param array array
     * @param value string
     * @returns boolean
     */
    this.inValues = function (array, value) {
        return array.some(function (item) {
            return value === item.id;
        });
    };

    /**
     * Write data of the query builder in the hidden #where_query field before submitting the form
     */
    this.saveQueryWhereData = function () {
        var rules = JSON.stringify(self.$query_where_builder.queryBuilder('getRules'));
        $('#where-query').val(rules);
        self.$query_where_builder.remove();
    };

    /**
     * hook into the form submission
     */
    this.bindSubmitEvents = function () {
        var valid = false;
        $('#query_builder_form').submit(function (e) {
            valid = self.validateMarkProperties(e);
            if (valid) {
                self.saveQueryWhereData();
            } else {
                e.preventDefault();
            }
        });
    };

    /**
     * remove all filter values of unchecked mark properties
     * (so we dont have any validation issues of hidden fields)
     */
    this.clearUncheckedMarkPropertyFilters = function () {
        $('.mark-property-selector:not(:checked)').each(function () {
            $(this).parent().find('.mark-property-filter-value').val('');
        });
    };

    /**
     * warn if marks view is selected as root table but no mark was chosen to display
     */
    this.validateMarkProperties = function () {
        if ('MarksView' !== $('#root-view').val()) {
            return true;
        }

        if (0 === $('.mark-property-selector:checked').length) {
            alert(trans.no_marks_selected);
            return false;
        }

        return true;
    };


    /**
     * Handle query where builder validation
     */
    this.bindValidatorEvents = function () {
        var valid = false;
        $('.validate_query_where_builder').on('click submit change', function (e) {

            // remove all values of hidden property filters first!
            self.clearUncheckedMarkPropertyFilters();

            // start off with an empty error list
            $('.query_builder_validation_error').find('ul').html('');

            // validate
            valid = self.$query_where_builder.queryBuilder('validate');

            // prevent submitting if not valid
            if (!valid) {
                e.preventDefault();
            }
        });

        // for each rule, that is not valid
        self.$query_where_builder.on('validationError.queryBuilder', function (e, rule, error, value) {
            $('.query_builder_validation_error').show()
                .find('ul')
                .append("<li class='" + rule.id + "'>" + error[0] + "</li>");
        });
    };

    /**
     * add utilities and needed classes to (new) rules
     */
    this.autoAddUtilities = function () {
        self.$query_where_builder.on('afterCreateRuleFilters.queryBuilder afterUpdateRuleFilter.queryBuilder', function (event, rule) {
            if (null !== rule.filter && undefined !== rule.filter.type && 'date' === rule.filter.type) {
                rule.$el.find('.rule-value-container input[type="text"].form-control')
                    .addClass('datepicker');
            }

            self.addUtilities();
        });
    };

    /**
     * add utilities like select2 and datepicker
     */
    this.addUtilities = function () {
        if (self.autoAddUtilitiesSwitch) {
            // wait one second to be sure the query where builder has fully loaded
            window.setTimeout(function () {
                self.General.instantiateSelect2();
                self.General.instantiateDatepicker();
            }, 1000);
        }
    };
}

module.exports = QueriesWhereBuilderModule;
