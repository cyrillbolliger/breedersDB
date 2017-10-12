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
        self.bindValidatorEvents();
        self.saveQueryWhereData();

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

        this.$query_where_builder.queryBuilder({
            icons: icons,
            filters: filters,
            rules: query_where_builder_rules,
            operators: operators
        });
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

        oldFilters.forEach(function (oel) {
            if (!self.inValues(newFilters, oel.id)) {
                self.$query_where_builder.queryBuilder('removeFilter', oel.id, true);
            }
        });

        newFilters.forEach(function (nel) {
            if (!self.inValues(oldFilters, nel.id)) {
                self.$query_where_builder.queryBuilder('addFilter', nel);
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
        $('#query_builder_form').submit(function () {
            var rules = JSON.stringify(self.$query_where_builder.queryBuilder('getRules'));
            $('#where-query').val(rules);
            self.$query_where_builder.remove();
        });
    };

    /**
     * Handle query where builder validation
     */
    this.bindValidatorEvents = function () {
        var valid = false;
        $('.validate_query_where_builder').on('click submit change', function (e) {
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
            self.General.instantiateSelect2();
            self.General.instantiateDatepicker();
        }
    };
}

module.exports = QueriesWhereBuilderModule;