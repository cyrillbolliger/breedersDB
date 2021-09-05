/**
 * handles all the marks stuff
 */
function MarksModule(General) {
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
     * initialize
     */
    this.initValidationRulesCreator = function () {
        var $mark_field_type = $('.mark_field_type');

        $mark_field_type.change(function () {
            self.showValidationRulesCreatorFields($(this).val());
        });

        self.showValidationRulesCreatorFields($mark_field_type.val());
    };

    /**
     * Show / hide the validation rules creator fields respection the selected
     * mark field type.
     *
     * @param  {String} val the field type
     */
    this.showValidationRulesCreatorFields = function (val) {
        var $all = $('.mark_validation_rule');

        switch (val) {
            case 'VARCHAR':
                this.removeControl($all);
                break;
            case 'BOOLEAN':
                this.removeControl($all);
                break;
            case 'DATE':
                this.removeControl($all);
                break;
            default:
                this.addControl($all);
        }
    };

    /**
     * hide and disable a given control
     *
     * @param  {jQuery} obj control we want to hide and disable
     */
    this.removeControl = function (obj) {
        $(obj).attr('disabled', 'disabled');
        $(obj).parent().hide();
    };

    /**
     * re-add and re-enable a removed control
     *
     * @param {jQuery} obj control we want to add again
     */
    this.addControl = function (obj) {
        $(obj).removeAttr('disabled');
        $(obj).parent().show();
    };

    /**
     * load mark form field when selected in the form editor
     */
    this.addMarkFormFieldInit = function () {
        $('.add_mark_form_field')
            .off('change')
            .change(function () {
                $.ajax({
                    url: webroot + 'mark-form-properties/get/' + $(this).val() + '/' + $(this).attr('data-mode'),
                    success: function (resp) {
                        $('.mark_form_fields').append(resp);
                        self.initNewField();
                    },
                    method: 'GET',
                    dataType: 'html',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    }
                });
            });
    };

    /**
     * Instantiate actions for newly added fields (applied to all fields)
     */
    this.initNewField = function () {
        self.makeFormFieldsSortable();
        self.makeFormFieldsDeletable();
        self.General.instantiateDatepicker();
    };

    /**
     * Enable sortable functionality for form fields. Grab them by the handle.
     */
    this.makeFormFieldsSortable = function () {
        $('.mark_form_fields.sortable').sortable({
            handle: '.sortable_handle'
        });
    };

    /**
     * Enable deletable functionality for form fields.
     */
    this.makeFormFieldsDeletable = function () {
        $('.mark_form_fields .delete_button')
            .off('click')
            .click(function () {
                var del = confirm(trans.delete_element + ' ' + $(this).prev().find('label').first().text() + '?');
                if (del === true) {
                    $(this).parents('.deletable_element').remove();
                }
            });
    };

    /**
     * Load fields of selected mark form
     */
    this.loadFormFields = function () {
        $('.form-field-selector').change(function () {
            $.ajax({
                url: webroot + 'marks/get-form-fields/' + $(this).val(),
                success: function (resp) {
                    $('.mark_form_fields_wrapper').html(resp);
                    self.initNewField();
                },
                method: 'GET',
                dataType: 'html',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                }
            });
        });
    };

    /**
     * Initialize the property loader for the scanner code generation form
     */
    this.initScannerCodeGenerationPropertyLoader = function () {
        $('.select_property').change(function () {
            $.ajax({
                url: webroot + 'mark-form-properties/get/' + $(this).val() + '/field_scanner_code_mode',
                success: function (resp) {
                    var $container = $('#mark_value_wrapper'),
                        $el = $('.replace_me'),
                        name = $el.attr('name'),
                        id = $el.attr('id'),
                        cls = $el.attr('class');

                    $container.html($(resp));
                    $container.find('input')
                        .attr('name', name)
                        .addClass(cls)
                        .attr('id', id);

                    self.General.instantiateDatepicker();
                },
                method: 'GET',
                dataType: 'html',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                }
            });
        });
    };

    /**
     * Unlock scanner field as soon as a mark form was chosen
     */
    this.unlockScannerField = function () {
        if ($('#mark-form-id').val()) {
            $('.scanner_mark_field')
                .removeAttr('disabled')
                .focus();
        } else {
            $('#mark-form-id').change(function () {
                $('.scanner_mark_field')
                    .removeAttr('disabled')
                    .focus();
            });
        }
    };

    /**
     * process scanner input
     */
    this.byScanner = function () {
        var $scanner = $('.scanner_mark_field').first();

        $scanner.bindWithDelay('keyup paste', function () {
            if (0 < $scanner.val().length) {
                self.processScannerCode($scanner);
            }
        }, 200);
    };

    /**
     * search tree from given value
     *
     * @param val
     */
    this.getTree = function (val) {
        var $container = $('#tree_container').first();
        var $searching = $('#searching').first();

        $.ajax({
            url: webroot + '/trees/getTree',
            data: {
                fields: ['publicid'],
                element: 'get_tree',
                term: val,
                printable: 'with_date'
            },
            success: function (resp, status) {
                if ('success' === status) {
                    $container.html(resp);
                    self.General.beep('success');
                } else {
                    $container.html('<div class="nothing_found">' + trans.no_tree_found + '</div>');
                    self.General.beep('error');
                }
                $searching.hide();
                $('.scanner_mark_field').first().focus();
            },
            dataType: 'html',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $searching.show();
            }
        });
    };

    /**
     * Get mark from given code and set correct value
     *
     * @param val
     */
    this.getScannerMark = function (val) {
        var $searching = $('#searching').first();

        $.ajax({
            url: webroot + 'mark-scanner-codes/get-mark',
            data: {
                term: val
            },
            success: function (resp) {
                self.setMark($.parseJSON(resp).data);
                $searching.hide();
                $('.scanner_mark_field').first().focus();
            },
            error: function () {
                self.General.beep('error');
                $searching.hide();
                $('.scanner_mark_field').first().focus();
            },
            dataType: 'html',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $searching.show();
            }
        });
    };

    /**
     * Set mark value from server response of scanner code request
     *
     * @param data
     */
    this.setMark = function (data) {
        var input_id = '#mark-form-fields-mark-form-properties-' + data.mark_form_property_id + '-mark-values-value';
        var radio_id = '#mark-form-fields-mark-form-properties-' + data.mark_form_property_id + '-mark-values-value-' + data.mark_value;
        var $el = 1 === $(input_id).length ? $(input_id) : $(radio_id);

        if (1 === $el.length) {
            if ('radio' === $el.attr('type')) {
                $el.attr('checked', 'checked');
            } else {
                if ('now' === data.mark_value && $el.attr('data-date')) {
                    $el.val(this.currentDate());
                } else {
                    $el.val(data.mark_value);
                }
            }
            self.General.beep('success');
        } else {
            self.General.beep('error').addEventListener("ended", function () {
                alert(String(trans.matching_elements).format($el.length));
            });
        }
    };

    this.currentDate = function () {
        var today = new Date();
        return ('0' + today.getDate()).slice(-2) + '.' + ('0' + (today.getMonth() + 1)).slice(-2) + '.' + today.getFullYear();
    };

    /**
     * Submit form
     */
    this.submitForm = function () {
        var $form = $('form');
        var $inputs = $form.find('input, select, textarea');
        var valid = true;
        var $tree_id = $('#tree_id');

        $inputs.each(function () {
            if (!$(this)[0].checkValidity()) {
                valid = false;
            }
        });

        if (0 === $tree_id.length || '' === $tree_id.val()) {
            valid = false;
        }

        if (valid) {
            self.General.beep('success2').addEventListener("ended", function () {
                $('button[type=submit]').trigger('click');
            });
        } else {
            self.General.beep('error');
        }
    };

    /**
     * detect what was scanned and call correct action (get tree, set mark, submit form)
     *
     * @param $scanner
     */
    this.processScannerCode = function ($scanner) {
        var val = $scanner.val();

        if (null !== val.match(/^M\d{5}$/)) {
            self.getScannerMark(val);
        } else if (null !== val.match(/^SUBMIT$/)) {
            self.submitForm();
        } else {
            self.getTree(val);
        }

        $scanner.val('');
    };
}

module.exports = MarksModule;
