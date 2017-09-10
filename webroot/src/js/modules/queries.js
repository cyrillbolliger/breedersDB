/**
 * handles all the queries stuff
 */
function QueriesModule(General) {
    "use strict";

    /**
     * having our class always accessible can get handy
     */
    var self = this;

    /**
     * The container that hold the query where builder
     *
     * @type jQuery object
     */
    var $query_where_builder;

    /**
     * make the general module accessible
     */
    this.General = General;

    /**
     * gets called on startup
     */
    this.init = function () {
        self.$query_where_builder = $('#query_where_builder');

        self.bindViewSelectorEvents();
        self.setViewSelectorInitState();
        self.bindRootViewSelectorEvents();
        self.setRootViewSelectorInitState();
        self.saveQueryWhereData();
    };

    /**
     * set visibility of the field selector on startup
     * respecting the selection of the main table selector
     */
    this.setRootViewSelectorInitState = function () {
        $('#root-view').trigger('change');
    };

    /**
     * bind main table selector events
     */
    this.bindRootViewSelectorEvents = function () {
        var $el;
        $('#root-view').on('change', function () {
            $el = $('input[name="' + $(this).val() + '"].view-selector');
            $el.prop('checked', true);
            $('input.view-selector')
                .prop('disabled', false)
                .removeClass('root_view_lock');
            $el
                .prop('disabled', true)
                .addClass('root_view_lock');
            $el.trigger('change');
            $('label[for="' + $el.attr('id') + '"]').removeClass('disabled-checkbox');
        });
    };

    /**
     * set visibility of the field selector on startup
     */
    this.setViewSelectorInitState = function () {
        $('.view-selector').each(function () {
            self.setFieldVisibilityFrom($(this));
        });
    };

    /**
     * set field selector on click events
     */
    this.bindViewSelectorEvents = function () {
        $('.view-selector').on('click change', function () {
            self.setFieldVisibilityFrom($(this));
            self.enableAssociated();
            self.setQueryWhereBuildersFilters();
        });
    };

    /**
     * Check if non connected entities are selected and deselect them if found.
     * Inform the user with an alert.
     *
     * @param $checked jQuery
     */
    this.validateAssociations = function ($checked) {
        var input = [];
        var valid = [];
        var $invalid;
        var possible;
        var in_input;
        var i = 0;
        var name;
        var root_view;

        // if none is checked exit
        if (0 === $checked.length) {
            return;
        }

        $checked.each(function () {
            input.push($(this).attr('name'));
        });

        // root view must be first to be always valid
        root_view = $('#root-view').val();
        input.splice(input.indexOf(root_view), 1);
        input.unshift(root_view);

        valid.push(input.shift());

        while (i < valid.length) {
            possible = query_builder_associations[valid[i]];
            for (var j = 0; j < possible.length; j++) {
                in_input = $.inArray(possible[j], input);
                if (0 <= in_input && -1 === $.inArray(possible[j], valid)) {
                    valid.push(possible[j]);
                    input.splice(in_input, 1);
                }
            }
            i++;
        }

        // the elements remaining in 'input' are invalid
        $.each(input, function (idx, invalid) {
            $invalid = $('input[name="' + invalid + '"]');
            $invalid.prop('checked', false);
            self.setFieldVisibilityFrom($invalid);
            self.enableCheckbox($invalid, false);
            name = $('label[for="' + $invalid.filter(':checkbox').attr('id') + '"]').text();
            alert(String(trans.impossible_selection).format(name));
        });
    };

    /**
     * Enable or disable table selector checkboxes regarding their associations
     */
    this.enableAssociated = function () {
        var enable = [];
        var tmp;
        var $checked = $('.view-selector:checked');
        var $view_selectors = $('.view-selector');

        // disable all to start clean
        self.enableCheckbox($view_selectors, false);

        // if none is checked enable all and exit
        if (0 === $checked.length) {
            self.enableCheckbox($view_selectors, true);
            return;
        }

        // mark invalid associations
        self.validateAssociations($checked);

        // query again because it may have changed
        $checked = $('.view-selector:checked');

        // put all names of associated checkboxes into the 'enable' array
        $checked.each(function () {
            tmp = $(this).attr('name');
            enable.push(tmp);
            $.each(query_builder_associations[tmp], function (idx, val) {
                enable.push(val);
            });
        });

        // enable associated checkboxes
        $.each(enable, function (idx, val) {
            self.enableCheckbox($('input[name="' + val + '"]'), true);
        });

    };

    /**
     * Enable or disable given checkboxes and set '.disabled-checkbox' class to its labels
     *
     * @param $elements jQuery object with the checkboxes to enable/disable
     * @param enable boolean
     */
    this.enableCheckbox = function ($elements, enable) {
        var $el;
        $elements.each(function () {
            $el = $(this).not('.root_view_lock');
            if (enable) {
                $el.prop('disabled', false);
                $('label[for="' + $el.attr('id') + '"]').removeClass('disabled-checkbox');
            } else {
                $el.prop('disabled', true);
                $('label[for="' + $el.attr('id') + '"]').addClass('disabled-checkbox');
            }
        });
    };

    /**
     * set visibility of fields corresponding to its switch
     *
     * @param $switch jQuery object of a checkbox with name of the view
     */
    this.setFieldVisibilityFrom = function ($switch) {
        var name = $switch.attr('name');
        var $target = $('.' + name + '-field-selector-container');
        var state = $switch.is(':checked');

        if (state) {
            $target.show();
        } else {
            $target.hide();
        }
    };

    /**
     * Instantiate the query where builder
     */
    this.instantiateQueryWhereBuilder = function (filters) {
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
    this.setQueryWhereBuildersFilters = function () {
        var $checked = $('.view-selector:checked');
        var filters = [];
        var tmp;

        $checked.each(function () {
            tmp = $(this).attr('name');
            $.each(query_where_builder_filters[tmp], function (key, val) {
                filters.push(val);
            });
        });

        // destroy old query builder because filters cant be changed
        this.$query_where_builder.queryBuilder('destroy');

        // reinstantiate a new onw with the new filters
        this.instantiateQueryWhereBuilder(filters);
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
}

module.exports = QueriesModule;