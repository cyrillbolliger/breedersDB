/**
 * handles all the queries stuff
 */
function QueriesViewSelectorModule(General) {
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
        self.onViewSelectorChange();
        self.setViewSelectorInitState();
        self.bindRootViewSelectorEvents();
        self.setRootViewSelectorInitState();
        self.uncheckOrphanedProperties();
        self.bindMarkPropertySelectorEvents();
        self.bindMarkPropertyFilterOperationSelectorEvents();
        self.bindBreedingObjectAggregationModeSelectorEvents();
        self.setMarkPropertyFilterSelectorInitState();
    };

    /**
     * set field selector on click events
     */
    this.onViewSelectorChange = function () {
        $('.view-selector').on('change', function () {
            self.setFieldVisibilityFrom($(this));
            self.enableAssociated();
        });
    };

    /**
     * Uncheck all properties if table is unchecked before form is submitted
     */
    this.uncheckOrphanedProperties = function () {
        $('#query_builder_form').submit(function () {
            $('.view-selector').not(':checked').each(function () {
                $('.' + $(this).attr('name') + '-field-selector-container')
                    .find('input[type="checkbox"]')
                    .prop('checked', false);
            });
        });
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
            if ('MarksView' === $(this).val()) {
                $('.breeding-object-aggregation-mode-selector').show();
                $('.mark-property').show();
                $('.MarksView-field-selector-container > .regular-property').hide();
            } else {
                $('.breeding-object-aggregation-mode-selector').hide();
                $('.mark-property').hide();
                $('.MarksView-field-selector-container > .regular-property').show();
                $('.mark-property-selector').prop('checked', false);
            }
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
            possible = self.getPossibleAssociationsOf(valid[i]);
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
     * Return array with possible associations respecting the limitations of the marks
     *
     * @param root string
     *
     * @return array
     */
    this.getPossibleAssociationsOf = function (root) {
        var possible = query_builder_associations[root];
        if ('MarksView' === $('#root-view').val()) {
            switch ($('#breeding-object-aggregation-mode').val()) {
                case 'trees':
                    possible = ['TreesView'];
                    break;

                case 'varieties':
                    possible = ['VarietiesView'];
                    break;

                case 'batches':
                    possible = ['BatchesView'];
                    break;

                case 'convar':
                    possible = ['VarietiesView'];
                    break;

                default:
                    throw new Error('Unknown breeding object aggreation mode.');
            }
        }
        return possible;
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
            $.each(self.getPossibleAssociationsOf(tmp), function (idx, val) {
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
     * Bind events of the mark property check boxes
     */
    this.bindMarkPropertySelectorEvents = function () {
        $('.mark-property-selector').click(function () {
            $('.mark-property-mode').trigger('change');
            self.setMarkPropertyFilterSelectorVisibility($(this));
        });
    };

    /**
     * Set the initial state of the mark property filter selectors
     */
    this.setMarkPropertyFilterSelectorInitState = function() {
        $('.mark-property-selector').each(function(){
            $('.mark-property-mode').trigger('change');
            self.setMarkPropertyFilterSelectorVisibility($(this));
        });
    };

    /**
     * control the visibility of the mark property mode selector
     *
     * @param $switch jQuery object of a mark property selector
     */
    this.setMarkPropertyFilterSelectorVisibility = function ($switch) {
        if ($switch.is(':checked')) {
            $switch.parent().find('.mark-property-filter-selector').show();
        } else {
            $switch.parent().find('.mark-property-filter-selector').hide();
        }
    };

    /**
     * Bind events breeding object aggregation mode selector
     */
    this.bindBreedingObjectAggregationModeSelectorEvents = function () {
        $('#breeding-object-aggregation-mode').change(function () {
            $('#root-view').trigger('change');
        });
    };

    /**
     * Bind events for the filter operator selector of mark properties
     */
    this.bindMarkPropertyFilterOperationSelectorEvents = function () {
        $('.mark-property-filter-operator').change(function () {
            self.setMarkPropertyFilterValueVisibility($(this));
        });
    };

    /**
     * Control the visibility of the mark property filter value.
     *
     * Make invisible if the filter operation is empty or doesn't require any input.
     *
     * @param $switch jQuery object of a mark filter operator selector
     */
    this.setMarkPropertyFilterValueVisibility = function ($switch) {
        var hide = ['', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null'];
        if (-1 !== $.inArray($switch.val(), hide)) {
            $switch.parent().find('.mark-property-filter-value').hide();
        } else {
            $switch.parent().find('.mark-property-filter-value').show();
        }
    };
}

module.exports = QueriesViewSelectorModule;
