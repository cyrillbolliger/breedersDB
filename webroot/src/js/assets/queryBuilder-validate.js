/**
 * overwrite jQuery-QueryBuilder validate method to
 * - use moment.js in strict mode
 * - have custom validation messages for nan values etc.
 */

/**
 * Customized validation function
 * @param {Rule} rule
 * @param {string|string[]} value
 * @returns {array|boolean} true or error array
 * @throws ConfigError
 * @private
 */
QueryBuilder.extend({
    _validateValue: function (rule, value) {

        var filter = rule.filter;
        var operator = rule.operator;
        var validation = filter.validation || {};
        var result = true;
        var tmp, tempValue;

        if (rule.operator.nb_inputs === 1) {
            value = [value];
        }

        for (var i = 0; i < operator.nb_inputs; i++) {
            if (!operator.multiple && $.isArray(value[i]) && value[i].length > 1) {
                result = ['operator_not_multiple', operator.type, this.translate('operators', operator.type)];
                break;
            }

            switch (filter.input) {
                case 'radio':
                    if (value[i] === undefined || value[i].length === 0) {
                        if (!validation.allow_empty_value) {
                            result = this.getValidationMessage(validation, 'allow_empty_value', 'radio_empty');
                        }
                        break;
                    }
                    break;

                case 'checkbox':
                    if (value[i] === undefined || value[i].length === 0) {
                        if (!validation.allow_empty_value) {
                            result = this.getValidationMessage(validation, 'allow_empty_value', 'checkbox_empty');
                        }
                        break;
                    }
                    break;

                case 'select':
                    if (value[i] === undefined || value[i].length === 0 || (filter.placeholder && value[i] == filter.placeholder_value)) {
                        if (!validation.allow_empty_value) {
                            result = this.getValidationMessage(validation, 'allow_empty_value', 'select_empty');
                        }
                        break;
                    }
                    break;

                default:
                    tempValue = $.isArray(value[i]) ? value[i] : [value[i]];

                    for (var j = 0; j < tempValue.length; j++) {
                        switch (QueryBuilder.types[filter.type]) {
                            case 'string':
                                if (tempValue[j] === undefined || tempValue[j].length === 0) {
                                    if (!validation.allow_empty_value) {
                                        result = this.getValidationMessage(validation, 'allow_empty_value', 'string_empty');
                                    }
                                    break;
                                }
                                if (validation.min !== undefined) {
                                    if (tempValue[j].length < parseInt(validation.min)) {
                                        result = [this.getValidationMessage(validation, 'min', 'string_exceed_min_length'), validation.min];
                                        break;
                                    }
                                }
                                if (validation.max !== undefined) {
                                    if (tempValue[j].length > parseInt(validation.max)) {
                                        result = [this.getValidationMessage(validation, 'max', 'string_exceed_max_length'), validation.max];
                                        break;
                                    }
                                }
                                if (validation.format) {
                                    if (typeof validation.format == 'string') {
                                        validation.format = new RegExp(validation.format);
                                    }
                                    if (!validation.format.test(tempValue[j])) {
                                        result = [this.getValidationMessage(validation, 'format', 'string_invalid_format'), validation.format];
                                        break;
                                    }
                                }
                                break;

                            case 'number':
                                if (tempValue[j] === undefined || tempValue[j].length === 0) {
                                    if (!validation.allow_empty_value) {
                                        result = this.getValidationMessage(validation, 'number_nan', 'number_nan');
                                    }
                                    break;
                                }
                                if (isNaN(tempValue[j])) {
                                    result = this.getValidationMessage(validation, 'number_nan', 'number_nan');
                                    break;
                                }
                                if (filter.type == 'integer') {
                                    if (parseInt(tempValue[j]) != tempValue[j]) {
                                        result = this.getValidationMessage(validation, 'number_not_integer', 'number_not_integer');
                                        break;
                                    }
                                }
                                else {
                                    if (parseFloat(tempValue[j]) != tempValue[j]) {
                                        result = this.getValidationMessage(validation, 'number_not_double', 'number_not_double');
                                        break;
                                    }
                                }
                                if (validation.min !== undefined) {
                                    if (tempValue[j] < parseFloat(validation.min)) {
                                        result = [this.getValidationMessage(validation, 'min', 'number_exceed_min'), validation.min];
                                        break;
                                    }
                                }
                                if (validation.max !== undefined) {
                                    if (tempValue[j] > parseFloat(validation.max)) {
                                        result = [this.getValidationMessage(validation, 'max', 'number_exceed_max'), validation.max];
                                        break;
                                    }
                                }
                                if (validation.step !== undefined && validation.step !== 'any') {
                                    var v = (tempValue[j] / validation.step).toPrecision(14);
                                    if (parseInt(v) != v) {
                                        result = [this.getValidationMessage(validation, 'step', 'number_wrong_step'), validation.step];
                                        break;
                                    }
                                }
                                break;

                            case 'datetime':
                                if (tempValue[j] === undefined || tempValue[j].length === 0) {
                                    if (!validation.allow_empty_value) {
                                        result = this.getValidationMessage(validation, 'allow_empty_value', 'datetime_empty');
                                    }
                                    break;
                                }

                                // we need MomentJS
                                if (validation.format) {
                                    if (!('moment' in window)) {
                                        Utils.error('MissingLibrary', 'MomentJS is required for Date/Time validation. Get it here http://momentjs.com');
                                    }

                                    var datetime = moment(tempValue[j], validation.format, true);
                                    if (!datetime.isValid()) {
                                        result = [this.getValidationMessage(validation, 'format', 'datetime_invalid'), validation.format];
                                        break;
                                    }
                                    else {
                                        if (validation.min) {
                                            if (datetime < moment(validation.min, validation.format)) {
                                                result = [this.getValidationMessage(validation, 'min', 'datetime_exceed_min'), validation.min];
                                                break;
                                            }
                                        }
                                        if (validation.max) {
                                            if (datetime > moment(validation.max, validation.format)) {
                                                result = [this.getValidationMessage(validation, 'max', 'datetime_exceed_max'), validation.max];
                                                break;
                                            }
                                        }
                                    }
                                }
                                break;

                            case 'boolean':
                                if (tempValue[j] === undefined || tempValue[j].length === 0) {
                                    if (!validation.allow_empty_value) {
                                        result = this.getValidationMessage(validation, 'boolean_not_valid', 'boolean_not_valid');
                                    }
                                    break;
                                }
                                tmp = ('' + tempValue[j]).trim().toLowerCase();
                                if (tmp !== 'true' && tmp !== 'false' && tmp !== '1' && tmp !== '0' && tempValue[j] !== 1 && tempValue[j] !== 0) {
                                    result = this.getValidationMessage(validation, 'boolean_not_valid', 'boolean_not_valid');
                                    break;
                                }
                        }

                        if (result !== true) {
                            break;
                        }
                    }
            }

            if (result !== true) {
                break;
            }
        }

        return result;
    }
});