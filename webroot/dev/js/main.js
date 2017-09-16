(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
// doT.js
// 2011-2014, Laura Doktorova, https://github.com/olado/doT
// Licensed under the MIT license.

(function () {
	"use strict";

	var doT = {
		name: "doT",
		version: "1.1.1",
		templateSettings: {
			evaluate:    /\{\{([\s\S]+?(\}?)+)\}\}/g,
			interpolate: /\{\{=([\s\S]+?)\}\}/g,
			encode:      /\{\{!([\s\S]+?)\}\}/g,
			use:         /\{\{#([\s\S]+?)\}\}/g,
			useParams:   /(^|[^\w$])def(?:\.|\[[\'\"])([\w$\.]+)(?:[\'\"]\])?\s*\:\s*([\w$\.]+|\"[^\"]+\"|\'[^\']+\'|\{[^\}]+\})/g,
			define:      /\{\{##\s*([\w\.$]+)\s*(\:|=)([\s\S]+?)#\}\}/g,
			defineParams:/^\s*([\w$]+):([\s\S]+)/,
			conditional: /\{\{\?(\?)?\s*([\s\S]*?)\s*\}\}/g,
			iterate:     /\{\{~\s*(?:\}\}|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\}\})/g,
			varname:	"it",
			strip:		true,
			append:		true,
			selfcontained: false,
			doNotSkipEncoded: false
		},
		template: undefined, //fn, compile template
		compile:  undefined, //fn, for express
		log: true
	}, _globals;

	doT.encodeHTMLSource = function(doNotSkipEncoded) {
		var encodeHTMLRules = { "&": "&#38;", "<": "&#60;", ">": "&#62;", '"': "&#34;", "'": "&#39;", "/": "&#47;" },
			matchHTML = doNotSkipEncoded ? /[&<>"'\/]/g : /&(?!#?\w+;)|<|>|"|'|\//g;
		return function(code) {
			return code ? code.toString().replace(matchHTML, function(m) {return encodeHTMLRules[m] || m;}) : "";
		};
	};

	_globals = (function(){ return this || (0,eval)("this"); }());

	/* istanbul ignore else */
	if (typeof module !== "undefined" && module.exports) {
		module.exports = doT;
	} else if (typeof define === "function" && define.amd) {
		define(function(){return doT;});
	} else {
		_globals.doT = doT;
	}

	var startend = {
		append: { start: "'+(",      end: ")+'",      startencode: "'+encodeHTML(" },
		split:  { start: "';out+=(", end: ");out+='", startencode: "';out+=encodeHTML(" }
	}, skip = /$^/;

	function resolveDefs(c, block, def) {
		return ((typeof block === "string") ? block : block.toString())
		.replace(c.define || skip, function(m, code, assign, value) {
			if (code.indexOf("def.") === 0) {
				code = code.substring(4);
			}
			if (!(code in def)) {
				if (assign === ":") {
					if (c.defineParams) value.replace(c.defineParams, function(m, param, v) {
						def[code] = {arg: param, text: v};
					});
					if (!(code in def)) def[code]= value;
				} else {
					new Function("def", "def['"+code+"']=" + value)(def);
				}
			}
			return "";
		})
		.replace(c.use || skip, function(m, code) {
			if (c.useParams) code = code.replace(c.useParams, function(m, s, d, param) {
				if (def[d] && def[d].arg && param) {
					var rw = (d+":"+param).replace(/'|\\/g, "_");
					def.__exp = def.__exp || {};
					def.__exp[rw] = def[d].text.replace(new RegExp("(^|[^\\w$])" + def[d].arg + "([^\\w$])", "g"), "$1" + param + "$2");
					return s + "def.__exp['"+rw+"']";
				}
			});
			var v = new Function("def", "return " + code)(def);
			return v ? resolveDefs(c, v, def) : v;
		});
	}

	function unescape(code) {
		return code.replace(/\\('|\\)/g, "$1").replace(/[\r\t\n]/g, " ");
	}

	doT.template = function(tmpl, c, def) {
		c = c || doT.templateSettings;
		var cse = c.append ? startend.append : startend.split, needhtmlencode, sid = 0, indv,
			str  = (c.use || c.define) ? resolveDefs(c, tmpl, def || {}) : tmpl;

		str = ("var out='" + (c.strip ? str.replace(/(^|\r|\n)\t* +| +\t*(\r|\n|$)/g," ")
					.replace(/\r|\n|\t|\/\*[\s\S]*?\*\//g,""): str)
			.replace(/'|\\/g, "\\$&")
			.replace(c.interpolate || skip, function(m, code) {
				return cse.start + unescape(code) + cse.end;
			})
			.replace(c.encode || skip, function(m, code) {
				needhtmlencode = true;
				return cse.startencode + unescape(code) + cse.end;
			})
			.replace(c.conditional || skip, function(m, elsecase, code) {
				return elsecase ?
					(code ? "';}else if(" + unescape(code) + "){out+='" : "';}else{out+='") :
					(code ? "';if(" + unescape(code) + "){out+='" : "';}out+='");
			})
			.replace(c.iterate || skip, function(m, iterate, vname, iname) {
				if (!iterate) return "';} } out+='";
				sid+=1; indv=iname || "i"+sid; iterate=unescape(iterate);
				return "';var arr"+sid+"="+iterate+";if(arr"+sid+"){var "+vname+","+indv+"=-1,l"+sid+"=arr"+sid+".length-1;while("+indv+"<l"+sid+"){"
					+vname+"=arr"+sid+"["+indv+"+=1];out+='";
			})
			.replace(c.evaluate || skip, function(m, code) {
				return "';" + unescape(code) + "out+='";
			})
			+ "';return out;")
			.replace(/\n/g, "\\n").replace(/\t/g, '\\t').replace(/\r/g, "\\r")
			.replace(/(\s|;|\}|^|\{)out\+='';/g, '$1').replace(/\+''/g, "");
			//.replace(/(\s|;|\}|^|\{)out\+=''\+/g,'$1out+=');

		if (needhtmlencode) {
			if (!c.selfcontained && _globals && !_globals._encodeHTML) _globals._encodeHTML = doT.encodeHTMLSource(c.doNotSkipEncoded);
			str = "var encodeHTML = typeof _encodeHTML !== 'undefined' ? _encodeHTML : ("
				+ doT.encodeHTMLSource.toString() + "(" + (c.doNotSkipEncoded || '') + "));"
				+ str;
		}
		try {
			return new Function(c.varname, str);
		} catch (e) {
			/* istanbul ignore else */
			if (typeof console !== "undefined") console.log("Could not create a template function: " + str);
			throw e;
		}
	};

	doT.compile = function(tmpl, def) {
		return doT.template(tmpl, null, def);
	};
}());

},{}],2:[function(require,module,exports){
/*!
 * jQuery QueryBuilder 2.4.4
 * Copyright 2014-2017 Damien "Mistic" Sorel (http://www.strangeplanet.fr)
 * Licensed under MIT (http://opensource.org/licenses/MIT)
 */
(function(root, factory) {
    if (typeof define == 'function' && define.amd) {
        define(['jquery', 'dot/doT', 'jquery-extendext'], factory);
    }
    else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'), require('dot/doT'), require('jquery-extendext'));
    }
    else {
        factory(root.jQuery, root.doT);
    }
}(this, function($, doT) {
"use strict";

/**
 * @typedef {object} Filter
 * @memberof QueryBuilder
 * @description See {@link http://querybuilder.js.org/index.html#filters}
 */

/**
 * @typedef {object} Operator
 * @memberof QueryBuilder
 * @description See {@link http://querybuilder.js.org/index.html#operators}
 */

/**
 * @param {jQuery} $el
 * @param {object} options - see {@link http://querybuilder.js.org/#options}
 * @constructor
 * @fires QueryBuilder.afterInit
 */
var QueryBuilder = function($el, options) {
    $el[0].queryBuilder = this;

    /**
     * Element container
     * @member {jQuery}
     * @readonly
     */
    this.$el = $el;

    /**
     * Configuration object
     * @member {object}
     * @readonly
     */
    this.settings = $.extendext(true, 'replace', {}, QueryBuilder.DEFAULTS, options);

    /**
     * Internal model
     * @member {Model}
     * @readonly
     */
    this.model = new Model();

    /**
     * Internal status
     * @member {object}
     * @property {string} id - id of the container
     * @property {boolean} generated_id - if the container id has been generated
     * @property {int} group_id - current group id
     * @property {int} rule_id - current rule id
     * @property {boolean} has_optgroup - if filters have optgroups
     * @property {boolean} has_operator_optgroup - if operators have optgroups
     * @readonly
     * @private
     */
    this.status = {
        id: null,
        generated_id: false,
        group_id: 0,
        rule_id: 0,
        has_optgroup: false,
        has_operator_optgroup: false
    };

    /**
     * List of filters
     * @member {QueryBuilder.Filter[]}
     * @readonly
     */
    this.filters = this.settings.filters;

    /**
     * List of icons
     * @member {object.<string, string>}
     * @readonly
     */
    this.icons = this.settings.icons;

    /**
     * List of operators
     * @member {QueryBuilder.Operator[]}
     * @readonly
     */
    this.operators = this.settings.operators;

    /**
     * List of templates
     * @member {object.<string, function>}
     * @readonly
     */
    this.templates = this.settings.templates;

    /**
     * Plugins configuration
     * @member {object.<string, object>}
     * @readonly
     */
    this.plugins = this.settings.plugins;

    /**
     * Translations object
     * @member {object}
     * @readonly
     */
    this.lang = null;

    // translations : english << 'lang_code' << custom
    if (QueryBuilder.regional['en'] === undefined) {
        Utils.error('Config', '"i18n/en.js" not loaded.');
    }
    this.lang = $.extendext(true, 'replace', {}, QueryBuilder.regional['en'], QueryBuilder.regional[this.settings.lang_code], this.settings.lang);

    // "allow_groups" can be boolean or int
    if (this.settings.allow_groups === false) {
        this.settings.allow_groups = 0;
    }
    else if (this.settings.allow_groups === true) {
        this.settings.allow_groups = -1;
    }

    // init templates
    Object.keys(this.templates).forEach(function(tpl) {
        if (!this.templates[tpl]) {
            this.templates[tpl] = QueryBuilder.templates[tpl];
        }
        if (typeof this.templates[tpl] == 'string') {
            this.templates[tpl] = doT.template(this.templates[tpl]);
        }
    }, this);

    // ensure we have a container id
    if (!this.$el.attr('id')) {
        this.$el.attr('id', 'qb_' + Math.floor(Math.random() * 99999));
        this.status.generated_id = true;
    }
    this.status.id = this.$el.attr('id');

    // INIT
    this.$el.addClass('query-builder form-inline');

    this.filters = this.checkFilters(this.filters);
    this.operators = this.checkOperators(this.operators);
    this.bindEvents();
    this.initPlugins();

    /**
     * When the initilization is done, just before creating the root group
     * @event afterInit
     * @memberof QueryBuilder
     */
    this.trigger('afterInit');

    if (options.rules) {
        this.setRules(options.rules);
        delete this.settings.rules;
    }
    else {
        this.setRoot(true);
    }
};

$.extend(QueryBuilder.prototype, /** @lends QueryBuilder.prototype */ {
    /**
     * Triggers an event on the builder container
     * @param {string} type
     * @returns {$.Event}
     */
    trigger: function(type) {
        var event = new $.Event(this._tojQueryEvent(type), {
            builder: this
        });

        this.$el.triggerHandler(event, Array.prototype.slice.call(arguments, 1));

        return event;
    },

    /**
     * Triggers an event on the builder container and returns the modified value
     * @param {string} type
     * @param {*} value
     * @returns {*}
     */
    change: function(type, value) {
        var event = new $.Event(this._tojQueryEvent(type, true), {
            builder: this,
            value: value
        });

        this.$el.triggerHandler(event, Array.prototype.slice.call(arguments, 2));

        return event.value;
    },

    /**
     * Attaches an event listener on the builder container
     * @param {string} type
     * @param {function} cb
     * @returns {QueryBuilder}
     */
    on: function(type, cb) {
        this.$el.on(this._tojQueryEvent(type), cb);
        return this;
    },

    /**
     * Removes an event listener from the builder container
     * @param {string} type
     * @param {function} [cb]
     * @returns {QueryBuilder}
     */
    off: function(type, cb) {
        this.$el.off(this._tojQueryEvent(type), cb);
        return this;
    },

    /**
     * Attaches an event listener called once on the builder container
     * @param {string} type
     * @param {function} cb
     * @returns {QueryBuilder}
     */
    once: function(type, cb) {
        this.$el.one(this._tojQueryEvent(type), cb);
        return this;
    },

    /**
     * Appends `.queryBuilder` and optionally `.filter` to the events names
     * @param {string} name
     * @param {boolean} [filter=false]
     * @returns {string}
     * @private
     */
    _tojQueryEvent: function(name, filter) {
        return name.split(' ').map(function(type) {
            return type + '.queryBuilder' + (filter ? '.filter' : '');
        }).join(' ');
    }
});


/**
 * Allowed types and their internal representation
 * @type {object.<string, string>}
 * @readonly
 * @private
 */
QueryBuilder.types = {
    'string':   'string',
    'integer':  'number',
    'double':   'number',
    'date':     'datetime',
    'time':     'datetime',
    'datetime': 'datetime',
    'boolean':  'boolean'
};

/**
 * Allowed inputs
 * @type {string[]}
 * @readonly
 * @private
 */
QueryBuilder.inputs = [
    'text',
    'number',
    'textarea',
    'radio',
    'checkbox',
    'select'
];

/**
 * Runtime modifiable options with `setOptions` method
 * @type {string[]}
 * @readonly
 * @private
 */
QueryBuilder.modifiable_options = [
    'display_errors',
    'allow_groups',
    'allow_empty',
    'default_condition',
    'default_filter'
];

/**
 * CSS selectors for common components
 * @type {object.<string, string>}
 * @readonly
 */
QueryBuilder.selectors = {
    group_container:      '.rules-group-container',
    rule_container:       '.rule-container',
    filter_container:     '.rule-filter-container',
    operator_container:   '.rule-operator-container',
    value_container:      '.rule-value-container',
    error_container:      '.error-container',
    condition_container:  '.rules-group-header .group-conditions',

    rule_header:          '.rule-header',
    group_header:         '.rules-group-header',
    group_actions:        '.group-actions',
    rule_actions:         '.rule-actions',

    rules_list:           '.rules-group-body>.rules-list',

    group_condition:      '.rules-group-header [name$=_cond]',
    rule_filter:          '.rule-filter-container [name$=_filter]',
    rule_operator:        '.rule-operator-container [name$=_operator]',
    rule_value:           '.rule-value-container [name*=_value_]',

    add_rule:             '[data-add=rule]',
    delete_rule:          '[data-delete=rule]',
    add_group:            '[data-add=group]',
    delete_group:         '[data-delete=group]'
};

/**
 * Template strings (see template.js)
 * @type {object.<string, string>}
 * @readonly
 */
QueryBuilder.templates = {};

/**
 * Localized strings (see i18n/)
 * @type {object.<string, object>}
 * @readonly
 */
QueryBuilder.regional = {};

/**
 * Default operators
 * @type {object.<string, object>}
 * @readonly
 */
QueryBuilder.OPERATORS = {
    equal:            { type: 'equal',            nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean'] },
    not_equal:        { type: 'not_equal',        nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean'] },
    in:               { type: 'in',               nb_inputs: 1, multiple: true,  apply_to: ['string', 'number', 'datetime'] },
    not_in:           { type: 'not_in',           nb_inputs: 1, multiple: true,  apply_to: ['string', 'number', 'datetime'] },
    less:             { type: 'less',             nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime'] },
    less_or_equal:    { type: 'less_or_equal',    nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime'] },
    greater:          { type: 'greater',          nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime'] },
    greater_or_equal: { type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['number', 'datetime'] },
    between:          { type: 'between',          nb_inputs: 2, multiple: false, apply_to: ['number', 'datetime'] },
    not_between:      { type: 'not_between',      nb_inputs: 2, multiple: false, apply_to: ['number', 'datetime'] },
    begins_with:      { type: 'begins_with',      nb_inputs: 1, multiple: false, apply_to: ['string'] },
    not_begins_with:  { type: 'not_begins_with',  nb_inputs: 1, multiple: false, apply_to: ['string'] },
    contains:         { type: 'contains',         nb_inputs: 1, multiple: false, apply_to: ['string'] },
    not_contains:     { type: 'not_contains',     nb_inputs: 1, multiple: false, apply_to: ['string'] },
    ends_with:        { type: 'ends_with',        nb_inputs: 1, multiple: false, apply_to: ['string'] },
    not_ends_with:    { type: 'not_ends_with',    nb_inputs: 1, multiple: false, apply_to: ['string'] },
    is_empty:         { type: 'is_empty',         nb_inputs: 0, multiple: false, apply_to: ['string'] },
    is_not_empty:     { type: 'is_not_empty',     nb_inputs: 0, multiple: false, apply_to: ['string'] },
    is_null:          { type: 'is_null',          nb_inputs: 0, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean'] },
    is_not_null:      { type: 'is_not_null',      nb_inputs: 0, multiple: false, apply_to: ['string', 'number', 'datetime', 'boolean'] }
};

/**
 * Default configuration
 * @type {object}
 * @readonly
 */
QueryBuilder.DEFAULTS = {
    filters: [],
    plugins: [],

    sort_filters: false,
    display_errors: true,
    allow_groups: -1,
    allow_empty: false,
    conditions: ['AND', 'OR'],
    default_condition: 'AND',
    inputs_separator: ' , ',
    select_placeholder: '------',
    display_empty_filter: true,
    default_filter: null,
    optgroups: {},

    default_rule_flags: {
        filter_readonly: false,
        operator_readonly: false,
        value_readonly: false,
        no_delete: false
    },

    default_group_flags: {
        condition_readonly: false,
        no_add_rule: false,
        no_add_group: false,
        no_delete: false
    },

    templates: {
        group: null,
        rule: null,
        filterSelect: null,
        operatorSelect: null
    },

    lang_code: 'en',
    lang: {},

    operators: [
        'equal',
        'not_equal',
        'in',
        'not_in',
        'less',
        'less_or_equal',
        'greater',
        'greater_or_equal',
        'between',
        'not_between',
        'begins_with',
        'not_begins_with',
        'contains',
        'not_contains',
        'ends_with',
        'not_ends_with',
        'is_empty',
        'is_not_empty',
        'is_null',
        'is_not_null'
    ],

    icons: {
        add_group:    'glyphicon glyphicon-plus-sign',
        add_rule:     'glyphicon glyphicon-plus',
        remove_group: 'glyphicon glyphicon-remove',
        remove_rule:  'glyphicon glyphicon-remove',
        error:        'glyphicon glyphicon-warning-sign'
    }
};


/**
 * @module plugins
 */

/**
 * Definition of available plugins
 * @type {object.<String, object>}
 */
QueryBuilder.plugins = {};

/**
 * Gets or extends the default configuration
 * @param {object} [options] - new configuration
 * @returns {undefined|object} nothing or configuration object (copy)
 */
QueryBuilder.defaults = function(options) {
    if (typeof options == 'object') {
        $.extendext(true, 'replace', QueryBuilder.DEFAULTS, options);
    }
    else if (typeof options == 'string') {
        if (typeof QueryBuilder.DEFAULTS[options] == 'object') {
            return $.extend(true, {}, QueryBuilder.DEFAULTS[options]);
        }
        else {
            return QueryBuilder.DEFAULTS[options];
        }
    }
    else {
        return $.extend(true, {}, QueryBuilder.DEFAULTS);
    }
};

/**
 * Registers a new plugin
 * @param {string} name
 * @param {function} fct - init function
 * @param {object} [def] - default options
 */
QueryBuilder.define = function(name, fct, def) {
    QueryBuilder.plugins[name] = {
        fct: fct,
        def: def || {}
    };
};

/**
 * Adds new methods to QueryBuilder prototype
 * @param {object.<string, function>} methods
 */
QueryBuilder.extend = function(methods) {
    $.extend(QueryBuilder.prototype, methods);
};

/**
 * Initializes plugins for an instance
 * @throws ConfigError
 * @private
 */
QueryBuilder.prototype.initPlugins = function() {
    if (!this.plugins) {
        return;
    }

    if ($.isArray(this.plugins)) {
        var tmp = {};
        this.plugins.forEach(function(plugin) {
            tmp[plugin] = null;
        });
        this.plugins = tmp;
    }

    Object.keys(this.plugins).forEach(function(plugin) {
        if (plugin in QueryBuilder.plugins) {
            this.plugins[plugin] = $.extend(true, {},
                QueryBuilder.plugins[plugin].def,
                this.plugins[plugin] || {}
            );

            QueryBuilder.plugins[plugin].fct.call(this, this.plugins[plugin]);
        }
        else {
            Utils.error('Config', 'Unable to find plugin "{0}"', plugin);
        }
    }, this);
};

/**
 * Returns the config of a plugin, if the plugin is not loaded, returns the default config.
 * @param {string} name
 * @param {string} [property]
 * @throws ConfigError
 * @returns {*}
 */
QueryBuilder.prototype.getPluginOptions = function(name, property) {
    var plugin;
    if (this.plugins && this.plugins[name]) {
        plugin = this.plugins[name];
    }
    else if (QueryBuilder.plugins[name]) {
        plugin = QueryBuilder.plugins[name].def;
    }

    if (plugin) {
        if (property) {
            return plugin[property];
        }
        else {
            return plugin;
        }
    }
    else {
        Utils.error('Config', 'Unable to find plugin "{0}"', name);
    }
};


/**
 * Checks the configuration of each filter
 * @param {QueryBuilder.Filter[]} filters
 * @returns {QueryBuilder.Filter[]}
 * @throws ConfigError
 */
QueryBuilder.prototype.checkFilters = function(filters) {
    var definedFilters = [];

    if (!filters || filters.length === 0) {
        Utils.error('Config', 'Missing filters list');
    }

    filters.forEach(function(filter, i) {
        if (!filter.id) {
            Utils.error('Config', 'Missing filter {0} id', i);
        }
        if (definedFilters.indexOf(filter.id) != -1) {
            Utils.error('Config', 'Filter "{0}" already defined', filter.id);
        }
        definedFilters.push(filter.id);

        if (!filter.type) {
            filter.type = 'string';
        }
        else if (!QueryBuilder.types[filter.type]) {
            Utils.error('Config', 'Invalid type "{0}"', filter.type);
        }

        if (!filter.input) {
            filter.input = QueryBuilder.types[filter.type] === 'number' ? 'number' : 'text';
        }
        else if (typeof filter.input != 'function' && QueryBuilder.inputs.indexOf(filter.input) == -1) {
            Utils.error('Config', 'Invalid input "{0}"', filter.input);
        }

        if (filter.operators) {
            filter.operators.forEach(function(operator) {
                if (typeof operator != 'string') {
                    Utils.error('Config', 'Filter operators must be global operators types (string)');
                }
            });
        }

        if (!filter.field) {
            filter.field = filter.id;
        }
        if (!filter.label) {
            filter.label = filter.field;
        }

        if (!filter.optgroup) {
            filter.optgroup = null;
        }
        else {
            this.status.has_optgroup = true;

            // register optgroup if needed
            if (!this.settings.optgroups[filter.optgroup]) {
                this.settings.optgroups[filter.optgroup] = filter.optgroup;
            }
        }

        switch (filter.input) {
            case 'radio':
            case 'checkbox':
                if (!filter.values || filter.values.length < 1) {
                    Utils.error('Config', 'Missing filter "{0}" values', filter.id);
                }
                break;

            case 'select':
                if (filter.placeholder) {
                    if (filter.placeholder_value === undefined) {
                        filter.placeholder_value = -1;
                    }
                    Utils.iterateOptions(filter.values, function(key) {
                        if (key == filter.placeholder_value) {
                            Utils.error('Config', 'Placeholder of filter "{0}" overlaps with one of its values', filter.id);
                        }
                    });
                }
                break;
        }
    }, this);

    if (this.settings.sort_filters) {
        if (typeof this.settings.sort_filters == 'function') {
            filters.sort(this.settings.sort_filters);
        }
        else {
            var self = this;
            filters.sort(function(a, b) {
                return self.translate(a.label).localeCompare(self.translate(b.label));
            });
        }
    }

    if (this.status.has_optgroup) {
        filters = Utils.groupSort(filters, 'optgroup');
    }

    return filters;
};

/**
 * Checks the configuration of each operator
 * @param {QueryBuilder.Operator[]} operators
 * @returns {QueryBuilder.Operator[]}
 * @throws ConfigError
 */
QueryBuilder.prototype.checkOperators = function(operators) {
    var definedOperators = [];

    operators.forEach(function(operator, i) {
        if (typeof operator == 'string') {
            if (!QueryBuilder.OPERATORS[operator]) {
                Utils.error('Config', 'Unknown operator "{0}"', operator);
            }

            operators[i] = operator = $.extendext(true, 'replace', {}, QueryBuilder.OPERATORS[operator]);
        }
        else {
            if (!operator.type) {
                Utils.error('Config', 'Missing "type" for operator {0}', i);
            }

            if (QueryBuilder.OPERATORS[operator.type]) {
                operators[i] = operator = $.extendext(true, 'replace', {}, QueryBuilder.OPERATORS[operator.type], operator);
            }

            if (operator.nb_inputs === undefined || operator.apply_to === undefined) {
                Utils.error('Config', 'Missing "nb_inputs" and/or "apply_to" for operator "{0}"', operator.type);
            }
        }

        if (definedOperators.indexOf(operator.type) != -1) {
            Utils.error('Config', 'Operator "{0}" already defined', operator.type);
        }
        definedOperators.push(operator.type);

        if (!operator.optgroup) {
            operator.optgroup = null;
        }
        else {
            this.status.has_operator_optgroup = true;

            // register optgroup if needed
            if (!this.settings.optgroups[operator.optgroup]) {
                this.settings.optgroups[operator.optgroup] = operator.optgroup;
            }
        }
    }, this);

    if (this.status.has_operator_optgroup) {
        operators = Utils.groupSort(operators, 'optgroup');
    }

    return operators;
};

/**
 * Adds all events listeners to the builder
 * @private
 */
QueryBuilder.prototype.bindEvents = function() {
    var self = this;
    var Selectors = QueryBuilder.selectors;

    // group condition change
    this.$el.on('change.queryBuilder', Selectors.group_condition, function() {
        if ($(this).is(':checked')) {
            var $group = $(this).closest(Selectors.group_container);
            self.getModel($group).condition = $(this).val();
        }
    });

    // rule filter change
    this.$el.on('change.queryBuilder', Selectors.rule_filter, function() {
        var $rule = $(this).closest(Selectors.rule_container);
        self.getModel($rule).filter = self.getFilterById($(this).val());
    });

    // rule operator change
    this.$el.on('change.queryBuilder', Selectors.rule_operator, function() {
        var $rule = $(this).closest(Selectors.rule_container);
        self.getModel($rule).operator = self.getOperatorByType($(this).val());
    });

    // add rule button
    this.$el.on('click.queryBuilder', Selectors.add_rule, function() {
        var $group = $(this).closest(Selectors.group_container);
        self.addRule(self.getModel($group));
    });

    // delete rule button
    this.$el.on('click.queryBuilder', Selectors.delete_rule, function() {
        var $rule = $(this).closest(Selectors.rule_container);
        self.deleteRule(self.getModel($rule));
    });

    if (this.settings.allow_groups !== 0) {
        // add group button
        this.$el.on('click.queryBuilder', Selectors.add_group, function() {
            var $group = $(this).closest(Selectors.group_container);
            self.addGroup(self.getModel($group));
        });

        // delete group button
        this.$el.on('click.queryBuilder', Selectors.delete_group, function() {
            var $group = $(this).closest(Selectors.group_container);
            self.deleteGroup(self.getModel($group));
        });
    }

    // model events
    this.model.on({
        'drop': function(e, node) {
            node.$el.remove();
            self.refreshGroupsConditions();
        },
        'add': function(e, parent, node, index) {
            if (index === 0) {
                node.$el.prependTo(parent.$el.find('>' + QueryBuilder.selectors.rules_list));
            }
            else {
                node.$el.insertAfter(parent.rules[index - 1].$el);
            }
            self.refreshGroupsConditions();
        },
        'move': function(e, node, group, index) {
            node.$el.detach();

            if (index === 0) {
                node.$el.prependTo(group.$el.find('>' + QueryBuilder.selectors.rules_list));
            }
            else {
                node.$el.insertAfter(group.rules[index - 1].$el);
            }
            self.refreshGroupsConditions();
        },
        'update': function(e, node, field, value, oldValue) {
            if (node instanceof Rule) {
                switch (field) {
                    case 'error':
                        self.updateError(node);
                        break;

                    case 'flags':
                        self.applyRuleFlags(node);
                        break;

                    case 'filter':
                        self.updateRuleFilter(node, oldValue);
                        break;

                    case 'operator':
                        self.updateRuleOperator(node, oldValue);
                        break;

                    case 'value':
                        self.updateRuleValue(node);
                        break;
                }
            }
            else {
                switch (field) {
                    case 'error':
                        self.updateError(node);
                        break;

                    case 'flags':
                        self.applyGroupFlags(node);
                        break;

                    case 'condition':
                        self.updateGroupCondition(node);
                        break;
                }
            }
        }
    });
};

/**
 * Creates the root group
 * @param {boolean} [addRule=true] - adds a default empty rule
 * @param {object} [data] - group custom data
 * @param {object} [flags] - flags to apply to the group
 * @returns {Group} root group
 * @fires QueryBuilder.afterAddGroup
 */
QueryBuilder.prototype.setRoot = function(addRule, data, flags) {
    addRule = (addRule === undefined || addRule === true);

    var group_id = this.nextGroupId();
    var $group = $(this.getGroupTemplate(group_id, 1));

    this.$el.append($group);
    this.model.root = new Group(null, $group);
    this.model.root.model = this.model;

    this.model.root.data = data;
    this.model.root.__.flags = $.extend({}, this.settings.default_group_flags, flags);

    this.trigger('afterAddGroup', this.model.root);

    this.model.root.condition = this.settings.default_condition;

    if (addRule) {
        this.addRule(this.model.root);
    }

    return this.model.root;
};

/**
 * Adds a new group
 * @param {Group} parent
 * @param {boolean} [addRule=true] - adds a default empty rule
 * @param {object} [data] - group custom data
 * @param {object} [flags] - flags to apply to the group
 * @returns {Group}
 * @fires QueryBuilder.beforeAddGroup
 * @fires QueryBuilder.afterAddGroup
 */
QueryBuilder.prototype.addGroup = function(parent, addRule, data, flags) {
    addRule = (addRule === undefined || addRule === true);

    var level = parent.level + 1;

    /**
     * Just before adding a group, can be prevented.
     * @event beforeAddGroup
     * @memberof QueryBuilder
     * @param {Group} parent
     * @param {boolean} addRule - if an empty rule will be added in the group
     * @param {int} level - nesting level of the group, 1 is the root group
     */
    var e = this.trigger('beforeAddGroup', parent, addRule, level);
    if (e.isDefaultPrevented()) {
        return null;
    }

    var group_id = this.nextGroupId();
    var $group = $(this.getGroupTemplate(group_id, level));
    var model = parent.addGroup($group);

    model.data = data;
    model.__.flags = $.extend({}, this.settings.default_group_flags, flags);

    /**
     * Just after adding a group
     * @event afterAddGroup
     * @memberof QueryBuilder
     * @param {Group} group
     */
    this.trigger('afterAddGroup', model);

    model.condition = this.settings.default_condition;

    if (addRule) {
        this.addRule(model);
    }

    return model;
};

/**
 * Tries to delete a group. The group is not deleted if at least one rule is flagged `no_delete`.
 * @param {Group} group
 * @returns {boolean} if the group has been deleted
 * @fires QueryBuilder.beforeDeleteGroup
 * @fires QueryBuilder.afterDeleteGroup
 */
QueryBuilder.prototype.deleteGroup = function(group) {
    if (group.isRoot()) {
        return false;
    }

    /**
     * Just before deleting a group, can be prevented
     * @event beforeDeleteGroup
     * @memberof QueryBuilder
     * @param {Group} parent
     */
    var e = this.trigger('beforeDeleteGroup', group);
    if (e.isDefaultPrevented()) {
        return false;
    }

    var del = true;

    group.each('reverse', function(rule) {
        del &= this.deleteRule(rule);
    }, function(group) {
        del &= this.deleteGroup(group);
    }, this);

    if (del) {
        group.drop();

        /**
         * Just after deleting a group
         * @event afterDeleteGroup
         * @memberof QueryBuilder
         */
        this.trigger('afterDeleteGroup');
    }

    return del;
};

/**
 * Performs actions when a group's condition changes
 * @param {Group} group
 * @fires QueryBuilder.afterUpdateGroupCondition
 * @private
 */
QueryBuilder.prototype.updateGroupCondition = function(group) {
    group.$el.find('>' + QueryBuilder.selectors.group_condition).each(function() {
        var $this = $(this);
        $this.prop('checked', $this.val() === group.condition);
        $this.parent().toggleClass('active', $this.val() === group.condition);
    });

    /**
     * After the group condition has been modified
     * @event afterUpdateGroupCondition
     * @memberof QueryBuilder
     * @param {Group} group
     */
    this.trigger('afterUpdateGroupCondition', group);
};

/**
 * Updates the visibility of conditions based on number of rules inside each group
 * @private
 */
QueryBuilder.prototype.refreshGroupsConditions = function() {
    (function walk(group) {
        if (!group.flags || (group.flags && !group.flags.condition_readonly)) {
            group.$el.find('>' + QueryBuilder.selectors.group_condition).prop('disabled', group.rules.length <= 1)
                .parent().toggleClass('disabled', group.rules.length <= 1);
        }

        group.each(null, function(group) {
            walk(group);
        }, this);
    }(this.model.root));
};

/**
 * Adds a new rule
 * @param {Group} parent
 * @param {object} [data] - rule custom data
 * @param {object} [flags] - flags to apply to the rule
 * @returns {Rule}
 * @fires QueryBuilder.beforeAddRule
 * @fires QueryBuilder.afterAddRule
 * @fires QueryBuilder.changer:getDefaultFilter
 */
QueryBuilder.prototype.addRule = function(parent, data, flags) {
    /**
     * Just before adding a rule, can be prevented
     * @event beforeAddRule
     * @memberof QueryBuilder
     * @param {Group} parent
     */
    var e = this.trigger('beforeAddRule', parent);
    if (e.isDefaultPrevented()) {
        return null;
    }

    var rule_id = this.nextRuleId();
    var $rule = $(this.getRuleTemplate(rule_id));
    var model = parent.addRule($rule);

    if (data !== undefined) {
        model.data = data;
    }

    model.__.flags = $.extend({}, this.settings.default_rule_flags, flags);

    /**
     * Just after adding a rule
     * @event afterAddRule
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterAddRule', model);

    this.createRuleFilters(model);

    if (this.settings.default_filter || !this.settings.display_empty_filter) {
        /**
         * Modifies the default filter for a rule
         * @event changer:getDefaultFilter
         * @memberof QueryBuilder
         * @param {QueryBuilder.Filter} filter
         * @param {Rule} rule
         * @returns {QueryBuilder.Filter}
         */
        model.filter = this.change('getDefaultFilter',
            this.getFilterById(this.settings.default_filter || this.filters[0].id),
            model
        );
    }

    return model;
};

/**
 * Tries to delete a rule
 * @param {Rule} rule
 * @returns {boolean} if the rule has been deleted
 * @fires QueryBuilder.beforeDeleteRule
 * @fires QueryBuilder.afterDeleteRule
 */
QueryBuilder.prototype.deleteRule = function(rule) {
    if (rule.flags.no_delete) {
        return false;
    }

    /**
     * Just before deleting a rule, can be prevented
     * @event beforeDeleteRule
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    var e = this.trigger('beforeDeleteRule', rule);
    if (e.isDefaultPrevented()) {
        return false;
    }

    rule.drop();

    /**
     * Just after deleting a rule
     * @event afterDeleteRule
     * @memberof QueryBuilder
     */
    this.trigger('afterDeleteRule');

    return true;
};

/**
 * Creates the filters for a rule
 * @param {Rule} rule
 * @fires QueryBuilder.changer:getRuleFilters
 * @fires QueryBuilder.afterCreateRuleFilters
 * @private
 */
QueryBuilder.prototype.createRuleFilters = function(rule) {
    /**
     * Modifies the list a filters available for a rule
     * @event changer:getRuleFilters
     * @memberof QueryBuilder
     * @param {QueryBuilder.Filter[]} filters
     * @param {Rule} rule
     * @returns {QueryBuilder.Filter[]}
     */
    var filters = this.change('getRuleFilters', this.filters, rule);
    var $filterSelect = $(this.getRuleFilterSelect(rule, filters));

    rule.$el.find(QueryBuilder.selectors.filter_container).html($filterSelect);

    /**
     * After creating the dropdown for filters
     * @event afterCreateRuleFilters
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterCreateRuleFilters', rule);
};

/**
 * Creates the operators for a rule and init the rule operator
 * @param {Rule} rule
 * @fires QueryBuilder.afterCreateRuleOperators
 * @private
 */
QueryBuilder.prototype.createRuleOperators = function(rule) {
    var $operatorContainer = rule.$el.find(QueryBuilder.selectors.operator_container).empty();

    if (!rule.filter) {
        return;
    }

    var operators = this.getOperators(rule.filter);
    var $operatorSelect = $(this.getRuleOperatorSelect(rule, operators));

    $operatorContainer.html($operatorSelect);

    // set the operator without triggering update event
    rule.__.operator = operators[0];

    /**
     * After creating the dropdown for operators
     * @event afterCreateRuleOperators
     * @memberof QueryBuilder
     * @param {Rule} rule
     * @param {QueryBuilder.Operator[]} operators - allowed operators for this rule
     */
    this.trigger('afterCreateRuleOperators', rule, operators);
};

/**
 * Creates the main input for a rule
 * @param {Rule} rule
 * @fires QueryBuilder.afterCreateRuleInput
 * @private
 */
QueryBuilder.prototype.createRuleInput = function(rule) {
    var $valueContainer = rule.$el.find(QueryBuilder.selectors.value_container).empty();

    rule.__.value = undefined;

    if (!rule.filter || !rule.operator || rule.operator.nb_inputs === 0) {
        return;
    }

    var self = this;
    var $inputs = $();
    var filter = rule.filter;

    for (var i = 0; i < rule.operator.nb_inputs; i++) {
        var $ruleInput = $(this.getRuleInput(rule, i));
        if (i > 0) $valueContainer.append(this.settings.inputs_separator);
        $valueContainer.append($ruleInput);
        $inputs = $inputs.add($ruleInput);
    }

    $valueContainer.show();

    $inputs.on('change ' + (filter.input_event || ''), function() {
        if (!this._updating_input) {
            rule._updating_value = true;
            rule.value = self.getRuleInputValue(rule);
            rule._updating_value = false;
        }
    });

    if (filter.plugin) {
        $inputs[filter.plugin](filter.plugin_config || {});
    }

    /**
     * After creating the input for a rule and initializing optional plugin
     * @event afterCreateRuleInput
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterCreateRuleInput', rule);

    if (filter.default_value !== undefined) {
        rule.value = filter.default_value;
    }
    else {
        rule._updating_value = true;
        rule.value = self.getRuleInputValue(rule);
        rule._updating_value = false;
    }
};

/**
 * Performs action when a rule's filter changes
 * @param {Rule} rule
 * @param {object} previousFilter
 * @fires QueryBuilder.afterUpdateRuleFilter
 * @private
 */
QueryBuilder.prototype.updateRuleFilter = function(rule, previousFilter) {
    this.createRuleOperators(rule);
    this.createRuleInput(rule);

    rule.$el.find(QueryBuilder.selectors.rule_filter).val(rule.filter ? rule.filter.id : '-1');

    // clear rule data if the filter changed
    if (previousFilter && rule.filter && previousFilter.id !== rule.filter.id) {
        rule.data = undefined;
    }

    /**
     * After the filter has been updated and the operators and input re-created
     * @event afterUpdateRuleFilter
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterUpdateRuleFilter', rule);
};

/**
 * Performs actions when a rule's operator changes
 * @param {Rule} rule
 * @param {object} previousOperator
 * @fires QueryBuilder.afterUpdateRuleOperator
 * @private
 */
QueryBuilder.prototype.updateRuleOperator = function(rule, previousOperator) {
    var $valueContainer = rule.$el.find(QueryBuilder.selectors.value_container);

    if (!rule.operator || rule.operator.nb_inputs === 0) {
        $valueContainer.hide();

        rule.__.value = undefined;
    }
    else {
        $valueContainer.show();

        if ($valueContainer.is(':empty') || !previousOperator ||
            rule.operator.nb_inputs !== previousOperator.nb_inputs ||
            rule.operator.optgroup !== previousOperator.optgroup
        ) {
            this.createRuleInput(rule);
        }
    }

    if (rule.operator) {
        rule.$el.find(QueryBuilder.selectors.rule_operator).val(rule.operator.type);
    }

    /**
     *  After the operator has been updated and the input optionally re-created
     * @event afterUpdateRuleOperator
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterUpdateRuleOperator', rule);

    this.updateRuleValue(rule);
};

/**
 * Performs actions when rule's value changes
 * @param {Rule} rule
 * @fires QueryBuilder.afterUpdateRuleValue
 * @private
 */
QueryBuilder.prototype.updateRuleValue = function(rule) {
    if (!rule._updating_value) {
        this.setRuleInputValue(rule, rule.value);
    }

    /**
     * After the rule value has been modified
     * @event afterUpdateRuleValue
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterUpdateRuleValue', rule);
};

/**
 * Changes a rule's properties depending on its flags
 * @param {Rule} rule
 * @fires QueryBuilder.afterApplyRuleFlags
 * @private
 */
QueryBuilder.prototype.applyRuleFlags = function(rule) {
    var flags = rule.flags;
    var Selectors = QueryBuilder.selectors;

    if (flags.filter_readonly) {
        rule.$el.find(Selectors.rule_filter).prop('disabled', true);
    }
    if (flags.operator_readonly) {
        rule.$el.find(Selectors.rule_operator).prop('disabled', true);
    }
    if (flags.value_readonly) {
        rule.$el.find(Selectors.rule_value).prop('disabled', true);
    }
    if (flags.no_delete) {
        rule.$el.find(Selectors.delete_rule).remove();
    }

    /**
     * After rule's flags has been applied
     * @event afterApplyRuleFlags
     * @memberof QueryBuilder
     * @param {Rule} rule
     */
    this.trigger('afterApplyRuleFlags', rule);
};

/**
 * Changes group's properties depending on its flags
 * @param {Group} group
 * @fires QueryBuilder.afterApplyGroupFlags
 * @private
 */
QueryBuilder.prototype.applyGroupFlags = function(group) {
    var flags = group.flags;
    var Selectors = QueryBuilder.selectors;

    if (flags.condition_readonly) {
        group.$el.find('>' + Selectors.group_condition).prop('disabled', true)
            .parent().addClass('readonly');
    }
    if (flags.no_add_rule) {
        group.$el.find(Selectors.add_rule).remove();
    }
    if (flags.no_add_group) {
        group.$el.find(Selectors.add_group).remove();
    }
    if (flags.no_delete) {
        group.$el.find(Selectors.delete_group).remove();
    }

    /**
     * After group's flags has been applied
     * @event afterApplyGroupFlags
     * @memberof QueryBuilder
     * @param {Group} group
     */
    this.trigger('afterApplyGroupFlags', group);
};

/**
 * Clears all errors markers
 * @param {Node} [node] default is root Group
 */
QueryBuilder.prototype.clearErrors = function(node) {
    node = node || this.model.root;

    if (!node) {
        return;
    }

    node.error = null;

    if (node instanceof Group) {
        node.each(function(rule) {
            rule.error = null;
        }, function(group) {
            this.clearErrors(group);
        }, this);
    }
};

/**
 * Adds/Removes error on a Rule or Group
 * @param {Node} node
 * @fires QueryBuilder.changer:displayError
 * @private
 */
QueryBuilder.prototype.updateError = function(node) {
    if (this.settings.display_errors) {
        if (node.error === null) {
            node.$el.removeClass('has-error');
        }
        else {
            var errorMessage = this.translate('errors', node.error[0]);
            errorMessage = Utils.fmt(errorMessage, node.error.slice(1));

            /**
             * Modifies an error message before display
             * @event changer:displayError
             * @memberof QueryBuilder
             * @param {string} errorMessage - the error message (translated and formatted)
             * @param {array} error - the raw error array (error code and optional arguments)
             * @param {Node} node
             * @returns {string}
             */
            errorMessage = this.change('displayError', errorMessage, node.error, node);

            node.$el.addClass('has-error')
                .find(QueryBuilder.selectors.error_container).eq(0)
                .attr('title', errorMessage);
        }
    }
};

/**
 * Triggers a validation error event
 * @param {Node} node
 * @param {string|array} error
 * @param {*} value
 * @fires QueryBuilder.validationError
 * @private
 */
QueryBuilder.prototype.triggerValidationError = function(node, error, value) {
    if (!$.isArray(error)) {
        error = [error];
    }

    /**
     * Fired when a validation error occurred, can be prevented
     * @event validationError
     * @memberof QueryBuilder
     * @param {Node} node
     * @param {string} error
     * @param {*} value
     */
    var e = this.trigger('validationError', node, error, value);
    if (!e.isDefaultPrevented()) {
        node.error = error;
    }
};


/**
 * Destroys the builder
 * @fires QueryBuilder.beforeDestroy
 */
QueryBuilder.prototype.destroy = function() {
    /**
     * Before the {@link QueryBuilder#destroy} method
     * @event beforeDestroy
     * @memberof QueryBuilder
     */
    this.trigger('beforeDestroy');

    if (this.status.generated_id) {
        this.$el.removeAttr('id');
    }

    this.clear();
    this.model = null;

    this.$el
        .off('.queryBuilder')
        .removeClass('query-builder')
        .removeData('queryBuilder');

    delete this.$el[0].queryBuilder;
};

/**
 * Clear all rules and resets the root group
 * @fires QueryBuilder.beforeReset
 * @fires QueryBuilder.afterReset
 */
QueryBuilder.prototype.reset = function() {
    /**
     * Before the {@link QueryBuilder#reset} method, can be prevented
     * @event beforeReset
     * @memberof QueryBuilder
     */
    var e = this.trigger('beforeReset');
    if (e.isDefaultPrevented()) {
        return;
    }

    this.status.group_id = 1;
    this.status.rule_id = 0;

    this.model.root.empty();

    this.addRule(this.model.root);

    /**
     * After the {@link QueryBuilder#reset} method
     * @event afterReset
     * @memberof QueryBuilder
     */
    this.trigger('afterReset');
};

/**
 * Clears all rules and removes the root group
 * @fires QueryBuilder.beforeClear
 * @fires QueryBuilder.afterClear
 */
QueryBuilder.prototype.clear = function() {
    /**
     * Before the {@link QueryBuilder#clear} method, can be prevented
     * @event beforeClear
     * @memberof QueryBuilder
     */
    var e = this.trigger('beforeClear');
    if (e.isDefaultPrevented()) {
        return;
    }

    this.status.group_id = 0;
    this.status.rule_id = 0;

    if (this.model.root) {
        this.model.root.drop();
        this.model.root = null;
    }

    /**
     * After the {@link QueryBuilder#clear} method
     * @event afterClear
     * @memberof QueryBuilder
     */
    this.trigger('afterClear');
};

/**
 * Modifies the builder configuration.<br>
 * Only options defined in QueryBuilder.modifiable_options are modifiable
 * @param {object} options
 */
QueryBuilder.prototype.setOptions = function(options) {
    $.each(options, function(opt, value) {
        if (QueryBuilder.modifiable_options.indexOf(opt) !== -1) {
            this.settings[opt] = value;
        }
    }.bind(this));
};

/**
 * Returns the model associated to a DOM object, or the root model
 * @param {jQuery} [target]
 * @returns {Node}
 */
QueryBuilder.prototype.getModel = function(target) {
    if (!target) {
        return this.model.root;
    }
    else if (target instanceof Node) {
        return target;
    }
    else {
        return $(target).data('queryBuilderModel');
    }
};

/**
 * Validates the whole builder
 * @param {object} [options]
 * @param {boolean} [options.skip_empty=false] - skips validating rules that have no filter selected
 * @returns {boolean}
 * @fires QueryBuilder.changer:validate
 */
QueryBuilder.prototype.validate = function(options) {
    options = $.extend({
        skip_empty: false
    }, options);

    this.clearErrors();

    var self = this;

    var valid = (function parse(group) {
        var done = 0;
        var errors = 0;

        group.each(function(rule) {
            if (!rule.filter && options.skip_empty) {
                return;
            }

            if (!rule.filter) {
                self.triggerValidationError(rule, 'no_filter', null);
                errors++;
                return;
            }

            if (!rule.operator) {
                self.triggerValidationError(rule, 'no_operator', null);
                errors++;
                return;
            }

            if (rule.operator.nb_inputs !== 0) {
                var valid = self.validateValue(rule, rule.value);

                if (valid !== true) {
                    self.triggerValidationError(rule, valid, rule.value);
                    errors++;
                    return;
                }
            }

            done++;

        }, function(group) {
            var res = parse(group);
            if (res === true) {
                done++;
            }
            else if (res === false) {
                errors++;
            }
        });

        if (errors > 0) {
            return false;
        }
        else if (done === 0 && !group.isRoot() && options.skip_empty) {
            return null;
        }
        else if (done === 0 && (!self.settings.allow_empty || !group.isRoot())) {
            self.triggerValidationError(group, 'empty_group', null);
            return false;
        }

        return true;

    }(this.model.root));

    /**
     * Modifies the result of the {@link QueryBuilder#validate} method
     * @event changer:validate
     * @memberof QueryBuilder
     * @param {boolean} valid
     * @returns {boolean}
     */
    return this.change('validate', valid);
};

/**
 * Gets an object representing current rules
 * @param {object} [options]
 * @param {boolean|string} [options.get_flags=false] - export flags, true: only changes from default flags or 'all'
 * @param {boolean} [options.allow_invalid=false] - returns rules even if they are invalid
 * @param {boolean} [options.skip_empty=false] - remove rules that have no filter selected
 * @returns {object}
 * @fires QueryBuilder.changer:ruleToJson
 * @fires QueryBuilder.changer:groupToJson
 * @fires QueryBuilder.changer:getRules
 */
QueryBuilder.prototype.getRules = function(options) {
    options = $.extend({
        get_flags: false,
        allow_invalid: false,
        skip_empty: false
    }, options);

    var valid = this.validate(options);
    if (!valid && !options.allow_invalid) {
        return null;
    }

    var self = this;

    var out = (function parse(group) {
        var groupData = {
            condition: group.condition,
            rules: []
        };

        if (group.data) {
            groupData.data = $.extendext(true, 'replace', {}, group.data);
        }

        if (options.get_flags) {
            var flags = self.getGroupFlags(group.flags, options.get_flags === 'all');
            if (!$.isEmptyObject(flags)) {
                groupData.flags = flags;
            }
        }

        group.each(function(rule) {
            if (!rule.filter && options.skip_empty) {
                return;
            }

            var value = null;
            if (!rule.operator || rule.operator.nb_inputs !== 0) {
                value = rule.value;
            }

            var ruleData = {
                id: rule.filter ? rule.filter.id : null,
                field: rule.filter ? rule.filter.field : null,
                type: rule.filter ? rule.filter.type : null,
                input: rule.filter ? rule.filter.input : null,
                operator: rule.operator ? rule.operator.type : null,
                value: value
            };

            if (rule.filter && rule.filter.data || rule.data) {
                ruleData.data = $.extendext(true, 'replace', {}, rule.filter.data, rule.data);
            }

            if (options.get_flags) {
                var flags = self.getRuleFlags(rule.flags, options.get_flags === 'all');
                if (!$.isEmptyObject(flags)) {
                    ruleData.flags = flags;
                }
            }

            /**
             * Modifies the JSON generated from a Rule object
             * @event changer:ruleToJson
             * @memberof QueryBuilder
             * @param {object} json
             * @param {Rule} rule
             * @returns {object}
             */
            groupData.rules.push(self.change('ruleToJson', ruleData, rule));

        }, function(model) {
            var data = parse(model);
            if (data.rules.length !== 0 || !options.skip_empty) {
                groupData.rules.push(data);
            }
        }, this);

        /**
         * Modifies the JSON generated from a Group object
         * @event changer:groupToJson
         * @memberof QueryBuilder
         * @param {object} json
         * @param {Group} group
         * @returns {object}
         */
        return self.change('groupToJson', groupData, group);

    }(this.model.root));

    out.valid = valid;

    /**
     * Modifies the result of the {@link QueryBuilder#getRules} method
     * @event changer:getRules
     * @memberof QueryBuilder
     * @param {object} json
     * @returns {object}
     */
    return this.change('getRules', out);
};

/**
 * Sets rules from object
 * @param {object} data
 * @param {object} [options]
 * @param {boolean} [options.allow_invalid=false] - silent-fail if the data are invalid
 * @throws RulesError, UndefinedConditionError
 * @fires QueryBuilder.changer:setRules
 * @fires QueryBuilder.changer:jsonToRule
 * @fires QueryBuilder.changer:jsonToGroup
 * @fires QueryBuilder.afterSetRules
 */
QueryBuilder.prototype.setRules = function(data, options) {
    options = $.extend({
        allow_invalid: false
    }, options);

    if ($.isArray(data)) {
        data = {
            condition: this.settings.default_condition,
            rules: data
        };
    }

    if (!data || !data.rules || (data.rules.length === 0 && !this.settings.allow_empty)) {
        Utils.error('RulesParse', 'Incorrect data object passed');
    }

    this.clear();
    this.setRoot(false, data.data, this.parseGroupFlags(data));
    this.applyGroupFlags(this.model.root);

    /**
     * Modifies data before the {@link QueryBuilder#setRules} method
     * @event changer:setRules
     * @memberof QueryBuilder
     * @param {object} json
     * @param {object} options
     * @returns {object}
     */
    data = this.change('setRules', data, options);

    var self = this;

    (function add(data, group) {
        if (group === null) {
            return;
        }

        if (data.condition === undefined) {
            data.condition = self.settings.default_condition;
        }
        else if (self.settings.conditions.indexOf(data.condition) == -1) {
            Utils.error(!options.allow_invalid, 'UndefinedCondition', 'Invalid condition "{0}"', data.condition);
            data.condition = self.settings.default_condition;
        }

        group.condition = data.condition;

        data.rules.forEach(function(item) {
            var model;

            if (item.rules !== undefined) {
                if (self.settings.allow_groups !== -1 && self.settings.allow_groups < group.level) {
                    Utils.error(!options.allow_invalid, 'RulesParse', 'No more than {0} groups are allowed', self.settings.allow_groups);
                    self.reset();
                }
                else {
                    model = self.addGroup(group, false, item.data, self.parseGroupFlags(item));
                    if (model === null) {
                        return;
                    }

                    self.applyGroupFlags(model);

                    add(item, model);
                }
            }
            else {
                if (!item.empty) {
                    if (item.id === undefined) {
                        Utils.error(!options.allow_invalid, 'RulesParse', 'Missing rule field id');
                        item.empty = true;
                    }
                    if (item.operator === undefined) {
                        item.operator = 'equal';
                    }
                }

                model = self.addRule(group, item.data, self.parseRuleFlags(item));
                if (model === null) {
                    return;
                }

                if (!item.empty) {
                    model.filter = self.getFilterById(item.id, !options.allow_invalid);

                    if (model.filter) {
                        model.operator = self.getOperatorByType(item.operator, !options.allow_invalid);

                        if (!model.operator) {
                            model.operator = self.getOperators(model.filter)[0];
                        }

                        if (model.operator && model.operator.nb_inputs !== 0 && item.value !== undefined) {
                            model.value = item.value;
                        }
                    }
                }

                self.applyRuleFlags(model);

                /**
                 * Modifies the Rule object generated from the JSON
                 * @event changer:jsonToRule
                 * @memberof QueryBuilder
                 * @param {Rule} rule
                 * @param {object} json
                 * @returns {Rule} the same rule
                 */
                if (self.change('jsonToRule', model, item) != model) {
                    Utils.error('RulesParse', 'Plugin tried to change rule reference');
                }
            }
        });

        /**
         * Modifies the Group object generated from the JSON
         * @event changer:jsonToGroup
         * @memberof QueryBuilder
         * @param {Group} group
         * @param {object} json
         * @returns {Group} the same group
         */
        if (self.change('jsonToGroup', group, data) != group) {
            Utils.error('RulesParse', 'Plugin tried to change group reference');
        }

    }(data, this.model.root));

    /**
     * After the {@link QueryBuilder#setRules} method
     * @event afterSetRules
     * @memberof QueryBuilder
     */
    this.trigger('afterSetRules');
};


/**
 * Performs value validation
 * @param {Rule} rule
 * @param {string|string[]} value
 * @returns {array|boolean} true or error array
 * @fires QueryBuilder.changer:validateValue
 */
QueryBuilder.prototype.validateValue = function(rule, value) {
    var validation = rule.filter.validation || {};
    var result = true;

    if (validation.callback) {
        result = validation.callback.call(this, value, rule);
    }
    else {
        result = this._validateValue(rule, value);
    }

    /**
     * Modifies the result of the rule validation method
     * @event changer:validateValue
     * @memberof QueryBuilder
     * @param {array|boolean} result - true or an error array
     * @param {*} value
     * @param {Rule} rule
     * @returns {array|boolean}
     */
    return this.change('validateValue', result, value, rule);
};

/**
 * Default validation function
 * @param {Rule} rule
 * @param {string|string[]} value
 * @returns {array|boolean} true or error array
 * @throws ConfigError
 * @private
 */
QueryBuilder.prototype._validateValue = function(rule, value) {
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
                        result = ['radio_empty'];
                    }
                    break;
                }
                break;

            case 'checkbox':
                if (value[i] === undefined || value[i].length === 0) {
                    if (!validation.allow_empty_value) {
                        result = ['checkbox_empty'];
                    }
                    break;
                }
                break;

            case 'select':
                if (value[i] === undefined || value[i].length === 0 || (filter.placeholder && value[i] == filter.placeholder_value)) {
                    if (!validation.allow_empty_value) {
                        result = ['select_empty'];
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
                                    result = ['string_empty'];
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
                                    result = ['number_nan'];
                                }
                                break;
                            }
                            if (isNaN(tempValue[j])) {
                                result = ['number_nan'];
                                break;
                            }
                            if (filter.type == 'integer') {
                                if (parseInt(tempValue[j]) != tempValue[j]) {
                                    result = ['number_not_integer'];
                                    break;
                                }
                            }
                            else {
                                if (parseFloat(tempValue[j]) != tempValue[j]) {
                                    result = ['number_not_double'];
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
                                    result = ['datetime_empty'];
                                }
                                break;
                            }

                            // we need MomentJS
                            if (validation.format) {
                                if (!('moment' in window)) {
                                    Utils.error('MissingLibrary', 'MomentJS is required for Date/Time validation. Get it here http://momentjs.com');
                                }

                                var datetime = moment(tempValue[j], validation.format);
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
                                    result = ['boolean_not_valid'];
                                }
                                break;
                            }
                            tmp = ('' + tempValue[j]).trim().toLowerCase();
                            if (tmp !== 'true' && tmp !== 'false' && tmp !== '1' && tmp !== '0' && tempValue[j] !== 1 && tempValue[j] !== 0) {
                                result = ['boolean_not_valid'];
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
};

/**
 * Returns an incremented group ID
 * @returns {string}
 * @private
 */
QueryBuilder.prototype.nextGroupId = function() {
    return this.status.id + '_group_' + (this.status.group_id++);
};

/**
 * Returns an incremented rule ID
 * @returns {string}
 * @private
 */
QueryBuilder.prototype.nextRuleId = function() {
    return this.status.id + '_rule_' + (this.status.rule_id++);
};

/**
 * Returns the operators for a filter
 * @param {string|object} filter - filter id or filter object
 * @returns {object[]}
 * @fires QueryBuilder.changer:getOperators
 * @private
 */
QueryBuilder.prototype.getOperators = function(filter) {
    if (typeof filter == 'string') {
        filter = this.getFilterById(filter);
    }

    var result = [];

    for (var i = 0, l = this.operators.length; i < l; i++) {
        // filter operators check
        if (filter.operators) {
            if (filter.operators.indexOf(this.operators[i].type) == -1) {
                continue;
            }
        }
        // type check
        else if (this.operators[i].apply_to.indexOf(QueryBuilder.types[filter.type]) == -1) {
            continue;
        }

        result.push(this.operators[i]);
    }

    // keep sort order defined for the filter
    if (filter.operators) {
        result.sort(function(a, b) {
            return filter.operators.indexOf(a.type) - filter.operators.indexOf(b.type);
        });
    }

    /**
     * Modifies the operators available for a filter
     * @event changer:getOperators
     * @memberof QueryBuilder
     * @param {QueryBuilder.Operator[]} operators
     * @param {QueryBuilder.Filter} filter
     * @returns {QueryBuilder.Operator[]}
     */
    return this.change('getOperators', result, filter);
};

/**
 * Returns a particular filter by its id
 * @param {string} id
 * @param {boolean} [doThrow=true]
 * @returns {object|null}
 * @throws UndefinedFilterError
 * @private
 */
QueryBuilder.prototype.getFilterById = function(id, doThrow) {
    if (id == '-1') {
        return null;
    }

    for (var i = 0, l = this.filters.length; i < l; i++) {
        if (this.filters[i].id == id) {
            return this.filters[i];
        }
    }

    Utils.error(doThrow !== false, 'UndefinedFilter', 'Undefined filter "{0}"', id);

    return null;
};

/**
 * Returns a particular operator by its type
 * @param {string} type
 * @param {boolean} [doThrow=true]
 * @returns {object|null}
 * @throws UndefinedOperatorError
 * @private
 */
QueryBuilder.prototype.getOperatorByType = function(type, doThrow) {
    if (type == '-1') {
        return null;
    }

    for (var i = 0, l = this.operators.length; i < l; i++) {
        if (this.operators[i].type == type) {
            return this.operators[i];
        }
    }

    Utils.error(doThrow !== false, 'UndefinedOperator', 'Undefined operator "{0}"', type);

    return null;
};

/**
 * Returns rule's current input value
 * @param {Rule} rule
 * @returns {*}
 * @fires QueryBuilder.changer:getRuleValue
 * @private
 */
QueryBuilder.prototype.getRuleInputValue = function(rule) {
    var filter = rule.filter;
    var operator = rule.operator;
    var value = [];

    if (filter.valueGetter) {
        value = filter.valueGetter.call(this, rule);
    }
    else {
        var $value = rule.$el.find(QueryBuilder.selectors.value_container);

        for (var i = 0; i < operator.nb_inputs; i++) {
            var name = Utils.escapeElementId(rule.id + '_value_' + i);
            var tmp;

            switch (filter.input) {
                case 'radio':
                    value.push($value.find('[name=' + name + ']:checked').val());
                    break;

                case 'checkbox':
                    tmp = [];
                    // jshint loopfunc:true
                    $value.find('[name=' + name + ']:checked').each(function() {
                        tmp.push($(this).val());
                    });
                    // jshint loopfunc:false
                    value.push(tmp);
                    break;

                case 'select':
                    if (filter.multiple) {
                        tmp = [];
                        // jshint loopfunc:true
                        $value.find('[name=' + name + '] option:selected').each(function() {
                            tmp.push($(this).val());
                        });
                        // jshint loopfunc:false
                        value.push(tmp);
                    }
                    else {
                        value.push($value.find('[name=' + name + '] option:selected').val());
                    }
                    break;

                default:
                    value.push($value.find('[name=' + name + ']').val());
            }
        }

        if (operator.multiple && filter.value_separator) {
            value = value.map(function(val) {
                return val.split(filter.value_separator);
            });
        }

        if (operator.nb_inputs === 1) {
            value = value[0];
        }

        // @deprecated
        if (filter.valueParser) {
            value = filter.valueParser.call(this, rule, value);
        }
    }

    /**
     * Modifies the rule's value grabbed from the DOM
     * @event changer:getRuleValue
     * @memberof QueryBuilder
     * @param {*} value
     * @param {Rule} rule
     * @returns {*}
     */
    return this.change('getRuleValue', value, rule);
};

/**
 * Sets the value of a rule's input
 * @param {Rule} rule
 * @param {*} value
 * @private
 */
QueryBuilder.prototype.setRuleInputValue = function(rule, value) {
    var filter = rule.filter;
    var operator = rule.operator;

    if (!filter || !operator) {
        return;
    }

    this._updating_input = true;

    if (filter.valueSetter) {
        filter.valueSetter.call(this, rule, value);
    }
    else {
        var $value = rule.$el.find(QueryBuilder.selectors.value_container);

        if (operator.nb_inputs == 1) {
            value = [value];
        }

        for (var i = 0; i < operator.nb_inputs; i++) {
            var name = Utils.escapeElementId(rule.id + '_value_' + i);

            switch (filter.input) {
                case 'radio':
                    $value.find('[name=' + name + '][value="' + value[i] + '"]').prop('checked', true).trigger('change');
                    break;

                case 'checkbox':
                    if (!$.isArray(value[i])) {
                        value[i] = [value[i]];
                    }
                    // jshint loopfunc:true
                    value[i].forEach(function(value) {
                        $value.find('[name=' + name + '][value="' + value + '"]').prop('checked', true).trigger('change');
                    });
                    // jshint loopfunc:false
                    break;

                default:
                    if (operator.multiple && filter.value_separator && $.isArray(value[i])) {
                        value[i] = value[i].join(filter.value_separator);
                    }
                    $value.find('[name=' + name + ']').val(value[i]).trigger('change');
                    break;
            }
        }
    }

    this._updating_input = false;
};

/**
 * Parses rule flags
 * @param {object} rule
 * @returns {object}
 * @fires QueryBuilder.changer:parseRuleFlags
 * @private
 */
QueryBuilder.prototype.parseRuleFlags = function(rule) {
    var flags = $.extend({}, this.settings.default_rule_flags);

    if (rule.readonly) {
        $.extend(flags, {
            filter_readonly: true,
            operator_readonly: true,
            value_readonly: true,
            no_delete: true
        });
    }

    if (rule.flags) {
        $.extend(flags, rule.flags);
    }

    /**
     * Modifies the consolidated rule's flags
     * @event changer:parseRuleFlags
     * @memberof QueryBuilder
     * @param {object} flags
     * @param {object} rule - <b>not</b> a Rule object
     * @returns {object}
     */
    return this.change('parseRuleFlags', flags, rule);
};

/**
 * Gets a copy of flags of a rule
 * @param {object} flags
 * @param {boolean} [all=false] - return all flags or only changes from default flags
 * @returns {object}
 * @private
 */
QueryBuilder.prototype.getRuleFlags = function(flags, all) {
    if (all) {
        return $.extend({}, flags);
    }
    else {
        var ret = {};
        $.each(this.settings.default_rule_flags, function(key, value) {
            if (flags[key] !== value) {
                ret[key] = flags[key];
            }
        });
        return ret;
    }
};

/**
 * Parses group flags
 * @param {object} group
 * @returns {object}
 * @fires QueryBuilder.changer:parseGroupFlags
 * @private
 */
QueryBuilder.prototype.parseGroupFlags = function(group) {
    var flags = $.extend({}, this.settings.default_group_flags);

    if (group.readonly) {
        $.extend(flags, {
            condition_readonly: true,
            no_add_rule: true,
            no_add_group: true,
            no_delete: true
        });
    }

    if (group.flags) {
        $.extend(flags, group.flags);
    }

    /**
     * Modifies the consolidated group's flags
     * @event changer:parseGroupFlags
     * @memberof QueryBuilder
     * @param {object} flags
     * @param {object} group - <b>not</b> a Group object
     * @returns {object}
     */
    return this.change('parseGroupFlags', flags, group);
};

/**
 * Gets a copy of flags of a group
 * @param {object} flags
 * @param {boolean} [all=false] - return all flags or only changes from default flags
 * @returns {object}
 * @private
 */
QueryBuilder.prototype.getGroupFlags = function(flags, all) {
    if (all) {
        return $.extend({}, flags);
    }
    else {
        var ret = {};
        $.each(this.settings.default_group_flags, function(key, value) {
            if (flags[key] !== value) {
                ret[key] = flags[key];
            }
        });
        return ret;
    }
};

/**
 * Translate a label either by looking in the `lang` object or in itself if it's an object where keys are language codes
 * @param {string} [category]
 * @param {string|object} key
 * @returns {string}
 * @fires QueryBuilder.changer:translate
 */
QueryBuilder.prototype.translate = function(category, key) {
    if (!key) {
        key = category;
        category = undefined;
    }

    var translation;
    if (typeof key === 'object') {
        translation = key[this.settings.lang_code] || key['en'];
    }
    else {
        translation = (category ? this.lang[category] : this.lang)[key] || key;
    }

    /**
     * Modifies the translated label
     * @event changer:translate
     * @memberof QueryBuilder
     * @param {string} translation
     * @param {string|object} key
     * @param {string} [category]
     * @returns {string}
     */
    return this.change('translate', translation, key, category);
};

/**
 * Returns a validation message
 * @param {object} validation
 * @param {string} type
 * @param {string} def
 * @returns {string}
 * @private
 */
QueryBuilder.prototype.getValidationMessage = function(validation, type, def) {
    return validation.messages && validation.messages[type] || def;
};


QueryBuilder.templates.group = '\
<dl id="{{= it.group_id }}" class="rules-group-container"> \
  <dt class="rules-group-header"> \
    <div class="btn-group pull-right group-actions"> \
      <button type="button" class="btn btn-xs btn-success" data-add="rule"> \
        <i class="{{= it.icons.add_rule }}"></i> {{= it.translate("add_rule") }} \
      </button> \
      {{? it.settings.allow_groups===-1 || it.settings.allow_groups>=it.level }} \
        <button type="button" class="btn btn-xs btn-success" data-add="group"> \
          <i class="{{= it.icons.add_group }}"></i> {{= it.translate("add_group") }} \
        </button> \
      {{?}} \
      {{? it.level>1 }} \
        <button type="button" class="btn btn-xs btn-danger" data-delete="group"> \
          <i class="{{= it.icons.remove_group }}"></i> {{= it.translate("delete_group") }} \
        </button> \
      {{?}} \
    </div> \
    <div class="btn-group group-conditions"> \
      {{~ it.conditions: condition }} \
        <label class="btn btn-xs btn-primary"> \
          <input type="radio" name="{{= it.group_id }}_cond" value="{{= condition }}"> {{= it.translate("conditions", condition) }} \
        </label> \
      {{~}} \
    </div> \
    {{? it.settings.display_errors }} \
      <div class="error-container"><i class="{{= it.icons.error }}"></i></div> \
    {{?}} \
  </dt> \
  <dd class=rules-group-body> \
    <ul class=rules-list></ul> \
  </dd> \
</dl>';

QueryBuilder.templates.rule = '\
<li id="{{= it.rule_id }}" class="rule-container"> \
  <div class="rule-header"> \
    <div class="btn-group pull-right rule-actions"> \
      <button type="button" class="btn btn-xs btn-danger" data-delete="rule"> \
        <i class="{{= it.icons.remove_rule }}"></i> {{= it.translate("delete_rule") }} \
      </button> \
    </div> \
  </div> \
  {{? it.settings.display_errors }} \
    <div class="error-container"><i class="{{= it.icons.error }}"></i></div> \
  {{?}} \
  <div class="rule-filter-container"></div> \
  <div class="rule-operator-container"></div> \
  <div class="rule-value-container"></div> \
</li>';

QueryBuilder.templates.filterSelect = '\
{{ var optgroup = null; }} \
<select class="form-control" name="{{= it.rule.id }}_filter"> \
  {{? it.settings.display_empty_filter }} \
    <option value="-1">{{= it.settings.select_placeholder }}</option> \
  {{?}} \
  {{~ it.filters: filter }} \
    {{? optgroup !== filter.optgroup }} \
      {{? optgroup !== null }}</optgroup>{{?}} \
      {{? (optgroup = filter.optgroup) !== null }} \
        <optgroup label="{{= it.translate(it.settings.optgroups[optgroup]) }}"> \
      {{?}} \
    {{?}} \
    <option value="{{= filter.id }}">{{= it.translate(filter.label) }}</option> \
  {{~}} \
  {{? optgroup !== null }}</optgroup>{{?}} \
</select>';

QueryBuilder.templates.operatorSelect = '\
{{? it.operators.length === 1 }} \
<span> \
{{= it.translate("operators", it.operators[0].type) }} \
</span> \
{{?}} \
{{ var optgroup = null; }} \
<select class="form-control {{? it.operators.length === 1 }}hide{{?}}" name="{{= it.rule.id }}_operator"> \
  {{~ it.operators: operator }} \
    {{? optgroup !== operator.optgroup }} \
      {{? optgroup !== null }}</optgroup>{{?}} \
      {{? (optgroup = operator.optgroup) !== null }} \
        <optgroup label="{{= it.translate(it.settings.optgroups[optgroup]) }}"> \
      {{?}} \
    {{?}} \
    <option value="{{= operator.type }}">{{= it.translate("operators", operator.type) }}</option> \
  {{~}} \
  {{? optgroup !== null }}</optgroup>{{?}} \
</select>';

/**
 * Returns group's HTML
 * @param {string} group_id
 * @param {int} level
 * @returns {string}
 * @fires QueryBuilder.changer:getGroupTemplate
 * @private
 */
QueryBuilder.prototype.getGroupTemplate = function(group_id, level) {
    var h = this.templates.group({
        builder: this,
        group_id: group_id,
        level: level,
        conditions: this.settings.conditions,
        icons: this.icons,
        settings: this.settings,
        translate: this.translate.bind(this)
    });

    /**
     * Modifies the raw HTML of a group
     * @event changer:getGroupTemplate
     * @memberof QueryBuilder
     * @param {string} html
     * @param {int} level
     * @returns {string}
     */
    return this.change('getGroupTemplate', h, level);
};

/**
 * Returns rule's HTML
 * @param {string} rule_id
 * @returns {string}
 * @fires QueryBuilder.changer:getRuleTemplate
 * @private
 */
QueryBuilder.prototype.getRuleTemplate = function(rule_id) {
    var h = this.templates.rule({
        builder: this,
        rule_id: rule_id,
        icons: this.icons,
        settings: this.settings,
        translate: this.translate.bind(this)
    });

    /**
     * Modifies the raw HTML of a rule
     * @event changer:getRuleTemplate
     * @memberof QueryBuilder
     * @param {string} html
     * @returns {string}
     */
    return this.change('getRuleTemplate', h);
};

/**
 * Returns rule's filter HTML
 * @param {Rule} rule
 * @param {object[]} filters
 * @returns {string}
 * @fires QueryBuilder.changer:getRuleFilterTemplate
 * @private
 */
QueryBuilder.prototype.getRuleFilterSelect = function(rule, filters) {
    var h = this.templates.filterSelect({
        builder: this,
        rule: rule,
        filters: filters,
        icons: this.icons,
        settings: this.settings,
        translate: this.translate.bind(this)
    });

    /**
     * Modifies the raw HTML of the rule's filter dropdown
     * @event changer:getRuleFilterTemplate
     * @memberof QueryBuilder
     * @param {string} html
     * @param {Rule} rule
     * @param {QueryBuilder.Filter[]} filters
     * @returns {string}
     */
    return this.change('getRuleFilterSelect', h, rule, filters);
};

/**
 * Returns rule's operator HTML
 * @param {Rule} rule
 * @param {object[]} operators
 * @returns {string}
 * @fires QueryBuilder.changer:getRuleOperatorTemplate
 * @private
 */
QueryBuilder.prototype.getRuleOperatorSelect = function(rule, operators) {
    var h = this.templates.operatorSelect({
        builder: this,
        rule: rule,
        operators: operators,
        icons: this.icons,
        settings: this.settings,
        translate: this.translate.bind(this)
    });

    /**
     * Modifies the raw HTML of the rule's operator dropdown
     * @event changer:getRuleOperatorTemplate
     * @memberof QueryBuilder
     * @param {string} html
     * @param {Rule} rule
     * @param {QueryBuilder.Operator[]} operators
     * @returns {string}
     */
    return this.change('getRuleOperatorSelect', h, rule, operators);
};

/**
 * Returns the rule's value HTML
 * @param {Rule} rule
 * @param {int} value_id
 * @returns {string}
 * @fires QueryBuilder.changer:getRuleInput
 * @private
 */
QueryBuilder.prototype.getRuleInput = function(rule, value_id) {
    var filter = rule.filter;
    var validation = rule.filter.validation || {};
    var name = rule.id + '_value_' + value_id;
    var c = filter.vertical ? ' class=block' : '';
    var h = '';

    if (typeof filter.input == 'function') {
        h = filter.input.call(this, rule, name);
    }
    else {
        switch (filter.input) {
            case 'radio':
            case 'checkbox':
                Utils.iterateOptions(filter.values, function(key, val) {
                    h += '<label' + c + '><input type="' + filter.input + '" name="' + name + '" value="' + key + '"> ' + val + '</label> ';
                });
                break;

            case 'select':
                h += '<select class="form-control" name="' + name + '"' + (filter.multiple ? ' multiple' : '') + '>';
                if (filter.placeholder) {
                    h += '<option value="' + filter.placeholder_value + '" disabled selected>' + filter.placeholder + '</option>';
                }
                Utils.iterateOptions(filter.values, function(key, val) {
                    h += '<option value="' + key + '">' + val + '</option> ';
                });
                h += '</select>';
                break;

            case 'textarea':
                h += '<textarea class="form-control" name="' + name + '"';
                if (filter.size) h += ' cols="' + filter.size + '"';
                if (filter.rows) h += ' rows="' + filter.rows + '"';
                if (validation.min !== undefined) h += ' minlength="' + validation.min + '"';
                if (validation.max !== undefined) h += ' maxlength="' + validation.max + '"';
                if (filter.placeholder) h += ' placeholder="' + filter.placeholder + '"';
                h += '></textarea>';
                break;

            case 'number':
                h += '<input class="form-control" type="number" name="' + name + '"';
                if (validation.step !== undefined) h += ' step="' + validation.step + '"';
                if (validation.min !== undefined) h += ' min="' + validation.min + '"';
                if (validation.max !== undefined) h += ' max="' + validation.max + '"';
                if (filter.placeholder) h += ' placeholder="' + filter.placeholder + '"';
                if (filter.size) h += ' size="' + filter.size + '"';
                h += '>';
                break;

            default:
                h += '<input class="form-control" type="text" name="' + name + '"';
                if (filter.placeholder) h += ' placeholder="' + filter.placeholder + '"';
                if (filter.type === 'string' && validation.min !== undefined) h += ' minlength="' + validation.min + '"';
                if (filter.type === 'string' && validation.max !== undefined) h += ' maxlength="' + validation.max + '"';
                if (filter.size) h += ' size="' + filter.size + '"';
                h += '>';
        }
    }

    /**
     * Modifies the raw HTML of the rule's input
     * @event changer:getRuleInput
     * @memberof QueryBuilder
     * @param {string} html
     * @param {Rule} rule
     * @param {string} name - the name that the input must have
     * @returns {string}
     */
    return this.change('getRuleInput', h, rule, name);
};


/**
 * @namespace
 */
var Utils = {};

/**
 * @member {object}
 * @memberof QueryBuilder
 * @see Utils
 */
QueryBuilder.utils = Utils;

/**
 * @callback Utils#OptionsIteratee
 * @param {string} key
 * @param {string} value
 */

/**
 * Iterates over radio/checkbox/selection options, it accept three formats
 *
 * @example
 * // array of values
 * options = ['one', 'two', 'three']
 * @example
 * // simple key-value map
 * options = {1: 'one', 2: 'two', 3: 'three'}
 * @example
 * // array of 1-element maps
 * options = [{1: 'one'}, {2: 'two'}, {3: 'three'}]
 *
 * @param {object|array} options
 * @param {Utils#OptionsIteratee} tpl
 */
Utils.iterateOptions = function(options, tpl) {
    if (options) {
        if ($.isArray(options)) {
            options.forEach(function(entry) {
                // array of one-element maps
                if ($.isPlainObject(entry)) {
                    $.each(entry, function(key, val) {
                        tpl(key, val);
                        return false; // break after first entry
                    });
                }
                // array of values
                else {
                    tpl(entry, entry);
                }
            });
        }
        // unordered map
        else {
            $.each(options, function(key, val) {
                tpl(key, val);
            });
        }
    }
};

/**
 * Replaces {0}, {1}, ... in a string
 * @param {string} str
 * @param {...*} args
 * @returns {string}
 */
Utils.fmt = function(str, args) {
    if (!Array.isArray(args)) {
        args = Array.prototype.slice.call(arguments, 1);
    }

    return str.replace(/{([0-9]+)}/g, function(m, i) {
        return args[parseInt(i)];
    });
};

/**
 * Throws an Error object with custom name or logs an error
 * @param {boolean} [doThrow=true]
 * @param {string} type
 * @param {string} message
 * @param {...*} args
 */
Utils.error = function() {
    var i = 0;
    var doThrow = typeof arguments[i] === 'boolean' ? arguments[i++] : true;
    var type = arguments[i++];
    var message = arguments[i++];
    var args = Array.isArray(arguments[i]) ? arguments[i] : Array.prototype.slice.call(arguments, i);

    if (doThrow) {
        var err = new Error(Utils.fmt(message, args));
        err.name = type + 'Error';
        err.args = args;
        throw err;
    }
    else {
        console.error(type + 'Error: ' + Utils.fmt(message, args));
    }
};

/**
 * Changes the type of a value to int, float or bool
 * @param {*} value
 * @param {string} type - 'integer', 'double', 'boolean' or anything else (passthrough)
 * @param {boolean} [boolAsInt=false] - return 0 or 1 for booleans
 * @returns {*}
 */
Utils.changeType = function(value, type, boolAsInt) {
    switch (type) {
        // @formatter:off
    case 'integer': return parseInt(value);
    case 'double': return parseFloat(value);
    case 'boolean':
        var bool = value.trim().toLowerCase() === 'true' || value.trim() === '1' || value === 1;
        return boolAsInt ? (bool ? 1 : 0) : bool;
    default: return value;
    // @formatter:on
    }
};

/**
 * Escapes a string like PHP's mysql_real_escape_string does
 * @param {string} value
 * @returns {string}
 */
Utils.escapeString = function(value) {
    if (typeof value != 'string') {
        return value;
    }

    return value
        .replace(/[\0\n\r\b\\\'\"]/g, function(s) {
            switch (s) {
                // @formatter:off
            case '\0': return '\\0';
            case '\n': return '\\n';
            case '\r': return '\\r';
            case '\b': return '\\b';
            default:   return '\\' + s;
            // @formatter:off
            }
        })
        // uglify compliant
        .replace(/\t/g, '\\t')
        .replace(/\x1a/g, '\\Z');
};

/**
 * Escapes a string for use in regex
 * @param {string} str
 * @returns {string}
 */
Utils.escapeRegExp = function(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
};

/**
 * Escapes a string for use in HTML element id
 * @param {string} str
 * @returns {string}
 */
Utils.escapeElementId = function(str) {
    // Regex based on that suggested by:
    // https://learn.jquery.com/using-jquery-core/faq/how-do-i-select-an-element-by-an-id-that-has-characters-used-in-css-notation/
    // - escapes : . [ ] ,
    // - avoids escaping already escaped values
    return (str) ? str.replace(/(\\)?([:.\[\],])/g,
            function( $0, $1, $2 ) { return $1 ? $0 : '\\' + $2; }) : str;
};

/**
 * Sorts objects by grouping them by `key`, preserving initial order when possible
 * @param {object[]} items
 * @param {string} key
 * @returns {object[]}
 */
Utils.groupSort = function(items, key) {
    var optgroups = [];
    var newItems = [];

    items.forEach(function(item) {
        var idx;

        if (item[key]) {
            idx = optgroups.lastIndexOf(item[key]);

            if (idx == -1) {
                idx = optgroups.length;
            }
            else {
                idx++;
            }
        }
        else {
            idx = optgroups.length;
        }

        optgroups.splice(idx, 0, item[key]);
        newItems.splice(idx, 0, item);
    });

    return newItems;
};

/**
 * Defines properties on an Node prototype with getter and setter.<br>
 *     Update events are emitted in the setter through root Model (if any).<br>
 *     The object must have a `__` object, non enumerable property to store values.
 * @param {function} obj
 * @param {string[]} fields
 */
Utils.defineModelProperties = function(obj, fields) {
    fields.forEach(function(field) {
        Object.defineProperty(obj.prototype, field, {
            enumerable: true,
            get: function() {
                return this.__[field];
            },
            set: function(value) {
                var previousValue = (this.__[field] !== null && typeof this.__[field] == 'object') ?
                    $.extend({}, this.__[field]) :
                    this.__[field];

                this.__[field] = value;

                if (this.model !== null) {
                    /**
                     * After a value of the model changed
                     * @event model:update
                     * @memberof Model
                     * @param {Node} node
                     * @param {string} field
                     * @param {*} value
                     * @param {*} previousValue
                     */
                    this.model.trigger('update', this, field, value, previousValue);
                }
            }
        });
    });
};


/**
 * Main object storing data model and emitting model events
 * @constructor
 */
function Model() {
    /**
     * @member {Group}
     * @readonly
     */
    this.root = null;

    /**
     * Base for event emitting
     * @member {jQuery}
     * @readonly
     * @private
     */
    this.$ = $(this);
}

$.extend(Model.prototype, /** @lends Model.prototype */ {
    /**
     * Triggers an event on the model
     * @param {string} type
     * @returns {$.Event}
     */
    trigger: function(type) {
        var event = new $.Event(type);
        this.$.triggerHandler(event, Array.prototype.slice.call(arguments, 1));
        return event;
    },

    /**
     * Attaches an event listener on the model
     * @param {string} type
     * @param {function} cb
     * @returns {Model}
     */
    on: function() {
        this.$.on.apply(this.$, Array.prototype.slice.call(arguments));
        return this;
    },

    /**
     * Removes an event listener from the model
     * @param {string} type
     * @param {function} [cb]
     * @returns {Model}
     */
    off: function() {
        this.$.off.apply(this.$, Array.prototype.slice.call(arguments));
        return this;
    },

    /**
     * Attaches an event listener called once on the model
     * @param {string} type
     * @param {function} cb
     * @returns {Model}
     */
    once: function() {
        this.$.one.apply(this.$, Array.prototype.slice.call(arguments));
        return this;
    }
});


/**
 * Root abstract object
 * @constructor
 * @param {Node} [parent]
 * @param {jQuery} $el
 */
var Node = function(parent, $el) {
    if (!(this instanceof Node)) {
        return new Node(parent, $el);
    }

    Object.defineProperty(this, '__', { value: {} });

    $el.data('queryBuilderModel', this);

    /**
     * @name level
     * @member {int}
     * @memberof Node
     * @instance
     * @readonly
     */
    this.__.level = 1;

    /**
     * @name error
     * @member {string}
     * @memberof Node
     * @instance
     */
    this.__.error = null;

    /**
     * @name flags
     * @member {object}
     * @memberof Node
     * @instance
     * @readonly
     */
    this.__.flags = {};

    /**
     * @name data
     * @member {object}
     * @memberof Node
     * @instance
     */
    this.__.data = undefined;

    /**
     * @member {jQuery}
     * @readonly
     */
    this.$el = $el;

    /**
     * @member {string}
     * @readonly
     */
    this.id = $el[0].id;

    /**
     * @member {Model}
     * @readonly
     */
    this.model = null;

    /**
     * @member {Group}
     * @readonly
     */
    this.parent = parent;
};

Utils.defineModelProperties(Node, ['level', 'error', 'data', 'flags']);

Object.defineProperty(Node.prototype, 'parent', {
    enumerable: true,
    get: function() {
        return this.__.parent;
    },
    set: function(value) {
        this.__.parent = value;
        this.level = value === null ? 1 : value.level + 1;
        this.model = value === null ? null : value.model;
    }
});

/**
 * Checks if this Node is the root
 * @returns {boolean}
 */
Node.prototype.isRoot = function() {
    return (this.level === 1);
};

/**
 * Returns the node position inside its parent
 * @returns {int}
 */
Node.prototype.getPos = function() {
    if (this.isRoot()) {
        return -1;
    }
    else {
        return this.parent.getNodePos(this);
    }
};

/**
 * Deletes self
 * @fires Model.model:drop
 */
Node.prototype.drop = function() {
    var model = this.model;

    if (!!this.parent) {
        this.parent.removeNode(this);
    }

    this.$el.removeData('queryBuilderModel');

    if (model !== null) {
        /**
         * After a node of the model has been removed
         * @event model:drop
         * @memberof Model
         * @param {Node} node
         */
        model.trigger('drop', this);
    }
};

/**
 * Moves itself after another Node
 * @param {Node} target
 * @fires Model.model:move
 */
Node.prototype.moveAfter = function(target) {
    if (!this.isRoot()) {
        this.move(target.parent, target.getPos() + 1);
    }
};

/**
 * Moves itself at the beginning of parent or another Group
 * @param {Group} [target]
 * @fires Model.model:move
 */
Node.prototype.moveAtBegin = function(target) {
    if (!this.isRoot()) {
        if (target === undefined) {
            target = this.parent;
        }

        this.move(target, 0);
    }
};

/**
 * Moves itself at the end of parent or another Group
 * @param {Group} [target]
 * @fires Model.model:move
 */
Node.prototype.moveAtEnd = function(target) {
    if (!this.isRoot()) {
        if (target === undefined) {
            target = this.parent;
        }

        this.move(target, target.length() === 0 ? 0 : target.length() - 1);
    }
};

/**
 * Moves itself at specific position of Group
 * @param {Group} target
 * @param {int} index
 * @fires Model.model:move
 */
Node.prototype.move = function(target, index) {
    if (!this.isRoot()) {
        if (typeof target === 'number') {
            index = target;
            target = this.parent;
        }

        this.parent.removeNode(this);
        target.insertNode(this, index, false);

        if (this.model !== null) {
            /**
             * After a node of the model has been moved
             * @event model:move
             * @memberof Model
             * @param {Node} node
             * @param {Node} target
             * @param {int} index
             */
            this.model.trigger('move', this, target, index);
        }
    }
};


/**
 * Group object
 * @constructor
 * @extends Node
 * @param {Group} [parent]
 * @param {jQuery} $el
 */
var Group = function(parent, $el) {
    if (!(this instanceof Group)) {
        return new Group(parent, $el);
    }

    Node.call(this, parent, $el);

    /**
     * @member {object[]}
     * @readonly
     */
    this.rules = [];

    /**
     * @name condition
     * @member {string}
     * @memberof Group
     * @instance
     */
    this.__.condition = null;
};

Group.prototype = Object.create(Node.prototype);
Group.prototype.constructor = Group;

Utils.defineModelProperties(Group, ['condition']);

/**
 * Removes group's content
 */
Group.prototype.empty = function() {
    this.each('reverse', function(rule) {
        rule.drop();
    }, function(group) {
        group.drop();
    });
};

/**
 * Deletes self
 */
Group.prototype.drop = function() {
    this.empty();
    Node.prototype.drop.call(this);
};

/**
 * Returns the number of children
 * @returns {int}
 */
Group.prototype.length = function() {
    return this.rules.length;
};

/**
 * Adds a Node at specified index
 * @param {Node} node
 * @param {int} [index=end]
 * @param {boolean} [trigger=false] - fire 'add' event
 * @returns {Node} the inserted node
 * @fires Model.model:add
 */
Group.prototype.insertNode = function(node, index, trigger) {
    if (index === undefined) {
        index = this.length();
    }

    this.rules.splice(index, 0, node);
    node.parent = this;

    if (trigger && this.model !== null) {
        /**
         * After a node of the model has been added
         * @event model:add
         * @memberof Model
         * @param {Node} parent
         * @param {Node} node
         * @param {int} index
         */
        this.model.trigger('add', this, node, index);
    }

    return node;
};

/**
 * Adds a new Group at specified index
 * @param {jQuery} $el
 * @param {int} [index=end]
 * @returns {Group}
 * @fires Model.model:add
 */
Group.prototype.addGroup = function($el, index) {
    return this.insertNode(new Group(this, $el), index, true);
};

/**
 * Adds a new Rule at specified index
 * @param {jQuery} $el
 * @param {int} [index=end]
 * @returns {Rule}
 * @fires Model.model:add
 */
Group.prototype.addRule = function($el, index) {
    return this.insertNode(new Rule(this, $el), index, true);
};

/**
 * Deletes a specific Node
 * @param {Node} node
 */
Group.prototype.removeNode = function(node) {
    var index = this.getNodePos(node);
    if (index !== -1) {
        node.parent = null;
        this.rules.splice(index, 1);
    }
};

/**
 * Returns the position of a child Node
 * @param {Node} node
 * @returns {int}
 */
Group.prototype.getNodePos = function(node) {
    return this.rules.indexOf(node);
};

/**
 * @callback Model#GroupIteratee
 * @param {Node} node
 * @returns {boolean} stop the iteration
 */

/**
 * Iterate over all Nodes
 * @param {boolean} [reverse=false] - iterate in reverse order, required if you delete nodes
 * @param {Model#GroupIteratee} cbRule - callback for Rules (can be `null` but not omitted)
 * @param {Model#GroupIteratee} [cbGroup] - callback for Groups
 * @param {object} [context] - context for callbacks
 * @returns {boolean} if the iteration has been stopped by a callback
 */
Group.prototype.each = function(reverse, cbRule, cbGroup, context) {
    if (typeof reverse !== 'boolean' && typeof reverse !== 'string') {
        context = cbGroup;
        cbGroup = cbRule;
        cbRule = reverse;
        reverse = false;
    }
    context = context === undefined ? null : context;

    var i = reverse ? this.rules.length - 1 : 0;
    var l = reverse ? 0 : this.rules.length - 1;
    var c = reverse ? -1 : 1;
    var next = function() {
        return reverse ? i >= l : i <= l;
    };
    var stop = false;

    for (; next(); i += c) {
        if (this.rules[i] instanceof Group) {
            if (!!cbGroup) {
                stop = cbGroup.call(context, this.rules[i]) === false;
            }
        }
        else if (!!cbRule) {
            stop = cbRule.call(context, this.rules[i]) === false;
        }

        if (stop) {
            break;
        }
    }

    return !stop;
};

/**
 * Checks if the group contains a particular Node
 * @param {Node} node
 * @param {boolean} [recursive=false]
 * @returns {boolean}
 */
Group.prototype.contains = function(node, recursive) {
    if (this.getNodePos(node) !== -1) {
        return true;
    }
    else if (!recursive) {
        return false;
    }
    else {
        // the loop will return with false as soon as the Node is found
        return !this.each(function() {
            return true;
        }, function(group) {
            return !group.contains(node, true);
        });
    }
};


/**
 * Rule object
 * @constructor
 * @extends Node
 * @param {Group} parent
 * @param {jQuery} $el
 */
var Rule = function(parent, $el) {
    if (!(this instanceof Rule)) {
        return new Rule(parent, $el);
    }

    Node.call(this, parent, $el);

    this._updating_value = false;
    this._updating_input = false;

    /**
     * @name filter
     * @member {QueryBuilder.Filter}
     * @memberof Rule
     * @instance
     */
    this.__.filter = null;

    /**
     * @name operator
     * @member {QueryBuilder.Operator}
     * @memberof Rule
     * @instance
     */
    this.__.operator = null;

    /**
     * @name value
     * @member {*}
     * @memberof Rule
     * @instance
     */
    this.__.value = undefined;
};

Rule.prototype = Object.create(Node.prototype);
Rule.prototype.constructor = Rule;

Utils.defineModelProperties(Rule, ['filter', 'operator', 'value']);

/**
 * Checks if this Node is the root
 * @returns {boolean} always false
 */
Rule.prototype.isRoot = function() {
    return false;
};


/**
 * @member {function}
 * @memberof QueryBuilder
 * @see Group
 */
QueryBuilder.Group = Group;

/**
 * @member {function}
 * @memberof QueryBuilder
 * @see Rule
 */
QueryBuilder.Rule = Rule;


/**
 * The {@link http://learn.jquery.com/plugins/|jQuery Plugins} namespace
 * @external "jQuery.fn"
 */

/**
 * Instanciates or accesses the {@link QueryBuilder} on an element
 * @function
 * @memberof external:"jQuery.fn"
 * @param {*} option - initial configuration or method name
 * @param {...*} args - method arguments
 *
 * @example
 * $('#builder').queryBuilder({ /** configuration object *\/ });
 * @example
 * $('#builder').queryBuilder('methodName', methodParam1, methodParam2);
 */
$.fn.queryBuilder = function(option) {
    if (this.length === 0) {
        Utils.error('Config', 'No target defined');
    }
    if (this.length > 1) {
        Utils.error('Config', 'Unable to initialize on multiple target');
    }

    var data = this.data('queryBuilder');
    var options = (typeof option == 'object' && option) || {};

    if (!data && option == 'destroy') {
        return this;
    }
    if (!data) {
        this.data('queryBuilder', new QueryBuilder(this, options));
    }
    if (typeof option == 'string') {
        return data[option].apply(data, Array.prototype.slice.call(arguments, 1));
    }

    return this;
};

/**
 * @function
 * @memberof external:"jQuery.fn"
 * @see QueryBuilder
 */
$.fn.queryBuilder.constructor = QueryBuilder;

/**
 * @function
 * @memberof external:"jQuery.fn"
 * @see QueryBuilder.defaults
 */
$.fn.queryBuilder.defaults = QueryBuilder.defaults;

/**
 * @function
 * @memberof external:"jQuery.fn"
 * @see QueryBuilder.defaults
 */
$.fn.queryBuilder.extend = QueryBuilder.extend;

/**
 * @function
 * @memberof external:"jQuery.fn"
 * @see QueryBuilder.define
 */
$.fn.queryBuilder.define = QueryBuilder.define;

/**
 * @function
 * @memberof external:"jQuery.fn"
 * @see QueryBuilder.regional
 */
$.fn.queryBuilder.regional = QueryBuilder.regional;


/**
 * @class BtCheckbox
 * @memberof module:plugins
 * @description Applies Awesome Bootstrap Checkbox for checkbox and radio inputs.
 * @param {object} [options]
 * @param {string} [options.font='glyphicons']
 * @param {string} [options.color='default']
 */
QueryBuilder.define('bt-checkbox', function(options) {
    if (options.font == 'glyphicons') {
        this.$el.addClass('bt-checkbox-glyphicons');
    }

    this.on('getRuleInput.filter', function(h, rule, name) {
        var filter = rule.filter;

        if ((filter.input === 'radio' || filter.input === 'checkbox') && !filter.plugin) {
            h.value = '';

            if (!filter.colors) {
                filter.colors = {};
            }
            if (filter.color) {
                filter.colors._def_ = filter.color;
            }

            var style = filter.vertical ? ' style="display:block"' : '';
            var i = 0;

            Utils.iterateOptions(filter.values, function(key, val) {
                var color = filter.colors[key] || filter.colors._def_ || options.color;
                var id = name + '_' + (i++);

                h.value+= '\
<div' + style + ' class="' + filter.input + ' ' + filter.input + '-' + color + '"> \
  <input type="' + filter.input + '" name="' + name + '" id="' + id + '" value="' + key + '"> \
  <label for="' + id + '">' + val + '</label> \
</div>';
            });
        }
    });
}, {
    font: 'glyphicons',
    color: 'default'
});


/**
 * @class BtSelectpicker
 * @memberof module:plugins
 * @descriptioon Applies Bootstrap Select on filters and operators combo-boxes.
 * @param {object} [options]
 * @param {string} [options.container='body']
 * @param {string} [options.style='btn-inverse btn-xs']
 * @param {int|string} [options.width='auto']
 * @param {boolean} [options.showIcon=false]
 * @throws MissingLibraryError
 */
QueryBuilder.define('bt-selectpicker', function(options) {
    if (!$.fn.selectpicker || !$.fn.selectpicker.Constructor) {
        Utils.error('MissingLibrary', 'Bootstrap Select is required to use "bt-selectpicker" plugin. Get it here: http://silviomoreto.github.io/bootstrap-select');
    }

    var Selectors = QueryBuilder.selectors;

    // init selectpicker
    this.on('afterCreateRuleFilters', function(e, rule) {
        rule.$el.find(Selectors.rule_filter).removeClass('form-control').selectpicker(options);
    });

    this.on('afterCreateRuleOperators', function(e, rule) {
        rule.$el.find(Selectors.rule_operator).removeClass('form-control').selectpicker(options);
    });

    // update selectpicker on change
    this.on('afterUpdateRuleFilter', function(e, rule) {
        rule.$el.find(Selectors.rule_filter).selectpicker('render');
    });

    this.on('afterUpdateRuleOperator', function(e, rule) {
        rule.$el.find(Selectors.rule_operator).selectpicker('render');
    });

    this.on('beforeDeleteRule', function(e, rule) {
        rule.$el.find(Selectors.rule_filter).selectpicker('destroy');
        rule.$el.find(Selectors.rule_operator).selectpicker('destroy');
    });
}, {
    container: 'body',
    style: 'btn-inverse btn-xs',
    width: 'auto',
    showIcon: false
});


/**
 * @class BtTooltipErrors
 * @memberof module:plugins
 * @description Applies Bootstrap Tooltips on validation error messages.
 * @param {object} [options]
 * @param {string} [options.placement='right']
 * @throws MissingLibraryError
 */
QueryBuilder.define('bt-tooltip-errors', function(options) {
    if (!$.fn.tooltip || !$.fn.tooltip.Constructor || !$.fn.tooltip.Constructor.prototype.fixTitle) {
        Utils.error('MissingLibrary', 'Bootstrap Tooltip is required to use "bt-tooltip-errors" plugin. Get it here: http://getbootstrap.com');
    }

    var self = this;

    // add BT Tooltip data
    this.on('getRuleTemplate.filter getGroupTemplate.filter', function(h) {
        var $h = $(h.value);
        $h.find(QueryBuilder.selectors.error_container).attr('data-toggle', 'tooltip');
        h.value = $h.prop('outerHTML');
    });

    // init/refresh tooltip when title changes
    this.model.on('update', function(e, node, field) {
        if (field == 'error' && self.settings.display_errors) {
            node.$el.find(QueryBuilder.selectors.error_container).eq(0)
                .tooltip(options)
                .tooltip('hide')
                .tooltip('fixTitle');
        }
    });
}, {
    placement: 'right'
});


/**
 * @class ChangeFilters
 * @memberof module:plugins
 * @description Allows to change available filters after plugin initialization.
 */

QueryBuilder.extend(/** @lends module:plugins.ChangeFilters.prototype */ {
    /**
     * Change the filters of the builder
     * @param {boolean} [deleteOrphans=false] - delete rules using old filters
     * @param {QueryBuilder[]} filters
     * @fires module:plugins.ChangeFilters.changer:setFilters
     * @fires module:plugins.ChangeFilters.afterSetFilters
     * @throws ChangeFilterError
     */
    setFilters: function(deleteOrphans, filters) {
        var self = this;

        if (filters === undefined) {
            filters = deleteOrphans;
            deleteOrphans = false;
        }

        filters = this.checkFilters(filters);

        /**
         * Modifies the filters before {@link module:plugins.ChangeFilters.setFilters} method
         * @event changer:setFilters
         * @memberof module:plugins.ChangeFilters
         * @param {QueryBuilder.Filter[]} filters
         * @returns {QueryBuilder.Filter[]}
         */
        filters = this.change('setFilters', filters);

        var filtersIds = filters.map(function(filter) {
            return filter.id;
        });

        // check for orphans
        if (!deleteOrphans) {
            (function checkOrphans(node) {
                node.each(
                    function(rule) {
                        if (rule.filter && filtersIds.indexOf(rule.filter.id) === -1) {
                            Utils.error('ChangeFilter', 'A rule is using filter "{0}"', rule.filter.id);
                        }
                    },
                    checkOrphans
                );
            }(this.model.root));
        }

        // replace filters
        this.filters = filters;

        // apply on existing DOM
        (function updateBuilder(node) {
            node.each(true,
                function(rule) {
                    if (rule.filter && filtersIds.indexOf(rule.filter.id) === -1) {
                        rule.drop();
                    }
                    else {
                        self.createRuleFilters(rule);

                        rule.$el.find(QueryBuilder.selectors.rule_filter).val(rule.filter ? rule.filter.id : '-1');
                        self.trigger('afterUpdateRuleFilter', rule);
                    }
                },
                updateBuilder
            );
        }(this.model.root));

        // update plugins
        if (this.settings.plugins) {
            if (this.settings.plugins['unique-filter']) {
                this.updateDisabledFilters();
            }
            if (this.settings.plugins['bt-selectpicker']) {
                this.$el.find(QueryBuilder.selectors.rule_filter).selectpicker('render');
            }
        }

        // reset the default_filter if does not exist anymore
        if (this.settings.default_filter) {
            try {
                this.getFilterById(this.settings.default_filter);
            }
            catch (e) {
                this.settings.default_filter = null;
            }
        }

        /**
         * After {@link module:plugins.ChangeFilters.setFilters} method
         * @event afterSetFilters
         * @memberof module:plugins.ChangeFilters
         * @param {QueryBuilder.Filter[]} filters
         */
        this.trigger('afterSetFilters', filters);
    },

    /**
     * Adds a new filter to the builder
     * @param {QueryBuilder.Filter|Filter[]} newFilters
     * @param {int|string} [position=#end] - index or '#start' or '#end'
     * @fires module:plugins.ChangeFilters.changer:setFilters
     * @fires module:plugins.ChangeFilters.afterSetFilters
     * @throws ChangeFilterError
     */
    addFilter: function(newFilters, position) {
        if (position === undefined || position == '#end') {
            position = this.filters.length;
        }
        else if (position == '#start') {
            position = 0;
        }

        if (!$.isArray(newFilters)) {
            newFilters = [newFilters];
        }

        var filters = $.extend(true, [], this.filters);

        // numeric position
        if (parseInt(position) == position) {
            Array.prototype.splice.apply(filters, [position, 0].concat(newFilters));
        }
        else {
            // after filter by its id
            if (this.filters.some(function(filter, index) {
                    if (filter.id == position) {
                        position = index + 1;
                        return true;
                    }
                })
            ) {
                Array.prototype.splice.apply(filters, [position, 0].concat(newFilters));
            }
            // defaults to end of list
            else {
                Array.prototype.push.apply(filters, newFilters);
            }
        }

        this.setFilters(filters);
    },

    /**
     * Removes a filter from the builder
     * @param {string|string[]} filterIds
     * @param {boolean} [deleteOrphans=false] delete rules using old filters
     * @fires module:plugins.ChangeFilters.changer:setFilters
     * @fires module:plugins.ChangeFilters.afterSetFilters
     * @throws ChangeFilterError
     */
    removeFilter: function(filterIds, deleteOrphans) {
        var filters = $.extend(true, [], this.filters);
        if (typeof filterIds === 'string') {
            filterIds = [filterIds];
        }

        filters = filters.filter(function(filter) {
            return filterIds.indexOf(filter.id) === -1;
        });

        this.setFilters(deleteOrphans, filters);
    }
});


/**
 * @class FilterDescription
 * @memberof module:plugins
 * @description Provides three ways to display a description about a filter: inline, Bootsrap Popover or Bootbox.
 * @param {object} [options]
 * @param {string} [options.icon='glyphicon glyphicon-info-sign']
 * @param {string} [options.mode='popover'] - inline, popover or bootbox
 * @throws ConfigError
 */
QueryBuilder.define('filter-description', function(options) {
    // INLINE
    if (options.mode === 'inline') {
        this.on('afterUpdateRuleFilter afterUpdateRuleOperator', function(e, rule) {
            var $p = rule.$el.find('p.filter-description');
            var description = e.builder.getFilterDescription(rule.filter, rule);

            if (!description) {
                $p.hide();
            }
            else {
                if ($p.length === 0) {
                    $p = $('<p class="filter-description"></p>');
                    $p.appendTo(rule.$el);
                }
                else {
                    $p.show();
                }

                $p.html('<i class="' + options.icon + '"></i> ' + description);
            }
        });
    }
    // POPOVER
    else if (options.mode === 'popover') {
        if (!$.fn.popover || !$.fn.popover.Constructor || !$.fn.popover.Constructor.prototype.fixTitle) {
            Utils.error('MissingLibrary', 'Bootstrap Popover is required to use "filter-description" plugin. Get it here: http://getbootstrap.com');
        }

        this.on('afterUpdateRuleFilter afterUpdateRuleOperator', function(e, rule) {
            var $b = rule.$el.find('button.filter-description');
            var description = e.builder.getFilterDescription(rule.filter, rule);

            if (!description) {
                $b.hide();

                if ($b.data('bs.popover')) {
                    $b.popover('hide');
                }
            }
            else {
                if ($b.length === 0) {
                    $b = $('<button type="button" class="btn btn-xs btn-info filter-description" data-toggle="popover"><i class="' + options.icon + '"></i></button>');
                    $b.prependTo(rule.$el.find(QueryBuilder.selectors.rule_actions));

                    $b.popover({
                        placement: 'left',
                        container: 'body',
                        html: true
                    });

                    $b.on('mouseout', function() {
                        $b.popover('hide');
                    });
                }
                else {
                    $b.show();
                }

                $b.data('bs.popover').options.content = description;

                if ($b.attr('aria-describedby')) {
                    $b.popover('show');
                }
            }
        });
    }
    // BOOTBOX
    else if (options.mode === 'bootbox') {
        if (!('bootbox' in window)) {
            Utils.error('MissingLibrary', 'Bootbox is required to use "filter-description" plugin. Get it here: http://bootboxjs.com');
        }

        this.on('afterUpdateRuleFilter afterUpdateRuleOperator', function(e, rule) {
            var $b = rule.$el.find('button.filter-description');
            var description = e.builder.getFilterDescription(rule.filter, rule);

            if (!description) {
                $b.hide();
            }
            else {
                if ($b.length === 0) {
                    $b = $('<button type="button" class="btn btn-xs btn-info filter-description" data-toggle="bootbox"><i class="' + options.icon + '"></i></button>');
                    $b.prependTo(rule.$el.find(QueryBuilder.selectors.rule_actions));

                    $b.on('click', function() {
                        bootbox.alert($b.data('description'));
                    });
                }

                $b.data('description', description);
            }
        });
    }
}, {
    icon: 'glyphicon glyphicon-info-sign',
    mode: 'popover'
});

QueryBuilder.extend(/** @lends module:plugins.FilterDescription.prototype */ {
    /**
     * Returns the description of a filter for a particular rule (if present)
     * @param {object} filter
     * @param {Rule} [rule]
     * @returns {string}
     * @private
     */
    getFilterDescription: function(filter, rule) {
        if (!filter) {
            return undefined;
        }
        else if (typeof filter.description == 'function') {
            return filter.description.call(this, rule);
        }
        else {
            return filter.description;
        }
    }
});


/**
 * @class Invert
 * @memberof module:plugins
 * @description Allows to invert a rule operator, a group condition or the entire builder.
 * @param {object} [options]
 * @param {string} [options.icon='glyphicon glyphicon-random']
 * @param {boolean} [options.recursive=true]
 * @param {boolean} [options.invert_rules=true]
 * @param {boolean} [options.display_rules_button=false]
 * @param {boolean} [options.silent_fail=false]
 */
QueryBuilder.define('invert', function(options) {
    var self = this;
    var Selectors = QueryBuilder.selectors;

    // Bind events
    this.on('afterInit', function() {
        self.$el.on('click.queryBuilder', '[data-invert=group]', function() {
            var $group = $(this).closest(Selectors.group_container);
            self.invert(self.getModel($group), options);
        });

        if (options.display_rules_button && options.invert_rules) {
            self.$el.on('click.queryBuilder', '[data-invert=rule]', function() {
                var $rule = $(this).closest(Selectors.rule_container);
                self.invert(self.getModel($rule), options);
            });
        }
    });

    // Modify templates
    this.on('getGroupTemplate.filter', function(h, level) {
        var $h = $(h.value);
        $h.find(Selectors.condition_container).after('<button type="button" class="btn btn-xs btn-default" data-invert="group"><i class="' + options.icon + '"></i> ' + self.translate('invert') + '</button>');
        h.value = $h.prop('outerHTML');
    });

    if (options.display_rules_button && options.invert_rules) {
        this.on('getRuleTemplate.filter', function(h) {
            var $h = $(h.value);
            $h.find(Selectors.rule_actions).prepend('<button type="button" class="btn btn-xs btn-default" data-invert="rule"><i class="' + options.icon + '"></i> ' + self.translate('invert') + '</button>');
            h.value = $h.prop('outerHTML');
        });
    }
}, {
    icon: 'glyphicon glyphicon-random',
    recursive: true,
    invert_rules: true,
    display_rules_button: false,
    silent_fail: false
});

QueryBuilder.defaults({
    operatorOpposites: {
        'equal':            'not_equal',
        'not_equal':        'equal',
        'in':               'not_in',
        'not_in':           'in',
        'less':             'greater_or_equal',
        'less_or_equal':    'greater',
        'greater':          'less_or_equal',
        'greater_or_equal': 'less',
        'between':          'not_between',
        'not_between':      'between',
        'begins_with':      'not_begins_with',
        'not_begins_with':  'begins_with',
        'contains':         'not_contains',
        'not_contains':     'contains',
        'ends_with':        'not_ends_with',
        'not_ends_with':    'ends_with',
        'is_empty':         'is_not_empty',
        'is_not_empty':     'is_empty',
        'is_null':          'is_not_null',
        'is_not_null':      'is_null'
    },

    conditionOpposites: {
        'AND': 'OR',
        'OR': 'AND'
    }
});

QueryBuilder.extend(/** @lends module:plugins.Invert.prototype */ {
    /**
     * Invert a Group, a Rule or the whole builder
     * @param {Node} [node]
     * @param {object} [options] {@link module:plugins.Invert}
     * @fires module:plugins.Invert.afterInvert
     * @throws InvertConditionError, InvertOperatorError
     */
    invert: function(node, options) {
        if (!(node instanceof Node)) {
            if (!this.model.root) return;
            options = node;
            node = this.model.root;
        }

        if (typeof options != 'object') options = {};
        if (options.recursive === undefined) options.recursive = true;
        if (options.invert_rules === undefined) options.invert_rules = true;
        if (options.silent_fail === undefined) options.silent_fail = false;
        if (options.trigger === undefined) options.trigger = true;

        if (node instanceof Group) {
            // invert group condition
            if (this.settings.conditionOpposites[node.condition]) {
                node.condition = this.settings.conditionOpposites[node.condition];
            }
            else if (!options.silent_fail) {
                Utils.error('InvertCondition', 'Unknown inverse of condition "{0}"', node.condition);
            }

            // recursive call
            if (options.recursive) {
                var tempOpts = $.extend({}, options, { trigger: false });
                node.each(function(rule) {
                    if (options.invert_rules) {
                        this.invert(rule, tempOpts);
                    }
                }, function(group) {
                    this.invert(group, tempOpts);
                }, this);
            }
        }
        else if (node instanceof Rule) {
            if (node.operator && !node.filter.no_invert) {
                // invert rule operator
                if (this.settings.operatorOpposites[node.operator.type]) {
                    var invert = this.settings.operatorOpposites[node.operator.type];
                    // check if the invert is "authorized"
                    if (!node.filter.operators || node.filter.operators.indexOf(invert) != -1) {
                        node.operator = this.getOperatorByType(invert);
                    }
                }
                else if (!options.silent_fail) {
                    Utils.error('InvertOperator', 'Unknown inverse of operator "{0}"', node.operator.type);
                }
            }
        }

        if (options.trigger) {
            /**
             * After {@link module:plugins.Invert.invert} method
             * @event afterInvert
             * @memberof module:plugins.Invert
             * @param {Node} node - the main group or rule that has been modified
             * @param {object} options
             */
            this.trigger('afterInvert', node, options);
        }
    }
});


/**
 * @class MongoDbSupport
 * @memberof module:plugins
 * @description Allows to export rules as a MongoDB find object as well as populating the builder from a MongoDB object.
 */

QueryBuilder.defaults({
    mongoOperators: {
        // @formatter:off
        equal:            function(v) { return v[0]; },
        not_equal:        function(v) { return { '$ne': v[0] }; },
        in:               function(v) { return { '$in': v }; },
        not_in:           function(v) { return { '$nin': v }; },
        less:             function(v) { return { '$lt': v[0] }; },
        less_or_equal:    function(v) { return { '$lte': v[0] }; },
        greater:          function(v) { return { '$gt': v[0] }; },
        greater_or_equal: function(v) { return { '$gte': v[0] }; },
        between:          function(v) { return { '$gte': v[0], '$lte': v[1] }; },
        not_between:      function(v) { return { '$lt': v[0], '$gt': v[1] }; },
        begins_with:      function(v) { return { '$regex': '^' + Utils.escapeRegExp(v[0]) }; },
        not_begins_with:  function(v) { return { '$regex': '^(?!' + Utils.escapeRegExp(v[0]) + ')' }; },
        contains:         function(v) { return { '$regex': Utils.escapeRegExp(v[0]) }; },
        not_contains:     function(v) { return { '$regex': '^((?!' + Utils.escapeRegExp(v[0]) + ').)*$', '$options': 's' }; },
        ends_with:        function(v) { return { '$regex': Utils.escapeRegExp(v[0]) + '$' }; },
        not_ends_with:    function(v) { return { '$regex': '(?<!' + Utils.escapeRegExp(v[0]) + ')$' }; },
        is_empty:         function(v) { return ''; },
        is_not_empty:     function(v) { return { '$ne': '' }; },
        is_null:          function(v) { return null; },
        is_not_null:      function(v) { return { '$ne': null }; }
        // @formatter:on
    },

    mongoRuleOperators: {
        $ne: function(v) {
            v = v.$ne;
            return {
                'val': v,
                'op': v === null ? 'is_not_null' : (v === '' ? 'is_not_empty' : 'not_equal')
            };
        },
        eq: function(v) {
            return {
                'val': v,
                'op': v === null ? 'is_null' : (v === '' ? 'is_empty' : 'equal')
            };
        },
        $regex: function(v) {
            v = v.$regex;
            if (v.slice(0, 4) == '^(?!' && v.slice(-1) == ')') {
                return { 'val': v.slice(4, -1), 'op': 'not_begins_with' };
            }
            else if (v.slice(0, 5) == '^((?!' && v.slice(-5) == ').)*$') {
                return { 'val': v.slice(5, -5), 'op': 'not_contains' };
            }
            else if (v.slice(0, 4) == '(?<!' && v.slice(-2) == ')$') {
                return { 'val': v.slice(4, -2), 'op': 'not_ends_with' };
            }
            else if (v.slice(-1) == '$') {
                return { 'val': v.slice(0, -1), 'op': 'ends_with' };
            }
            else if (v.slice(0, 1) == '^') {
                return { 'val': v.slice(1), 'op': 'begins_with' };
            }
            else {
                return { 'val': v, 'op': 'contains' };
            }
        },
        between: function(v) {
            return { 'val': [v.$gte, v.$lte], 'op': 'between' };
        },
        not_between: function(v) {
            return { 'val': [v.$lt, v.$gt], 'op': 'not_between' };
        },
        $in: function(v) {
            return { 'val': v.$in, 'op': 'in' };
        },
        $nin: function(v) {
            return { 'val': v.$nin, 'op': 'not_in' };
        },
        $lt: function(v) {
            return { 'val': v.$lt, 'op': 'less' };
        },
        $lte: function(v) {
            return { 'val': v.$lte, 'op': 'less_or_equal' };
        },
        $gt: function(v) {
            return { 'val': v.$gt, 'op': 'greater' };
        },
        $gte: function(v) {
            return { 'val': v.$gte, 'op': 'greater_or_equal' };
        }
    }
});

QueryBuilder.extend(/** @lends module:plugins.MongoDbSupport.prototype */ {
    /**
     * Returns rules as a MongoDB query
     * @param {object} [data] - current rules by default
     * @returns {object}
     * @fires module:plugins.MongoDbSupport.changer:getMongoDBField
     * @fires module:plugins.MongoDbSupport.changer:ruleToMongo
     * @fires module:plugins.MongoDbSupport.changer:groupToMongo
     * @throws UndefinedMongoConditionError, UndefinedMongoOperatorError
     */
    getMongo: function(data) {
        data = (data === undefined) ? this.getRules() : data;

        var self = this;

        return (function parse(group) {
            if (!group.condition) {
                group.condition = self.settings.default_condition;
            }
            if (['AND', 'OR'].indexOf(group.condition.toUpperCase()) === -1) {
                Utils.error('UndefinedMongoCondition', 'Unable to build MongoDB query with condition "{0}"', group.condition);
            }

            if (!group.rules) {
                return {};
            }

            var parts = [];

            group.rules.forEach(function(rule) {
                if (rule.rules && rule.rules.length > 0) {
                    parts.push(parse(rule));
                }
                else {
                    var mdb = self.settings.mongoOperators[rule.operator];
                    var ope = self.getOperatorByType(rule.operator);
                    var values = [];

                    if (mdb === undefined) {
                        Utils.error('UndefinedMongoOperator', 'Unknown MongoDB operation for operator "{0}"', rule.operator);
                    }

                    if (ope.nb_inputs !== 0) {
                        if (!(rule.value instanceof Array)) {
                            rule.value = [rule.value];
                        }

                        rule.value.forEach(function(v) {
                            values.push(Utils.changeType(v, rule.type, false));
                        });
                    }

                    /**
                     * Modifies the MongoDB field used by a rule
                     * @event changer:getMongoDBField
                     * @memberof module:plugins.MongoDbSupport
                     * @param {string} field
                     * @param {Rule} rule
                     * @returns {string}
                     */
                    var field = self.change('getMongoDBField', rule.field, rule);

                    var ruleExpression = {};
                    ruleExpression[field] = mdb.call(self, values);

                    /**
                     * Modifies the MongoDB expression generated for a rul
                     * @event changer:ruleToMongo
                     * @memberof module:plugins.MongoDbSupport
                     * @param {object} expression
                     * @param {Rule} rule
                     * @param {*} value
                     * @param {function} valueWrapper - function that takes the value and adds the operator
                     * @returns {object}
                     */
                    parts.push(self.change('ruleToMongo', ruleExpression, rule, values, mdb));
                }
            });

            var groupExpression = {};
            groupExpression['$' + group.condition.toLowerCase()] = parts;

            /**
             * Modifies the MongoDB expression generated for a group
             * @event changer:groupToMongo
             * @memberof module:plugins.MongoDbSupport
             * @param {object} expression
             * @param {Group} group
             * @returns {object}
             */
            return self.change('groupToMongo', groupExpression, group);
        }(data));
    },

    /**
     * Converts a MongoDB query to rules
     * @param {object} query
     * @returns {object}
     * @fires module:plugins.MongoDbSupport.changer:parseMongoNode
     * @fires module:plugins.MongoDbSupport.changer:getMongoDBFieldID
     * @fires module:plugins.MongoDbSupport.changer:mongoToRule
     * @fires module:plugins.MongoDbSupport.changer:mongoToGroup
     * @throws MongoParseError, UndefinedMongoConditionError, UndefinedMongoOperatorError
     */
    getRulesFromMongo: function(query) {
        if (query === undefined || query === null) {
            return null;
        }

        var self = this;

        /**
         * Custom parsing of a MongoDB expression, you can return a sub-part of the expression, or a well formed group or rule JSON
         * @event changer:parseMongoNode
         * @memberof module:plugins.MongoDbSupport
         * @param {object} expression
         * @returns {object} expression, rule or group
         */
        query = self.change('parseMongoNode', query);

        // a plugin returned a group
        if ('rules' in query && 'condition' in query) {
            return query;
        }

        // a plugin returned a rule
        if ('id' in query && 'operator' in query && 'value' in query) {
            return {
                condition: this.settings.default_condition,
                rules: [query]
            };
        }

        var key = andOr(query);
        if (!key) {
            Utils.error('MongoParse', 'Invalid MongoDB query format');
        }

        return (function parse(data, topKey) {
            var rules = data[topKey];
            var parts = [];

            rules.forEach(function(data) {
                // allow plugins to manually parse or handle special cases
                data = self.change('parseMongoNode', data);

                // a plugin returned a group
                if ('rules' in data && 'condition' in data) {
                    parts.push(data);
                    return;
                }

                // a plugin returned a rule
                if ('id' in data && 'operator' in data && 'value' in data) {
                    parts.push(data);
                    return;
                }

                var key = andOr(data);
                if (key) {
                    parts.push(parse(data, key));
                }
                else {
                    var field = Object.keys(data)[0];
                    var value = data[field];

                    var operator = determineMongoOperator(value, field);
                    if (operator === undefined) {
                        Utils.error('MongoParse', 'Invalid MongoDB query format');
                    }

                    var mdbrl = self.settings.mongoRuleOperators[operator];
                    if (mdbrl === undefined) {
                        Utils.error('UndefinedMongoOperator', 'JSON Rule operation unknown for operator "{0}"', operator);
                    }

                    var opVal = mdbrl.call(self, value);

                    var id = self.getMongoDBFieldID(field, value);

                    /**
                     * Modifies the rule generated from the MongoDB expression
                     * @event changer:mongoToRule
                     * @memberof module:plugins.MongoDbSupport
                     * @param {object} rule
                     * @param {object} expression
                     * @returns {object}
                     */
                    var rule = self.change('mongoToRule', {
                        id: id,
                        field: field,
                        operator: opVal.op,
                        value: opVal.val
                    }, data);

                    parts.push(rule);
                }
            });

            /**
             * Modifies the group generated from the MongoDB expression
             * @event changer:mongoToGroup
             * @memberof module:plugins.MongoDbSupport
             * @param {object} group
             * @param {object} expression
             * @returns {object}
             */
            return self.change('mongoToGroup', {
                condition: topKey.replace('$', '').toUpperCase(),
                rules: parts
            }, data);
        }(query, key));
    },

    /**
     * Sets rules a from MongoDB query
     * @see module:plugins.MongoDbSupport.getRulesFromMongo
     */
    setRulesFromMongo: function(query) {
        this.setRules(this.getRulesFromMongo(query));
    },

    /**
     * Returns a filter identifier from the MongoDB field.
     * Automatically use the only one filter with a matching field, fires a changer otherwise.
     * @param {string} field
     * @param {*} value
     * @fires module:plugins.MongoDbSupport:changer:getMongoDBFieldID
     * @returns {string}
     * @private
     */
    getMongoDBFieldID: function(field, value) {
        var matchingFilters = this.filters.filter(function(filter) {
            return filter.field === field;
        });

        var id;
        if (matchingFilters.length === 1) {
            id = matchingFilters[0].id;
        }
        else {
            /**
             * Returns a filter identifier from the MongoDB field
             * @event changer:getMongoDBFieldID
             * @memberof module:plugins.MongoDbSupport
             * @param {string} field
             * @param {*} value
             * @returns {string}
             */
            id = this.change('getMongoDBFieldID', field, value);
        }

        return id;
    }
});

/**
 * Finds which operator is used in a MongoDB sub-object
 * @memberof module:plugins.MongoDbSupport
 * @param {*} value
 * @returns {string|undefined}
 * @private
 */
function determineMongoOperator(value) {
    if (value !== null && typeof value == 'object') {
        var subkeys = Object.keys(value);

        if (subkeys.length === 1) {
            return subkeys[0];
        }
        else {
            if (value.$gte !== undefined && value.$lte !== undefined) {
                return 'between';
            }
            if (value.$lt !== undefined && value.$gt !== undefined) {
                return 'not_between';
            }
            else if (value.$regex !== undefined) { // optional $options
                return '$regex';
            }
            else {
                return;
            }
        }
    }
    else {
        return 'eq';
    }
}

/**
 * Returns the key corresponding to "$or" or "$and"
 * @memberof module:plugins.MongoDbSupport
 * @param {object} data
 * @returns {string}
 * @private
 */
function andOr(data) {
    var keys = Object.keys(data);

    for (var i = 0, l = keys.length; i < l; i++) {
        if (keys[i].toLowerCase() == '$or' || keys[i].toLowerCase() == '$and') {
            return keys[i];
        }
    }

    return undefined;
}


/**
 * @class NotGroup
 * @memberof module:plugins
 * @description Adds a "Not" checkbox in front of group conditions.
 * @param {object} [options]
 * @param {string} [options.icon_checked='glyphicon glyphicon-checked']
 * @param {string} [options.icon_unchecked='glyphicon glyphicon-unchecked']
 */
QueryBuilder.define('not-group', function(options) {
    var self = this;

    // Bind events
    this.on('afterInit', function() {
        self.$el.on('click.queryBuilder', '[data-not=group]', function() {
            var $group = $(this).closest(QueryBuilder.selectors.group_container);
            var group = self.getModel($group);
            group.not = !group.not;
        });

        self.model.on('update', function(e, node, field) {
            if (node instanceof Group && field === 'not') {
                self.updateGroupNot(node);
            }
        });
    });

    // Init "not" property
    this.on('afterAddGroup', function(e, group) {
        group.__.not = false;
    });

    // Modify templates
    this.on('getGroupTemplate.filter', function(h, level) {
        var $h = $(h.value);
        $h.find(QueryBuilder.selectors.condition_container).prepend(
            '<button type="button" class="btn btn-xs btn-default" data-not="group">' +
            '<i class="' + options.icon_unchecked + '"></i> ' + self.translate('NOT') +
            '</button>'
        );
        h.value = $h.prop('outerHTML');
    });

    // Export "not" to JSON
    this.on('groupToJson.filter', function(e, group) {
        e.value.not = group.not;
    });

    // Read "not" from JSON
    this.on('jsonToGroup.filter', function(e, json) {
        e.value.not = !!json.not;
    });

    // Export "not" to SQL
    this.on('groupToSQL.filter', function(e, group) {
        if (group.not) {
            e.value = 'NOT ( ' + e.value + ' )';
        }
    });

    // Parse "NOT" function from sqlparser
    this.on('parseSQLNode.filter', function(e) {
        if (e.value.name && e.value.name.toUpperCase() == 'NOT') {
            e.value = e.value.arguments.value[0];
            e.value.not = true;
        }
    });

    // Read "not" from parsed SQL
    this.on('sqlToGroup.filter', function(e, data) {
        e.value.not = !!data.not;
    });

    // Export "not" to Mongo
    this.on('groupToMongo.filter', function(e, group) {
        var key = '$' + group.condition.toLowerCase();
        if (group.not && e.value[key]) {
            e.value = { '$nor': [e.value] };
        }
    });

    // Parse "$nor" operator from Mongo
    this.on('parseMongoNode.filter', function(e) {
        var keys = Object.keys(e.value);

        if (keys[0] == '$nor') {
            e.value = e.value[keys[0]][0];
            e.value.not = true;
        }
    });

    // Read "not" from parsed Mongo
    this.on('mongoToGroup.filter', function(e, data) {
        e.value.not = !!data.not;
    });
}, {
    icon_unchecked: 'glyphicon glyphicon-unchecked',
    icon_checked: 'glyphicon glyphicon-check'
});

/**
 * From {@link module:plugins.NotGroup}
 * @name not
 * @member {boolean}
 * @memberof Group
 * @instance
 */
Utils.defineModelProperties(Group, ['not']);

QueryBuilder.selectors.group_not = QueryBuilder.selectors.group_header + ' [data-not=group]';

QueryBuilder.extend(/** @lends module:plugins.NotGroup.prototype */ {
    /**
     * Performs actions when a group's not changes
     * @param {Group} group
     * @fires module:plugins.NotGroup.afterUpdateGroupNot
     * @private
     */
    updateGroupNot: function(group) {
        var options = this.plugins['not-group'];
        group.$el.find('>' + QueryBuilder.selectors.group_not)
            .toggleClass('active', group.not)
            .find('i').attr('class', group.not ? options.icon_checked : options.icon_unchecked);

        /**
         * After the group's not flag has been modified
         * @event afterUpdateGroupNot
         * @memberof module:plugins.NotGroup
         * @param {Group} group
         */
        this.trigger('afterUpdateGroupNot', group);
    }
});


/**
 * @class Sortable
 * @memberof module:plugins
 * @description Enables drag & drop sort of rules.
 * @param {object} [options]
 * @param {boolean} [options.inherit_no_drop=true]
 * @param {boolean} [options.inherit_no_sortable=true]
 * @param {string} [options.icon='glyphicon glyphicon-sort']
 * @throws MissingLibraryError, ConfigError
 */
QueryBuilder.define('sortable', function(options) {
    if (!('interact' in window)) {
        Utils.error('MissingLibrary', 'interact.js is required to use "sortable" plugin. Get it here: http://interactjs.io');
    }

    if (options.default_no_sortable !== undefined) {
        Utils.error(false, 'Config', 'Sortable plugin : "default_no_sortable" options is deprecated, use standard "default_rule_flags" and "default_group_flags" instead');
        this.settings.default_rule_flags.no_sortable = this.settings.default_group_flags.no_sortable = options.default_no_sortable;
    }

    // recompute drop-zones during drag (when a rule is hidden)
    interact.dynamicDrop(true);

    // set move threshold to 10px
    interact.pointerMoveTolerance(10);

    var placeholder;
    var ghost;
    var src;

    // Init drag and drop
    this.on('afterAddRule afterAddGroup', function(e, node) {
        if (node == placeholder) {
            return;
        }

        var self = e.builder;

        // Inherit flags
        if (options.inherit_no_sortable && node.parent && node.parent.flags.no_sortable) {
            node.flags.no_sortable = true;
        }
        if (options.inherit_no_drop && node.parent && node.parent.flags.no_drop) {
            node.flags.no_drop = true;
        }

        // Configure drag
        if (!node.flags.no_sortable) {
            interact(node.$el[0])
                .allowFrom(QueryBuilder.selectors.drag_handle)
                .draggable({
                    onstart: function(event) {
                        // get model of dragged element
                        src = self.getModel(event.target);

                        // create ghost
                        ghost = src.$el.clone()
                            .appendTo(src.$el.parent())
                            .width(src.$el.outerWidth())
                            .addClass('dragging');

                        // create drop placeholder
                        var ph = $('<div class="rule-placeholder">&nbsp;</div>')
                            .height(src.$el.outerHeight());

                        placeholder = src.parent.addRule(ph, src.getPos());

                        // hide dragged element
                        src.$el.hide();
                    },
                    onmove: function(event) {
                        // make the ghost follow the cursor
                        ghost[0].style.top = event.clientY - 15 + 'px';
                        ghost[0].style.left = event.clientX - 15 + 'px';
                    },
                    onend: function() {
                        // remove ghost
                        ghost.remove();
                        ghost = undefined;

                        // remove placeholder
                        placeholder.drop();
                        placeholder = undefined;

                        // show element
                        src.$el.show();

                        /**
                         * After a node has been moved with {@link module:plugins.Sortable}
                         * @event afterMove
                         * @memberof module:plugins.Sortable
                         * @param {Node} node
                         */
                        self.trigger('afterMove', src);
                    }
                });
        }

        if (!node.flags.no_drop) {
            //  Configure drop on groups and rules
            interact(node.$el[0])
                .dropzone({
                    accept: QueryBuilder.selectors.rule_and_group_containers,
                    ondragenter: function(event) {
                        moveSortableToTarget(placeholder, $(event.target), self);
                    },
                    ondrop: function(event) {
                        moveSortableToTarget(src, $(event.target), self);
                    }
                });

            // Configure drop on group headers
            if (node instanceof Group) {
                interact(node.$el.find(QueryBuilder.selectors.group_header)[0])
                    .dropzone({
                        accept: QueryBuilder.selectors.rule_and_group_containers,
                        ondragenter: function(event) {
                            moveSortableToTarget(placeholder, $(event.target), self);
                        },
                        ondrop: function(event) {
                            moveSortableToTarget(src, $(event.target), self);
                        }
                    });
            }
        }
    });

    // Detach interactables
    this.on('beforeDeleteRule beforeDeleteGroup', function(e, node) {
        if (!e.isDefaultPrevented()) {
            interact(node.$el[0]).unset();

            if (node instanceof Group) {
                interact(node.$el.find(QueryBuilder.selectors.group_header)[0]).unset();
            }
        }
    });

    // Remove drag handle from non-sortable items
    this.on('afterApplyRuleFlags afterApplyGroupFlags', function(e, node) {
        if (node.flags.no_sortable) {
            node.$el.find('.drag-handle').remove();
        }
    });

    // Modify templates
    this.on('getGroupTemplate.filter', function(h, level) {
        if (level > 1) {
            var $h = $(h.value);
            $h.find(QueryBuilder.selectors.condition_container).after('<div class="drag-handle"><i class="' + options.icon + '"></i></div>');
            h.value = $h.prop('outerHTML');
        }
    });

    this.on('getRuleTemplate.filter', function(h) {
        var $h = $(h.value);
        $h.find(QueryBuilder.selectors.rule_header).after('<div class="drag-handle"><i class="' + options.icon + '"></i></div>');
        h.value = $h.prop('outerHTML');
    });
}, {
    inherit_no_sortable: true,
    inherit_no_drop: true,
    icon: 'glyphicon glyphicon-sort'
});

QueryBuilder.selectors.rule_and_group_containers = QueryBuilder.selectors.rule_container + ', ' + QueryBuilder.selectors.group_container;
QueryBuilder.selectors.drag_handle = '.drag-handle';

QueryBuilder.defaults({
    default_rule_flags: {
        no_sortable: false,
        no_drop: false
    },
    default_group_flags: {
        no_sortable: false,
        no_drop: false
    }
});

/**
 * Moves an element (placeholder or actual object) depending on active target
 * @memberof module:plugins.Sortable
 * @param {Node} node
 * @param {jQuery} target
 * @param {QueryBuilder} [builder]
 * @private
 */
function moveSortableToTarget(node, target, builder) {
    var parent, method;
    var Selectors = QueryBuilder.selectors;

    // on rule
    parent = target.closest(Selectors.rule_container);
    if (parent.length) {
        method = 'moveAfter';
    }

    // on group header
    if (!method) {
        parent = target.closest(Selectors.group_header);
        if (parent.length) {
            parent = target.closest(Selectors.group_container);
            method = 'moveAtBegin';
        }
    }

    // on group
    if (!method) {
        parent = target.closest(Selectors.group_container);
        if (parent.length) {
            method = 'moveAtEnd';
        }
    }

    if (method) {
        node[method](builder.getModel(parent));

        // refresh radio value
        if (builder && node instanceof Rule) {
            builder.setRuleInputValue(node, node.value);
        }
    }
}


/**
 * @class SqlSupport
 * @memberof module:plugins
 * @description Allows to export rules as a SQL WHERE statement as well as populating the builder from an SQL query.
 * @param {object} [options]
 * @param {boolean} [options.boolean_as_integer=true] - `true` to convert boolean values to integer in the SQL output
 */
QueryBuilder.define('sql-support', function(options) {

}, {
    boolean_as_integer: true
});

QueryBuilder.defaults({
    // operators for internal -> SQL conversion
    sqlOperators: {
        equal: { op: '= ?' },
        not_equal: { op: '!= ?' },
        in: { op: 'IN(?)', sep: ', ' },
        not_in: { op: 'NOT IN(?)', sep: ', ' },
        less: { op: '< ?' },
        less_or_equal: { op: '<= ?' },
        greater: { op: '> ?' },
        greater_or_equal: { op: '>= ?' },
        between: { op: 'BETWEEN ?', sep: ' AND ' },
        not_between: { op: 'NOT BETWEEN ?', sep: ' AND ' },
        begins_with: { op: 'LIKE(?)', mod: '{0}%' },
        not_begins_with: { op: 'NOT LIKE(?)', mod: '{0}%' },
        contains: { op: 'LIKE(?)', mod: '%{0}%' },
        not_contains: { op: 'NOT LIKE(?)', mod: '%{0}%' },
        ends_with: { op: 'LIKE(?)', mod: '%{0}' },
        not_ends_with: { op: 'NOT LIKE(?)', mod: '%{0}' },
        is_empty: { op: '= \'\'' },
        is_not_empty: { op: '!= \'\'' },
        is_null: { op: 'IS NULL' },
        is_not_null: { op: 'IS NOT NULL' }
    },

    // operators for SQL -> internal conversion
    sqlRuleOperator: {
        '=': function(v) {
            return {
                val: v,
                op: v === '' ? 'is_empty' : 'equal'
            };
        },
        '!=': function(v) {
            return {
                val: v,
                op: v === '' ? 'is_not_empty' : 'not_equal'
            };
        },
        'LIKE': function(v) {
            if (v.slice(0, 1) == '%' && v.slice(-1) == '%') {
                return {
                    val: v.slice(1, -1),
                    op: 'contains'
                };
            }
            else if (v.slice(0, 1) == '%') {
                return {
                    val: v.slice(1),
                    op: 'ends_with'
                };
            }
            else if (v.slice(-1) == '%') {
                return {
                    val: v.slice(0, -1),
                    op: 'begins_with'
                };
            }
            else {
                Utils.error('SQLParse', 'Invalid value for LIKE operator "{0}"', v);
            }
        },
        'NOT LIKE': function(v) {
            if (v.slice(0, 1) == '%' && v.slice(-1) == '%') {
                return {
                    val: v.slice(1, -1),
                    op: 'not_contains'
                };
            }
            else if (v.slice(0, 1) == '%') {
                return {
                    val: v.slice(1),
                    op: 'not_ends_with'
                };
            }
            else if (v.slice(-1) == '%') {
                return {
                    val: v.slice(0, -1),
                    op: 'not_begins_with'
                };
            }
            else {
                Utils.error('SQLParse', 'Invalid value for NOT LIKE operator "{0}"', v);
            }
        },
        'IN': function(v) {
            return { val: v, op: 'in' };
        },
        'NOT IN': function(v) {
            return { val: v, op: 'not_in' };
        },
        '<': function(v) {
            return { val: v, op: 'less' };
        },
        '<=': function(v) {
            return { val: v, op: 'less_or_equal' };
        },
        '>': function(v) {
            return { val: v, op: 'greater' };
        },
        '>=': function(v) {
            return { val: v, op: 'greater_or_equal' };
        },
        'BETWEEN': function(v) {
            return { val: v, op: 'between' };
        },
        'NOT BETWEEN': function(v) {
            return { val: v, op: 'not_between' };
        },
        'IS': function(v) {
            if (v !== null) {
                Utils.error('SQLParse', 'Invalid value for IS operator');
            }
            return { val: null, op: 'is_null' };
        },
        'IS NOT': function(v) {
            if (v !== null) {
                Utils.error('SQLParse', 'Invalid value for IS operator');
            }
            return { val: null, op: 'is_not_null' };
        }
    },

    // statements for internal -> SQL conversion
    sqlStatements: {
        'question_mark': function() {
            var params = [];
            return {
                add: function(rule, value) {
                    params.push(value);
                    return '?';
                },
                run: function() {
                    return params;
                }
            };
        },

        'numbered': function(char) {
            if (!char || char.length > 1) char = '$';
            var index = 0;
            var params = [];
            return {
                add: function(rule, value) {
                    params.push(value);
                    index++;
                    return char + index;
                },
                run: function() {
                    return params;
                }
            };
        },

        'named': function(char) {
            if (!char || char.length > 1) char = ':';
            var indexes = {};
            var params = {};
            return {
                add: function(rule, value) {
                    if (!indexes[rule.field]) indexes[rule.field] = 1;
                    var key = rule.field + '_' + (indexes[rule.field]++);
                    params[key] = value;
                    return char + key;
                },
                run: function() {
                    return params;
                }
            };
        }
    },

    // statements for SQL -> internal conversion
    sqlRuleStatement: {
        'question_mark': function(values) {
            var index = 0;
            return {
                parse: function(v) {
                    return v == '?' ? values[index++] : v;
                },
                esc: function(sql) {
                    return sql.replace(/\?/g, '\'?\'');
                }
            };
        },

        'numbered': function(values, char) {
            if (!char || char.length > 1) char = '$';
            var regex1 = new RegExp('^\\' + char + '[0-9]+$');
            var regex2 = new RegExp('\\' + char + '([0-9]+)', 'g');
            return {
                parse: function(v) {
                    return regex1.test(v) ? values[v.slice(1) - 1] : v;
                },
                esc: function(sql) {
                    return sql.replace(regex2, '\'' + (char == '$' ? '$$' : char) + '$1\'');
                }
            };
        },

        'named': function(values, char) {
            if (!char || char.length > 1) char = ':';
            var regex1 = new RegExp('^\\' + char);
            var regex2 = new RegExp('\\' + char + '(' + Object.keys(values).join('|') + ')', 'g');
            return {
                parse: function(v) {
                    return regex1.test(v) ? values[v.slice(1)] : v;
                },
                esc: function(sql) {
                    return sql.replace(regex2, '\'' + (char == '$' ? '$$' : char) + '$1\'');
                }
            };
        }
    }
});

/**
 * @typedef {object} SqlQuery
 * @memberof module:plugins.SqlSupport
 * @property {string} sql
 * @property {object} params
 */

QueryBuilder.extend(/** @lends module:plugins.SqlSupport.prototype */ {
    /**
     * Returns rules as a SQL query
     * @param {boolean|string} [stmt] - use prepared statements: false, 'question_mark', 'numbered', 'numbered(@)', 'named', 'named(@)'
     * @param {boolean} [nl=false] output with new lines
     * @param {object} [data] - current rules by default
     * @returns {module:plugins.SqlSupport.SqlQuery}
     * @fires module:plugins.SqlSupport.changer:getSQLField
     * @fires module:plugins.SqlSupport.changer:ruleToSQL
     * @fires module:plugins.SqlSupport.changer:groupToSQL
     * @throws UndefinedSQLConditionError, UndefinedSQLOperatorError
     */
    getSQL: function(stmt, nl, data) {
        data = (data === undefined) ? this.getRules() : data;
        nl = !!nl ? '\n' : ' ';
        var boolean_as_integer = this.getPluginOptions('sql-support', 'boolean_as_integer');

        if (stmt === true) stmt = 'question_mark';
        if (typeof stmt == 'string') {
            var config = getStmtConfig(stmt);
            stmt = this.settings.sqlStatements[config[1]](config[2]);
        }

        var self = this;

        var sql = (function parse(group) {
            if (!group.condition) {
                group.condition = self.settings.default_condition;
            }
            if (['AND', 'OR'].indexOf(group.condition.toUpperCase()) === -1) {
                Utils.error('UndefinedSQLCondition', 'Unable to build SQL query with condition "{0}"', group.condition);
            }

            if (!group.rules) {
                return '';
            }

            var parts = [];

            group.rules.forEach(function(rule) {
                if (rule.rules && rule.rules.length > 0) {
                    parts.push('(' + nl + parse(rule) + nl + ')' + nl);
                }
                else {
                    var sql = self.settings.sqlOperators[rule.operator];
                    var ope = self.getOperatorByType(rule.operator);
                    var value = '';

                    if (sql === undefined) {
                        Utils.error('UndefinedSQLOperator', 'Unknown SQL operation for operator "{0}"', rule.operator);
                    }

                    if (ope.nb_inputs !== 0) {
                        if (!(rule.value instanceof Array)) {
                            rule.value = [rule.value];
                        }

                        rule.value.forEach(function(v, i) {
                            if (i > 0) {
                                value += sql.sep;
                            }

                            if (rule.type == 'integer' || rule.type == 'double' || rule.type == 'boolean') {
                                v = Utils.changeType(v, rule.type, boolean_as_integer);
                            }
                            else if (!stmt) {
                                v = Utils.escapeString(v);
                            }

                            if (sql.mod) {
                                v = Utils.fmt(sql.mod, v);
                            }

                            if (stmt) {
                                value += stmt.add(rule, v);
                            }
                            else {
                                if (typeof v == 'string') {
                                    v = '\'' + v + '\'';
                                }

                                value += v;
                            }
                        });
                    }

                    var sqlFn = function(v) {
                        return sql.op.replace(/\?/, v);
                    };

                    /**
                     * Modifies the SQL field used by a rule
                     * @event changer:getSQLField
                     * @memberof module:plugins.SqlSupport
                     * @param {string} field
                     * @param {Rule} rule
                     * @returns {string}
                     */
                    var field = self.change('getSQLField', rule.field, rule);

                    var ruleExpression = field + ' ' + sqlFn(value);

                    /**
                     * Modifies the SQL generated for a rule
                     * @event changer:ruleToSQL
                     * @memberof module:plugins.SqlSupport
                     * @param {string} expression
                     * @param {Rule} rule
                     * @param {*} value
                     * @param {function} valueWrapper - function that takes the value and adds the operator
                     * @returns {string}
                     */
                    parts.push(self.change('ruleToSQL', ruleExpression, rule, value, sqlFn));
                }
            });

            var groupExpression = parts.join(' ' + group.condition + nl);

            /**
             * Modifies the SQL generated for a group
             * @event changer:groupToSQL
             * @memberof module:plugins.SqlSupport
             * @param {string} expression
             * @param {Group} group
             * @returns {string}
             */
            return self.change('groupToSQL', groupExpression, group);
        }(data));

        if (stmt) {
            return {
                sql: sql,
                params: stmt.run()
            };
        }
        else {
            return {
                sql: sql
            };
        }
    },

    /**
     * Convert a SQL query to rules
     * @param {string|module:plugins.SqlSupport.SqlQuery} query
     * @param {boolean|string} stmt
     * @returns {object}
     * @fires module:plugins.SqlSupport.changer:parseSQLNode
     * @fires module:plugins.SqlSupport.changer:getSQLFieldID
     * @fires module:plugins.SqlSupport.changer:sqlToRule
     * @fires module:plugins.SqlSupport.changer:sqlToGroup
     * @throws MissingLibraryError, SQLParseError, UndefinedSQLOperatorError
     */
    getRulesFromSQL: function(query, stmt) {
        if (!('SQLParser' in window)) {
            Utils.error('MissingLibrary', 'SQLParser is required to parse SQL queries. Get it here https://github.com/mistic100/sql-parser');
        }

        var self = this;

        if (typeof query == 'string') {
            query = { sql: query };
        }

        if (stmt === true) stmt = 'question_mark';
        if (typeof stmt == 'string') {
            var config = getStmtConfig(stmt);
            stmt = this.settings.sqlRuleStatement[config[1]](query.params, config[2]);
        }

        if (stmt) {
            query.sql = stmt.esc(query.sql);
        }

        if (query.sql.toUpperCase().indexOf('SELECT') !== 0) {
            query.sql = 'SELECT * FROM table WHERE ' + query.sql;
        }

        var parsed = SQLParser.parse(query.sql);

        if (!parsed.where) {
            Utils.error('SQLParse', 'No WHERE clause found');
        }

        /**
         * Custom parsing of an AST node generated by SQLParser, you can return a sub-part of the tree, or a well formed group or rule JSON
         * @event changer:parseSQLNode
         * @memberof module:plugins.SqlSupport
         * @param {object} AST node
         * @returns {object} tree, rule or group
         */
        var data = self.change('parseSQLNode', parsed.where.conditions);

        // a plugin returned a group
        if ('rules' in data && 'condition' in data) {
            return data;
        }

        // a plugin returned a rule
        if ('id' in data && 'operator' in data && 'value' in data) {
            return {
                condition: this.settings.default_condition,
                rules: [data]
            };
        }

        // create root group
        var out = self.change('sqlToGroup', {
            condition: this.settings.default_condition,
            rules: []
        }, data);

        // keep track of current group
        var curr = out;

        (function flatten(data, i) {
            // allow plugins to manually parse or handle special cases
            data = self.change('parseSQLNode', data);

            // a plugin returned a group
            if ('rules' in data && 'condition' in data) {
                curr.rules.push(data);
                return;
            }

            // a plugin returned a rule
            if ('id' in data && 'operator' in data && 'value' in data) {
                curr.rules.push(data);
                return;
            }

            // data must be a SQL parser node
            if (!('left' in data) || !('right' in data) || !('operation' in data)) {
                Utils.error('SQLParse', 'Unable to parse WHERE clause');
            }

            // it's a node
            if (['AND', 'OR'].indexOf(data.operation.toUpperCase()) !== -1) {
                // create a sub-group if the condition is not the same and it's not the first level
                if (i > 0 && curr.condition != data.operation.toUpperCase()) {
                    /**
                     * Modifies the group generated from the SQL expression (this is called before the group is filled with rules)
                     * @event changer:sqlToGroup
                     * @memberof module:plugins.SqlSupport
                     * @param {object} group
                     * @param {object} AST
                     * @returns {object}
                     */
                    var group = self.change('sqlToGroup', {
                        condition: self.settings.default_condition,
                        rules: []
                    }, data);

                    curr.rules.push(group);
                    curr = group;
                }

                curr.condition = data.operation.toUpperCase();
                i++;

                // some magic !
                var next = curr;
                flatten(data.left, i);

                curr = next;
                flatten(data.right, i);
            }
            // it's a leaf
            else {
                if ($.isPlainObject(data.right.value)) {
                    Utils.error('SQLParse', 'Value format not supported for {0}.', data.left.value);
                }

                // convert array
                var value;
                if ($.isArray(data.right.value)) {
                    value = data.right.value.map(function(v) {
                        return v.value;
                    });
                }
                else {
                    value = data.right.value;
                }

                // get actual values
                if (stmt) {
                    if ($.isArray(value)) {
                        value = value.map(stmt.parse);
                    }
                    else {
                        value = stmt.parse(value);
                    }
                }

                // convert operator
                var operator = data.operation.toUpperCase();
                if (operator == '<>') {
                    operator = '!=';
                }

                var sqlrl = self.settings.sqlRuleOperator[operator];
                if (sqlrl === undefined) {
                    Utils.error('UndefinedSQLOperator', 'Invalid SQL operation "{0}".', data.operation);
                }

                var opVal = sqlrl.call(this, value, data.operation);

                // find field name
                var field;
                if ('values' in data.left) {
                    field = data.left.values.join('.');
                }
                else if ('value' in data.left) {
                    field = data.left.value;
                }
                else {
                    Utils.error('SQLParse', 'Cannot find field name in {0}', JSON.stringify(data.left));
                }

                var id = self.getSQLFieldID(field, value);

                /**
                 * Modifies the rule generated from the SQL expression
                 * @event changer:sqlToRule
                 * @memberof module:plugins.SqlSupport
                 * @param {object} rule
                 * @param {object} AST
                 * @returns {object}
                 */
                var rule = self.change('sqlToRule', {
                    id: id,
                    field: field,
                    operator: opVal.op,
                    value: opVal.val
                }, data);

                curr.rules.push(rule);
            }
        }(data, 0));

        return out;
    },

    /**
     * Sets the builder's rules from a SQL query
     * @see module:plugins.SqlSupport.getRulesFromSQL
     */
    setRulesFromSQL: function(query, stmt) {
        this.setRules(this.getRulesFromSQL(query, stmt));
    },

    /**
     * Returns a filter identifier from the SQL field.
     * Automatically use the only one filter with a matching field, fires a changer otherwise.
     * @param {string} field
     * @param {*} value
     * @fires module:plugins.SqlSupport:changer:getSQLFieldID
     * @returns {string}
     * @private
     */
    getSQLFieldID: function(field, value) {
        var matchingFilters = this.filters.filter(function(filter) {
            return filter.field === field;
        });

        var id;
        if (matchingFilters.length === 1) {
            id = matchingFilters[0].id;
        }
        else {
            /**
             * Returns a filter identifier from the SQL field
             * @event changer:getSQLFieldID
             * @memberof module:plugins.SqlSupport
             * @param {string} field
             * @param {*} value
             * @returns {string}
             */
            id = this.change('getSQLFieldID', field, value);
        }

        return id;
    }
});

/**
 * Parses the statement configuration
 * @memberof module:plugins.SqlSupport
 * @param {string} stmt
 * @returns {Array} null, mode, option
 * @private
 */
function getStmtConfig(stmt) {
    var config = stmt.match(/(question_mark|numbered|named)(?:\((.)\))?/);
    if (!config) config = [null, 'question_mark', undefined];
    return config;
}


/**
 * @class UniqueFilter
 * @memberof module:plugins
 * @description Allows to define some filters as "unique": ie which can be used for only one rule, globally or in the same group.
 */
QueryBuilder.define('unique-filter', function() {
    this.status.used_filters = {};

    this.on('afterUpdateRuleFilter', this.updateDisabledFilters);
    this.on('afterDeleteRule', this.updateDisabledFilters);
    this.on('afterCreateRuleFilters', this.applyDisabledFilters);
    this.on('afterReset', this.clearDisabledFilters);
    this.on('afterClear', this.clearDisabledFilters);

    // Ensure that the default filter is not already used if unique
    this.on('getDefaultFilter.filter', function(e, model) {
        var self = e.builder;

        self.updateDisabledFilters();

        if (e.value.id in self.status.used_filters) {
            var found = self.filters.some(function(filter) {
                if (!(filter.id in self.status.used_filters) || self.status.used_filters[filter.id].length > 0 && self.status.used_filters[filter.id].indexOf(model.parent) === -1) {
                    e.value = filter;
                    return true;
                }
            });

            if (!found) {
                Utils.error(false, 'UniqueFilter', 'No more non-unique filters available');
                e.value = undefined;
            }
        }
    });
});

QueryBuilder.extend(/** @lends module:plugins.UniqueFilter.prototype */ {
    /**
     * Updates the list of used filters
     * @param {$.Event} [e]
     * @private
     */
    updateDisabledFilters: function(e) {
        var self = e ? e.builder : this;

        self.status.used_filters = {};

        if (!self.model) {
            return;
        }

        // get used filters
        (function walk(group) {
            group.each(function(rule) {
                if (rule.filter && rule.filter.unique) {
                    if (!self.status.used_filters[rule.filter.id]) {
                        self.status.used_filters[rule.filter.id] = [];
                    }
                    if (rule.filter.unique == 'group') {
                        self.status.used_filters[rule.filter.id].push(rule.parent);
                    }
                }
            }, function(group) {
                walk(group);
            });
        }(self.model.root));

        self.applyDisabledFilters(e);
    },

    /**
     * Clear the list of used filters
     * @param {$.Event} [e]
     * @private
     */
    clearDisabledFilters: function(e) {
        var self = e ? e.builder : this;

        self.status.used_filters = {};

        self.applyDisabledFilters(e);
    },

    /**
     * Disabled filters depending on the list of used ones
     * @param {$.Event} [e]
     * @private
     */
    applyDisabledFilters: function(e) {
        var self = e ? e.builder : this;

        // re-enable everything
        self.$el.find(QueryBuilder.selectors.filter_container + ' option').prop('disabled', false);

        // disable some
        $.each(self.status.used_filters, function(filterId, groups) {
            if (groups.length === 0) {
                self.$el.find(QueryBuilder.selectors.filter_container + ' option[value="' + filterId + '"]:not(:selected)').prop('disabled', true);
            }
            else {
                groups.forEach(function(group) {
                    group.each(function(rule) {
                        rule.$el.find(QueryBuilder.selectors.filter_container + ' option[value="' + filterId + '"]:not(:selected)').prop('disabled', true);
                    });
                });
            }
        });

        // update Selectpicker
        if (self.settings.plugins && self.settings.plugins['bt-selectpicker']) {
            self.$el.find(QueryBuilder.selectors.rule_filter).selectpicker('render');
        }
    }
});


/*!
 * jQuery QueryBuilder 2.4.4
 * Locale: English (en)
 * Author: Damien "Mistic" Sorel, http://www.strangeplanet.fr
 * Licensed under MIT (http://opensource.org/licenses/MIT)
 */

QueryBuilder.regional['en'] = {
  "__locale": "English (en)",
  "__author": "Damien \"Mistic\" Sorel, http://www.strangeplanet.fr",
  "add_rule": "Add rule",
  "add_group": "Add group",
  "delete_rule": "Delete",
  "delete_group": "Delete",
  "conditions": {
    "AND": "AND",
    "OR": "OR"
  },
  "operators": {
    "equal": "equal",
    "not_equal": "not equal",
    "in": "in",
    "not_in": "not in",
    "less": "less",
    "less_or_equal": "less or equal",
    "greater": "greater",
    "greater_or_equal": "greater or equal",
    "between": "between",
    "not_between": "not between",
    "begins_with": "begins with",
    "not_begins_with": "doesn't begin with",
    "contains": "contains",
    "not_contains": "doesn't contain",
    "ends_with": "ends with",
    "not_ends_with": "doesn't end with",
    "is_empty": "is empty",
    "is_not_empty": "is not empty",
    "is_null": "is null",
    "is_not_null": "is not null"
  },
  "errors": {
    "no_filter": "No filter selected",
    "empty_group": "The group is empty",
    "radio_empty": "No value selected",
    "checkbox_empty": "No value selected",
    "select_empty": "No value selected",
    "string_empty": "Empty value",
    "string_exceed_min_length": "Must contain at least {0} characters",
    "string_exceed_max_length": "Must not contain more than {0} characters",
    "string_invalid_format": "Invalid format ({0})",
    "number_nan": "Not a number",
    "number_not_integer": "Not an integer",
    "number_not_double": "Not a real number",
    "number_exceed_min": "Must be greater than {0}",
    "number_exceed_max": "Must be lower than {0}",
    "number_wrong_step": "Must be a multiple of {0}",
    "datetime_empty": "Empty value",
    "datetime_invalid": "Invalid date format ({0})",
    "datetime_exceed_min": "Must be after {0}",
    "datetime_exceed_max": "Must be before {0}",
    "boolean_not_valid": "Not a boolean",
    "operator_not_multiple": "Operator \"{1}\" cannot accept multiple values"
  },
  "invert": "Invert",
  "NOT": "NOT"
};

QueryBuilder.defaults({ lang_code: 'en' });
return QueryBuilder;

}));

},{"dot/doT":1,"jquery":5,"jquery-extendext":3}],3:[function(require,module,exports){
/*!
 * jQuery.extendext 0.1.2
 *
 * Copyright 2014-2016 Damien "Mistic" Sorel (http://www.strangeplanet.fr)
 * Licensed under MIT (http://opensource.org/licenses/MIT)
 * 
 * Based on jQuery.extend by jQuery Foundation, Inc. and other contributors
 */

/*jshint -W083 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    }
    else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    }
    else {
        factory(root.jQuery);
    }
}(this, function ($) {
    "use strict";

    $.extendext = function () {
        var options, name, src, copy, copyIsArray, clone,
            target = arguments[0] || {},
            i = 1,
            length = arguments.length,
            deep = false,
            arrayMode = 'default';

        // Handle a deep copy situation
        if (typeof target === "boolean") {
            deep = target;

            // Skip the boolean and the target
            target = arguments[i++] || {};
        }

        // Handle array mode parameter
        if (typeof target === "string") {
            arrayMode = target.toLowerCase();
            if (arrayMode !== 'concat' && arrayMode !== 'replace' && arrayMode !== 'extend') {
                arrayMode = 'default';
            }

            // Skip the string param
            target = arguments[i++] || {};
        }

        // Handle case when target is a string or something (possible in deep copy)
        if (typeof target !== "object" && !$.isFunction(target)) {
            target = {};
        }

        // Extend jQuery itself if only one argument is passed
        if (i === length) {
            target = this;
            i--;
        }

        for (; i < length; i++) {
            // Only deal with non-null/undefined values
            if ((options = arguments[i]) !== null) {
                // Special operations for arrays
                if ($.isArray(options) && arrayMode !== 'default') {
                    clone = target && $.isArray(target) ? target : [];

                    switch (arrayMode) {
                    case 'concat':
                        target = clone.concat($.extend(deep, [], options));
                        break;

                    case 'replace':
                        target = $.extend(deep, [], options);
                        break;

                    case 'extend':
                        options.forEach(function (e, i) {
                            if (typeof e === 'object') {
                                var type = $.isArray(e) ? [] : {};
                                clone[i] = $.extendext(deep, arrayMode, clone[i] || type, e);

                            } else if (clone.indexOf(e) === -1) {
                                clone.push(e);
                            }
                        });

                        target = clone;
                        break;
                    }

                } else {
                    // Extend the base object
                    for (name in options) {
                        src = target[name];
                        copy = options[name];

                        // Prevent never-ending loop
                        if (target === copy) {
                            continue;
                        }

                        // Recurse if we're merging plain objects or arrays
                        if (deep && copy && ( $.isPlainObject(copy) ||
                            (copyIsArray = $.isArray(copy)) )) {

                            if (copyIsArray) {
                                copyIsArray = false;
                                clone = src && $.isArray(src) ? src : [];

                            } else {
                                clone = src && $.isPlainObject(src) ? src : {};
                            }

                            // Never move original objects, clone them
                            target[name] = $.extendext(deep, arrayMode, clone, copy);

                            // Don't bring in undefined values
                        } else if (copy !== undefined) {
                            target[name] = copy;
                        }
                    }
                }
            }
        }

        // Return the modified object
        return target;
    };
}));
},{"jquery":5}],4:[function(require,module,exports){
/*!
 * jQuery UI Widget 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: Widget
//>>group: Core
//>>description: Provides a factory for creating stateful widgets with a common API.
//>>docs: http://api.jqueryui.com/jQuery.widget/
//>>demos: http://jqueryui.com/widget/

( function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "jquery", "./version" ], factory );
	} else {

		// Browser globals
		factory( jQuery );
	}
}( function( $ ) {

var widgetUuid = 0;
var widgetSlice = Array.prototype.slice;

$.cleanData = ( function( orig ) {
	return function( elems ) {
		var events, elem, i;
		for ( i = 0; ( elem = elems[ i ] ) != null; i++ ) {
			try {

				// Only trigger remove when necessary to save time
				events = $._data( elem, "events" );
				if ( events && events.remove ) {
					$( elem ).triggerHandler( "remove" );
				}

			// Http://bugs.jquery.com/ticket/8235
			} catch ( e ) {}
		}
		orig( elems );
	};
} )( $.cleanData );

$.widget = function( name, base, prototype ) {
	var existingConstructor, constructor, basePrototype;

	// ProxiedPrototype allows the provided prototype to remain unmodified
	// so that it can be used as a mixin for multiple widgets (#8876)
	var proxiedPrototype = {};

	var namespace = name.split( "." )[ 0 ];
	name = name.split( "." )[ 1 ];
	var fullName = namespace + "-" + name;

	if ( !prototype ) {
		prototype = base;
		base = $.Widget;
	}

	if ( $.isArray( prototype ) ) {
		prototype = $.extend.apply( null, [ {} ].concat( prototype ) );
	}

	// Create selector for plugin
	$.expr[ ":" ][ fullName.toLowerCase() ] = function( elem ) {
		return !!$.data( elem, fullName );
	};

	$[ namespace ] = $[ namespace ] || {};
	existingConstructor = $[ namespace ][ name ];
	constructor = $[ namespace ][ name ] = function( options, element ) {

		// Allow instantiation without "new" keyword
		if ( !this._createWidget ) {
			return new constructor( options, element );
		}

		// Allow instantiation without initializing for simple inheritance
		// must use "new" keyword (the code above always passes args)
		if ( arguments.length ) {
			this._createWidget( options, element );
		}
	};

	// Extend with the existing constructor to carry over any static properties
	$.extend( constructor, existingConstructor, {
		version: prototype.version,

		// Copy the object used to create the prototype in case we need to
		// redefine the widget later
		_proto: $.extend( {}, prototype ),

		// Track widgets that inherit from this widget in case this widget is
		// redefined after a widget inherits from it
		_childConstructors: []
	} );

	basePrototype = new base();

	// We need to make the options hash a property directly on the new instance
	// otherwise we'll modify the options hash on the prototype that we're
	// inheriting from
	basePrototype.options = $.widget.extend( {}, basePrototype.options );
	$.each( prototype, function( prop, value ) {
		if ( !$.isFunction( value ) ) {
			proxiedPrototype[ prop ] = value;
			return;
		}
		proxiedPrototype[ prop ] = ( function() {
			function _super() {
				return base.prototype[ prop ].apply( this, arguments );
			}

			function _superApply( args ) {
				return base.prototype[ prop ].apply( this, args );
			}

			return function() {
				var __super = this._super;
				var __superApply = this._superApply;
				var returnValue;

				this._super = _super;
				this._superApply = _superApply;

				returnValue = value.apply( this, arguments );

				this._super = __super;
				this._superApply = __superApply;

				return returnValue;
			};
		} )();
	} );
	constructor.prototype = $.widget.extend( basePrototype, {

		// TODO: remove support for widgetEventPrefix
		// always use the name + a colon as the prefix, e.g., draggable:start
		// don't prefix for widgets that aren't DOM-based
		widgetEventPrefix: existingConstructor ? ( basePrototype.widgetEventPrefix || name ) : name
	}, proxiedPrototype, {
		constructor: constructor,
		namespace: namespace,
		widgetName: name,
		widgetFullName: fullName
	} );

	// If this widget is being redefined then we need to find all widgets that
	// are inheriting from it and redefine all of them so that they inherit from
	// the new version of this widget. We're essentially trying to replace one
	// level in the prototype chain.
	if ( existingConstructor ) {
		$.each( existingConstructor._childConstructors, function( i, child ) {
			var childPrototype = child.prototype;

			// Redefine the child widget using the same prototype that was
			// originally used, but inherit from the new version of the base
			$.widget( childPrototype.namespace + "." + childPrototype.widgetName, constructor,
				child._proto );
		} );

		// Remove the list of existing child constructors from the old constructor
		// so the old child constructors can be garbage collected
		delete existingConstructor._childConstructors;
	} else {
		base._childConstructors.push( constructor );
	}

	$.widget.bridge( name, constructor );

	return constructor;
};

$.widget.extend = function( target ) {
	var input = widgetSlice.call( arguments, 1 );
	var inputIndex = 0;
	var inputLength = input.length;
	var key;
	var value;

	for ( ; inputIndex < inputLength; inputIndex++ ) {
		for ( key in input[ inputIndex ] ) {
			value = input[ inputIndex ][ key ];
			if ( input[ inputIndex ].hasOwnProperty( key ) && value !== undefined ) {

				// Clone objects
				if ( $.isPlainObject( value ) ) {
					target[ key ] = $.isPlainObject( target[ key ] ) ?
						$.widget.extend( {}, target[ key ], value ) :

						// Don't extend strings, arrays, etc. with objects
						$.widget.extend( {}, value );

				// Copy everything else by reference
				} else {
					target[ key ] = value;
				}
			}
		}
	}
	return target;
};

$.widget.bridge = function( name, object ) {
	var fullName = object.prototype.widgetFullName || name;
	$.fn[ name ] = function( options ) {
		var isMethodCall = typeof options === "string";
		var args = widgetSlice.call( arguments, 1 );
		var returnValue = this;

		if ( isMethodCall ) {

			// If this is an empty collection, we need to have the instance method
			// return undefined instead of the jQuery instance
			if ( !this.length && options === "instance" ) {
				returnValue = undefined;
			} else {
				this.each( function() {
					var methodValue;
					var instance = $.data( this, fullName );

					if ( options === "instance" ) {
						returnValue = instance;
						return false;
					}

					if ( !instance ) {
						return $.error( "cannot call methods on " + name +
							" prior to initialization; " +
							"attempted to call method '" + options + "'" );
					}

					if ( !$.isFunction( instance[ options ] ) || options.charAt( 0 ) === "_" ) {
						return $.error( "no such method '" + options + "' for " + name +
							" widget instance" );
					}

					methodValue = instance[ options ].apply( instance, args );

					if ( methodValue !== instance && methodValue !== undefined ) {
						returnValue = methodValue && methodValue.jquery ?
							returnValue.pushStack( methodValue.get() ) :
							methodValue;
						return false;
					}
				} );
			}
		} else {

			// Allow multiple hashes to be passed on init
			if ( args.length ) {
				options = $.widget.extend.apply( null, [ options ].concat( args ) );
			}

			this.each( function() {
				var instance = $.data( this, fullName );
				if ( instance ) {
					instance.option( options || {} );
					if ( instance._init ) {
						instance._init();
					}
				} else {
					$.data( this, fullName, new object( options, this ) );
				}
			} );
		}

		return returnValue;
	};
};

$.Widget = function( /* options, element */ ) {};
$.Widget._childConstructors = [];

$.Widget.prototype = {
	widgetName: "widget",
	widgetEventPrefix: "",
	defaultElement: "<div>",

	options: {
		classes: {},
		disabled: false,

		// Callbacks
		create: null
	},

	_createWidget: function( options, element ) {
		element = $( element || this.defaultElement || this )[ 0 ];
		this.element = $( element );
		this.uuid = widgetUuid++;
		this.eventNamespace = "." + this.widgetName + this.uuid;

		this.bindings = $();
		this.hoverable = $();
		this.focusable = $();
		this.classesElementLookup = {};

		if ( element !== this ) {
			$.data( element, this.widgetFullName, this );
			this._on( true, this.element, {
				remove: function( event ) {
					if ( event.target === element ) {
						this.destroy();
					}
				}
			} );
			this.document = $( element.style ?

				// Element within the document
				element.ownerDocument :

				// Element is window or document
				element.document || element );
			this.window = $( this.document[ 0 ].defaultView || this.document[ 0 ].parentWindow );
		}

		this.options = $.widget.extend( {},
			this.options,
			this._getCreateOptions(),
			options );

		this._create();

		if ( this.options.disabled ) {
			this._setOptionDisabled( this.options.disabled );
		}

		this._trigger( "create", null, this._getCreateEventData() );
		this._init();
	},

	_getCreateOptions: function() {
		return {};
	},

	_getCreateEventData: $.noop,

	_create: $.noop,

	_init: $.noop,

	destroy: function() {
		var that = this;

		this._destroy();
		$.each( this.classesElementLookup, function( key, value ) {
			that._removeClass( value, key );
		} );

		// We can probably remove the unbind calls in 2.0
		// all event bindings should go through this._on()
		this.element
			.off( this.eventNamespace )
			.removeData( this.widgetFullName );
		this.widget()
			.off( this.eventNamespace )
			.removeAttr( "aria-disabled" );

		// Clean up events and states
		this.bindings.off( this.eventNamespace );
	},

	_destroy: $.noop,

	widget: function() {
		return this.element;
	},

	option: function( key, value ) {
		var options = key;
		var parts;
		var curOption;
		var i;

		if ( arguments.length === 0 ) {

			// Don't return a reference to the internal hash
			return $.widget.extend( {}, this.options );
		}

		if ( typeof key === "string" ) {

			// Handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
			options = {};
			parts = key.split( "." );
			key = parts.shift();
			if ( parts.length ) {
				curOption = options[ key ] = $.widget.extend( {}, this.options[ key ] );
				for ( i = 0; i < parts.length - 1; i++ ) {
					curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
					curOption = curOption[ parts[ i ] ];
				}
				key = parts.pop();
				if ( arguments.length === 1 ) {
					return curOption[ key ] === undefined ? null : curOption[ key ];
				}
				curOption[ key ] = value;
			} else {
				if ( arguments.length === 1 ) {
					return this.options[ key ] === undefined ? null : this.options[ key ];
				}
				options[ key ] = value;
			}
		}

		this._setOptions( options );

		return this;
	},

	_setOptions: function( options ) {
		var key;

		for ( key in options ) {
			this._setOption( key, options[ key ] );
		}

		return this;
	},

	_setOption: function( key, value ) {
		if ( key === "classes" ) {
			this._setOptionClasses( value );
		}

		this.options[ key ] = value;

		if ( key === "disabled" ) {
			this._setOptionDisabled( value );
		}

		return this;
	},

	_setOptionClasses: function( value ) {
		var classKey, elements, currentElements;

		for ( classKey in value ) {
			currentElements = this.classesElementLookup[ classKey ];
			if ( value[ classKey ] === this.options.classes[ classKey ] ||
					!currentElements ||
					!currentElements.length ) {
				continue;
			}

			// We are doing this to create a new jQuery object because the _removeClass() call
			// on the next line is going to destroy the reference to the current elements being
			// tracked. We need to save a copy of this collection so that we can add the new classes
			// below.
			elements = $( currentElements.get() );
			this._removeClass( currentElements, classKey );

			// We don't use _addClass() here, because that uses this.options.classes
			// for generating the string of classes. We want to use the value passed in from
			// _setOption(), this is the new value of the classes option which was passed to
			// _setOption(). We pass this value directly to _classes().
			elements.addClass( this._classes( {
				element: elements,
				keys: classKey,
				classes: value,
				add: true
			} ) );
		}
	},

	_setOptionDisabled: function( value ) {
		this._toggleClass( this.widget(), this.widgetFullName + "-disabled", null, !!value );

		// If the widget is becoming disabled, then nothing is interactive
		if ( value ) {
			this._removeClass( this.hoverable, null, "ui-state-hover" );
			this._removeClass( this.focusable, null, "ui-state-focus" );
		}
	},

	enable: function() {
		return this._setOptions( { disabled: false } );
	},

	disable: function() {
		return this._setOptions( { disabled: true } );
	},

	_classes: function( options ) {
		var full = [];
		var that = this;

		options = $.extend( {
			element: this.element,
			classes: this.options.classes || {}
		}, options );

		function processClassString( classes, checkOption ) {
			var current, i;
			for ( i = 0; i < classes.length; i++ ) {
				current = that.classesElementLookup[ classes[ i ] ] || $();
				if ( options.add ) {
					current = $( $.unique( current.get().concat( options.element.get() ) ) );
				} else {
					current = $( current.not( options.element ).get() );
				}
				that.classesElementLookup[ classes[ i ] ] = current;
				full.push( classes[ i ] );
				if ( checkOption && options.classes[ classes[ i ] ] ) {
					full.push( options.classes[ classes[ i ] ] );
				}
			}
		}

		this._on( options.element, {
			"remove": "_untrackClassesElement"
		} );

		if ( options.keys ) {
			processClassString( options.keys.match( /\S+/g ) || [], true );
		}
		if ( options.extra ) {
			processClassString( options.extra.match( /\S+/g ) || [] );
		}

		return full.join( " " );
	},

	_untrackClassesElement: function( event ) {
		var that = this;
		$.each( that.classesElementLookup, function( key, value ) {
			if ( $.inArray( event.target, value ) !== -1 ) {
				that.classesElementLookup[ key ] = $( value.not( event.target ).get() );
			}
		} );
	},

	_removeClass: function( element, keys, extra ) {
		return this._toggleClass( element, keys, extra, false );
	},

	_addClass: function( element, keys, extra ) {
		return this._toggleClass( element, keys, extra, true );
	},

	_toggleClass: function( element, keys, extra, add ) {
		add = ( typeof add === "boolean" ) ? add : extra;
		var shift = ( typeof element === "string" || element === null ),
			options = {
				extra: shift ? keys : extra,
				keys: shift ? element : keys,
				element: shift ? this.element : element,
				add: add
			};
		options.element.toggleClass( this._classes( options ), add );
		return this;
	},

	_on: function( suppressDisabledCheck, element, handlers ) {
		var delegateElement;
		var instance = this;

		// No suppressDisabledCheck flag, shuffle arguments
		if ( typeof suppressDisabledCheck !== "boolean" ) {
			handlers = element;
			element = suppressDisabledCheck;
			suppressDisabledCheck = false;
		}

		// No element argument, shuffle and use this.element
		if ( !handlers ) {
			handlers = element;
			element = this.element;
			delegateElement = this.widget();
		} else {
			element = delegateElement = $( element );
			this.bindings = this.bindings.add( element );
		}

		$.each( handlers, function( event, handler ) {
			function handlerProxy() {

				// Allow widgets to customize the disabled handling
				// - disabled as an array instead of boolean
				// - disabled class as method for disabling individual parts
				if ( !suppressDisabledCheck &&
						( instance.options.disabled === true ||
						$( this ).hasClass( "ui-state-disabled" ) ) ) {
					return;
				}
				return ( typeof handler === "string" ? instance[ handler ] : handler )
					.apply( instance, arguments );
			}

			// Copy the guid so direct unbinding works
			if ( typeof handler !== "string" ) {
				handlerProxy.guid = handler.guid =
					handler.guid || handlerProxy.guid || $.guid++;
			}

			var match = event.match( /^([\w:-]*)\s*(.*)$/ );
			var eventName = match[ 1 ] + instance.eventNamespace;
			var selector = match[ 2 ];

			if ( selector ) {
				delegateElement.on( eventName, selector, handlerProxy );
			} else {
				element.on( eventName, handlerProxy );
			}
		} );
	},

	_off: function( element, eventName ) {
		eventName = ( eventName || "" ).split( " " ).join( this.eventNamespace + " " ) +
			this.eventNamespace;
		element.off( eventName ).off( eventName );

		// Clear the stack to avoid memory leaks (#10056)
		this.bindings = $( this.bindings.not( element ).get() );
		this.focusable = $( this.focusable.not( element ).get() );
		this.hoverable = $( this.hoverable.not( element ).get() );
	},

	_delay: function( handler, delay ) {
		function handlerProxy() {
			return ( typeof handler === "string" ? instance[ handler ] : handler )
				.apply( instance, arguments );
		}
		var instance = this;
		return setTimeout( handlerProxy, delay || 0 );
	},

	_hoverable: function( element ) {
		this.hoverable = this.hoverable.add( element );
		this._on( element, {
			mouseenter: function( event ) {
				this._addClass( $( event.currentTarget ), null, "ui-state-hover" );
			},
			mouseleave: function( event ) {
				this._removeClass( $( event.currentTarget ), null, "ui-state-hover" );
			}
		} );
	},

	_focusable: function( element ) {
		this.focusable = this.focusable.add( element );
		this._on( element, {
			focusin: function( event ) {
				this._addClass( $( event.currentTarget ), null, "ui-state-focus" );
			},
			focusout: function( event ) {
				this._removeClass( $( event.currentTarget ), null, "ui-state-focus" );
			}
		} );
	},

	_trigger: function( type, event, data ) {
		var prop, orig;
		var callback = this.options[ type ];

		data = data || {};
		event = $.Event( event );
		event.type = ( type === this.widgetEventPrefix ?
			type :
			this.widgetEventPrefix + type ).toLowerCase();

		// The original event may come from any element
		// so we need to reset the target on the new event
		event.target = this.element[ 0 ];

		// Copy original event properties over to the new event
		orig = event.originalEvent;
		if ( orig ) {
			for ( prop in orig ) {
				if ( !( prop in event ) ) {
					event[ prop ] = orig[ prop ];
				}
			}
		}

		this.element.trigger( event, data );
		return !( $.isFunction( callback ) &&
			callback.apply( this.element[ 0 ], [ event ].concat( data ) ) === false ||
			event.isDefaultPrevented() );
	}
};

$.each( { show: "fadeIn", hide: "fadeOut" }, function( method, defaultEffect ) {
	$.Widget.prototype[ "_" + method ] = function( element, options, callback ) {
		if ( typeof options === "string" ) {
			options = { effect: options };
		}

		var hasOptions;
		var effectName = !options ?
			method :
			options === true || typeof options === "number" ?
				defaultEffect :
				options.effect || defaultEffect;

		options = options || {};
		if ( typeof options === "number" ) {
			options = { duration: options };
		}

		hasOptions = !$.isEmptyObject( options );
		options.complete = callback;

		if ( options.delay ) {
			element.delay( options.delay );
		}

		if ( hasOptions && $.effects && $.effects.effect[ effectName ] ) {
			element[ method ]( options );
		} else if ( effectName !== method && element[ effectName ] ) {
			element[ effectName ]( options.duration, options.easing, callback );
		} else {
			element.queue( function( next ) {
				$( this )[ method ]();
				if ( callback ) {
					callback.call( element[ 0 ] );
				}
				next();
			} );
		}
	};
} );

return $.widget;

} ) );

},{}],5:[function(require,module,exports){
/*!
 * jQuery JavaScript Library v3.2.1
 * https://jquery.com/
 *
 * Includes Sizzle.js
 * https://sizzlejs.com/
 *
 * Copyright JS Foundation and other contributors
 * Released under the MIT license
 * https://jquery.org/license
 *
 * Date: 2017-03-20T18:59Z
 */
( function( global, factory ) {

	"use strict";

	if ( typeof module === "object" && typeof module.exports === "object" ) {

		// For CommonJS and CommonJS-like environments where a proper `window`
		// is present, execute the factory and get jQuery.
		// For environments that do not have a `window` with a `document`
		// (such as Node.js), expose a factory as module.exports.
		// This accentuates the need for the creation of a real `window`.
		// e.g. var jQuery = require("jquery")(window);
		// See ticket #14549 for more info.
		module.exports = global.document ?
			factory( global, true ) :
			function( w ) {
				if ( !w.document ) {
					throw new Error( "jQuery requires a window with a document" );
				}
				return factory( w );
			};
	} else {
		factory( global );
	}

// Pass this if window is not defined yet
} )( typeof window !== "undefined" ? window : this, function( window, noGlobal ) {

// Edge <= 12 - 13+, Firefox <=18 - 45+, IE 10 - 11, Safari 5.1 - 9+, iOS 6 - 9.1
// throw exceptions when non-strict code (e.g., ASP.NET 4.5) accesses strict mode
// arguments.callee.caller (trac-13335). But as of jQuery 3.0 (2016), strict mode should be common
// enough that all such attempts are guarded in a try block.
"use strict";

var arr = [];

var document = window.document;

var getProto = Object.getPrototypeOf;

var slice = arr.slice;

var concat = arr.concat;

var push = arr.push;

var indexOf = arr.indexOf;

var class2type = {};

var toString = class2type.toString;

var hasOwn = class2type.hasOwnProperty;

var fnToString = hasOwn.toString;

var ObjectFunctionString = fnToString.call( Object );

var support = {};



	function DOMEval( code, doc ) {
		doc = doc || document;

		var script = doc.createElement( "script" );

		script.text = code;
		doc.head.appendChild( script ).parentNode.removeChild( script );
	}
/* global Symbol */
// Defining this global in .eslintrc.json would create a danger of using the global
// unguarded in another place, it seems safer to define global only for this module



var
	version = "3.2.1",

	// Define a local copy of jQuery
	jQuery = function( selector, context ) {

		// The jQuery object is actually just the init constructor 'enhanced'
		// Need init if jQuery is called (just allow error to be thrown if not included)
		return new jQuery.fn.init( selector, context );
	},

	// Support: Android <=4.0 only
	// Make sure we trim BOM and NBSP
	rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,

	// Matches dashed string for camelizing
	rmsPrefix = /^-ms-/,
	rdashAlpha = /-([a-z])/g,

	// Used by jQuery.camelCase as callback to replace()
	fcamelCase = function( all, letter ) {
		return letter.toUpperCase();
	};

jQuery.fn = jQuery.prototype = {

	// The current version of jQuery being used
	jquery: version,

	constructor: jQuery,

	// The default length of a jQuery object is 0
	length: 0,

	toArray: function() {
		return slice.call( this );
	},

	// Get the Nth element in the matched element set OR
	// Get the whole matched element set as a clean array
	get: function( num ) {

		// Return all the elements in a clean array
		if ( num == null ) {
			return slice.call( this );
		}

		// Return just the one element from the set
		return num < 0 ? this[ num + this.length ] : this[ num ];
	},

	// Take an array of elements and push it onto the stack
	// (returning the new matched element set)
	pushStack: function( elems ) {

		// Build a new jQuery matched element set
		var ret = jQuery.merge( this.constructor(), elems );

		// Add the old object onto the stack (as a reference)
		ret.prevObject = this;

		// Return the newly-formed element set
		return ret;
	},

	// Execute a callback for every element in the matched set.
	each: function( callback ) {
		return jQuery.each( this, callback );
	},

	map: function( callback ) {
		return this.pushStack( jQuery.map( this, function( elem, i ) {
			return callback.call( elem, i, elem );
		} ) );
	},

	slice: function() {
		return this.pushStack( slice.apply( this, arguments ) );
	},

	first: function() {
		return this.eq( 0 );
	},

	last: function() {
		return this.eq( -1 );
	},

	eq: function( i ) {
		var len = this.length,
			j = +i + ( i < 0 ? len : 0 );
		return this.pushStack( j >= 0 && j < len ? [ this[ j ] ] : [] );
	},

	end: function() {
		return this.prevObject || this.constructor();
	},

	// For internal use only.
	// Behaves like an Array's method, not like a jQuery method.
	push: push,
	sort: arr.sort,
	splice: arr.splice
};

jQuery.extend = jQuery.fn.extend = function() {
	var options, name, src, copy, copyIsArray, clone,
		target = arguments[ 0 ] || {},
		i = 1,
		length = arguments.length,
		deep = false;

	// Handle a deep copy situation
	if ( typeof target === "boolean" ) {
		deep = target;

		// Skip the boolean and the target
		target = arguments[ i ] || {};
		i++;
	}

	// Handle case when target is a string or something (possible in deep copy)
	if ( typeof target !== "object" && !jQuery.isFunction( target ) ) {
		target = {};
	}

	// Extend jQuery itself if only one argument is passed
	if ( i === length ) {
		target = this;
		i--;
	}

	for ( ; i < length; i++ ) {

		// Only deal with non-null/undefined values
		if ( ( options = arguments[ i ] ) != null ) {

			// Extend the base object
			for ( name in options ) {
				src = target[ name ];
				copy = options[ name ];

				// Prevent never-ending loop
				if ( target === copy ) {
					continue;
				}

				// Recurse if we're merging plain objects or arrays
				if ( deep && copy && ( jQuery.isPlainObject( copy ) ||
					( copyIsArray = Array.isArray( copy ) ) ) ) {

					if ( copyIsArray ) {
						copyIsArray = false;
						clone = src && Array.isArray( src ) ? src : [];

					} else {
						clone = src && jQuery.isPlainObject( src ) ? src : {};
					}

					// Never move original objects, clone them
					target[ name ] = jQuery.extend( deep, clone, copy );

				// Don't bring in undefined values
				} else if ( copy !== undefined ) {
					target[ name ] = copy;
				}
			}
		}
	}

	// Return the modified object
	return target;
};

jQuery.extend( {

	// Unique for each copy of jQuery on the page
	expando: "jQuery" + ( version + Math.random() ).replace( /\D/g, "" ),

	// Assume jQuery is ready without the ready module
	isReady: true,

	error: function( msg ) {
		throw new Error( msg );
	},

	noop: function() {},

	isFunction: function( obj ) {
		return jQuery.type( obj ) === "function";
	},

	isWindow: function( obj ) {
		return obj != null && obj === obj.window;
	},

	isNumeric: function( obj ) {

		// As of jQuery 3.0, isNumeric is limited to
		// strings and numbers (primitives or objects)
		// that can be coerced to finite numbers (gh-2662)
		var type = jQuery.type( obj );
		return ( type === "number" || type === "string" ) &&

			// parseFloat NaNs numeric-cast false positives ("")
			// ...but misinterprets leading-number strings, particularly hex literals ("0x...")
			// subtraction forces infinities to NaN
			!isNaN( obj - parseFloat( obj ) );
	},

	isPlainObject: function( obj ) {
		var proto, Ctor;

		// Detect obvious negatives
		// Use toString instead of jQuery.type to catch host objects
		if ( !obj || toString.call( obj ) !== "[object Object]" ) {
			return false;
		}

		proto = getProto( obj );

		// Objects with no prototype (e.g., `Object.create( null )`) are plain
		if ( !proto ) {
			return true;
		}

		// Objects with prototype are plain iff they were constructed by a global Object function
		Ctor = hasOwn.call( proto, "constructor" ) && proto.constructor;
		return typeof Ctor === "function" && fnToString.call( Ctor ) === ObjectFunctionString;
	},

	isEmptyObject: function( obj ) {

		/* eslint-disable no-unused-vars */
		// See https://github.com/eslint/eslint/issues/6125
		var name;

		for ( name in obj ) {
			return false;
		}
		return true;
	},

	type: function( obj ) {
		if ( obj == null ) {
			return obj + "";
		}

		// Support: Android <=2.3 only (functionish RegExp)
		return typeof obj === "object" || typeof obj === "function" ?
			class2type[ toString.call( obj ) ] || "object" :
			typeof obj;
	},

	// Evaluates a script in a global context
	globalEval: function( code ) {
		DOMEval( code );
	},

	// Convert dashed to camelCase; used by the css and data modules
	// Support: IE <=9 - 11, Edge 12 - 13
	// Microsoft forgot to hump their vendor prefix (#9572)
	camelCase: function( string ) {
		return string.replace( rmsPrefix, "ms-" ).replace( rdashAlpha, fcamelCase );
	},

	each: function( obj, callback ) {
		var length, i = 0;

		if ( isArrayLike( obj ) ) {
			length = obj.length;
			for ( ; i < length; i++ ) {
				if ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					break;
				}
			}
		} else {
			for ( i in obj ) {
				if ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					break;
				}
			}
		}

		return obj;
	},

	// Support: Android <=4.0 only
	trim: function( text ) {
		return text == null ?
			"" :
			( text + "" ).replace( rtrim, "" );
	},

	// results is for internal usage only
	makeArray: function( arr, results ) {
		var ret = results || [];

		if ( arr != null ) {
			if ( isArrayLike( Object( arr ) ) ) {
				jQuery.merge( ret,
					typeof arr === "string" ?
					[ arr ] : arr
				);
			} else {
				push.call( ret, arr );
			}
		}

		return ret;
	},

	inArray: function( elem, arr, i ) {
		return arr == null ? -1 : indexOf.call( arr, elem, i );
	},

	// Support: Android <=4.0 only, PhantomJS 1 only
	// push.apply(_, arraylike) throws on ancient WebKit
	merge: function( first, second ) {
		var len = +second.length,
			j = 0,
			i = first.length;

		for ( ; j < len; j++ ) {
			first[ i++ ] = second[ j ];
		}

		first.length = i;

		return first;
	},

	grep: function( elems, callback, invert ) {
		var callbackInverse,
			matches = [],
			i = 0,
			length = elems.length,
			callbackExpect = !invert;

		// Go through the array, only saving the items
		// that pass the validator function
		for ( ; i < length; i++ ) {
			callbackInverse = !callback( elems[ i ], i );
			if ( callbackInverse !== callbackExpect ) {
				matches.push( elems[ i ] );
			}
		}

		return matches;
	},

	// arg is for internal usage only
	map: function( elems, callback, arg ) {
		var length, value,
			i = 0,
			ret = [];

		// Go through the array, translating each of the items to their new values
		if ( isArrayLike( elems ) ) {
			length = elems.length;
			for ( ; i < length; i++ ) {
				value = callback( elems[ i ], i, arg );

				if ( value != null ) {
					ret.push( value );
				}
			}

		// Go through every key on the object,
		} else {
			for ( i in elems ) {
				value = callback( elems[ i ], i, arg );

				if ( value != null ) {
					ret.push( value );
				}
			}
		}

		// Flatten any nested arrays
		return concat.apply( [], ret );
	},

	// A global GUID counter for objects
	guid: 1,

	// Bind a function to a context, optionally partially applying any
	// arguments.
	proxy: function( fn, context ) {
		var tmp, args, proxy;

		if ( typeof context === "string" ) {
			tmp = fn[ context ];
			context = fn;
			fn = tmp;
		}

		// Quick check to determine if target is callable, in the spec
		// this throws a TypeError, but we will just return undefined.
		if ( !jQuery.isFunction( fn ) ) {
			return undefined;
		}

		// Simulated bind
		args = slice.call( arguments, 2 );
		proxy = function() {
			return fn.apply( context || this, args.concat( slice.call( arguments ) ) );
		};

		// Set the guid of unique handler to the same of original handler, so it can be removed
		proxy.guid = fn.guid = fn.guid || jQuery.guid++;

		return proxy;
	},

	now: Date.now,

	// jQuery.support is not used in Core but other projects attach their
	// properties to it so it needs to exist.
	support: support
} );

if ( typeof Symbol === "function" ) {
	jQuery.fn[ Symbol.iterator ] = arr[ Symbol.iterator ];
}

// Populate the class2type map
jQuery.each( "Boolean Number String Function Array Date RegExp Object Error Symbol".split( " " ),
function( i, name ) {
	class2type[ "[object " + name + "]" ] = name.toLowerCase();
} );

function isArrayLike( obj ) {

	// Support: real iOS 8.2 only (not reproducible in simulator)
	// `in` check used to prevent JIT error (gh-2145)
	// hasOwn isn't used here due to false negatives
	// regarding Nodelist length in IE
	var length = !!obj && "length" in obj && obj.length,
		type = jQuery.type( obj );

	if ( type === "function" || jQuery.isWindow( obj ) ) {
		return false;
	}

	return type === "array" || length === 0 ||
		typeof length === "number" && length > 0 && ( length - 1 ) in obj;
}
var Sizzle =
/*!
 * Sizzle CSS Selector Engine v2.3.3
 * https://sizzlejs.com/
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license
 * http://jquery.org/license
 *
 * Date: 2016-08-08
 */
(function( window ) {

var i,
	support,
	Expr,
	getText,
	isXML,
	tokenize,
	compile,
	select,
	outermostContext,
	sortInput,
	hasDuplicate,

	// Local document vars
	setDocument,
	document,
	docElem,
	documentIsHTML,
	rbuggyQSA,
	rbuggyMatches,
	matches,
	contains,

	// Instance-specific data
	expando = "sizzle" + 1 * new Date(),
	preferredDoc = window.document,
	dirruns = 0,
	done = 0,
	classCache = createCache(),
	tokenCache = createCache(),
	compilerCache = createCache(),
	sortOrder = function( a, b ) {
		if ( a === b ) {
			hasDuplicate = true;
		}
		return 0;
	},

	// Instance methods
	hasOwn = ({}).hasOwnProperty,
	arr = [],
	pop = arr.pop,
	push_native = arr.push,
	push = arr.push,
	slice = arr.slice,
	// Use a stripped-down indexOf as it's faster than native
	// https://jsperf.com/thor-indexof-vs-for/5
	indexOf = function( list, elem ) {
		var i = 0,
			len = list.length;
		for ( ; i < len; i++ ) {
			if ( list[i] === elem ) {
				return i;
			}
		}
		return -1;
	},

	booleans = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",

	// Regular expressions

	// http://www.w3.org/TR/css3-selectors/#whitespace
	whitespace = "[\\x20\\t\\r\\n\\f]",

	// http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
	identifier = "(?:\\\\.|[\\w-]|[^\0-\\xa0])+",

	// Attribute selectors: http://www.w3.org/TR/selectors/#attribute-selectors
	attributes = "\\[" + whitespace + "*(" + identifier + ")(?:" + whitespace +
		// Operator (capture 2)
		"*([*^$|!~]?=)" + whitespace +
		// "Attribute values must be CSS identifiers [capture 5] or strings [capture 3 or capture 4]"
		"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + identifier + "))|)" + whitespace +
		"*\\]",

	pseudos = ":(" + identifier + ")(?:\\((" +
		// To reduce the number of selectors needing tokenize in the preFilter, prefer arguments:
		// 1. quoted (capture 3; capture 4 or capture 5)
		"('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|" +
		// 2. simple (capture 6)
		"((?:\\\\.|[^\\\\()[\\]]|" + attributes + ")*)|" +
		// 3. anything else (capture 2)
		".*" +
		")\\)|)",

	// Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
	rwhitespace = new RegExp( whitespace + "+", "g" ),
	rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" ),

	rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" ),
	rcombinators = new RegExp( "^" + whitespace + "*([>+~]|" + whitespace + ")" + whitespace + "*" ),

	rattributeQuotes = new RegExp( "=" + whitespace + "*([^\\]'\"]*?)" + whitespace + "*\\]", "g" ),

	rpseudo = new RegExp( pseudos ),
	ridentifier = new RegExp( "^" + identifier + "$" ),

	matchExpr = {
		"ID": new RegExp( "^#(" + identifier + ")" ),
		"CLASS": new RegExp( "^\\.(" + identifier + ")" ),
		"TAG": new RegExp( "^(" + identifier + "|[*])" ),
		"ATTR": new RegExp( "^" + attributes ),
		"PSEUDO": new RegExp( "^" + pseudos ),
		"CHILD": new RegExp( "^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + whitespace +
			"*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace +
			"*(\\d+)|))" + whitespace + "*\\)|)", "i" ),
		"bool": new RegExp( "^(?:" + booleans + ")$", "i" ),
		// For use in libraries implementing .is()
		// We use this for POS matching in `select`
		"needsContext": new RegExp( "^" + whitespace + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" +
			whitespace + "*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)", "i" )
	},

	rinputs = /^(?:input|select|textarea|button)$/i,
	rheader = /^h\d$/i,

	rnative = /^[^{]+\{\s*\[native \w/,

	// Easily-parseable/retrievable ID or TAG or CLASS selectors
	rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,

	rsibling = /[+~]/,

	// CSS escapes
	// http://www.w3.org/TR/CSS21/syndata.html#escaped-characters
	runescape = new RegExp( "\\\\([\\da-f]{1,6}" + whitespace + "?|(" + whitespace + ")|.)", "ig" ),
	funescape = function( _, escaped, escapedWhitespace ) {
		var high = "0x" + escaped - 0x10000;
		// NaN means non-codepoint
		// Support: Firefox<24
		// Workaround erroneous numeric interpretation of +"0x"
		return high !== high || escapedWhitespace ?
			escaped :
			high < 0 ?
				// BMP codepoint
				String.fromCharCode( high + 0x10000 ) :
				// Supplemental Plane codepoint (surrogate pair)
				String.fromCharCode( high >> 10 | 0xD800, high & 0x3FF | 0xDC00 );
	},

	// CSS string/identifier serialization
	// https://drafts.csswg.org/cssom/#common-serializing-idioms
	rcssescape = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,
	fcssescape = function( ch, asCodePoint ) {
		if ( asCodePoint ) {

			// U+0000 NULL becomes U+FFFD REPLACEMENT CHARACTER
			if ( ch === "\0" ) {
				return "\uFFFD";
			}

			// Control characters and (dependent upon position) numbers get escaped as code points
			return ch.slice( 0, -1 ) + "\\" + ch.charCodeAt( ch.length - 1 ).toString( 16 ) + " ";
		}

		// Other potentially-special ASCII characters get backslash-escaped
		return "\\" + ch;
	},

	// Used for iframes
	// See setDocument()
	// Removing the function wrapper causes a "Permission Denied"
	// error in IE
	unloadHandler = function() {
		setDocument();
	},

	disabledAncestor = addCombinator(
		function( elem ) {
			return elem.disabled === true && ("form" in elem || "label" in elem);
		},
		{ dir: "parentNode", next: "legend" }
	);

// Optimize for push.apply( _, NodeList )
try {
	push.apply(
		(arr = slice.call( preferredDoc.childNodes )),
		preferredDoc.childNodes
	);
	// Support: Android<4.0
	// Detect silently failing push.apply
	arr[ preferredDoc.childNodes.length ].nodeType;
} catch ( e ) {
	push = { apply: arr.length ?

		// Leverage slice if possible
		function( target, els ) {
			push_native.apply( target, slice.call(els) );
		} :

		// Support: IE<9
		// Otherwise append directly
		function( target, els ) {
			var j = target.length,
				i = 0;
			// Can't trust NodeList.length
			while ( (target[j++] = els[i++]) ) {}
			target.length = j - 1;
		}
	};
}

function Sizzle( selector, context, results, seed ) {
	var m, i, elem, nid, match, groups, newSelector,
		newContext = context && context.ownerDocument,

		// nodeType defaults to 9, since context defaults to document
		nodeType = context ? context.nodeType : 9;

	results = results || [];

	// Return early from calls with invalid selector or context
	if ( typeof selector !== "string" || !selector ||
		nodeType !== 1 && nodeType !== 9 && nodeType !== 11 ) {

		return results;
	}

	// Try to shortcut find operations (as opposed to filters) in HTML documents
	if ( !seed ) {

		if ( ( context ? context.ownerDocument || context : preferredDoc ) !== document ) {
			setDocument( context );
		}
		context = context || document;

		if ( documentIsHTML ) {

			// If the selector is sufficiently simple, try using a "get*By*" DOM method
			// (excepting DocumentFragment context, where the methods don't exist)
			if ( nodeType !== 11 && (match = rquickExpr.exec( selector )) ) {

				// ID selector
				if ( (m = match[1]) ) {

					// Document context
					if ( nodeType === 9 ) {
						if ( (elem = context.getElementById( m )) ) {

							// Support: IE, Opera, Webkit
							// TODO: identify versions
							// getElementById can match elements by name instead of ID
							if ( elem.id === m ) {
								results.push( elem );
								return results;
							}
						} else {
							return results;
						}

					// Element context
					} else {

						// Support: IE, Opera, Webkit
						// TODO: identify versions
						// getElementById can match elements by name instead of ID
						if ( newContext && (elem = newContext.getElementById( m )) &&
							contains( context, elem ) &&
							elem.id === m ) {

							results.push( elem );
							return results;
						}
					}

				// Type selector
				} else if ( match[2] ) {
					push.apply( results, context.getElementsByTagName( selector ) );
					return results;

				// Class selector
				} else if ( (m = match[3]) && support.getElementsByClassName &&
					context.getElementsByClassName ) {

					push.apply( results, context.getElementsByClassName( m ) );
					return results;
				}
			}

			// Take advantage of querySelectorAll
			if ( support.qsa &&
				!compilerCache[ selector + " " ] &&
				(!rbuggyQSA || !rbuggyQSA.test( selector )) ) {

				if ( nodeType !== 1 ) {
					newContext = context;
					newSelector = selector;

				// qSA looks outside Element context, which is not what we want
				// Thanks to Andrew Dupont for this workaround technique
				// Support: IE <=8
				// Exclude object elements
				} else if ( context.nodeName.toLowerCase() !== "object" ) {

					// Capture the context ID, setting it first if necessary
					if ( (nid = context.getAttribute( "id" )) ) {
						nid = nid.replace( rcssescape, fcssescape );
					} else {
						context.setAttribute( "id", (nid = expando) );
					}

					// Prefix every selector in the list
					groups = tokenize( selector );
					i = groups.length;
					while ( i-- ) {
						groups[i] = "#" + nid + " " + toSelector( groups[i] );
					}
					newSelector = groups.join( "," );

					// Expand context for sibling selectors
					newContext = rsibling.test( selector ) && testContext( context.parentNode ) ||
						context;
				}

				if ( newSelector ) {
					try {
						push.apply( results,
							newContext.querySelectorAll( newSelector )
						);
						return results;
					} catch ( qsaError ) {
					} finally {
						if ( nid === expando ) {
							context.removeAttribute( "id" );
						}
					}
				}
			}
		}
	}

	// All others
	return select( selector.replace( rtrim, "$1" ), context, results, seed );
}

/**
 * Create key-value caches of limited size
 * @returns {function(string, object)} Returns the Object data after storing it on itself with
 *	property name the (space-suffixed) string and (if the cache is larger than Expr.cacheLength)
 *	deleting the oldest entry
 */
function createCache() {
	var keys = [];

	function cache( key, value ) {
		// Use (key + " ") to avoid collision with native prototype properties (see Issue #157)
		if ( keys.push( key + " " ) > Expr.cacheLength ) {
			// Only keep the most recent entries
			delete cache[ keys.shift() ];
		}
		return (cache[ key + " " ] = value);
	}
	return cache;
}

/**
 * Mark a function for special use by Sizzle
 * @param {Function} fn The function to mark
 */
function markFunction( fn ) {
	fn[ expando ] = true;
	return fn;
}

/**
 * Support testing using an element
 * @param {Function} fn Passed the created element and returns a boolean result
 */
function assert( fn ) {
	var el = document.createElement("fieldset");

	try {
		return !!fn( el );
	} catch (e) {
		return false;
	} finally {
		// Remove from its parent by default
		if ( el.parentNode ) {
			el.parentNode.removeChild( el );
		}
		// release memory in IE
		el = null;
	}
}

/**
 * Adds the same handler for all of the specified attrs
 * @param {String} attrs Pipe-separated list of attributes
 * @param {Function} handler The method that will be applied
 */
function addHandle( attrs, handler ) {
	var arr = attrs.split("|"),
		i = arr.length;

	while ( i-- ) {
		Expr.attrHandle[ arr[i] ] = handler;
	}
}

/**
 * Checks document order of two siblings
 * @param {Element} a
 * @param {Element} b
 * @returns {Number} Returns less than 0 if a precedes b, greater than 0 if a follows b
 */
function siblingCheck( a, b ) {
	var cur = b && a,
		diff = cur && a.nodeType === 1 && b.nodeType === 1 &&
			a.sourceIndex - b.sourceIndex;

	// Use IE sourceIndex if available on both nodes
	if ( diff ) {
		return diff;
	}

	// Check if b follows a
	if ( cur ) {
		while ( (cur = cur.nextSibling) ) {
			if ( cur === b ) {
				return -1;
			}
		}
	}

	return a ? 1 : -1;
}

/**
 * Returns a function to use in pseudos for input types
 * @param {String} type
 */
function createInputPseudo( type ) {
	return function( elem ) {
		var name = elem.nodeName.toLowerCase();
		return name === "input" && elem.type === type;
	};
}

/**
 * Returns a function to use in pseudos for buttons
 * @param {String} type
 */
function createButtonPseudo( type ) {
	return function( elem ) {
		var name = elem.nodeName.toLowerCase();
		return (name === "input" || name === "button") && elem.type === type;
	};
}

/**
 * Returns a function to use in pseudos for :enabled/:disabled
 * @param {Boolean} disabled true for :disabled; false for :enabled
 */
function createDisabledPseudo( disabled ) {

	// Known :disabled false positives: fieldset[disabled] > legend:nth-of-type(n+2) :can-disable
	return function( elem ) {

		// Only certain elements can match :enabled or :disabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-enabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-disabled
		if ( "form" in elem ) {

			// Check for inherited disabledness on relevant non-disabled elements:
			// * listed form-associated elements in a disabled fieldset
			//   https://html.spec.whatwg.org/multipage/forms.html#category-listed
			//   https://html.spec.whatwg.org/multipage/forms.html#concept-fe-disabled
			// * option elements in a disabled optgroup
			//   https://html.spec.whatwg.org/multipage/forms.html#concept-option-disabled
			// All such elements have a "form" property.
			if ( elem.parentNode && elem.disabled === false ) {

				// Option elements defer to a parent optgroup if present
				if ( "label" in elem ) {
					if ( "label" in elem.parentNode ) {
						return elem.parentNode.disabled === disabled;
					} else {
						return elem.disabled === disabled;
					}
				}

				// Support: IE 6 - 11
				// Use the isDisabled shortcut property to check for disabled fieldset ancestors
				return elem.isDisabled === disabled ||

					// Where there is no isDisabled, check manually
					/* jshint -W018 */
					elem.isDisabled !== !disabled &&
						disabledAncestor( elem ) === disabled;
			}

			return elem.disabled === disabled;

		// Try to winnow out elements that can't be disabled before trusting the disabled property.
		// Some victims get caught in our net (label, legend, menu, track), but it shouldn't
		// even exist on them, let alone have a boolean value.
		} else if ( "label" in elem ) {
			return elem.disabled === disabled;
		}

		// Remaining elements are neither :enabled nor :disabled
		return false;
	};
}

/**
 * Returns a function to use in pseudos for positionals
 * @param {Function} fn
 */
function createPositionalPseudo( fn ) {
	return markFunction(function( argument ) {
		argument = +argument;
		return markFunction(function( seed, matches ) {
			var j,
				matchIndexes = fn( [], seed.length, argument ),
				i = matchIndexes.length;

			// Match elements found at the specified indexes
			while ( i-- ) {
				if ( seed[ (j = matchIndexes[i]) ] ) {
					seed[j] = !(matches[j] = seed[j]);
				}
			}
		});
	});
}

/**
 * Checks a node for validity as a Sizzle context
 * @param {Element|Object=} context
 * @returns {Element|Object|Boolean} The input node if acceptable, otherwise a falsy value
 */
function testContext( context ) {
	return context && typeof context.getElementsByTagName !== "undefined" && context;
}

// Expose support vars for convenience
support = Sizzle.support = {};

/**
 * Detects XML nodes
 * @param {Element|Object} elem An element or a document
 * @returns {Boolean} True iff elem is a non-HTML XML node
 */
isXML = Sizzle.isXML = function( elem ) {
	// documentElement is verified for cases where it doesn't yet exist
	// (such as loading iframes in IE - #4833)
	var documentElement = elem && (elem.ownerDocument || elem).documentElement;
	return documentElement ? documentElement.nodeName !== "HTML" : false;
};

/**
 * Sets document-related variables once based on the current document
 * @param {Element|Object} [doc] An element or document object to use to set the document
 * @returns {Object} Returns the current document
 */
setDocument = Sizzle.setDocument = function( node ) {
	var hasCompare, subWindow,
		doc = node ? node.ownerDocument || node : preferredDoc;

	// Return early if doc is invalid or already selected
	if ( doc === document || doc.nodeType !== 9 || !doc.documentElement ) {
		return document;
	}

	// Update global variables
	document = doc;
	docElem = document.documentElement;
	documentIsHTML = !isXML( document );

	// Support: IE 9-11, Edge
	// Accessing iframe documents after unload throws "permission denied" errors (jQuery #13936)
	if ( preferredDoc !== document &&
		(subWindow = document.defaultView) && subWindow.top !== subWindow ) {

		// Support: IE 11, Edge
		if ( subWindow.addEventListener ) {
			subWindow.addEventListener( "unload", unloadHandler, false );

		// Support: IE 9 - 10 only
		} else if ( subWindow.attachEvent ) {
			subWindow.attachEvent( "onunload", unloadHandler );
		}
	}

	/* Attributes
	---------------------------------------------------------------------- */

	// Support: IE<8
	// Verify that getAttribute really returns attributes and not properties
	// (excepting IE8 booleans)
	support.attributes = assert(function( el ) {
		el.className = "i";
		return !el.getAttribute("className");
	});

	/* getElement(s)By*
	---------------------------------------------------------------------- */

	// Check if getElementsByTagName("*") returns only elements
	support.getElementsByTagName = assert(function( el ) {
		el.appendChild( document.createComment("") );
		return !el.getElementsByTagName("*").length;
	});

	// Support: IE<9
	support.getElementsByClassName = rnative.test( document.getElementsByClassName );

	// Support: IE<10
	// Check if getElementById returns elements by name
	// The broken getElementById methods don't pick up programmatically-set names,
	// so use a roundabout getElementsByName test
	support.getById = assert(function( el ) {
		docElem.appendChild( el ).id = expando;
		return !document.getElementsByName || !document.getElementsByName( expando ).length;
	});

	// ID filter and find
	if ( support.getById ) {
		Expr.filter["ID"] = function( id ) {
			var attrId = id.replace( runescape, funescape );
			return function( elem ) {
				return elem.getAttribute("id") === attrId;
			};
		};
		Expr.find["ID"] = function( id, context ) {
			if ( typeof context.getElementById !== "undefined" && documentIsHTML ) {
				var elem = context.getElementById( id );
				return elem ? [ elem ] : [];
			}
		};
	} else {
		Expr.filter["ID"] =  function( id ) {
			var attrId = id.replace( runescape, funescape );
			return function( elem ) {
				var node = typeof elem.getAttributeNode !== "undefined" &&
					elem.getAttributeNode("id");
				return node && node.value === attrId;
			};
		};

		// Support: IE 6 - 7 only
		// getElementById is not reliable as a find shortcut
		Expr.find["ID"] = function( id, context ) {
			if ( typeof context.getElementById !== "undefined" && documentIsHTML ) {
				var node, i, elems,
					elem = context.getElementById( id );

				if ( elem ) {

					// Verify the id attribute
					node = elem.getAttributeNode("id");
					if ( node && node.value === id ) {
						return [ elem ];
					}

					// Fall back on getElementsByName
					elems = context.getElementsByName( id );
					i = 0;
					while ( (elem = elems[i++]) ) {
						node = elem.getAttributeNode("id");
						if ( node && node.value === id ) {
							return [ elem ];
						}
					}
				}

				return [];
			}
		};
	}

	// Tag
	Expr.find["TAG"] = support.getElementsByTagName ?
		function( tag, context ) {
			if ( typeof context.getElementsByTagName !== "undefined" ) {
				return context.getElementsByTagName( tag );

			// DocumentFragment nodes don't have gEBTN
			} else if ( support.qsa ) {
				return context.querySelectorAll( tag );
			}
		} :

		function( tag, context ) {
			var elem,
				tmp = [],
				i = 0,
				// By happy coincidence, a (broken) gEBTN appears on DocumentFragment nodes too
				results = context.getElementsByTagName( tag );

			// Filter out possible comments
			if ( tag === "*" ) {
				while ( (elem = results[i++]) ) {
					if ( elem.nodeType === 1 ) {
						tmp.push( elem );
					}
				}

				return tmp;
			}
			return results;
		};

	// Class
	Expr.find["CLASS"] = support.getElementsByClassName && function( className, context ) {
		if ( typeof context.getElementsByClassName !== "undefined" && documentIsHTML ) {
			return context.getElementsByClassName( className );
		}
	};

	/* QSA/matchesSelector
	---------------------------------------------------------------------- */

	// QSA and matchesSelector support

	// matchesSelector(:active) reports false when true (IE9/Opera 11.5)
	rbuggyMatches = [];

	// qSa(:focus) reports false when true (Chrome 21)
	// We allow this because of a bug in IE8/9 that throws an error
	// whenever `document.activeElement` is accessed on an iframe
	// So, we allow :focus to pass through QSA all the time to avoid the IE error
	// See https://bugs.jquery.com/ticket/13378
	rbuggyQSA = [];

	if ( (support.qsa = rnative.test( document.querySelectorAll )) ) {
		// Build QSA regex
		// Regex strategy adopted from Diego Perini
		assert(function( el ) {
			// Select is set to empty string on purpose
			// This is to test IE's treatment of not explicitly
			// setting a boolean content attribute,
			// since its presence should be enough
			// https://bugs.jquery.com/ticket/12359
			docElem.appendChild( el ).innerHTML = "<a id='" + expando + "'></a>" +
				"<select id='" + expando + "-\r\\' msallowcapture=''>" +
				"<option selected=''></option></select>";

			// Support: IE8, Opera 11-12.16
			// Nothing should be selected when empty strings follow ^= or $= or *=
			// The test attribute must be unknown in Opera but "safe" for WinRT
			// https://msdn.microsoft.com/en-us/library/ie/hh465388.aspx#attribute_section
			if ( el.querySelectorAll("[msallowcapture^='']").length ) {
				rbuggyQSA.push( "[*^$]=" + whitespace + "*(?:''|\"\")" );
			}

			// Support: IE8
			// Boolean attributes and "value" are not treated correctly
			if ( !el.querySelectorAll("[selected]").length ) {
				rbuggyQSA.push( "\\[" + whitespace + "*(?:value|" + booleans + ")" );
			}

			// Support: Chrome<29, Android<4.4, Safari<7.0+, iOS<7.0+, PhantomJS<1.9.8+
			if ( !el.querySelectorAll( "[id~=" + expando + "-]" ).length ) {
				rbuggyQSA.push("~=");
			}

			// Webkit/Opera - :checked should return selected option elements
			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			// IE8 throws error here and will not see later tests
			if ( !el.querySelectorAll(":checked").length ) {
				rbuggyQSA.push(":checked");
			}

			// Support: Safari 8+, iOS 8+
			// https://bugs.webkit.org/show_bug.cgi?id=136851
			// In-page `selector#id sibling-combinator selector` fails
			if ( !el.querySelectorAll( "a#" + expando + "+*" ).length ) {
				rbuggyQSA.push(".#.+[+~]");
			}
		});

		assert(function( el ) {
			el.innerHTML = "<a href='' disabled='disabled'></a>" +
				"<select disabled='disabled'><option/></select>";

			// Support: Windows 8 Native Apps
			// The type and name attributes are restricted during .innerHTML assignment
			var input = document.createElement("input");
			input.setAttribute( "type", "hidden" );
			el.appendChild( input ).setAttribute( "name", "D" );

			// Support: IE8
			// Enforce case-sensitivity of name attribute
			if ( el.querySelectorAll("[name=d]").length ) {
				rbuggyQSA.push( "name" + whitespace + "*[*^$|!~]?=" );
			}

			// FF 3.5 - :enabled/:disabled and hidden elements (hidden elements are still enabled)
			// IE8 throws error here and will not see later tests
			if ( el.querySelectorAll(":enabled").length !== 2 ) {
				rbuggyQSA.push( ":enabled", ":disabled" );
			}

			// Support: IE9-11+
			// IE's :disabled selector does not pick up the children of disabled fieldsets
			docElem.appendChild( el ).disabled = true;
			if ( el.querySelectorAll(":disabled").length !== 2 ) {
				rbuggyQSA.push( ":enabled", ":disabled" );
			}

			// Opera 10-11 does not throw on post-comma invalid pseudos
			el.querySelectorAll("*,:x");
			rbuggyQSA.push(",.*:");
		});
	}

	if ( (support.matchesSelector = rnative.test( (matches = docElem.matches ||
		docElem.webkitMatchesSelector ||
		docElem.mozMatchesSelector ||
		docElem.oMatchesSelector ||
		docElem.msMatchesSelector) )) ) {

		assert(function( el ) {
			// Check to see if it's possible to do matchesSelector
			// on a disconnected node (IE 9)
			support.disconnectedMatch = matches.call( el, "*" );

			// This should fail with an exception
			// Gecko does not error, returns false instead
			matches.call( el, "[s!='']:x" );
			rbuggyMatches.push( "!=", pseudos );
		});
	}

	rbuggyQSA = rbuggyQSA.length && new RegExp( rbuggyQSA.join("|") );
	rbuggyMatches = rbuggyMatches.length && new RegExp( rbuggyMatches.join("|") );

	/* Contains
	---------------------------------------------------------------------- */
	hasCompare = rnative.test( docElem.compareDocumentPosition );

	// Element contains another
	// Purposefully self-exclusive
	// As in, an element does not contain itself
	contains = hasCompare || rnative.test( docElem.contains ) ?
		function( a, b ) {
			var adown = a.nodeType === 9 ? a.documentElement : a,
				bup = b && b.parentNode;
			return a === bup || !!( bup && bup.nodeType === 1 && (
				adown.contains ?
					adown.contains( bup ) :
					a.compareDocumentPosition && a.compareDocumentPosition( bup ) & 16
			));
		} :
		function( a, b ) {
			if ( b ) {
				while ( (b = b.parentNode) ) {
					if ( b === a ) {
						return true;
					}
				}
			}
			return false;
		};

	/* Sorting
	---------------------------------------------------------------------- */

	// Document order sorting
	sortOrder = hasCompare ?
	function( a, b ) {

		// Flag for duplicate removal
		if ( a === b ) {
			hasDuplicate = true;
			return 0;
		}

		// Sort on method existence if only one input has compareDocumentPosition
		var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
		if ( compare ) {
			return compare;
		}

		// Calculate position if both inputs belong to the same document
		compare = ( a.ownerDocument || a ) === ( b.ownerDocument || b ) ?
			a.compareDocumentPosition( b ) :

			// Otherwise we know they are disconnected
			1;

		// Disconnected nodes
		if ( compare & 1 ||
			(!support.sortDetached && b.compareDocumentPosition( a ) === compare) ) {

			// Choose the first element that is related to our preferred document
			if ( a === document || a.ownerDocument === preferredDoc && contains(preferredDoc, a) ) {
				return -1;
			}
			if ( b === document || b.ownerDocument === preferredDoc && contains(preferredDoc, b) ) {
				return 1;
			}

			// Maintain original order
			return sortInput ?
				( indexOf( sortInput, a ) - indexOf( sortInput, b ) ) :
				0;
		}

		return compare & 4 ? -1 : 1;
	} :
	function( a, b ) {
		// Exit early if the nodes are identical
		if ( a === b ) {
			hasDuplicate = true;
			return 0;
		}

		var cur,
			i = 0,
			aup = a.parentNode,
			bup = b.parentNode,
			ap = [ a ],
			bp = [ b ];

		// Parentless nodes are either documents or disconnected
		if ( !aup || !bup ) {
			return a === document ? -1 :
				b === document ? 1 :
				aup ? -1 :
				bup ? 1 :
				sortInput ?
				( indexOf( sortInput, a ) - indexOf( sortInput, b ) ) :
				0;

		// If the nodes are siblings, we can do a quick check
		} else if ( aup === bup ) {
			return siblingCheck( a, b );
		}

		// Otherwise we need full lists of their ancestors for comparison
		cur = a;
		while ( (cur = cur.parentNode) ) {
			ap.unshift( cur );
		}
		cur = b;
		while ( (cur = cur.parentNode) ) {
			bp.unshift( cur );
		}

		// Walk down the tree looking for a discrepancy
		while ( ap[i] === bp[i] ) {
			i++;
		}

		return i ?
			// Do a sibling check if the nodes have a common ancestor
			siblingCheck( ap[i], bp[i] ) :

			// Otherwise nodes in our document sort first
			ap[i] === preferredDoc ? -1 :
			bp[i] === preferredDoc ? 1 :
			0;
	};

	return document;
};

Sizzle.matches = function( expr, elements ) {
	return Sizzle( expr, null, null, elements );
};

Sizzle.matchesSelector = function( elem, expr ) {
	// Set document vars if needed
	if ( ( elem.ownerDocument || elem ) !== document ) {
		setDocument( elem );
	}

	// Make sure that attribute selectors are quoted
	expr = expr.replace( rattributeQuotes, "='$1']" );

	if ( support.matchesSelector && documentIsHTML &&
		!compilerCache[ expr + " " ] &&
		( !rbuggyMatches || !rbuggyMatches.test( expr ) ) &&
		( !rbuggyQSA     || !rbuggyQSA.test( expr ) ) ) {

		try {
			var ret = matches.call( elem, expr );

			// IE 9's matchesSelector returns false on disconnected nodes
			if ( ret || support.disconnectedMatch ||
					// As well, disconnected nodes are said to be in a document
					// fragment in IE 9
					elem.document && elem.document.nodeType !== 11 ) {
				return ret;
			}
		} catch (e) {}
	}

	return Sizzle( expr, document, null, [ elem ] ).length > 0;
};

Sizzle.contains = function( context, elem ) {
	// Set document vars if needed
	if ( ( context.ownerDocument || context ) !== document ) {
		setDocument( context );
	}
	return contains( context, elem );
};

Sizzle.attr = function( elem, name ) {
	// Set document vars if needed
	if ( ( elem.ownerDocument || elem ) !== document ) {
		setDocument( elem );
	}

	var fn = Expr.attrHandle[ name.toLowerCase() ],
		// Don't get fooled by Object.prototype properties (jQuery #13807)
		val = fn && hasOwn.call( Expr.attrHandle, name.toLowerCase() ) ?
			fn( elem, name, !documentIsHTML ) :
			undefined;

	return val !== undefined ?
		val :
		support.attributes || !documentIsHTML ?
			elem.getAttribute( name ) :
			(val = elem.getAttributeNode(name)) && val.specified ?
				val.value :
				null;
};

Sizzle.escape = function( sel ) {
	return (sel + "").replace( rcssescape, fcssescape );
};

Sizzle.error = function( msg ) {
	throw new Error( "Syntax error, unrecognized expression: " + msg );
};

/**
 * Document sorting and removing duplicates
 * @param {ArrayLike} results
 */
Sizzle.uniqueSort = function( results ) {
	var elem,
		duplicates = [],
		j = 0,
		i = 0;

	// Unless we *know* we can detect duplicates, assume their presence
	hasDuplicate = !support.detectDuplicates;
	sortInput = !support.sortStable && results.slice( 0 );
	results.sort( sortOrder );

	if ( hasDuplicate ) {
		while ( (elem = results[i++]) ) {
			if ( elem === results[ i ] ) {
				j = duplicates.push( i );
			}
		}
		while ( j-- ) {
			results.splice( duplicates[ j ], 1 );
		}
	}

	// Clear input after sorting to release objects
	// See https://github.com/jquery/sizzle/pull/225
	sortInput = null;

	return results;
};

/**
 * Utility function for retrieving the text value of an array of DOM nodes
 * @param {Array|Element} elem
 */
getText = Sizzle.getText = function( elem ) {
	var node,
		ret = "",
		i = 0,
		nodeType = elem.nodeType;

	if ( !nodeType ) {
		// If no nodeType, this is expected to be an array
		while ( (node = elem[i++]) ) {
			// Do not traverse comment nodes
			ret += getText( node );
		}
	} else if ( nodeType === 1 || nodeType === 9 || nodeType === 11 ) {
		// Use textContent for elements
		// innerText usage removed for consistency of new lines (jQuery #11153)
		if ( typeof elem.textContent === "string" ) {
			return elem.textContent;
		} else {
			// Traverse its children
			for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
				ret += getText( elem );
			}
		}
	} else if ( nodeType === 3 || nodeType === 4 ) {
		return elem.nodeValue;
	}
	// Do not include comment or processing instruction nodes

	return ret;
};

Expr = Sizzle.selectors = {

	// Can be adjusted by the user
	cacheLength: 50,

	createPseudo: markFunction,

	match: matchExpr,

	attrHandle: {},

	find: {},

	relative: {
		">": { dir: "parentNode", first: true },
		" ": { dir: "parentNode" },
		"+": { dir: "previousSibling", first: true },
		"~": { dir: "previousSibling" }
	},

	preFilter: {
		"ATTR": function( match ) {
			match[1] = match[1].replace( runescape, funescape );

			// Move the given value to match[3] whether quoted or unquoted
			match[3] = ( match[3] || match[4] || match[5] || "" ).replace( runescape, funescape );

			if ( match[2] === "~=" ) {
				match[3] = " " + match[3] + " ";
			}

			return match.slice( 0, 4 );
		},

		"CHILD": function( match ) {
			/* matches from matchExpr["CHILD"]
				1 type (only|nth|...)
				2 what (child|of-type)
				3 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
				4 xn-component of xn+y argument ([+-]?\d*n|)
				5 sign of xn-component
				6 x of xn-component
				7 sign of y-component
				8 y of y-component
			*/
			match[1] = match[1].toLowerCase();

			if ( match[1].slice( 0, 3 ) === "nth" ) {
				// nth-* requires argument
				if ( !match[3] ) {
					Sizzle.error( match[0] );
				}

				// numeric x and y parameters for Expr.filter.CHILD
				// remember that false/true cast respectively to 0/1
				match[4] = +( match[4] ? match[5] + (match[6] || 1) : 2 * ( match[3] === "even" || match[3] === "odd" ) );
				match[5] = +( ( match[7] + match[8] ) || match[3] === "odd" );

			// other types prohibit arguments
			} else if ( match[3] ) {
				Sizzle.error( match[0] );
			}

			return match;
		},

		"PSEUDO": function( match ) {
			var excess,
				unquoted = !match[6] && match[2];

			if ( matchExpr["CHILD"].test( match[0] ) ) {
				return null;
			}

			// Accept quoted arguments as-is
			if ( match[3] ) {
				match[2] = match[4] || match[5] || "";

			// Strip excess characters from unquoted arguments
			} else if ( unquoted && rpseudo.test( unquoted ) &&
				// Get excess from tokenize (recursively)
				(excess = tokenize( unquoted, true )) &&
				// advance to the next closing parenthesis
				(excess = unquoted.indexOf( ")", unquoted.length - excess ) - unquoted.length) ) {

				// excess is a negative index
				match[0] = match[0].slice( 0, excess );
				match[2] = unquoted.slice( 0, excess );
			}

			// Return only captures needed by the pseudo filter method (type and argument)
			return match.slice( 0, 3 );
		}
	},

	filter: {

		"TAG": function( nodeNameSelector ) {
			var nodeName = nodeNameSelector.replace( runescape, funescape ).toLowerCase();
			return nodeNameSelector === "*" ?
				function() { return true; } :
				function( elem ) {
					return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
				};
		},

		"CLASS": function( className ) {
			var pattern = classCache[ className + " " ];

			return pattern ||
				(pattern = new RegExp( "(^|" + whitespace + ")" + className + "(" + whitespace + "|$)" )) &&
				classCache( className, function( elem ) {
					return pattern.test( typeof elem.className === "string" && elem.className || typeof elem.getAttribute !== "undefined" && elem.getAttribute("class") || "" );
				});
		},

		"ATTR": function( name, operator, check ) {
			return function( elem ) {
				var result = Sizzle.attr( elem, name );

				if ( result == null ) {
					return operator === "!=";
				}
				if ( !operator ) {
					return true;
				}

				result += "";

				return operator === "=" ? result === check :
					operator === "!=" ? result !== check :
					operator === "^=" ? check && result.indexOf( check ) === 0 :
					operator === "*=" ? check && result.indexOf( check ) > -1 :
					operator === "$=" ? check && result.slice( -check.length ) === check :
					operator === "~=" ? ( " " + result.replace( rwhitespace, " " ) + " " ).indexOf( check ) > -1 :
					operator === "|=" ? result === check || result.slice( 0, check.length + 1 ) === check + "-" :
					false;
			};
		},

		"CHILD": function( type, what, argument, first, last ) {
			var simple = type.slice( 0, 3 ) !== "nth",
				forward = type.slice( -4 ) !== "last",
				ofType = what === "of-type";

			return first === 1 && last === 0 ?

				// Shortcut for :nth-*(n)
				function( elem ) {
					return !!elem.parentNode;
				} :

				function( elem, context, xml ) {
					var cache, uniqueCache, outerCache, node, nodeIndex, start,
						dir = simple !== forward ? "nextSibling" : "previousSibling",
						parent = elem.parentNode,
						name = ofType && elem.nodeName.toLowerCase(),
						useCache = !xml && !ofType,
						diff = false;

					if ( parent ) {

						// :(first|last|only)-(child|of-type)
						if ( simple ) {
							while ( dir ) {
								node = elem;
								while ( (node = node[ dir ]) ) {
									if ( ofType ?
										node.nodeName.toLowerCase() === name :
										node.nodeType === 1 ) {

										return false;
									}
								}
								// Reverse direction for :only-* (if we haven't yet done so)
								start = dir = type === "only" && !start && "nextSibling";
							}
							return true;
						}

						start = [ forward ? parent.firstChild : parent.lastChild ];

						// non-xml :nth-child(...) stores cache data on `parent`
						if ( forward && useCache ) {

							// Seek `elem` from a previously-cached index

							// ...in a gzip-friendly way
							node = parent;
							outerCache = node[ expando ] || (node[ expando ] = {});

							// Support: IE <9 only
							// Defend against cloned attroperties (jQuery gh-1709)
							uniqueCache = outerCache[ node.uniqueID ] ||
								(outerCache[ node.uniqueID ] = {});

							cache = uniqueCache[ type ] || [];
							nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
							diff = nodeIndex && cache[ 2 ];
							node = nodeIndex && parent.childNodes[ nodeIndex ];

							while ( (node = ++nodeIndex && node && node[ dir ] ||

								// Fallback to seeking `elem` from the start
								(diff = nodeIndex = 0) || start.pop()) ) {

								// When found, cache indexes on `parent` and break
								if ( node.nodeType === 1 && ++diff && node === elem ) {
									uniqueCache[ type ] = [ dirruns, nodeIndex, diff ];
									break;
								}
							}

						} else {
							// Use previously-cached element index if available
							if ( useCache ) {
								// ...in a gzip-friendly way
								node = elem;
								outerCache = node[ expando ] || (node[ expando ] = {});

								// Support: IE <9 only
								// Defend against cloned attroperties (jQuery gh-1709)
								uniqueCache = outerCache[ node.uniqueID ] ||
									(outerCache[ node.uniqueID ] = {});

								cache = uniqueCache[ type ] || [];
								nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
								diff = nodeIndex;
							}

							// xml :nth-child(...)
							// or :nth-last-child(...) or :nth(-last)?-of-type(...)
							if ( diff === false ) {
								// Use the same loop as above to seek `elem` from the start
								while ( (node = ++nodeIndex && node && node[ dir ] ||
									(diff = nodeIndex = 0) || start.pop()) ) {

									if ( ( ofType ?
										node.nodeName.toLowerCase() === name :
										node.nodeType === 1 ) &&
										++diff ) {

										// Cache the index of each encountered element
										if ( useCache ) {
											outerCache = node[ expando ] || (node[ expando ] = {});

											// Support: IE <9 only
											// Defend against cloned attroperties (jQuery gh-1709)
											uniqueCache = outerCache[ node.uniqueID ] ||
												(outerCache[ node.uniqueID ] = {});

											uniqueCache[ type ] = [ dirruns, diff ];
										}

										if ( node === elem ) {
											break;
										}
									}
								}
							}
						}

						// Incorporate the offset, then check against cycle size
						diff -= last;
						return diff === first || ( diff % first === 0 && diff / first >= 0 );
					}
				};
		},

		"PSEUDO": function( pseudo, argument ) {
			// pseudo-class names are case-insensitive
			// http://www.w3.org/TR/selectors/#pseudo-classes
			// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
			// Remember that setFilters inherits from pseudos
			var args,
				fn = Expr.pseudos[ pseudo ] || Expr.setFilters[ pseudo.toLowerCase() ] ||
					Sizzle.error( "unsupported pseudo: " + pseudo );

			// The user may use createPseudo to indicate that
			// arguments are needed to create the filter function
			// just as Sizzle does
			if ( fn[ expando ] ) {
				return fn( argument );
			}

			// But maintain support for old signatures
			if ( fn.length > 1 ) {
				args = [ pseudo, pseudo, "", argument ];
				return Expr.setFilters.hasOwnProperty( pseudo.toLowerCase() ) ?
					markFunction(function( seed, matches ) {
						var idx,
							matched = fn( seed, argument ),
							i = matched.length;
						while ( i-- ) {
							idx = indexOf( seed, matched[i] );
							seed[ idx ] = !( matches[ idx ] = matched[i] );
						}
					}) :
					function( elem ) {
						return fn( elem, 0, args );
					};
			}

			return fn;
		}
	},

	pseudos: {
		// Potentially complex pseudos
		"not": markFunction(function( selector ) {
			// Trim the selector passed to compile
			// to avoid treating leading and trailing
			// spaces as combinators
			var input = [],
				results = [],
				matcher = compile( selector.replace( rtrim, "$1" ) );

			return matcher[ expando ] ?
				markFunction(function( seed, matches, context, xml ) {
					var elem,
						unmatched = matcher( seed, null, xml, [] ),
						i = seed.length;

					// Match elements unmatched by `matcher`
					while ( i-- ) {
						if ( (elem = unmatched[i]) ) {
							seed[i] = !(matches[i] = elem);
						}
					}
				}) :
				function( elem, context, xml ) {
					input[0] = elem;
					matcher( input, null, xml, results );
					// Don't keep the element (issue #299)
					input[0] = null;
					return !results.pop();
				};
		}),

		"has": markFunction(function( selector ) {
			return function( elem ) {
				return Sizzle( selector, elem ).length > 0;
			};
		}),

		"contains": markFunction(function( text ) {
			text = text.replace( runescape, funescape );
			return function( elem ) {
				return ( elem.textContent || elem.innerText || getText( elem ) ).indexOf( text ) > -1;
			};
		}),

		// "Whether an element is represented by a :lang() selector
		// is based solely on the element's language value
		// being equal to the identifier C,
		// or beginning with the identifier C immediately followed by "-".
		// The matching of C against the element's language value is performed case-insensitively.
		// The identifier C does not have to be a valid language name."
		// http://www.w3.org/TR/selectors/#lang-pseudo
		"lang": markFunction( function( lang ) {
			// lang value must be a valid identifier
			if ( !ridentifier.test(lang || "") ) {
				Sizzle.error( "unsupported lang: " + lang );
			}
			lang = lang.replace( runescape, funescape ).toLowerCase();
			return function( elem ) {
				var elemLang;
				do {
					if ( (elemLang = documentIsHTML ?
						elem.lang :
						elem.getAttribute("xml:lang") || elem.getAttribute("lang")) ) {

						elemLang = elemLang.toLowerCase();
						return elemLang === lang || elemLang.indexOf( lang + "-" ) === 0;
					}
				} while ( (elem = elem.parentNode) && elem.nodeType === 1 );
				return false;
			};
		}),

		// Miscellaneous
		"target": function( elem ) {
			var hash = window.location && window.location.hash;
			return hash && hash.slice( 1 ) === elem.id;
		},

		"root": function( elem ) {
			return elem === docElem;
		},

		"focus": function( elem ) {
			return elem === document.activeElement && (!document.hasFocus || document.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
		},

		// Boolean properties
		"enabled": createDisabledPseudo( false ),
		"disabled": createDisabledPseudo( true ),

		"checked": function( elem ) {
			// In CSS3, :checked should return both checked and selected elements
			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			var nodeName = elem.nodeName.toLowerCase();
			return (nodeName === "input" && !!elem.checked) || (nodeName === "option" && !!elem.selected);
		},

		"selected": function( elem ) {
			// Accessing this property makes selected-by-default
			// options in Safari work properly
			if ( elem.parentNode ) {
				elem.parentNode.selectedIndex;
			}

			return elem.selected === true;
		},

		// Contents
		"empty": function( elem ) {
			// http://www.w3.org/TR/selectors/#empty-pseudo
			// :empty is negated by element (1) or content nodes (text: 3; cdata: 4; entity ref: 5),
			//   but not by others (comment: 8; processing instruction: 7; etc.)
			// nodeType < 6 works because attributes (2) do not appear as children
			for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
				if ( elem.nodeType < 6 ) {
					return false;
				}
			}
			return true;
		},

		"parent": function( elem ) {
			return !Expr.pseudos["empty"]( elem );
		},

		// Element/input types
		"header": function( elem ) {
			return rheader.test( elem.nodeName );
		},

		"input": function( elem ) {
			return rinputs.test( elem.nodeName );
		},

		"button": function( elem ) {
			var name = elem.nodeName.toLowerCase();
			return name === "input" && elem.type === "button" || name === "button";
		},

		"text": function( elem ) {
			var attr;
			return elem.nodeName.toLowerCase() === "input" &&
				elem.type === "text" &&

				// Support: IE<8
				// New HTML5 attribute values (e.g., "search") appear with elem.type === "text"
				( (attr = elem.getAttribute("type")) == null || attr.toLowerCase() === "text" );
		},

		// Position-in-collection
		"first": createPositionalPseudo(function() {
			return [ 0 ];
		}),

		"last": createPositionalPseudo(function( matchIndexes, length ) {
			return [ length - 1 ];
		}),

		"eq": createPositionalPseudo(function( matchIndexes, length, argument ) {
			return [ argument < 0 ? argument + length : argument ];
		}),

		"even": createPositionalPseudo(function( matchIndexes, length ) {
			var i = 0;
			for ( ; i < length; i += 2 ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		}),

		"odd": createPositionalPseudo(function( matchIndexes, length ) {
			var i = 1;
			for ( ; i < length; i += 2 ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		}),

		"lt": createPositionalPseudo(function( matchIndexes, length, argument ) {
			var i = argument < 0 ? argument + length : argument;
			for ( ; --i >= 0; ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		}),

		"gt": createPositionalPseudo(function( matchIndexes, length, argument ) {
			var i = argument < 0 ? argument + length : argument;
			for ( ; ++i < length; ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		})
	}
};

Expr.pseudos["nth"] = Expr.pseudos["eq"];

// Add button/input type pseudos
for ( i in { radio: true, checkbox: true, file: true, password: true, image: true } ) {
	Expr.pseudos[ i ] = createInputPseudo( i );
}
for ( i in { submit: true, reset: true } ) {
	Expr.pseudos[ i ] = createButtonPseudo( i );
}

// Easy API for creating new setFilters
function setFilters() {}
setFilters.prototype = Expr.filters = Expr.pseudos;
Expr.setFilters = new setFilters();

tokenize = Sizzle.tokenize = function( selector, parseOnly ) {
	var matched, match, tokens, type,
		soFar, groups, preFilters,
		cached = tokenCache[ selector + " " ];

	if ( cached ) {
		return parseOnly ? 0 : cached.slice( 0 );
	}

	soFar = selector;
	groups = [];
	preFilters = Expr.preFilter;

	while ( soFar ) {

		// Comma and first run
		if ( !matched || (match = rcomma.exec( soFar )) ) {
			if ( match ) {
				// Don't consume trailing commas as valid
				soFar = soFar.slice( match[0].length ) || soFar;
			}
			groups.push( (tokens = []) );
		}

		matched = false;

		// Combinators
		if ( (match = rcombinators.exec( soFar )) ) {
			matched = match.shift();
			tokens.push({
				value: matched,
				// Cast descendant combinators to space
				type: match[0].replace( rtrim, " " )
			});
			soFar = soFar.slice( matched.length );
		}

		// Filters
		for ( type in Expr.filter ) {
			if ( (match = matchExpr[ type ].exec( soFar )) && (!preFilters[ type ] ||
				(match = preFilters[ type ]( match ))) ) {
				matched = match.shift();
				tokens.push({
					value: matched,
					type: type,
					matches: match
				});
				soFar = soFar.slice( matched.length );
			}
		}

		if ( !matched ) {
			break;
		}
	}

	// Return the length of the invalid excess
	// if we're just parsing
	// Otherwise, throw an error or return tokens
	return parseOnly ?
		soFar.length :
		soFar ?
			Sizzle.error( selector ) :
			// Cache the tokens
			tokenCache( selector, groups ).slice( 0 );
};

function toSelector( tokens ) {
	var i = 0,
		len = tokens.length,
		selector = "";
	for ( ; i < len; i++ ) {
		selector += tokens[i].value;
	}
	return selector;
}

function addCombinator( matcher, combinator, base ) {
	var dir = combinator.dir,
		skip = combinator.next,
		key = skip || dir,
		checkNonElements = base && key === "parentNode",
		doneName = done++;

	return combinator.first ?
		// Check against closest ancestor/preceding element
		function( elem, context, xml ) {
			while ( (elem = elem[ dir ]) ) {
				if ( elem.nodeType === 1 || checkNonElements ) {
					return matcher( elem, context, xml );
				}
			}
			return false;
		} :

		// Check against all ancestor/preceding elements
		function( elem, context, xml ) {
			var oldCache, uniqueCache, outerCache,
				newCache = [ dirruns, doneName ];

			// We can't set arbitrary data on XML nodes, so they don't benefit from combinator caching
			if ( xml ) {
				while ( (elem = elem[ dir ]) ) {
					if ( elem.nodeType === 1 || checkNonElements ) {
						if ( matcher( elem, context, xml ) ) {
							return true;
						}
					}
				}
			} else {
				while ( (elem = elem[ dir ]) ) {
					if ( elem.nodeType === 1 || checkNonElements ) {
						outerCache = elem[ expando ] || (elem[ expando ] = {});

						// Support: IE <9 only
						// Defend against cloned attroperties (jQuery gh-1709)
						uniqueCache = outerCache[ elem.uniqueID ] || (outerCache[ elem.uniqueID ] = {});

						if ( skip && skip === elem.nodeName.toLowerCase() ) {
							elem = elem[ dir ] || elem;
						} else if ( (oldCache = uniqueCache[ key ]) &&
							oldCache[ 0 ] === dirruns && oldCache[ 1 ] === doneName ) {

							// Assign to newCache so results back-propagate to previous elements
							return (newCache[ 2 ] = oldCache[ 2 ]);
						} else {
							// Reuse newcache so results back-propagate to previous elements
							uniqueCache[ key ] = newCache;

							// A match means we're done; a fail means we have to keep checking
							if ( (newCache[ 2 ] = matcher( elem, context, xml )) ) {
								return true;
							}
						}
					}
				}
			}
			return false;
		};
}

function elementMatcher( matchers ) {
	return matchers.length > 1 ?
		function( elem, context, xml ) {
			var i = matchers.length;
			while ( i-- ) {
				if ( !matchers[i]( elem, context, xml ) ) {
					return false;
				}
			}
			return true;
		} :
		matchers[0];
}

function multipleContexts( selector, contexts, results ) {
	var i = 0,
		len = contexts.length;
	for ( ; i < len; i++ ) {
		Sizzle( selector, contexts[i], results );
	}
	return results;
}

function condense( unmatched, map, filter, context, xml ) {
	var elem,
		newUnmatched = [],
		i = 0,
		len = unmatched.length,
		mapped = map != null;

	for ( ; i < len; i++ ) {
		if ( (elem = unmatched[i]) ) {
			if ( !filter || filter( elem, context, xml ) ) {
				newUnmatched.push( elem );
				if ( mapped ) {
					map.push( i );
				}
			}
		}
	}

	return newUnmatched;
}

function setMatcher( preFilter, selector, matcher, postFilter, postFinder, postSelector ) {
	if ( postFilter && !postFilter[ expando ] ) {
		postFilter = setMatcher( postFilter );
	}
	if ( postFinder && !postFinder[ expando ] ) {
		postFinder = setMatcher( postFinder, postSelector );
	}
	return markFunction(function( seed, results, context, xml ) {
		var temp, i, elem,
			preMap = [],
			postMap = [],
			preexisting = results.length,

			// Get initial elements from seed or context
			elems = seed || multipleContexts( selector || "*", context.nodeType ? [ context ] : context, [] ),

			// Prefilter to get matcher input, preserving a map for seed-results synchronization
			matcherIn = preFilter && ( seed || !selector ) ?
				condense( elems, preMap, preFilter, context, xml ) :
				elems,

			matcherOut = matcher ?
				// If we have a postFinder, or filtered seed, or non-seed postFilter or preexisting results,
				postFinder || ( seed ? preFilter : preexisting || postFilter ) ?

					// ...intermediate processing is necessary
					[] :

					// ...otherwise use results directly
					results :
				matcherIn;

		// Find primary matches
		if ( matcher ) {
			matcher( matcherIn, matcherOut, context, xml );
		}

		// Apply postFilter
		if ( postFilter ) {
			temp = condense( matcherOut, postMap );
			postFilter( temp, [], context, xml );

			// Un-match failing elements by moving them back to matcherIn
			i = temp.length;
			while ( i-- ) {
				if ( (elem = temp[i]) ) {
					matcherOut[ postMap[i] ] = !(matcherIn[ postMap[i] ] = elem);
				}
			}
		}

		if ( seed ) {
			if ( postFinder || preFilter ) {
				if ( postFinder ) {
					// Get the final matcherOut by condensing this intermediate into postFinder contexts
					temp = [];
					i = matcherOut.length;
					while ( i-- ) {
						if ( (elem = matcherOut[i]) ) {
							// Restore matcherIn since elem is not yet a final match
							temp.push( (matcherIn[i] = elem) );
						}
					}
					postFinder( null, (matcherOut = []), temp, xml );
				}

				// Move matched elements from seed to results to keep them synchronized
				i = matcherOut.length;
				while ( i-- ) {
					if ( (elem = matcherOut[i]) &&
						(temp = postFinder ? indexOf( seed, elem ) : preMap[i]) > -1 ) {

						seed[temp] = !(results[temp] = elem);
					}
				}
			}

		// Add elements to results, through postFinder if defined
		} else {
			matcherOut = condense(
				matcherOut === results ?
					matcherOut.splice( preexisting, matcherOut.length ) :
					matcherOut
			);
			if ( postFinder ) {
				postFinder( null, results, matcherOut, xml );
			} else {
				push.apply( results, matcherOut );
			}
		}
	});
}

function matcherFromTokens( tokens ) {
	var checkContext, matcher, j,
		len = tokens.length,
		leadingRelative = Expr.relative[ tokens[0].type ],
		implicitRelative = leadingRelative || Expr.relative[" "],
		i = leadingRelative ? 1 : 0,

		// The foundational matcher ensures that elements are reachable from top-level context(s)
		matchContext = addCombinator( function( elem ) {
			return elem === checkContext;
		}, implicitRelative, true ),
		matchAnyContext = addCombinator( function( elem ) {
			return indexOf( checkContext, elem ) > -1;
		}, implicitRelative, true ),
		matchers = [ function( elem, context, xml ) {
			var ret = ( !leadingRelative && ( xml || context !== outermostContext ) ) || (
				(checkContext = context).nodeType ?
					matchContext( elem, context, xml ) :
					matchAnyContext( elem, context, xml ) );
			// Avoid hanging onto element (issue #299)
			checkContext = null;
			return ret;
		} ];

	for ( ; i < len; i++ ) {
		if ( (matcher = Expr.relative[ tokens[i].type ]) ) {
			matchers = [ addCombinator(elementMatcher( matchers ), matcher) ];
		} else {
			matcher = Expr.filter[ tokens[i].type ].apply( null, tokens[i].matches );

			// Return special upon seeing a positional matcher
			if ( matcher[ expando ] ) {
				// Find the next relative operator (if any) for proper handling
				j = ++i;
				for ( ; j < len; j++ ) {
					if ( Expr.relative[ tokens[j].type ] ) {
						break;
					}
				}
				return setMatcher(
					i > 1 && elementMatcher( matchers ),
					i > 1 && toSelector(
						// If the preceding token was a descendant combinator, insert an implicit any-element `*`
						tokens.slice( 0, i - 1 ).concat({ value: tokens[ i - 2 ].type === " " ? "*" : "" })
					).replace( rtrim, "$1" ),
					matcher,
					i < j && matcherFromTokens( tokens.slice( i, j ) ),
					j < len && matcherFromTokens( (tokens = tokens.slice( j )) ),
					j < len && toSelector( tokens )
				);
			}
			matchers.push( matcher );
		}
	}

	return elementMatcher( matchers );
}

function matcherFromGroupMatchers( elementMatchers, setMatchers ) {
	var bySet = setMatchers.length > 0,
		byElement = elementMatchers.length > 0,
		superMatcher = function( seed, context, xml, results, outermost ) {
			var elem, j, matcher,
				matchedCount = 0,
				i = "0",
				unmatched = seed && [],
				setMatched = [],
				contextBackup = outermostContext,
				// We must always have either seed elements or outermost context
				elems = seed || byElement && Expr.find["TAG"]( "*", outermost ),
				// Use integer dirruns iff this is the outermost matcher
				dirrunsUnique = (dirruns += contextBackup == null ? 1 : Math.random() || 0.1),
				len = elems.length;

			if ( outermost ) {
				outermostContext = context === document || context || outermost;
			}

			// Add elements passing elementMatchers directly to results
			// Support: IE<9, Safari
			// Tolerate NodeList properties (IE: "length"; Safari: <number>) matching elements by id
			for ( ; i !== len && (elem = elems[i]) != null; i++ ) {
				if ( byElement && elem ) {
					j = 0;
					if ( !context && elem.ownerDocument !== document ) {
						setDocument( elem );
						xml = !documentIsHTML;
					}
					while ( (matcher = elementMatchers[j++]) ) {
						if ( matcher( elem, context || document, xml) ) {
							results.push( elem );
							break;
						}
					}
					if ( outermost ) {
						dirruns = dirrunsUnique;
					}
				}

				// Track unmatched elements for set filters
				if ( bySet ) {
					// They will have gone through all possible matchers
					if ( (elem = !matcher && elem) ) {
						matchedCount--;
					}

					// Lengthen the array for every element, matched or not
					if ( seed ) {
						unmatched.push( elem );
					}
				}
			}

			// `i` is now the count of elements visited above, and adding it to `matchedCount`
			// makes the latter nonnegative.
			matchedCount += i;

			// Apply set filters to unmatched elements
			// NOTE: This can be skipped if there are no unmatched elements (i.e., `matchedCount`
			// equals `i`), unless we didn't visit _any_ elements in the above loop because we have
			// no element matchers and no seed.
			// Incrementing an initially-string "0" `i` allows `i` to remain a string only in that
			// case, which will result in a "00" `matchedCount` that differs from `i` but is also
			// numerically zero.
			if ( bySet && i !== matchedCount ) {
				j = 0;
				while ( (matcher = setMatchers[j++]) ) {
					matcher( unmatched, setMatched, context, xml );
				}

				if ( seed ) {
					// Reintegrate element matches to eliminate the need for sorting
					if ( matchedCount > 0 ) {
						while ( i-- ) {
							if ( !(unmatched[i] || setMatched[i]) ) {
								setMatched[i] = pop.call( results );
							}
						}
					}

					// Discard index placeholder values to get only actual matches
					setMatched = condense( setMatched );
				}

				// Add matches to results
				push.apply( results, setMatched );

				// Seedless set matches succeeding multiple successful matchers stipulate sorting
				if ( outermost && !seed && setMatched.length > 0 &&
					( matchedCount + setMatchers.length ) > 1 ) {

					Sizzle.uniqueSort( results );
				}
			}

			// Override manipulation of globals by nested matchers
			if ( outermost ) {
				dirruns = dirrunsUnique;
				outermostContext = contextBackup;
			}

			return unmatched;
		};

	return bySet ?
		markFunction( superMatcher ) :
		superMatcher;
}

compile = Sizzle.compile = function( selector, match /* Internal Use Only */ ) {
	var i,
		setMatchers = [],
		elementMatchers = [],
		cached = compilerCache[ selector + " " ];

	if ( !cached ) {
		// Generate a function of recursive functions that can be used to check each element
		if ( !match ) {
			match = tokenize( selector );
		}
		i = match.length;
		while ( i-- ) {
			cached = matcherFromTokens( match[i] );
			if ( cached[ expando ] ) {
				setMatchers.push( cached );
			} else {
				elementMatchers.push( cached );
			}
		}

		// Cache the compiled function
		cached = compilerCache( selector, matcherFromGroupMatchers( elementMatchers, setMatchers ) );

		// Save selector and tokenization
		cached.selector = selector;
	}
	return cached;
};

/**
 * A low-level selection function that works with Sizzle's compiled
 *  selector functions
 * @param {String|Function} selector A selector or a pre-compiled
 *  selector function built with Sizzle.compile
 * @param {Element} context
 * @param {Array} [results]
 * @param {Array} [seed] A set of elements to match against
 */
select = Sizzle.select = function( selector, context, results, seed ) {
	var i, tokens, token, type, find,
		compiled = typeof selector === "function" && selector,
		match = !seed && tokenize( (selector = compiled.selector || selector) );

	results = results || [];

	// Try to minimize operations if there is only one selector in the list and no seed
	// (the latter of which guarantees us context)
	if ( match.length === 1 ) {

		// Reduce context if the leading compound selector is an ID
		tokens = match[0] = match[0].slice( 0 );
		if ( tokens.length > 2 && (token = tokens[0]).type === "ID" &&
				context.nodeType === 9 && documentIsHTML && Expr.relative[ tokens[1].type ] ) {

			context = ( Expr.find["ID"]( token.matches[0].replace(runescape, funescape), context ) || [] )[0];
			if ( !context ) {
				return results;

			// Precompiled matchers will still verify ancestry, so step up a level
			} else if ( compiled ) {
				context = context.parentNode;
			}

			selector = selector.slice( tokens.shift().value.length );
		}

		// Fetch a seed set for right-to-left matching
		i = matchExpr["needsContext"].test( selector ) ? 0 : tokens.length;
		while ( i-- ) {
			token = tokens[i];

			// Abort if we hit a combinator
			if ( Expr.relative[ (type = token.type) ] ) {
				break;
			}
			if ( (find = Expr.find[ type ]) ) {
				// Search, expanding context for leading sibling combinators
				if ( (seed = find(
					token.matches[0].replace( runescape, funescape ),
					rsibling.test( tokens[0].type ) && testContext( context.parentNode ) || context
				)) ) {

					// If seed is empty or no tokens remain, we can return early
					tokens.splice( i, 1 );
					selector = seed.length && toSelector( tokens );
					if ( !selector ) {
						push.apply( results, seed );
						return results;
					}

					break;
				}
			}
		}
	}

	// Compile and execute a filtering function if one is not provided
	// Provide `match` to avoid retokenization if we modified the selector above
	( compiled || compile( selector, match ) )(
		seed,
		context,
		!documentIsHTML,
		results,
		!context || rsibling.test( selector ) && testContext( context.parentNode ) || context
	);
	return results;
};

// One-time assignments

// Sort stability
support.sortStable = expando.split("").sort( sortOrder ).join("") === expando;

// Support: Chrome 14-35+
// Always assume duplicates if they aren't passed to the comparison function
support.detectDuplicates = !!hasDuplicate;

// Initialize against the default document
setDocument();

// Support: Webkit<537.32 - Safari 6.0.3/Chrome 25 (fixed in Chrome 27)
// Detached nodes confoundingly follow *each other*
support.sortDetached = assert(function( el ) {
	// Should return 1, but returns 4 (following)
	return el.compareDocumentPosition( document.createElement("fieldset") ) & 1;
});

// Support: IE<8
// Prevent attribute/property "interpolation"
// https://msdn.microsoft.com/en-us/library/ms536429%28VS.85%29.aspx
if ( !assert(function( el ) {
	el.innerHTML = "<a href='#'></a>";
	return el.firstChild.getAttribute("href") === "#" ;
}) ) {
	addHandle( "type|href|height|width", function( elem, name, isXML ) {
		if ( !isXML ) {
			return elem.getAttribute( name, name.toLowerCase() === "type" ? 1 : 2 );
		}
	});
}

// Support: IE<9
// Use defaultValue in place of getAttribute("value")
if ( !support.attributes || !assert(function( el ) {
	el.innerHTML = "<input/>";
	el.firstChild.setAttribute( "value", "" );
	return el.firstChild.getAttribute( "value" ) === "";
}) ) {
	addHandle( "value", function( elem, name, isXML ) {
		if ( !isXML && elem.nodeName.toLowerCase() === "input" ) {
			return elem.defaultValue;
		}
	});
}

// Support: IE<9
// Use getAttributeNode to fetch booleans when getAttribute lies
if ( !assert(function( el ) {
	return el.getAttribute("disabled") == null;
}) ) {
	addHandle( booleans, function( elem, name, isXML ) {
		var val;
		if ( !isXML ) {
			return elem[ name ] === true ? name.toLowerCase() :
					(val = elem.getAttributeNode( name )) && val.specified ?
					val.value :
				null;
		}
	});
}

return Sizzle;

})( window );



jQuery.find = Sizzle;
jQuery.expr = Sizzle.selectors;

// Deprecated
jQuery.expr[ ":" ] = jQuery.expr.pseudos;
jQuery.uniqueSort = jQuery.unique = Sizzle.uniqueSort;
jQuery.text = Sizzle.getText;
jQuery.isXMLDoc = Sizzle.isXML;
jQuery.contains = Sizzle.contains;
jQuery.escapeSelector = Sizzle.escape;




var dir = function( elem, dir, until ) {
	var matched = [],
		truncate = until !== undefined;

	while ( ( elem = elem[ dir ] ) && elem.nodeType !== 9 ) {
		if ( elem.nodeType === 1 ) {
			if ( truncate && jQuery( elem ).is( until ) ) {
				break;
			}
			matched.push( elem );
		}
	}
	return matched;
};


var siblings = function( n, elem ) {
	var matched = [];

	for ( ; n; n = n.nextSibling ) {
		if ( n.nodeType === 1 && n !== elem ) {
			matched.push( n );
		}
	}

	return matched;
};


var rneedsContext = jQuery.expr.match.needsContext;



function nodeName( elem, name ) {

  return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();

};
var rsingleTag = ( /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i );



var risSimple = /^.[^:#\[\.,]*$/;

// Implement the identical functionality for filter and not
function winnow( elements, qualifier, not ) {
	if ( jQuery.isFunction( qualifier ) ) {
		return jQuery.grep( elements, function( elem, i ) {
			return !!qualifier.call( elem, i, elem ) !== not;
		} );
	}

	// Single element
	if ( qualifier.nodeType ) {
		return jQuery.grep( elements, function( elem ) {
			return ( elem === qualifier ) !== not;
		} );
	}

	// Arraylike of elements (jQuery, arguments, Array)
	if ( typeof qualifier !== "string" ) {
		return jQuery.grep( elements, function( elem ) {
			return ( indexOf.call( qualifier, elem ) > -1 ) !== not;
		} );
	}

	// Simple selector that can be filtered directly, removing non-Elements
	if ( risSimple.test( qualifier ) ) {
		return jQuery.filter( qualifier, elements, not );
	}

	// Complex selector, compare the two sets, removing non-Elements
	qualifier = jQuery.filter( qualifier, elements );
	return jQuery.grep( elements, function( elem ) {
		return ( indexOf.call( qualifier, elem ) > -1 ) !== not && elem.nodeType === 1;
	} );
}

jQuery.filter = function( expr, elems, not ) {
	var elem = elems[ 0 ];

	if ( not ) {
		expr = ":not(" + expr + ")";
	}

	if ( elems.length === 1 && elem.nodeType === 1 ) {
		return jQuery.find.matchesSelector( elem, expr ) ? [ elem ] : [];
	}

	return jQuery.find.matches( expr, jQuery.grep( elems, function( elem ) {
		return elem.nodeType === 1;
	} ) );
};

jQuery.fn.extend( {
	find: function( selector ) {
		var i, ret,
			len = this.length,
			self = this;

		if ( typeof selector !== "string" ) {
			return this.pushStack( jQuery( selector ).filter( function() {
				for ( i = 0; i < len; i++ ) {
					if ( jQuery.contains( self[ i ], this ) ) {
						return true;
					}
				}
			} ) );
		}

		ret = this.pushStack( [] );

		for ( i = 0; i < len; i++ ) {
			jQuery.find( selector, self[ i ], ret );
		}

		return len > 1 ? jQuery.uniqueSort( ret ) : ret;
	},
	filter: function( selector ) {
		return this.pushStack( winnow( this, selector || [], false ) );
	},
	not: function( selector ) {
		return this.pushStack( winnow( this, selector || [], true ) );
	},
	is: function( selector ) {
		return !!winnow(
			this,

			// If this is a positional/relative selector, check membership in the returned set
			// so $("p:first").is("p:last") won't return true for a doc with two "p".
			typeof selector === "string" && rneedsContext.test( selector ) ?
				jQuery( selector ) :
				selector || [],
			false
		).length;
	}
} );


// Initialize a jQuery object


// A central reference to the root jQuery(document)
var rootjQuery,

	// A simple way to check for HTML strings
	// Prioritize #id over <tag> to avoid XSS via location.hash (#9521)
	// Strict HTML recognition (#11290: must start with <)
	// Shortcut simple #id case for speed
	rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/,

	init = jQuery.fn.init = function( selector, context, root ) {
		var match, elem;

		// HANDLE: $(""), $(null), $(undefined), $(false)
		if ( !selector ) {
			return this;
		}

		// Method init() accepts an alternate rootjQuery
		// so migrate can support jQuery.sub (gh-2101)
		root = root || rootjQuery;

		// Handle HTML strings
		if ( typeof selector === "string" ) {
			if ( selector[ 0 ] === "<" &&
				selector[ selector.length - 1 ] === ">" &&
				selector.length >= 3 ) {

				// Assume that strings that start and end with <> are HTML and skip the regex check
				match = [ null, selector, null ];

			} else {
				match = rquickExpr.exec( selector );
			}

			// Match html or make sure no context is specified for #id
			if ( match && ( match[ 1 ] || !context ) ) {

				// HANDLE: $(html) -> $(array)
				if ( match[ 1 ] ) {
					context = context instanceof jQuery ? context[ 0 ] : context;

					// Option to run scripts is true for back-compat
					// Intentionally let the error be thrown if parseHTML is not present
					jQuery.merge( this, jQuery.parseHTML(
						match[ 1 ],
						context && context.nodeType ? context.ownerDocument || context : document,
						true
					) );

					// HANDLE: $(html, props)
					if ( rsingleTag.test( match[ 1 ] ) && jQuery.isPlainObject( context ) ) {
						for ( match in context ) {

							// Properties of context are called as methods if possible
							if ( jQuery.isFunction( this[ match ] ) ) {
								this[ match ]( context[ match ] );

							// ...and otherwise set as attributes
							} else {
								this.attr( match, context[ match ] );
							}
						}
					}

					return this;

				// HANDLE: $(#id)
				} else {
					elem = document.getElementById( match[ 2 ] );

					if ( elem ) {

						// Inject the element directly into the jQuery object
						this[ 0 ] = elem;
						this.length = 1;
					}
					return this;
				}

			// HANDLE: $(expr, $(...))
			} else if ( !context || context.jquery ) {
				return ( context || root ).find( selector );

			// HANDLE: $(expr, context)
			// (which is just equivalent to: $(context).find(expr)
			} else {
				return this.constructor( context ).find( selector );
			}

		// HANDLE: $(DOMElement)
		} else if ( selector.nodeType ) {
			this[ 0 ] = selector;
			this.length = 1;
			return this;

		// HANDLE: $(function)
		// Shortcut for document ready
		} else if ( jQuery.isFunction( selector ) ) {
			return root.ready !== undefined ?
				root.ready( selector ) :

				// Execute immediately if ready is not present
				selector( jQuery );
		}

		return jQuery.makeArray( selector, this );
	};

// Give the init function the jQuery prototype for later instantiation
init.prototype = jQuery.fn;

// Initialize central reference
rootjQuery = jQuery( document );


var rparentsprev = /^(?:parents|prev(?:Until|All))/,

	// Methods guaranteed to produce a unique set when starting from a unique set
	guaranteedUnique = {
		children: true,
		contents: true,
		next: true,
		prev: true
	};

jQuery.fn.extend( {
	has: function( target ) {
		var targets = jQuery( target, this ),
			l = targets.length;

		return this.filter( function() {
			var i = 0;
			for ( ; i < l; i++ ) {
				if ( jQuery.contains( this, targets[ i ] ) ) {
					return true;
				}
			}
		} );
	},

	closest: function( selectors, context ) {
		var cur,
			i = 0,
			l = this.length,
			matched = [],
			targets = typeof selectors !== "string" && jQuery( selectors );

		// Positional selectors never match, since there's no _selection_ context
		if ( !rneedsContext.test( selectors ) ) {
			for ( ; i < l; i++ ) {
				for ( cur = this[ i ]; cur && cur !== context; cur = cur.parentNode ) {

					// Always skip document fragments
					if ( cur.nodeType < 11 && ( targets ?
						targets.index( cur ) > -1 :

						// Don't pass non-elements to Sizzle
						cur.nodeType === 1 &&
							jQuery.find.matchesSelector( cur, selectors ) ) ) {

						matched.push( cur );
						break;
					}
				}
			}
		}

		return this.pushStack( matched.length > 1 ? jQuery.uniqueSort( matched ) : matched );
	},

	// Determine the position of an element within the set
	index: function( elem ) {

		// No argument, return index in parent
		if ( !elem ) {
			return ( this[ 0 ] && this[ 0 ].parentNode ) ? this.first().prevAll().length : -1;
		}

		// Index in selector
		if ( typeof elem === "string" ) {
			return indexOf.call( jQuery( elem ), this[ 0 ] );
		}

		// Locate the position of the desired element
		return indexOf.call( this,

			// If it receives a jQuery object, the first element is used
			elem.jquery ? elem[ 0 ] : elem
		);
	},

	add: function( selector, context ) {
		return this.pushStack(
			jQuery.uniqueSort(
				jQuery.merge( this.get(), jQuery( selector, context ) )
			)
		);
	},

	addBack: function( selector ) {
		return this.add( selector == null ?
			this.prevObject : this.prevObject.filter( selector )
		);
	}
} );

function sibling( cur, dir ) {
	while ( ( cur = cur[ dir ] ) && cur.nodeType !== 1 ) {}
	return cur;
}

jQuery.each( {
	parent: function( elem ) {
		var parent = elem.parentNode;
		return parent && parent.nodeType !== 11 ? parent : null;
	},
	parents: function( elem ) {
		return dir( elem, "parentNode" );
	},
	parentsUntil: function( elem, i, until ) {
		return dir( elem, "parentNode", until );
	},
	next: function( elem ) {
		return sibling( elem, "nextSibling" );
	},
	prev: function( elem ) {
		return sibling( elem, "previousSibling" );
	},
	nextAll: function( elem ) {
		return dir( elem, "nextSibling" );
	},
	prevAll: function( elem ) {
		return dir( elem, "previousSibling" );
	},
	nextUntil: function( elem, i, until ) {
		return dir( elem, "nextSibling", until );
	},
	prevUntil: function( elem, i, until ) {
		return dir( elem, "previousSibling", until );
	},
	siblings: function( elem ) {
		return siblings( ( elem.parentNode || {} ).firstChild, elem );
	},
	children: function( elem ) {
		return siblings( elem.firstChild );
	},
	contents: function( elem ) {
        if ( nodeName( elem, "iframe" ) ) {
            return elem.contentDocument;
        }

        // Support: IE 9 - 11 only, iOS 7 only, Android Browser <=4.3 only
        // Treat the template element as a regular one in browsers that
        // don't support it.
        if ( nodeName( elem, "template" ) ) {
            elem = elem.content || elem;
        }

        return jQuery.merge( [], elem.childNodes );
	}
}, function( name, fn ) {
	jQuery.fn[ name ] = function( until, selector ) {
		var matched = jQuery.map( this, fn, until );

		if ( name.slice( -5 ) !== "Until" ) {
			selector = until;
		}

		if ( selector && typeof selector === "string" ) {
			matched = jQuery.filter( selector, matched );
		}

		if ( this.length > 1 ) {

			// Remove duplicates
			if ( !guaranteedUnique[ name ] ) {
				jQuery.uniqueSort( matched );
			}

			// Reverse order for parents* and prev-derivatives
			if ( rparentsprev.test( name ) ) {
				matched.reverse();
			}
		}

		return this.pushStack( matched );
	};
} );
var rnothtmlwhite = ( /[^\x20\t\r\n\f]+/g );



// Convert String-formatted options into Object-formatted ones
function createOptions( options ) {
	var object = {};
	jQuery.each( options.match( rnothtmlwhite ) || [], function( _, flag ) {
		object[ flag ] = true;
	} );
	return object;
}

/*
 * Create a callback list using the following parameters:
 *
 *	options: an optional list of space-separated options that will change how
 *			the callback list behaves or a more traditional option object
 *
 * By default a callback list will act like an event callback list and can be
 * "fired" multiple times.
 *
 * Possible options:
 *
 *	once:			will ensure the callback list can only be fired once (like a Deferred)
 *
 *	memory:			will keep track of previous values and will call any callback added
 *					after the list has been fired right away with the latest "memorized"
 *					values (like a Deferred)
 *
 *	unique:			will ensure a callback can only be added once (no duplicate in the list)
 *
 *	stopOnFalse:	interrupt callings when a callback returns false
 *
 */
jQuery.Callbacks = function( options ) {

	// Convert options from String-formatted to Object-formatted if needed
	// (we check in cache first)
	options = typeof options === "string" ?
		createOptions( options ) :
		jQuery.extend( {}, options );

	var // Flag to know if list is currently firing
		firing,

		// Last fire value for non-forgettable lists
		memory,

		// Flag to know if list was already fired
		fired,

		// Flag to prevent firing
		locked,

		// Actual callback list
		list = [],

		// Queue of execution data for repeatable lists
		queue = [],

		// Index of currently firing callback (modified by add/remove as needed)
		firingIndex = -1,

		// Fire callbacks
		fire = function() {

			// Enforce single-firing
			locked = locked || options.once;

			// Execute callbacks for all pending executions,
			// respecting firingIndex overrides and runtime changes
			fired = firing = true;
			for ( ; queue.length; firingIndex = -1 ) {
				memory = queue.shift();
				while ( ++firingIndex < list.length ) {

					// Run callback and check for early termination
					if ( list[ firingIndex ].apply( memory[ 0 ], memory[ 1 ] ) === false &&
						options.stopOnFalse ) {

						// Jump to end and forget the data so .add doesn't re-fire
						firingIndex = list.length;
						memory = false;
					}
				}
			}

			// Forget the data if we're done with it
			if ( !options.memory ) {
				memory = false;
			}

			firing = false;

			// Clean up if we're done firing for good
			if ( locked ) {

				// Keep an empty list if we have data for future add calls
				if ( memory ) {
					list = [];

				// Otherwise, this object is spent
				} else {
					list = "";
				}
			}
		},

		// Actual Callbacks object
		self = {

			// Add a callback or a collection of callbacks to the list
			add: function() {
				if ( list ) {

					// If we have memory from a past run, we should fire after adding
					if ( memory && !firing ) {
						firingIndex = list.length - 1;
						queue.push( memory );
					}

					( function add( args ) {
						jQuery.each( args, function( _, arg ) {
							if ( jQuery.isFunction( arg ) ) {
								if ( !options.unique || !self.has( arg ) ) {
									list.push( arg );
								}
							} else if ( arg && arg.length && jQuery.type( arg ) !== "string" ) {

								// Inspect recursively
								add( arg );
							}
						} );
					} )( arguments );

					if ( memory && !firing ) {
						fire();
					}
				}
				return this;
			},

			// Remove a callback from the list
			remove: function() {
				jQuery.each( arguments, function( _, arg ) {
					var index;
					while ( ( index = jQuery.inArray( arg, list, index ) ) > -1 ) {
						list.splice( index, 1 );

						// Handle firing indexes
						if ( index <= firingIndex ) {
							firingIndex--;
						}
					}
				} );
				return this;
			},

			// Check if a given callback is in the list.
			// If no argument is given, return whether or not list has callbacks attached.
			has: function( fn ) {
				return fn ?
					jQuery.inArray( fn, list ) > -1 :
					list.length > 0;
			},

			// Remove all callbacks from the list
			empty: function() {
				if ( list ) {
					list = [];
				}
				return this;
			},

			// Disable .fire and .add
			// Abort any current/pending executions
			// Clear all callbacks and values
			disable: function() {
				locked = queue = [];
				list = memory = "";
				return this;
			},
			disabled: function() {
				return !list;
			},

			// Disable .fire
			// Also disable .add unless we have memory (since it would have no effect)
			// Abort any pending executions
			lock: function() {
				locked = queue = [];
				if ( !memory && !firing ) {
					list = memory = "";
				}
				return this;
			},
			locked: function() {
				return !!locked;
			},

			// Call all callbacks with the given context and arguments
			fireWith: function( context, args ) {
				if ( !locked ) {
					args = args || [];
					args = [ context, args.slice ? args.slice() : args ];
					queue.push( args );
					if ( !firing ) {
						fire();
					}
				}
				return this;
			},

			// Call all the callbacks with the given arguments
			fire: function() {
				self.fireWith( this, arguments );
				return this;
			},

			// To know if the callbacks have already been called at least once
			fired: function() {
				return !!fired;
			}
		};

	return self;
};


function Identity( v ) {
	return v;
}
function Thrower( ex ) {
	throw ex;
}

function adoptValue( value, resolve, reject, noValue ) {
	var method;

	try {

		// Check for promise aspect first to privilege synchronous behavior
		if ( value && jQuery.isFunction( ( method = value.promise ) ) ) {
			method.call( value ).done( resolve ).fail( reject );

		// Other thenables
		} else if ( value && jQuery.isFunction( ( method = value.then ) ) ) {
			method.call( value, resolve, reject );

		// Other non-thenables
		} else {

			// Control `resolve` arguments by letting Array#slice cast boolean `noValue` to integer:
			// * false: [ value ].slice( 0 ) => resolve( value )
			// * true: [ value ].slice( 1 ) => resolve()
			resolve.apply( undefined, [ value ].slice( noValue ) );
		}

	// For Promises/A+, convert exceptions into rejections
	// Since jQuery.when doesn't unwrap thenables, we can skip the extra checks appearing in
	// Deferred#then to conditionally suppress rejection.
	} catch ( value ) {

		// Support: Android 4.0 only
		// Strict mode functions invoked without .call/.apply get global-object context
		reject.apply( undefined, [ value ] );
	}
}

jQuery.extend( {

	Deferred: function( func ) {
		var tuples = [

				// action, add listener, callbacks,
				// ... .then handlers, argument index, [final state]
				[ "notify", "progress", jQuery.Callbacks( "memory" ),
					jQuery.Callbacks( "memory" ), 2 ],
				[ "resolve", "done", jQuery.Callbacks( "once memory" ),
					jQuery.Callbacks( "once memory" ), 0, "resolved" ],
				[ "reject", "fail", jQuery.Callbacks( "once memory" ),
					jQuery.Callbacks( "once memory" ), 1, "rejected" ]
			],
			state = "pending",
			promise = {
				state: function() {
					return state;
				},
				always: function() {
					deferred.done( arguments ).fail( arguments );
					return this;
				},
				"catch": function( fn ) {
					return promise.then( null, fn );
				},

				// Keep pipe for back-compat
				pipe: function( /* fnDone, fnFail, fnProgress */ ) {
					var fns = arguments;

					return jQuery.Deferred( function( newDefer ) {
						jQuery.each( tuples, function( i, tuple ) {

							// Map tuples (progress, done, fail) to arguments (done, fail, progress)
							var fn = jQuery.isFunction( fns[ tuple[ 4 ] ] ) && fns[ tuple[ 4 ] ];

							// deferred.progress(function() { bind to newDefer or newDefer.notify })
							// deferred.done(function() { bind to newDefer or newDefer.resolve })
							// deferred.fail(function() { bind to newDefer or newDefer.reject })
							deferred[ tuple[ 1 ] ]( function() {
								var returned = fn && fn.apply( this, arguments );
								if ( returned && jQuery.isFunction( returned.promise ) ) {
									returned.promise()
										.progress( newDefer.notify )
										.done( newDefer.resolve )
										.fail( newDefer.reject );
								} else {
									newDefer[ tuple[ 0 ] + "With" ](
										this,
										fn ? [ returned ] : arguments
									);
								}
							} );
						} );
						fns = null;
					} ).promise();
				},
				then: function( onFulfilled, onRejected, onProgress ) {
					var maxDepth = 0;
					function resolve( depth, deferred, handler, special ) {
						return function() {
							var that = this,
								args = arguments,
								mightThrow = function() {
									var returned, then;

									// Support: Promises/A+ section 2.3.3.3.3
									// https://promisesaplus.com/#point-59
									// Ignore double-resolution attempts
									if ( depth < maxDepth ) {
										return;
									}

									returned = handler.apply( that, args );

									// Support: Promises/A+ section 2.3.1
									// https://promisesaplus.com/#point-48
									if ( returned === deferred.promise() ) {
										throw new TypeError( "Thenable self-resolution" );
									}

									// Support: Promises/A+ sections 2.3.3.1, 3.5
									// https://promisesaplus.com/#point-54
									// https://promisesaplus.com/#point-75
									// Retrieve `then` only once
									then = returned &&

										// Support: Promises/A+ section 2.3.4
										// https://promisesaplus.com/#point-64
										// Only check objects and functions for thenability
										( typeof returned === "object" ||
											typeof returned === "function" ) &&
										returned.then;

									// Handle a returned thenable
									if ( jQuery.isFunction( then ) ) {

										// Special processors (notify) just wait for resolution
										if ( special ) {
											then.call(
												returned,
												resolve( maxDepth, deferred, Identity, special ),
												resolve( maxDepth, deferred, Thrower, special )
											);

										// Normal processors (resolve) also hook into progress
										} else {

											// ...and disregard older resolution values
											maxDepth++;

											then.call(
												returned,
												resolve( maxDepth, deferred, Identity, special ),
												resolve( maxDepth, deferred, Thrower, special ),
												resolve( maxDepth, deferred, Identity,
													deferred.notifyWith )
											);
										}

									// Handle all other returned values
									} else {

										// Only substitute handlers pass on context
										// and multiple values (non-spec behavior)
										if ( handler !== Identity ) {
											that = undefined;
											args = [ returned ];
										}

										// Process the value(s)
										// Default process is resolve
										( special || deferred.resolveWith )( that, args );
									}
								},

								// Only normal processors (resolve) catch and reject exceptions
								process = special ?
									mightThrow :
									function() {
										try {
											mightThrow();
										} catch ( e ) {

											if ( jQuery.Deferred.exceptionHook ) {
												jQuery.Deferred.exceptionHook( e,
													process.stackTrace );
											}

											// Support: Promises/A+ section 2.3.3.3.4.1
											// https://promisesaplus.com/#point-61
											// Ignore post-resolution exceptions
											if ( depth + 1 >= maxDepth ) {

												// Only substitute handlers pass on context
												// and multiple values (non-spec behavior)
												if ( handler !== Thrower ) {
													that = undefined;
													args = [ e ];
												}

												deferred.rejectWith( that, args );
											}
										}
									};

							// Support: Promises/A+ section 2.3.3.3.1
							// https://promisesaplus.com/#point-57
							// Re-resolve promises immediately to dodge false rejection from
							// subsequent errors
							if ( depth ) {
								process();
							} else {

								// Call an optional hook to record the stack, in case of exception
								// since it's otherwise lost when execution goes async
								if ( jQuery.Deferred.getStackHook ) {
									process.stackTrace = jQuery.Deferred.getStackHook();
								}
								window.setTimeout( process );
							}
						};
					}

					return jQuery.Deferred( function( newDefer ) {

						// progress_handlers.add( ... )
						tuples[ 0 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								jQuery.isFunction( onProgress ) ?
									onProgress :
									Identity,
								newDefer.notifyWith
							)
						);

						// fulfilled_handlers.add( ... )
						tuples[ 1 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								jQuery.isFunction( onFulfilled ) ?
									onFulfilled :
									Identity
							)
						);

						// rejected_handlers.add( ... )
						tuples[ 2 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								jQuery.isFunction( onRejected ) ?
									onRejected :
									Thrower
							)
						);
					} ).promise();
				},

				// Get a promise for this deferred
				// If obj is provided, the promise aspect is added to the object
				promise: function( obj ) {
					return obj != null ? jQuery.extend( obj, promise ) : promise;
				}
			},
			deferred = {};

		// Add list-specific methods
		jQuery.each( tuples, function( i, tuple ) {
			var list = tuple[ 2 ],
				stateString = tuple[ 5 ];

			// promise.progress = list.add
			// promise.done = list.add
			// promise.fail = list.add
			promise[ tuple[ 1 ] ] = list.add;

			// Handle state
			if ( stateString ) {
				list.add(
					function() {

						// state = "resolved" (i.e., fulfilled)
						// state = "rejected"
						state = stateString;
					},

					// rejected_callbacks.disable
					// fulfilled_callbacks.disable
					tuples[ 3 - i ][ 2 ].disable,

					// progress_callbacks.lock
					tuples[ 0 ][ 2 ].lock
				);
			}

			// progress_handlers.fire
			// fulfilled_handlers.fire
			// rejected_handlers.fire
			list.add( tuple[ 3 ].fire );

			// deferred.notify = function() { deferred.notifyWith(...) }
			// deferred.resolve = function() { deferred.resolveWith(...) }
			// deferred.reject = function() { deferred.rejectWith(...) }
			deferred[ tuple[ 0 ] ] = function() {
				deferred[ tuple[ 0 ] + "With" ]( this === deferred ? undefined : this, arguments );
				return this;
			};

			// deferred.notifyWith = list.fireWith
			// deferred.resolveWith = list.fireWith
			// deferred.rejectWith = list.fireWith
			deferred[ tuple[ 0 ] + "With" ] = list.fireWith;
		} );

		// Make the deferred a promise
		promise.promise( deferred );

		// Call given func if any
		if ( func ) {
			func.call( deferred, deferred );
		}

		// All done!
		return deferred;
	},

	// Deferred helper
	when: function( singleValue ) {
		var

			// count of uncompleted subordinates
			remaining = arguments.length,

			// count of unprocessed arguments
			i = remaining,

			// subordinate fulfillment data
			resolveContexts = Array( i ),
			resolveValues = slice.call( arguments ),

			// the master Deferred
			master = jQuery.Deferred(),

			// subordinate callback factory
			updateFunc = function( i ) {
				return function( value ) {
					resolveContexts[ i ] = this;
					resolveValues[ i ] = arguments.length > 1 ? slice.call( arguments ) : value;
					if ( !( --remaining ) ) {
						master.resolveWith( resolveContexts, resolveValues );
					}
				};
			};

		// Single- and empty arguments are adopted like Promise.resolve
		if ( remaining <= 1 ) {
			adoptValue( singleValue, master.done( updateFunc( i ) ).resolve, master.reject,
				!remaining );

			// Use .then() to unwrap secondary thenables (cf. gh-3000)
			if ( master.state() === "pending" ||
				jQuery.isFunction( resolveValues[ i ] && resolveValues[ i ].then ) ) {

				return master.then();
			}
		}

		// Multiple arguments are aggregated like Promise.all array elements
		while ( i-- ) {
			adoptValue( resolveValues[ i ], updateFunc( i ), master.reject );
		}

		return master.promise();
	}
} );


// These usually indicate a programmer mistake during development,
// warn about them ASAP rather than swallowing them by default.
var rerrorNames = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;

jQuery.Deferred.exceptionHook = function( error, stack ) {

	// Support: IE 8 - 9 only
	// Console exists when dev tools are open, which can happen at any time
	if ( window.console && window.console.warn && error && rerrorNames.test( error.name ) ) {
		window.console.warn( "jQuery.Deferred exception: " + error.message, error.stack, stack );
	}
};




jQuery.readyException = function( error ) {
	window.setTimeout( function() {
		throw error;
	} );
};




// The deferred used on DOM ready
var readyList = jQuery.Deferred();

jQuery.fn.ready = function( fn ) {

	readyList
		.then( fn )

		// Wrap jQuery.readyException in a function so that the lookup
		// happens at the time of error handling instead of callback
		// registration.
		.catch( function( error ) {
			jQuery.readyException( error );
		} );

	return this;
};

jQuery.extend( {

	// Is the DOM ready to be used? Set to true once it occurs.
	isReady: false,

	// A counter to track how many items to wait for before
	// the ready event fires. See #6781
	readyWait: 1,

	// Handle when the DOM is ready
	ready: function( wait ) {

		// Abort if there are pending holds or we're already ready
		if ( wait === true ? --jQuery.readyWait : jQuery.isReady ) {
			return;
		}

		// Remember that the DOM is ready
		jQuery.isReady = true;

		// If a normal DOM Ready event fired, decrement, and wait if need be
		if ( wait !== true && --jQuery.readyWait > 0 ) {
			return;
		}

		// If there are functions bound, to execute
		readyList.resolveWith( document, [ jQuery ] );
	}
} );

jQuery.ready.then = readyList.then;

// The ready event handler and self cleanup method
function completed() {
	document.removeEventListener( "DOMContentLoaded", completed );
	window.removeEventListener( "load", completed );
	jQuery.ready();
}

// Catch cases where $(document).ready() is called
// after the browser event has already occurred.
// Support: IE <=9 - 10 only
// Older IE sometimes signals "interactive" too soon
if ( document.readyState === "complete" ||
	( document.readyState !== "loading" && !document.documentElement.doScroll ) ) {

	// Handle it asynchronously to allow scripts the opportunity to delay ready
	window.setTimeout( jQuery.ready );

} else {

	// Use the handy event callback
	document.addEventListener( "DOMContentLoaded", completed );

	// A fallback to window.onload, that will always work
	window.addEventListener( "load", completed );
}




// Multifunctional method to get and set values of a collection
// The value/s can optionally be executed if it's a function
var access = function( elems, fn, key, value, chainable, emptyGet, raw ) {
	var i = 0,
		len = elems.length,
		bulk = key == null;

	// Sets many values
	if ( jQuery.type( key ) === "object" ) {
		chainable = true;
		for ( i in key ) {
			access( elems, fn, i, key[ i ], true, emptyGet, raw );
		}

	// Sets one value
	} else if ( value !== undefined ) {
		chainable = true;

		if ( !jQuery.isFunction( value ) ) {
			raw = true;
		}

		if ( bulk ) {

			// Bulk operations run against the entire set
			if ( raw ) {
				fn.call( elems, value );
				fn = null;

			// ...except when executing function values
			} else {
				bulk = fn;
				fn = function( elem, key, value ) {
					return bulk.call( jQuery( elem ), value );
				};
			}
		}

		if ( fn ) {
			for ( ; i < len; i++ ) {
				fn(
					elems[ i ], key, raw ?
					value :
					value.call( elems[ i ], i, fn( elems[ i ], key ) )
				);
			}
		}
	}

	if ( chainable ) {
		return elems;
	}

	// Gets
	if ( bulk ) {
		return fn.call( elems );
	}

	return len ? fn( elems[ 0 ], key ) : emptyGet;
};
var acceptData = function( owner ) {

	// Accepts only:
	//  - Node
	//    - Node.ELEMENT_NODE
	//    - Node.DOCUMENT_NODE
	//  - Object
	//    - Any
	return owner.nodeType === 1 || owner.nodeType === 9 || !( +owner.nodeType );
};




function Data() {
	this.expando = jQuery.expando + Data.uid++;
}

Data.uid = 1;

Data.prototype = {

	cache: function( owner ) {

		// Check if the owner object already has a cache
		var value = owner[ this.expando ];

		// If not, create one
		if ( !value ) {
			value = {};

			// We can accept data for non-element nodes in modern browsers,
			// but we should not, see #8335.
			// Always return an empty object.
			if ( acceptData( owner ) ) {

				// If it is a node unlikely to be stringify-ed or looped over
				// use plain assignment
				if ( owner.nodeType ) {
					owner[ this.expando ] = value;

				// Otherwise secure it in a non-enumerable property
				// configurable must be true to allow the property to be
				// deleted when data is removed
				} else {
					Object.defineProperty( owner, this.expando, {
						value: value,
						configurable: true
					} );
				}
			}
		}

		return value;
	},
	set: function( owner, data, value ) {
		var prop,
			cache = this.cache( owner );

		// Handle: [ owner, key, value ] args
		// Always use camelCase key (gh-2257)
		if ( typeof data === "string" ) {
			cache[ jQuery.camelCase( data ) ] = value;

		// Handle: [ owner, { properties } ] args
		} else {

			// Copy the properties one-by-one to the cache object
			for ( prop in data ) {
				cache[ jQuery.camelCase( prop ) ] = data[ prop ];
			}
		}
		return cache;
	},
	get: function( owner, key ) {
		return key === undefined ?
			this.cache( owner ) :

			// Always use camelCase key (gh-2257)
			owner[ this.expando ] && owner[ this.expando ][ jQuery.camelCase( key ) ];
	},
	access: function( owner, key, value ) {

		// In cases where either:
		//
		//   1. No key was specified
		//   2. A string key was specified, but no value provided
		//
		// Take the "read" path and allow the get method to determine
		// which value to return, respectively either:
		//
		//   1. The entire cache object
		//   2. The data stored at the key
		//
		if ( key === undefined ||
				( ( key && typeof key === "string" ) && value === undefined ) ) {

			return this.get( owner, key );
		}

		// When the key is not a string, or both a key and value
		// are specified, set or extend (existing objects) with either:
		//
		//   1. An object of properties
		//   2. A key and value
		//
		this.set( owner, key, value );

		// Since the "set" path can have two possible entry points
		// return the expected data based on which path was taken[*]
		return value !== undefined ? value : key;
	},
	remove: function( owner, key ) {
		var i,
			cache = owner[ this.expando ];

		if ( cache === undefined ) {
			return;
		}

		if ( key !== undefined ) {

			// Support array or space separated string of keys
			if ( Array.isArray( key ) ) {

				// If key is an array of keys...
				// We always set camelCase keys, so remove that.
				key = key.map( jQuery.camelCase );
			} else {
				key = jQuery.camelCase( key );

				// If a key with the spaces exists, use it.
				// Otherwise, create an array by matching non-whitespace
				key = key in cache ?
					[ key ] :
					( key.match( rnothtmlwhite ) || [] );
			}

			i = key.length;

			while ( i-- ) {
				delete cache[ key[ i ] ];
			}
		}

		// Remove the expando if there's no more data
		if ( key === undefined || jQuery.isEmptyObject( cache ) ) {

			// Support: Chrome <=35 - 45
			// Webkit & Blink performance suffers when deleting properties
			// from DOM nodes, so set to undefined instead
			// https://bugs.chromium.org/p/chromium/issues/detail?id=378607 (bug restricted)
			if ( owner.nodeType ) {
				owner[ this.expando ] = undefined;
			} else {
				delete owner[ this.expando ];
			}
		}
	},
	hasData: function( owner ) {
		var cache = owner[ this.expando ];
		return cache !== undefined && !jQuery.isEmptyObject( cache );
	}
};
var dataPriv = new Data();

var dataUser = new Data();



//	Implementation Summary
//
//	1. Enforce API surface and semantic compatibility with 1.9.x branch
//	2. Improve the module's maintainability by reducing the storage
//		paths to a single mechanism.
//	3. Use the same single mechanism to support "private" and "user" data.
//	4. _Never_ expose "private" data to user code (TODO: Drop _data, _removeData)
//	5. Avoid exposing implementation details on user objects (eg. expando properties)
//	6. Provide a clear path for implementation upgrade to WeakMap in 2014

var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
	rmultiDash = /[A-Z]/g;

function getData( data ) {
	if ( data === "true" ) {
		return true;
	}

	if ( data === "false" ) {
		return false;
	}

	if ( data === "null" ) {
		return null;
	}

	// Only convert to a number if it doesn't change the string
	if ( data === +data + "" ) {
		return +data;
	}

	if ( rbrace.test( data ) ) {
		return JSON.parse( data );
	}

	return data;
}

function dataAttr( elem, key, data ) {
	var name;

	// If nothing was found internally, try to fetch any
	// data from the HTML5 data-* attribute
	if ( data === undefined && elem.nodeType === 1 ) {
		name = "data-" + key.replace( rmultiDash, "-$&" ).toLowerCase();
		data = elem.getAttribute( name );

		if ( typeof data === "string" ) {
			try {
				data = getData( data );
			} catch ( e ) {}

			// Make sure we set the data so it isn't changed later
			dataUser.set( elem, key, data );
		} else {
			data = undefined;
		}
	}
	return data;
}

jQuery.extend( {
	hasData: function( elem ) {
		return dataUser.hasData( elem ) || dataPriv.hasData( elem );
	},

	data: function( elem, name, data ) {
		return dataUser.access( elem, name, data );
	},

	removeData: function( elem, name ) {
		dataUser.remove( elem, name );
	},

	// TODO: Now that all calls to _data and _removeData have been replaced
	// with direct calls to dataPriv methods, these can be deprecated.
	_data: function( elem, name, data ) {
		return dataPriv.access( elem, name, data );
	},

	_removeData: function( elem, name ) {
		dataPriv.remove( elem, name );
	}
} );

jQuery.fn.extend( {
	data: function( key, value ) {
		var i, name, data,
			elem = this[ 0 ],
			attrs = elem && elem.attributes;

		// Gets all values
		if ( key === undefined ) {
			if ( this.length ) {
				data = dataUser.get( elem );

				if ( elem.nodeType === 1 && !dataPriv.get( elem, "hasDataAttrs" ) ) {
					i = attrs.length;
					while ( i-- ) {

						// Support: IE 11 only
						// The attrs elements can be null (#14894)
						if ( attrs[ i ] ) {
							name = attrs[ i ].name;
							if ( name.indexOf( "data-" ) === 0 ) {
								name = jQuery.camelCase( name.slice( 5 ) );
								dataAttr( elem, name, data[ name ] );
							}
						}
					}
					dataPriv.set( elem, "hasDataAttrs", true );
				}
			}

			return data;
		}

		// Sets multiple values
		if ( typeof key === "object" ) {
			return this.each( function() {
				dataUser.set( this, key );
			} );
		}

		return access( this, function( value ) {
			var data;

			// The calling jQuery object (element matches) is not empty
			// (and therefore has an element appears at this[ 0 ]) and the
			// `value` parameter was not undefined. An empty jQuery object
			// will result in `undefined` for elem = this[ 0 ] which will
			// throw an exception if an attempt to read a data cache is made.
			if ( elem && value === undefined ) {

				// Attempt to get data from the cache
				// The key will always be camelCased in Data
				data = dataUser.get( elem, key );
				if ( data !== undefined ) {
					return data;
				}

				// Attempt to "discover" the data in
				// HTML5 custom data-* attrs
				data = dataAttr( elem, key );
				if ( data !== undefined ) {
					return data;
				}

				// We tried really hard, but the data doesn't exist.
				return;
			}

			// Set the data...
			this.each( function() {

				// We always store the camelCased key
				dataUser.set( this, key, value );
			} );
		}, null, value, arguments.length > 1, null, true );
	},

	removeData: function( key ) {
		return this.each( function() {
			dataUser.remove( this, key );
		} );
	}
} );


jQuery.extend( {
	queue: function( elem, type, data ) {
		var queue;

		if ( elem ) {
			type = ( type || "fx" ) + "queue";
			queue = dataPriv.get( elem, type );

			// Speed up dequeue by getting out quickly if this is just a lookup
			if ( data ) {
				if ( !queue || Array.isArray( data ) ) {
					queue = dataPriv.access( elem, type, jQuery.makeArray( data ) );
				} else {
					queue.push( data );
				}
			}
			return queue || [];
		}
	},

	dequeue: function( elem, type ) {
		type = type || "fx";

		var queue = jQuery.queue( elem, type ),
			startLength = queue.length,
			fn = queue.shift(),
			hooks = jQuery._queueHooks( elem, type ),
			next = function() {
				jQuery.dequeue( elem, type );
			};

		// If the fx queue is dequeued, always remove the progress sentinel
		if ( fn === "inprogress" ) {
			fn = queue.shift();
			startLength--;
		}

		if ( fn ) {

			// Add a progress sentinel to prevent the fx queue from being
			// automatically dequeued
			if ( type === "fx" ) {
				queue.unshift( "inprogress" );
			}

			// Clear up the last queue stop function
			delete hooks.stop;
			fn.call( elem, next, hooks );
		}

		if ( !startLength && hooks ) {
			hooks.empty.fire();
		}
	},

	// Not public - generate a queueHooks object, or return the current one
	_queueHooks: function( elem, type ) {
		var key = type + "queueHooks";
		return dataPriv.get( elem, key ) || dataPriv.access( elem, key, {
			empty: jQuery.Callbacks( "once memory" ).add( function() {
				dataPriv.remove( elem, [ type + "queue", key ] );
			} )
		} );
	}
} );

jQuery.fn.extend( {
	queue: function( type, data ) {
		var setter = 2;

		if ( typeof type !== "string" ) {
			data = type;
			type = "fx";
			setter--;
		}

		if ( arguments.length < setter ) {
			return jQuery.queue( this[ 0 ], type );
		}

		return data === undefined ?
			this :
			this.each( function() {
				var queue = jQuery.queue( this, type, data );

				// Ensure a hooks for this queue
				jQuery._queueHooks( this, type );

				if ( type === "fx" && queue[ 0 ] !== "inprogress" ) {
					jQuery.dequeue( this, type );
				}
			} );
	},
	dequeue: function( type ) {
		return this.each( function() {
			jQuery.dequeue( this, type );
		} );
	},
	clearQueue: function( type ) {
		return this.queue( type || "fx", [] );
	},

	// Get a promise resolved when queues of a certain type
	// are emptied (fx is the type by default)
	promise: function( type, obj ) {
		var tmp,
			count = 1,
			defer = jQuery.Deferred(),
			elements = this,
			i = this.length,
			resolve = function() {
				if ( !( --count ) ) {
					defer.resolveWith( elements, [ elements ] );
				}
			};

		if ( typeof type !== "string" ) {
			obj = type;
			type = undefined;
		}
		type = type || "fx";

		while ( i-- ) {
			tmp = dataPriv.get( elements[ i ], type + "queueHooks" );
			if ( tmp && tmp.empty ) {
				count++;
				tmp.empty.add( resolve );
			}
		}
		resolve();
		return defer.promise( obj );
	}
} );
var pnum = ( /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/ ).source;

var rcssNum = new RegExp( "^(?:([+-])=|)(" + pnum + ")([a-z%]*)$", "i" );


var cssExpand = [ "Top", "Right", "Bottom", "Left" ];

var isHiddenWithinTree = function( elem, el ) {

		// isHiddenWithinTree might be called from jQuery#filter function;
		// in that case, element will be second argument
		elem = el || elem;

		// Inline style trumps all
		return elem.style.display === "none" ||
			elem.style.display === "" &&

			// Otherwise, check computed style
			// Support: Firefox <=43 - 45
			// Disconnected elements can have computed display: none, so first confirm that elem is
			// in the document.
			jQuery.contains( elem.ownerDocument, elem ) &&

			jQuery.css( elem, "display" ) === "none";
	};

var swap = function( elem, options, callback, args ) {
	var ret, name,
		old = {};

	// Remember the old values, and insert the new ones
	for ( name in options ) {
		old[ name ] = elem.style[ name ];
		elem.style[ name ] = options[ name ];
	}

	ret = callback.apply( elem, args || [] );

	// Revert the old values
	for ( name in options ) {
		elem.style[ name ] = old[ name ];
	}

	return ret;
};




function adjustCSS( elem, prop, valueParts, tween ) {
	var adjusted,
		scale = 1,
		maxIterations = 20,
		currentValue = tween ?
			function() {
				return tween.cur();
			} :
			function() {
				return jQuery.css( elem, prop, "" );
			},
		initial = currentValue(),
		unit = valueParts && valueParts[ 3 ] || ( jQuery.cssNumber[ prop ] ? "" : "px" ),

		// Starting value computation is required for potential unit mismatches
		initialInUnit = ( jQuery.cssNumber[ prop ] || unit !== "px" && +initial ) &&
			rcssNum.exec( jQuery.css( elem, prop ) );

	if ( initialInUnit && initialInUnit[ 3 ] !== unit ) {

		// Trust units reported by jQuery.css
		unit = unit || initialInUnit[ 3 ];

		// Make sure we update the tween properties later on
		valueParts = valueParts || [];

		// Iteratively approximate from a nonzero starting point
		initialInUnit = +initial || 1;

		do {

			// If previous iteration zeroed out, double until we get *something*.
			// Use string for doubling so we don't accidentally see scale as unchanged below
			scale = scale || ".5";

			// Adjust and apply
			initialInUnit = initialInUnit / scale;
			jQuery.style( elem, prop, initialInUnit + unit );

		// Update scale, tolerating zero or NaN from tween.cur()
		// Break the loop if scale is unchanged or perfect, or if we've just had enough.
		} while (
			scale !== ( scale = currentValue() / initial ) && scale !== 1 && --maxIterations
		);
	}

	if ( valueParts ) {
		initialInUnit = +initialInUnit || +initial || 0;

		// Apply relative offset (+=/-=) if specified
		adjusted = valueParts[ 1 ] ?
			initialInUnit + ( valueParts[ 1 ] + 1 ) * valueParts[ 2 ] :
			+valueParts[ 2 ];
		if ( tween ) {
			tween.unit = unit;
			tween.start = initialInUnit;
			tween.end = adjusted;
		}
	}
	return adjusted;
}


var defaultDisplayMap = {};

function getDefaultDisplay( elem ) {
	var temp,
		doc = elem.ownerDocument,
		nodeName = elem.nodeName,
		display = defaultDisplayMap[ nodeName ];

	if ( display ) {
		return display;
	}

	temp = doc.body.appendChild( doc.createElement( nodeName ) );
	display = jQuery.css( temp, "display" );

	temp.parentNode.removeChild( temp );

	if ( display === "none" ) {
		display = "block";
	}
	defaultDisplayMap[ nodeName ] = display;

	return display;
}

function showHide( elements, show ) {
	var display, elem,
		values = [],
		index = 0,
		length = elements.length;

	// Determine new display value for elements that need to change
	for ( ; index < length; index++ ) {
		elem = elements[ index ];
		if ( !elem.style ) {
			continue;
		}

		display = elem.style.display;
		if ( show ) {

			// Since we force visibility upon cascade-hidden elements, an immediate (and slow)
			// check is required in this first loop unless we have a nonempty display value (either
			// inline or about-to-be-restored)
			if ( display === "none" ) {
				values[ index ] = dataPriv.get( elem, "display" ) || null;
				if ( !values[ index ] ) {
					elem.style.display = "";
				}
			}
			if ( elem.style.display === "" && isHiddenWithinTree( elem ) ) {
				values[ index ] = getDefaultDisplay( elem );
			}
		} else {
			if ( display !== "none" ) {
				values[ index ] = "none";

				// Remember what we're overwriting
				dataPriv.set( elem, "display", display );
			}
		}
	}

	// Set the display of the elements in a second loop to avoid constant reflow
	for ( index = 0; index < length; index++ ) {
		if ( values[ index ] != null ) {
			elements[ index ].style.display = values[ index ];
		}
	}

	return elements;
}

jQuery.fn.extend( {
	show: function() {
		return showHide( this, true );
	},
	hide: function() {
		return showHide( this );
	},
	toggle: function( state ) {
		if ( typeof state === "boolean" ) {
			return state ? this.show() : this.hide();
		}

		return this.each( function() {
			if ( isHiddenWithinTree( this ) ) {
				jQuery( this ).show();
			} else {
				jQuery( this ).hide();
			}
		} );
	}
} );
var rcheckableType = ( /^(?:checkbox|radio)$/i );

var rtagName = ( /<([a-z][^\/\0>\x20\t\r\n\f]+)/i );

var rscriptType = ( /^$|\/(?:java|ecma)script/i );



// We have to close these tags to support XHTML (#13200)
var wrapMap = {

	// Support: IE <=9 only
	option: [ 1, "<select multiple='multiple'>", "</select>" ],

	// XHTML parsers do not magically insert elements in the
	// same way that tag soup parsers do. So we cannot shorten
	// this by omitting <tbody> or other required elements.
	thead: [ 1, "<table>", "</table>" ],
	col: [ 2, "<table><colgroup>", "</colgroup></table>" ],
	tr: [ 2, "<table><tbody>", "</tbody></table>" ],
	td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],

	_default: [ 0, "", "" ]
};

// Support: IE <=9 only
wrapMap.optgroup = wrapMap.option;

wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;


function getAll( context, tag ) {

	// Support: IE <=9 - 11 only
	// Use typeof to avoid zero-argument method invocation on host objects (#15151)
	var ret;

	if ( typeof context.getElementsByTagName !== "undefined" ) {
		ret = context.getElementsByTagName( tag || "*" );

	} else if ( typeof context.querySelectorAll !== "undefined" ) {
		ret = context.querySelectorAll( tag || "*" );

	} else {
		ret = [];
	}

	if ( tag === undefined || tag && nodeName( context, tag ) ) {
		return jQuery.merge( [ context ], ret );
	}

	return ret;
}


// Mark scripts as having already been evaluated
function setGlobalEval( elems, refElements ) {
	var i = 0,
		l = elems.length;

	for ( ; i < l; i++ ) {
		dataPriv.set(
			elems[ i ],
			"globalEval",
			!refElements || dataPriv.get( refElements[ i ], "globalEval" )
		);
	}
}


var rhtml = /<|&#?\w+;/;

function buildFragment( elems, context, scripts, selection, ignored ) {
	var elem, tmp, tag, wrap, contains, j,
		fragment = context.createDocumentFragment(),
		nodes = [],
		i = 0,
		l = elems.length;

	for ( ; i < l; i++ ) {
		elem = elems[ i ];

		if ( elem || elem === 0 ) {

			// Add nodes directly
			if ( jQuery.type( elem ) === "object" ) {

				// Support: Android <=4.0 only, PhantomJS 1 only
				// push.apply(_, arraylike) throws on ancient WebKit
				jQuery.merge( nodes, elem.nodeType ? [ elem ] : elem );

			// Convert non-html into a text node
			} else if ( !rhtml.test( elem ) ) {
				nodes.push( context.createTextNode( elem ) );

			// Convert html into DOM nodes
			} else {
				tmp = tmp || fragment.appendChild( context.createElement( "div" ) );

				// Deserialize a standard representation
				tag = ( rtagName.exec( elem ) || [ "", "" ] )[ 1 ].toLowerCase();
				wrap = wrapMap[ tag ] || wrapMap._default;
				tmp.innerHTML = wrap[ 1 ] + jQuery.htmlPrefilter( elem ) + wrap[ 2 ];

				// Descend through wrappers to the right content
				j = wrap[ 0 ];
				while ( j-- ) {
					tmp = tmp.lastChild;
				}

				// Support: Android <=4.0 only, PhantomJS 1 only
				// push.apply(_, arraylike) throws on ancient WebKit
				jQuery.merge( nodes, tmp.childNodes );

				// Remember the top-level container
				tmp = fragment.firstChild;

				// Ensure the created nodes are orphaned (#12392)
				tmp.textContent = "";
			}
		}
	}

	// Remove wrapper from fragment
	fragment.textContent = "";

	i = 0;
	while ( ( elem = nodes[ i++ ] ) ) {

		// Skip elements already in the context collection (trac-4087)
		if ( selection && jQuery.inArray( elem, selection ) > -1 ) {
			if ( ignored ) {
				ignored.push( elem );
			}
			continue;
		}

		contains = jQuery.contains( elem.ownerDocument, elem );

		// Append to fragment
		tmp = getAll( fragment.appendChild( elem ), "script" );

		// Preserve script evaluation history
		if ( contains ) {
			setGlobalEval( tmp );
		}

		// Capture executables
		if ( scripts ) {
			j = 0;
			while ( ( elem = tmp[ j++ ] ) ) {
				if ( rscriptType.test( elem.type || "" ) ) {
					scripts.push( elem );
				}
			}
		}
	}

	return fragment;
}


( function() {
	var fragment = document.createDocumentFragment(),
		div = fragment.appendChild( document.createElement( "div" ) ),
		input = document.createElement( "input" );

	// Support: Android 4.0 - 4.3 only
	// Check state lost if the name is set (#11217)
	// Support: Windows Web Apps (WWA)
	// `name` and `type` must use .setAttribute for WWA (#14901)
	input.setAttribute( "type", "radio" );
	input.setAttribute( "checked", "checked" );
	input.setAttribute( "name", "t" );

	div.appendChild( input );

	// Support: Android <=4.1 only
	// Older WebKit doesn't clone checked state correctly in fragments
	support.checkClone = div.cloneNode( true ).cloneNode( true ).lastChild.checked;

	// Support: IE <=11 only
	// Make sure textarea (and checkbox) defaultValue is properly cloned
	div.innerHTML = "<textarea>x</textarea>";
	support.noCloneChecked = !!div.cloneNode( true ).lastChild.defaultValue;
} )();
var documentElement = document.documentElement;



var
	rkeyEvent = /^key/,
	rmouseEvent = /^(?:mouse|pointer|contextmenu|drag|drop)|click/,
	rtypenamespace = /^([^.]*)(?:\.(.+)|)/;

function returnTrue() {
	return true;
}

function returnFalse() {
	return false;
}

// Support: IE <=9 only
// See #13393 for more info
function safeActiveElement() {
	try {
		return document.activeElement;
	} catch ( err ) { }
}

function on( elem, types, selector, data, fn, one ) {
	var origFn, type;

	// Types can be a map of types/handlers
	if ( typeof types === "object" ) {

		// ( types-Object, selector, data )
		if ( typeof selector !== "string" ) {

			// ( types-Object, data )
			data = data || selector;
			selector = undefined;
		}
		for ( type in types ) {
			on( elem, type, selector, data, types[ type ], one );
		}
		return elem;
	}

	if ( data == null && fn == null ) {

		// ( types, fn )
		fn = selector;
		data = selector = undefined;
	} else if ( fn == null ) {
		if ( typeof selector === "string" ) {

			// ( types, selector, fn )
			fn = data;
			data = undefined;
		} else {

			// ( types, data, fn )
			fn = data;
			data = selector;
			selector = undefined;
		}
	}
	if ( fn === false ) {
		fn = returnFalse;
	} else if ( !fn ) {
		return elem;
	}

	if ( one === 1 ) {
		origFn = fn;
		fn = function( event ) {

			// Can use an empty set, since event contains the info
			jQuery().off( event );
			return origFn.apply( this, arguments );
		};

		// Use same guid so caller can remove using origFn
		fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
	}
	return elem.each( function() {
		jQuery.event.add( this, types, fn, data, selector );
	} );
}

/*
 * Helper functions for managing events -- not part of the public interface.
 * Props to Dean Edwards' addEvent library for many of the ideas.
 */
jQuery.event = {

	global: {},

	add: function( elem, types, handler, data, selector ) {

		var handleObjIn, eventHandle, tmp,
			events, t, handleObj,
			special, handlers, type, namespaces, origType,
			elemData = dataPriv.get( elem );

		// Don't attach events to noData or text/comment nodes (but allow plain objects)
		if ( !elemData ) {
			return;
		}

		// Caller can pass in an object of custom data in lieu of the handler
		if ( handler.handler ) {
			handleObjIn = handler;
			handler = handleObjIn.handler;
			selector = handleObjIn.selector;
		}

		// Ensure that invalid selectors throw exceptions at attach time
		// Evaluate against documentElement in case elem is a non-element node (e.g., document)
		if ( selector ) {
			jQuery.find.matchesSelector( documentElement, selector );
		}

		// Make sure that the handler has a unique ID, used to find/remove it later
		if ( !handler.guid ) {
			handler.guid = jQuery.guid++;
		}

		// Init the element's event structure and main handler, if this is the first
		if ( !( events = elemData.events ) ) {
			events = elemData.events = {};
		}
		if ( !( eventHandle = elemData.handle ) ) {
			eventHandle = elemData.handle = function( e ) {

				// Discard the second event of a jQuery.event.trigger() and
				// when an event is called after a page has unloaded
				return typeof jQuery !== "undefined" && jQuery.event.triggered !== e.type ?
					jQuery.event.dispatch.apply( elem, arguments ) : undefined;
			};
		}

		// Handle multiple events separated by a space
		types = ( types || "" ).match( rnothtmlwhite ) || [ "" ];
		t = types.length;
		while ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			type = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// There *must* be a type, no attaching namespace-only handlers
			if ( !type ) {
				continue;
			}

			// If event changes its type, use the special event handlers for the changed type
			special = jQuery.event.special[ type ] || {};

			// If selector defined, determine special event api type, otherwise given type
			type = ( selector ? special.delegateType : special.bindType ) || type;

			// Update special based on newly reset type
			special = jQuery.event.special[ type ] || {};

			// handleObj is passed to all event handlers
			handleObj = jQuery.extend( {
				type: type,
				origType: origType,
				data: data,
				handler: handler,
				guid: handler.guid,
				selector: selector,
				needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
				namespace: namespaces.join( "." )
			}, handleObjIn );

			// Init the event handler queue if we're the first
			if ( !( handlers = events[ type ] ) ) {
				handlers = events[ type ] = [];
				handlers.delegateCount = 0;

				// Only use addEventListener if the special events handler returns false
				if ( !special.setup ||
					special.setup.call( elem, data, namespaces, eventHandle ) === false ) {

					if ( elem.addEventListener ) {
						elem.addEventListener( type, eventHandle );
					}
				}
			}

			if ( special.add ) {
				special.add.call( elem, handleObj );

				if ( !handleObj.handler.guid ) {
					handleObj.handler.guid = handler.guid;
				}
			}

			// Add to the element's handler list, delegates in front
			if ( selector ) {
				handlers.splice( handlers.delegateCount++, 0, handleObj );
			} else {
				handlers.push( handleObj );
			}

			// Keep track of which events have ever been used, for event optimization
			jQuery.event.global[ type ] = true;
		}

	},

	// Detach an event or set of events from an element
	remove: function( elem, types, handler, selector, mappedTypes ) {

		var j, origCount, tmp,
			events, t, handleObj,
			special, handlers, type, namespaces, origType,
			elemData = dataPriv.hasData( elem ) && dataPriv.get( elem );

		if ( !elemData || !( events = elemData.events ) ) {
			return;
		}

		// Once for each type.namespace in types; type may be omitted
		types = ( types || "" ).match( rnothtmlwhite ) || [ "" ];
		t = types.length;
		while ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			type = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// Unbind all events (on this namespace, if provided) for the element
			if ( !type ) {
				for ( type in events ) {
					jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
				}
				continue;
			}

			special = jQuery.event.special[ type ] || {};
			type = ( selector ? special.delegateType : special.bindType ) || type;
			handlers = events[ type ] || [];
			tmp = tmp[ 2 ] &&
				new RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" );

			// Remove matching events
			origCount = j = handlers.length;
			while ( j-- ) {
				handleObj = handlers[ j ];

				if ( ( mappedTypes || origType === handleObj.origType ) &&
					( !handler || handler.guid === handleObj.guid ) &&
					( !tmp || tmp.test( handleObj.namespace ) ) &&
					( !selector || selector === handleObj.selector ||
						selector === "**" && handleObj.selector ) ) {
					handlers.splice( j, 1 );

					if ( handleObj.selector ) {
						handlers.delegateCount--;
					}
					if ( special.remove ) {
						special.remove.call( elem, handleObj );
					}
				}
			}

			// Remove generic event handler if we removed something and no more handlers exist
			// (avoids potential for endless recursion during removal of special event handlers)
			if ( origCount && !handlers.length ) {
				if ( !special.teardown ||
					special.teardown.call( elem, namespaces, elemData.handle ) === false ) {

					jQuery.removeEvent( elem, type, elemData.handle );
				}

				delete events[ type ];
			}
		}

		// Remove data and the expando if it's no longer used
		if ( jQuery.isEmptyObject( events ) ) {
			dataPriv.remove( elem, "handle events" );
		}
	},

	dispatch: function( nativeEvent ) {

		// Make a writable jQuery.Event from the native event object
		var event = jQuery.event.fix( nativeEvent );

		var i, j, ret, matched, handleObj, handlerQueue,
			args = new Array( arguments.length ),
			handlers = ( dataPriv.get( this, "events" ) || {} )[ event.type ] || [],
			special = jQuery.event.special[ event.type ] || {};

		// Use the fix-ed jQuery.Event rather than the (read-only) native event
		args[ 0 ] = event;

		for ( i = 1; i < arguments.length; i++ ) {
			args[ i ] = arguments[ i ];
		}

		event.delegateTarget = this;

		// Call the preDispatch hook for the mapped type, and let it bail if desired
		if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
			return;
		}

		// Determine handlers
		handlerQueue = jQuery.event.handlers.call( this, event, handlers );

		// Run delegates first; they may want to stop propagation beneath us
		i = 0;
		while ( ( matched = handlerQueue[ i++ ] ) && !event.isPropagationStopped() ) {
			event.currentTarget = matched.elem;

			j = 0;
			while ( ( handleObj = matched.handlers[ j++ ] ) &&
				!event.isImmediatePropagationStopped() ) {

				// Triggered event must either 1) have no namespace, or 2) have namespace(s)
				// a subset or equal to those in the bound event (both can have no namespace).
				if ( !event.rnamespace || event.rnamespace.test( handleObj.namespace ) ) {

					event.handleObj = handleObj;
					event.data = handleObj.data;

					ret = ( ( jQuery.event.special[ handleObj.origType ] || {} ).handle ||
						handleObj.handler ).apply( matched.elem, args );

					if ( ret !== undefined ) {
						if ( ( event.result = ret ) === false ) {
							event.preventDefault();
							event.stopPropagation();
						}
					}
				}
			}
		}

		// Call the postDispatch hook for the mapped type
		if ( special.postDispatch ) {
			special.postDispatch.call( this, event );
		}

		return event.result;
	},

	handlers: function( event, handlers ) {
		var i, handleObj, sel, matchedHandlers, matchedSelectors,
			handlerQueue = [],
			delegateCount = handlers.delegateCount,
			cur = event.target;

		// Find delegate handlers
		if ( delegateCount &&

			// Support: IE <=9
			// Black-hole SVG <use> instance trees (trac-13180)
			cur.nodeType &&

			// Support: Firefox <=42
			// Suppress spec-violating clicks indicating a non-primary pointer button (trac-3861)
			// https://www.w3.org/TR/DOM-Level-3-Events/#event-type-click
			// Support: IE 11 only
			// ...but not arrow key "clicks" of radio inputs, which can have `button` -1 (gh-2343)
			!( event.type === "click" && event.button >= 1 ) ) {

			for ( ; cur !== this; cur = cur.parentNode || this ) {

				// Don't check non-elements (#13208)
				// Don't process clicks on disabled elements (#6911, #8165, #11382, #11764)
				if ( cur.nodeType === 1 && !( event.type === "click" && cur.disabled === true ) ) {
					matchedHandlers = [];
					matchedSelectors = {};
					for ( i = 0; i < delegateCount; i++ ) {
						handleObj = handlers[ i ];

						// Don't conflict with Object.prototype properties (#13203)
						sel = handleObj.selector + " ";

						if ( matchedSelectors[ sel ] === undefined ) {
							matchedSelectors[ sel ] = handleObj.needsContext ?
								jQuery( sel, this ).index( cur ) > -1 :
								jQuery.find( sel, this, null, [ cur ] ).length;
						}
						if ( matchedSelectors[ sel ] ) {
							matchedHandlers.push( handleObj );
						}
					}
					if ( matchedHandlers.length ) {
						handlerQueue.push( { elem: cur, handlers: matchedHandlers } );
					}
				}
			}
		}

		// Add the remaining (directly-bound) handlers
		cur = this;
		if ( delegateCount < handlers.length ) {
			handlerQueue.push( { elem: cur, handlers: handlers.slice( delegateCount ) } );
		}

		return handlerQueue;
	},

	addProp: function( name, hook ) {
		Object.defineProperty( jQuery.Event.prototype, name, {
			enumerable: true,
			configurable: true,

			get: jQuery.isFunction( hook ) ?
				function() {
					if ( this.originalEvent ) {
							return hook( this.originalEvent );
					}
				} :
				function() {
					if ( this.originalEvent ) {
							return this.originalEvent[ name ];
					}
				},

			set: function( value ) {
				Object.defineProperty( this, name, {
					enumerable: true,
					configurable: true,
					writable: true,
					value: value
				} );
			}
		} );
	},

	fix: function( originalEvent ) {
		return originalEvent[ jQuery.expando ] ?
			originalEvent :
			new jQuery.Event( originalEvent );
	},

	special: {
		load: {

			// Prevent triggered image.load events from bubbling to window.load
			noBubble: true
		},
		focus: {

			// Fire native event if possible so blur/focus sequence is correct
			trigger: function() {
				if ( this !== safeActiveElement() && this.focus ) {
					this.focus();
					return false;
				}
			},
			delegateType: "focusin"
		},
		blur: {
			trigger: function() {
				if ( this === safeActiveElement() && this.blur ) {
					this.blur();
					return false;
				}
			},
			delegateType: "focusout"
		},
		click: {

			// For checkbox, fire native event so checked state will be right
			trigger: function() {
				if ( this.type === "checkbox" && this.click && nodeName( this, "input" ) ) {
					this.click();
					return false;
				}
			},

			// For cross-browser consistency, don't fire native .click() on links
			_default: function( event ) {
				return nodeName( event.target, "a" );
			}
		},

		beforeunload: {
			postDispatch: function( event ) {

				// Support: Firefox 20+
				// Firefox doesn't alert if the returnValue field is not set.
				if ( event.result !== undefined && event.originalEvent ) {
					event.originalEvent.returnValue = event.result;
				}
			}
		}
	}
};

jQuery.removeEvent = function( elem, type, handle ) {

	// This "if" is needed for plain objects
	if ( elem.removeEventListener ) {
		elem.removeEventListener( type, handle );
	}
};

jQuery.Event = function( src, props ) {

	// Allow instantiation without the 'new' keyword
	if ( !( this instanceof jQuery.Event ) ) {
		return new jQuery.Event( src, props );
	}

	// Event object
	if ( src && src.type ) {
		this.originalEvent = src;
		this.type = src.type;

		// Events bubbling up the document may have been marked as prevented
		// by a handler lower down the tree; reflect the correct value.
		this.isDefaultPrevented = src.defaultPrevented ||
				src.defaultPrevented === undefined &&

				// Support: Android <=2.3 only
				src.returnValue === false ?
			returnTrue :
			returnFalse;

		// Create target properties
		// Support: Safari <=6 - 7 only
		// Target should not be a text node (#504, #13143)
		this.target = ( src.target && src.target.nodeType === 3 ) ?
			src.target.parentNode :
			src.target;

		this.currentTarget = src.currentTarget;
		this.relatedTarget = src.relatedTarget;

	// Event type
	} else {
		this.type = src;
	}

	// Put explicitly provided properties onto the event object
	if ( props ) {
		jQuery.extend( this, props );
	}

	// Create a timestamp if incoming event doesn't have one
	this.timeStamp = src && src.timeStamp || jQuery.now();

	// Mark it as fixed
	this[ jQuery.expando ] = true;
};

// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
// https://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
jQuery.Event.prototype = {
	constructor: jQuery.Event,
	isDefaultPrevented: returnFalse,
	isPropagationStopped: returnFalse,
	isImmediatePropagationStopped: returnFalse,
	isSimulated: false,

	preventDefault: function() {
		var e = this.originalEvent;

		this.isDefaultPrevented = returnTrue;

		if ( e && !this.isSimulated ) {
			e.preventDefault();
		}
	},
	stopPropagation: function() {
		var e = this.originalEvent;

		this.isPropagationStopped = returnTrue;

		if ( e && !this.isSimulated ) {
			e.stopPropagation();
		}
	},
	stopImmediatePropagation: function() {
		var e = this.originalEvent;

		this.isImmediatePropagationStopped = returnTrue;

		if ( e && !this.isSimulated ) {
			e.stopImmediatePropagation();
		}

		this.stopPropagation();
	}
};

// Includes all common event props including KeyEvent and MouseEvent specific props
jQuery.each( {
	altKey: true,
	bubbles: true,
	cancelable: true,
	changedTouches: true,
	ctrlKey: true,
	detail: true,
	eventPhase: true,
	metaKey: true,
	pageX: true,
	pageY: true,
	shiftKey: true,
	view: true,
	"char": true,
	charCode: true,
	key: true,
	keyCode: true,
	button: true,
	buttons: true,
	clientX: true,
	clientY: true,
	offsetX: true,
	offsetY: true,
	pointerId: true,
	pointerType: true,
	screenX: true,
	screenY: true,
	targetTouches: true,
	toElement: true,
	touches: true,

	which: function( event ) {
		var button = event.button;

		// Add which for key events
		if ( event.which == null && rkeyEvent.test( event.type ) ) {
			return event.charCode != null ? event.charCode : event.keyCode;
		}

		// Add which for click: 1 === left; 2 === middle; 3 === right
		if ( !event.which && button !== undefined && rmouseEvent.test( event.type ) ) {
			if ( button & 1 ) {
				return 1;
			}

			if ( button & 2 ) {
				return 3;
			}

			if ( button & 4 ) {
				return 2;
			}

			return 0;
		}

		return event.which;
	}
}, jQuery.event.addProp );

// Create mouseenter/leave events using mouseover/out and event-time checks
// so that event delegation works in jQuery.
// Do the same for pointerenter/pointerleave and pointerover/pointerout
//
// Support: Safari 7 only
// Safari sends mouseenter too often; see:
// https://bugs.chromium.org/p/chromium/issues/detail?id=470258
// for the description of the bug (it existed in older Chrome versions as well).
jQuery.each( {
	mouseenter: "mouseover",
	mouseleave: "mouseout",
	pointerenter: "pointerover",
	pointerleave: "pointerout"
}, function( orig, fix ) {
	jQuery.event.special[ orig ] = {
		delegateType: fix,
		bindType: fix,

		handle: function( event ) {
			var ret,
				target = this,
				related = event.relatedTarget,
				handleObj = event.handleObj;

			// For mouseenter/leave call the handler if related is outside the target.
			// NB: No relatedTarget if the mouse left/entered the browser window
			if ( !related || ( related !== target && !jQuery.contains( target, related ) ) ) {
				event.type = handleObj.origType;
				ret = handleObj.handler.apply( this, arguments );
				event.type = fix;
			}
			return ret;
		}
	};
} );

jQuery.fn.extend( {

	on: function( types, selector, data, fn ) {
		return on( this, types, selector, data, fn );
	},
	one: function( types, selector, data, fn ) {
		return on( this, types, selector, data, fn, 1 );
	},
	off: function( types, selector, fn ) {
		var handleObj, type;
		if ( types && types.preventDefault && types.handleObj ) {

			// ( event )  dispatched jQuery.Event
			handleObj = types.handleObj;
			jQuery( types.delegateTarget ).off(
				handleObj.namespace ?
					handleObj.origType + "." + handleObj.namespace :
					handleObj.origType,
				handleObj.selector,
				handleObj.handler
			);
			return this;
		}
		if ( typeof types === "object" ) {

			// ( types-object [, selector] )
			for ( type in types ) {
				this.off( type, selector, types[ type ] );
			}
			return this;
		}
		if ( selector === false || typeof selector === "function" ) {

			// ( types [, fn] )
			fn = selector;
			selector = undefined;
		}
		if ( fn === false ) {
			fn = returnFalse;
		}
		return this.each( function() {
			jQuery.event.remove( this, types, fn, selector );
		} );
	}
} );


var

	/* eslint-disable max-len */

	// See https://github.com/eslint/eslint/issues/3229
	rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,

	/* eslint-enable */

	// Support: IE <=10 - 11, Edge 12 - 13
	// In IE/Edge using regex groups here causes severe slowdowns.
	// See https://connect.microsoft.com/IE/feedback/details/1736512/
	rnoInnerhtml = /<script|<style|<link/i,

	// checked="checked" or checked
	rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,
	rscriptTypeMasked = /^true\/(.*)/,
	rcleanScript = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;

// Prefer a tbody over its parent table for containing new rows
function manipulationTarget( elem, content ) {
	if ( nodeName( elem, "table" ) &&
		nodeName( content.nodeType !== 11 ? content : content.firstChild, "tr" ) ) {

		return jQuery( ">tbody", elem )[ 0 ] || elem;
	}

	return elem;
}

// Replace/restore the type attribute of script elements for safe DOM manipulation
function disableScript( elem ) {
	elem.type = ( elem.getAttribute( "type" ) !== null ) + "/" + elem.type;
	return elem;
}
function restoreScript( elem ) {
	var match = rscriptTypeMasked.exec( elem.type );

	if ( match ) {
		elem.type = match[ 1 ];
	} else {
		elem.removeAttribute( "type" );
	}

	return elem;
}

function cloneCopyEvent( src, dest ) {
	var i, l, type, pdataOld, pdataCur, udataOld, udataCur, events;

	if ( dest.nodeType !== 1 ) {
		return;
	}

	// 1. Copy private data: events, handlers, etc.
	if ( dataPriv.hasData( src ) ) {
		pdataOld = dataPriv.access( src );
		pdataCur = dataPriv.set( dest, pdataOld );
		events = pdataOld.events;

		if ( events ) {
			delete pdataCur.handle;
			pdataCur.events = {};

			for ( type in events ) {
				for ( i = 0, l = events[ type ].length; i < l; i++ ) {
					jQuery.event.add( dest, type, events[ type ][ i ] );
				}
			}
		}
	}

	// 2. Copy user data
	if ( dataUser.hasData( src ) ) {
		udataOld = dataUser.access( src );
		udataCur = jQuery.extend( {}, udataOld );

		dataUser.set( dest, udataCur );
	}
}

// Fix IE bugs, see support tests
function fixInput( src, dest ) {
	var nodeName = dest.nodeName.toLowerCase();

	// Fails to persist the checked state of a cloned checkbox or radio button.
	if ( nodeName === "input" && rcheckableType.test( src.type ) ) {
		dest.checked = src.checked;

	// Fails to return the selected option to the default selected state when cloning options
	} else if ( nodeName === "input" || nodeName === "textarea" ) {
		dest.defaultValue = src.defaultValue;
	}
}

function domManip( collection, args, callback, ignored ) {

	// Flatten any nested arrays
	args = concat.apply( [], args );

	var fragment, first, scripts, hasScripts, node, doc,
		i = 0,
		l = collection.length,
		iNoClone = l - 1,
		value = args[ 0 ],
		isFunction = jQuery.isFunction( value );

	// We can't cloneNode fragments that contain checked, in WebKit
	if ( isFunction ||
			( l > 1 && typeof value === "string" &&
				!support.checkClone && rchecked.test( value ) ) ) {
		return collection.each( function( index ) {
			var self = collection.eq( index );
			if ( isFunction ) {
				args[ 0 ] = value.call( this, index, self.html() );
			}
			domManip( self, args, callback, ignored );
		} );
	}

	if ( l ) {
		fragment = buildFragment( args, collection[ 0 ].ownerDocument, false, collection, ignored );
		first = fragment.firstChild;

		if ( fragment.childNodes.length === 1 ) {
			fragment = first;
		}

		// Require either new content or an interest in ignored elements to invoke the callback
		if ( first || ignored ) {
			scripts = jQuery.map( getAll( fragment, "script" ), disableScript );
			hasScripts = scripts.length;

			// Use the original fragment for the last item
			// instead of the first because it can end up
			// being emptied incorrectly in certain situations (#8070).
			for ( ; i < l; i++ ) {
				node = fragment;

				if ( i !== iNoClone ) {
					node = jQuery.clone( node, true, true );

					// Keep references to cloned scripts for later restoration
					if ( hasScripts ) {

						// Support: Android <=4.0 only, PhantomJS 1 only
						// push.apply(_, arraylike) throws on ancient WebKit
						jQuery.merge( scripts, getAll( node, "script" ) );
					}
				}

				callback.call( collection[ i ], node, i );
			}

			if ( hasScripts ) {
				doc = scripts[ scripts.length - 1 ].ownerDocument;

				// Reenable scripts
				jQuery.map( scripts, restoreScript );

				// Evaluate executable scripts on first document insertion
				for ( i = 0; i < hasScripts; i++ ) {
					node = scripts[ i ];
					if ( rscriptType.test( node.type || "" ) &&
						!dataPriv.access( node, "globalEval" ) &&
						jQuery.contains( doc, node ) ) {

						if ( node.src ) {

							// Optional AJAX dependency, but won't run scripts if not present
							if ( jQuery._evalUrl ) {
								jQuery._evalUrl( node.src );
							}
						} else {
							DOMEval( node.textContent.replace( rcleanScript, "" ), doc );
						}
					}
				}
			}
		}
	}

	return collection;
}

function remove( elem, selector, keepData ) {
	var node,
		nodes = selector ? jQuery.filter( selector, elem ) : elem,
		i = 0;

	for ( ; ( node = nodes[ i ] ) != null; i++ ) {
		if ( !keepData && node.nodeType === 1 ) {
			jQuery.cleanData( getAll( node ) );
		}

		if ( node.parentNode ) {
			if ( keepData && jQuery.contains( node.ownerDocument, node ) ) {
				setGlobalEval( getAll( node, "script" ) );
			}
			node.parentNode.removeChild( node );
		}
	}

	return elem;
}

jQuery.extend( {
	htmlPrefilter: function( html ) {
		return html.replace( rxhtmlTag, "<$1></$2>" );
	},

	clone: function( elem, dataAndEvents, deepDataAndEvents ) {
		var i, l, srcElements, destElements,
			clone = elem.cloneNode( true ),
			inPage = jQuery.contains( elem.ownerDocument, elem );

		// Fix IE cloning issues
		if ( !support.noCloneChecked && ( elem.nodeType === 1 || elem.nodeType === 11 ) &&
				!jQuery.isXMLDoc( elem ) ) {

			// We eschew Sizzle here for performance reasons: https://jsperf.com/getall-vs-sizzle/2
			destElements = getAll( clone );
			srcElements = getAll( elem );

			for ( i = 0, l = srcElements.length; i < l; i++ ) {
				fixInput( srcElements[ i ], destElements[ i ] );
			}
		}

		// Copy the events from the original to the clone
		if ( dataAndEvents ) {
			if ( deepDataAndEvents ) {
				srcElements = srcElements || getAll( elem );
				destElements = destElements || getAll( clone );

				for ( i = 0, l = srcElements.length; i < l; i++ ) {
					cloneCopyEvent( srcElements[ i ], destElements[ i ] );
				}
			} else {
				cloneCopyEvent( elem, clone );
			}
		}

		// Preserve script evaluation history
		destElements = getAll( clone, "script" );
		if ( destElements.length > 0 ) {
			setGlobalEval( destElements, !inPage && getAll( elem, "script" ) );
		}

		// Return the cloned set
		return clone;
	},

	cleanData: function( elems ) {
		var data, elem, type,
			special = jQuery.event.special,
			i = 0;

		for ( ; ( elem = elems[ i ] ) !== undefined; i++ ) {
			if ( acceptData( elem ) ) {
				if ( ( data = elem[ dataPriv.expando ] ) ) {
					if ( data.events ) {
						for ( type in data.events ) {
							if ( special[ type ] ) {
								jQuery.event.remove( elem, type );

							// This is a shortcut to avoid jQuery.event.remove's overhead
							} else {
								jQuery.removeEvent( elem, type, data.handle );
							}
						}
					}

					// Support: Chrome <=35 - 45+
					// Assign undefined instead of using delete, see Data#remove
					elem[ dataPriv.expando ] = undefined;
				}
				if ( elem[ dataUser.expando ] ) {

					// Support: Chrome <=35 - 45+
					// Assign undefined instead of using delete, see Data#remove
					elem[ dataUser.expando ] = undefined;
				}
			}
		}
	}
} );

jQuery.fn.extend( {
	detach: function( selector ) {
		return remove( this, selector, true );
	},

	remove: function( selector ) {
		return remove( this, selector );
	},

	text: function( value ) {
		return access( this, function( value ) {
			return value === undefined ?
				jQuery.text( this ) :
				this.empty().each( function() {
					if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
						this.textContent = value;
					}
				} );
		}, null, value, arguments.length );
	},

	append: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var target = manipulationTarget( this, elem );
				target.appendChild( elem );
			}
		} );
	},

	prepend: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var target = manipulationTarget( this, elem );
				target.insertBefore( elem, target.firstChild );
			}
		} );
	},

	before: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.parentNode ) {
				this.parentNode.insertBefore( elem, this );
			}
		} );
	},

	after: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.parentNode ) {
				this.parentNode.insertBefore( elem, this.nextSibling );
			}
		} );
	},

	empty: function() {
		var elem,
			i = 0;

		for ( ; ( elem = this[ i ] ) != null; i++ ) {
			if ( elem.nodeType === 1 ) {

				// Prevent memory leaks
				jQuery.cleanData( getAll( elem, false ) );

				// Remove any remaining nodes
				elem.textContent = "";
			}
		}

		return this;
	},

	clone: function( dataAndEvents, deepDataAndEvents ) {
		dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
		deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;

		return this.map( function() {
			return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
		} );
	},

	html: function( value ) {
		return access( this, function( value ) {
			var elem = this[ 0 ] || {},
				i = 0,
				l = this.length;

			if ( value === undefined && elem.nodeType === 1 ) {
				return elem.innerHTML;
			}

			// See if we can take a shortcut and just use innerHTML
			if ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
				!wrapMap[ ( rtagName.exec( value ) || [ "", "" ] )[ 1 ].toLowerCase() ] ) {

				value = jQuery.htmlPrefilter( value );

				try {
					for ( ; i < l; i++ ) {
						elem = this[ i ] || {};

						// Remove element nodes and prevent memory leaks
						if ( elem.nodeType === 1 ) {
							jQuery.cleanData( getAll( elem, false ) );
							elem.innerHTML = value;
						}
					}

					elem = 0;

				// If using innerHTML throws an exception, use the fallback method
				} catch ( e ) {}
			}

			if ( elem ) {
				this.empty().append( value );
			}
		}, null, value, arguments.length );
	},

	replaceWith: function() {
		var ignored = [];

		// Make the changes, replacing each non-ignored context element with the new content
		return domManip( this, arguments, function( elem ) {
			var parent = this.parentNode;

			if ( jQuery.inArray( this, ignored ) < 0 ) {
				jQuery.cleanData( getAll( this ) );
				if ( parent ) {
					parent.replaceChild( elem, this );
				}
			}

		// Force callback invocation
		}, ignored );
	}
} );

jQuery.each( {
	appendTo: "append",
	prependTo: "prepend",
	insertBefore: "before",
	insertAfter: "after",
	replaceAll: "replaceWith"
}, function( name, original ) {
	jQuery.fn[ name ] = function( selector ) {
		var elems,
			ret = [],
			insert = jQuery( selector ),
			last = insert.length - 1,
			i = 0;

		for ( ; i <= last; i++ ) {
			elems = i === last ? this : this.clone( true );
			jQuery( insert[ i ] )[ original ]( elems );

			// Support: Android <=4.0 only, PhantomJS 1 only
			// .get() because push.apply(_, arraylike) throws on ancient WebKit
			push.apply( ret, elems.get() );
		}

		return this.pushStack( ret );
	};
} );
var rmargin = ( /^margin/ );

var rnumnonpx = new RegExp( "^(" + pnum + ")(?!px)[a-z%]+$", "i" );

var getStyles = function( elem ) {

		// Support: IE <=11 only, Firefox <=30 (#15098, #14150)
		// IE throws on elements created in popups
		// FF meanwhile throws on frame elements through "defaultView.getComputedStyle"
		var view = elem.ownerDocument.defaultView;

		if ( !view || !view.opener ) {
			view = window;
		}

		return view.getComputedStyle( elem );
	};



( function() {

	// Executing both pixelPosition & boxSizingReliable tests require only one layout
	// so they're executed at the same time to save the second computation.
	function computeStyleTests() {

		// This is a singleton, we need to execute it only once
		if ( !div ) {
			return;
		}

		div.style.cssText =
			"box-sizing:border-box;" +
			"position:relative;display:block;" +
			"margin:auto;border:1px;padding:1px;" +
			"top:1%;width:50%";
		div.innerHTML = "";
		documentElement.appendChild( container );

		var divStyle = window.getComputedStyle( div );
		pixelPositionVal = divStyle.top !== "1%";

		// Support: Android 4.0 - 4.3 only, Firefox <=3 - 44
		reliableMarginLeftVal = divStyle.marginLeft === "2px";
		boxSizingReliableVal = divStyle.width === "4px";

		// Support: Android 4.0 - 4.3 only
		// Some styles come back with percentage values, even though they shouldn't
		div.style.marginRight = "50%";
		pixelMarginRightVal = divStyle.marginRight === "4px";

		documentElement.removeChild( container );

		// Nullify the div so it wouldn't be stored in the memory and
		// it will also be a sign that checks already performed
		div = null;
	}

	var pixelPositionVal, boxSizingReliableVal, pixelMarginRightVal, reliableMarginLeftVal,
		container = document.createElement( "div" ),
		div = document.createElement( "div" );

	// Finish early in limited (non-browser) environments
	if ( !div.style ) {
		return;
	}

	// Support: IE <=9 - 11 only
	// Style of cloned element affects source element cloned (#8908)
	div.style.backgroundClip = "content-box";
	div.cloneNode( true ).style.backgroundClip = "";
	support.clearCloneStyle = div.style.backgroundClip === "content-box";

	container.style.cssText = "border:0;width:8px;height:0;top:0;left:-9999px;" +
		"padding:0;margin-top:1px;position:absolute";
	container.appendChild( div );

	jQuery.extend( support, {
		pixelPosition: function() {
			computeStyleTests();
			return pixelPositionVal;
		},
		boxSizingReliable: function() {
			computeStyleTests();
			return boxSizingReliableVal;
		},
		pixelMarginRight: function() {
			computeStyleTests();
			return pixelMarginRightVal;
		},
		reliableMarginLeft: function() {
			computeStyleTests();
			return reliableMarginLeftVal;
		}
	} );
} )();


function curCSS( elem, name, computed ) {
	var width, minWidth, maxWidth, ret,

		// Support: Firefox 51+
		// Retrieving style before computed somehow
		// fixes an issue with getting wrong values
		// on detached elements
		style = elem.style;

	computed = computed || getStyles( elem );

	// getPropertyValue is needed for:
	//   .css('filter') (IE 9 only, #12537)
	//   .css('--customProperty) (#3144)
	if ( computed ) {
		ret = computed.getPropertyValue( name ) || computed[ name ];

		if ( ret === "" && !jQuery.contains( elem.ownerDocument, elem ) ) {
			ret = jQuery.style( elem, name );
		}

		// A tribute to the "awesome hack by Dean Edwards"
		// Android Browser returns percentage for some values,
		// but width seems to be reliably pixels.
		// This is against the CSSOM draft spec:
		// https://drafts.csswg.org/cssom/#resolved-values
		if ( !support.pixelMarginRight() && rnumnonpx.test( ret ) && rmargin.test( name ) ) {

			// Remember the original values
			width = style.width;
			minWidth = style.minWidth;
			maxWidth = style.maxWidth;

			// Put in the new values to get a computed value out
			style.minWidth = style.maxWidth = style.width = ret;
			ret = computed.width;

			// Revert the changed values
			style.width = width;
			style.minWidth = minWidth;
			style.maxWidth = maxWidth;
		}
	}

	return ret !== undefined ?

		// Support: IE <=9 - 11 only
		// IE returns zIndex value as an integer.
		ret + "" :
		ret;
}


function addGetHookIf( conditionFn, hookFn ) {

	// Define the hook, we'll check on the first run if it's really needed.
	return {
		get: function() {
			if ( conditionFn() ) {

				// Hook not needed (or it's not possible to use it due
				// to missing dependency), remove it.
				delete this.get;
				return;
			}

			// Hook needed; redefine it so that the support test is not executed again.
			return ( this.get = hookFn ).apply( this, arguments );
		}
	};
}


var

	// Swappable if display is none or starts with table
	// except "table", "table-cell", or "table-caption"
	// See here for display values: https://developer.mozilla.org/en-US/docs/CSS/display
	rdisplayswap = /^(none|table(?!-c[ea]).+)/,
	rcustomProp = /^--/,
	cssShow = { position: "absolute", visibility: "hidden", display: "block" },
	cssNormalTransform = {
		letterSpacing: "0",
		fontWeight: "400"
	},

	cssPrefixes = [ "Webkit", "Moz", "ms" ],
	emptyStyle = document.createElement( "div" ).style;

// Return a css property mapped to a potentially vendor prefixed property
function vendorPropName( name ) {

	// Shortcut for names that are not vendor prefixed
	if ( name in emptyStyle ) {
		return name;
	}

	// Check for vendor prefixed names
	var capName = name[ 0 ].toUpperCase() + name.slice( 1 ),
		i = cssPrefixes.length;

	while ( i-- ) {
		name = cssPrefixes[ i ] + capName;
		if ( name in emptyStyle ) {
			return name;
		}
	}
}

// Return a property mapped along what jQuery.cssProps suggests or to
// a vendor prefixed property.
function finalPropName( name ) {
	var ret = jQuery.cssProps[ name ];
	if ( !ret ) {
		ret = jQuery.cssProps[ name ] = vendorPropName( name ) || name;
	}
	return ret;
}

function setPositiveNumber( elem, value, subtract ) {

	// Any relative (+/-) values have already been
	// normalized at this point
	var matches = rcssNum.exec( value );
	return matches ?

		// Guard against undefined "subtract", e.g., when used as in cssHooks
		Math.max( 0, matches[ 2 ] - ( subtract || 0 ) ) + ( matches[ 3 ] || "px" ) :
		value;
}

function augmentWidthOrHeight( elem, name, extra, isBorderBox, styles ) {
	var i,
		val = 0;

	// If we already have the right measurement, avoid augmentation
	if ( extra === ( isBorderBox ? "border" : "content" ) ) {
		i = 4;

	// Otherwise initialize for horizontal or vertical properties
	} else {
		i = name === "width" ? 1 : 0;
	}

	for ( ; i < 4; i += 2 ) {

		// Both box models exclude margin, so add it if we want it
		if ( extra === "margin" ) {
			val += jQuery.css( elem, extra + cssExpand[ i ], true, styles );
		}

		if ( isBorderBox ) {

			// border-box includes padding, so remove it if we want content
			if ( extra === "content" ) {
				val -= jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );
			}

			// At this point, extra isn't border nor margin, so remove border
			if ( extra !== "margin" ) {
				val -= jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}
		} else {

			// At this point, extra isn't content, so add padding
			val += jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );

			// At this point, extra isn't content nor padding, so add border
			if ( extra !== "padding" ) {
				val += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}
		}
	}

	return val;
}

function getWidthOrHeight( elem, name, extra ) {

	// Start with computed style
	var valueIsBorderBox,
		styles = getStyles( elem ),
		val = curCSS( elem, name, styles ),
		isBorderBox = jQuery.css( elem, "boxSizing", false, styles ) === "border-box";

	// Computed unit is not pixels. Stop here and return.
	if ( rnumnonpx.test( val ) ) {
		return val;
	}

	// Check for style in case a browser which returns unreliable values
	// for getComputedStyle silently falls back to the reliable elem.style
	valueIsBorderBox = isBorderBox &&
		( support.boxSizingReliable() || val === elem.style[ name ] );

	// Fall back to offsetWidth/Height when value is "auto"
	// This happens for inline elements with no explicit setting (gh-3571)
	if ( val === "auto" ) {
		val = elem[ "offset" + name[ 0 ].toUpperCase() + name.slice( 1 ) ];
	}

	// Normalize "", auto, and prepare for extra
	val = parseFloat( val ) || 0;

	// Use the active box-sizing model to add/subtract irrelevant styles
	return ( val +
		augmentWidthOrHeight(
			elem,
			name,
			extra || ( isBorderBox ? "border" : "content" ),
			valueIsBorderBox,
			styles
		)
	) + "px";
}

jQuery.extend( {

	// Add in style property hooks for overriding the default
	// behavior of getting and setting a style property
	cssHooks: {
		opacity: {
			get: function( elem, computed ) {
				if ( computed ) {

					// We should always get a number back from opacity
					var ret = curCSS( elem, "opacity" );
					return ret === "" ? "1" : ret;
				}
			}
		}
	},

	// Don't automatically add "px" to these possibly-unitless properties
	cssNumber: {
		"animationIterationCount": true,
		"columnCount": true,
		"fillOpacity": true,
		"flexGrow": true,
		"flexShrink": true,
		"fontWeight": true,
		"lineHeight": true,
		"opacity": true,
		"order": true,
		"orphans": true,
		"widows": true,
		"zIndex": true,
		"zoom": true
	},

	// Add in properties whose names you wish to fix before
	// setting or getting the value
	cssProps: {
		"float": "cssFloat"
	},

	// Get and set the style property on a DOM Node
	style: function( elem, name, value, extra ) {

		// Don't set styles on text and comment nodes
		if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
			return;
		}

		// Make sure that we're working with the right name
		var ret, type, hooks,
			origName = jQuery.camelCase( name ),
			isCustomProp = rcustomProp.test( name ),
			style = elem.style;

		// Make sure that we're working with the right name. We don't
		// want to query the value if it is a CSS custom property
		// since they are user-defined.
		if ( !isCustomProp ) {
			name = finalPropName( origName );
		}

		// Gets hook for the prefixed version, then unprefixed version
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// Check if we're setting a value
		if ( value !== undefined ) {
			type = typeof value;

			// Convert "+=" or "-=" to relative numbers (#7345)
			if ( type === "string" && ( ret = rcssNum.exec( value ) ) && ret[ 1 ] ) {
				value = adjustCSS( elem, name, ret );

				// Fixes bug #9237
				type = "number";
			}

			// Make sure that null and NaN values aren't set (#7116)
			if ( value == null || value !== value ) {
				return;
			}

			// If a number was passed in, add the unit (except for certain CSS properties)
			if ( type === "number" ) {
				value += ret && ret[ 3 ] || ( jQuery.cssNumber[ origName ] ? "" : "px" );
			}

			// background-* props affect original clone's values
			if ( !support.clearCloneStyle && value === "" && name.indexOf( "background" ) === 0 ) {
				style[ name ] = "inherit";
			}

			// If a hook was provided, use that value, otherwise just set the specified value
			if ( !hooks || !( "set" in hooks ) ||
				( value = hooks.set( elem, value, extra ) ) !== undefined ) {

				if ( isCustomProp ) {
					style.setProperty( name, value );
				} else {
					style[ name ] = value;
				}
			}

		} else {

			// If a hook was provided get the non-computed value from there
			if ( hooks && "get" in hooks &&
				( ret = hooks.get( elem, false, extra ) ) !== undefined ) {

				return ret;
			}

			// Otherwise just get the value from the style object
			return style[ name ];
		}
	},

	css: function( elem, name, extra, styles ) {
		var val, num, hooks,
			origName = jQuery.camelCase( name ),
			isCustomProp = rcustomProp.test( name );

		// Make sure that we're working with the right name. We don't
		// want to modify the value if it is a CSS custom property
		// since they are user-defined.
		if ( !isCustomProp ) {
			name = finalPropName( origName );
		}

		// Try prefixed name followed by the unprefixed name
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// If a hook was provided get the computed value from there
		if ( hooks && "get" in hooks ) {
			val = hooks.get( elem, true, extra );
		}

		// Otherwise, if a way to get the computed value exists, use that
		if ( val === undefined ) {
			val = curCSS( elem, name, styles );
		}

		// Convert "normal" to computed value
		if ( val === "normal" && name in cssNormalTransform ) {
			val = cssNormalTransform[ name ];
		}

		// Make numeric if forced or a qualifier was provided and val looks numeric
		if ( extra === "" || extra ) {
			num = parseFloat( val );
			return extra === true || isFinite( num ) ? num || 0 : val;
		}

		return val;
	}
} );

jQuery.each( [ "height", "width" ], function( i, name ) {
	jQuery.cssHooks[ name ] = {
		get: function( elem, computed, extra ) {
			if ( computed ) {

				// Certain elements can have dimension info if we invisibly show them
				// but it must have a current display style that would benefit
				return rdisplayswap.test( jQuery.css( elem, "display" ) ) &&

					// Support: Safari 8+
					// Table columns in Safari have non-zero offsetWidth & zero
					// getBoundingClientRect().width unless display is changed.
					// Support: IE <=11 only
					// Running getBoundingClientRect on a disconnected node
					// in IE throws an error.
					( !elem.getClientRects().length || !elem.getBoundingClientRect().width ) ?
						swap( elem, cssShow, function() {
							return getWidthOrHeight( elem, name, extra );
						} ) :
						getWidthOrHeight( elem, name, extra );
			}
		},

		set: function( elem, value, extra ) {
			var matches,
				styles = extra && getStyles( elem ),
				subtract = extra && augmentWidthOrHeight(
					elem,
					name,
					extra,
					jQuery.css( elem, "boxSizing", false, styles ) === "border-box",
					styles
				);

			// Convert to pixels if value adjustment is needed
			if ( subtract && ( matches = rcssNum.exec( value ) ) &&
				( matches[ 3 ] || "px" ) !== "px" ) {

				elem.style[ name ] = value;
				value = jQuery.css( elem, name );
			}

			return setPositiveNumber( elem, value, subtract );
		}
	};
} );

jQuery.cssHooks.marginLeft = addGetHookIf( support.reliableMarginLeft,
	function( elem, computed ) {
		if ( computed ) {
			return ( parseFloat( curCSS( elem, "marginLeft" ) ) ||
				elem.getBoundingClientRect().left -
					swap( elem, { marginLeft: 0 }, function() {
						return elem.getBoundingClientRect().left;
					} )
				) + "px";
		}
	}
);

// These hooks are used by animate to expand properties
jQuery.each( {
	margin: "",
	padding: "",
	border: "Width"
}, function( prefix, suffix ) {
	jQuery.cssHooks[ prefix + suffix ] = {
		expand: function( value ) {
			var i = 0,
				expanded = {},

				// Assumes a single number if not a string
				parts = typeof value === "string" ? value.split( " " ) : [ value ];

			for ( ; i < 4; i++ ) {
				expanded[ prefix + cssExpand[ i ] + suffix ] =
					parts[ i ] || parts[ i - 2 ] || parts[ 0 ];
			}

			return expanded;
		}
	};

	if ( !rmargin.test( prefix ) ) {
		jQuery.cssHooks[ prefix + suffix ].set = setPositiveNumber;
	}
} );

jQuery.fn.extend( {
	css: function( name, value ) {
		return access( this, function( elem, name, value ) {
			var styles, len,
				map = {},
				i = 0;

			if ( Array.isArray( name ) ) {
				styles = getStyles( elem );
				len = name.length;

				for ( ; i < len; i++ ) {
					map[ name[ i ] ] = jQuery.css( elem, name[ i ], false, styles );
				}

				return map;
			}

			return value !== undefined ?
				jQuery.style( elem, name, value ) :
				jQuery.css( elem, name );
		}, name, value, arguments.length > 1 );
	}
} );


function Tween( elem, options, prop, end, easing ) {
	return new Tween.prototype.init( elem, options, prop, end, easing );
}
jQuery.Tween = Tween;

Tween.prototype = {
	constructor: Tween,
	init: function( elem, options, prop, end, easing, unit ) {
		this.elem = elem;
		this.prop = prop;
		this.easing = easing || jQuery.easing._default;
		this.options = options;
		this.start = this.now = this.cur();
		this.end = end;
		this.unit = unit || ( jQuery.cssNumber[ prop ] ? "" : "px" );
	},
	cur: function() {
		var hooks = Tween.propHooks[ this.prop ];

		return hooks && hooks.get ?
			hooks.get( this ) :
			Tween.propHooks._default.get( this );
	},
	run: function( percent ) {
		var eased,
			hooks = Tween.propHooks[ this.prop ];

		if ( this.options.duration ) {
			this.pos = eased = jQuery.easing[ this.easing ](
				percent, this.options.duration * percent, 0, 1, this.options.duration
			);
		} else {
			this.pos = eased = percent;
		}
		this.now = ( this.end - this.start ) * eased + this.start;

		if ( this.options.step ) {
			this.options.step.call( this.elem, this.now, this );
		}

		if ( hooks && hooks.set ) {
			hooks.set( this );
		} else {
			Tween.propHooks._default.set( this );
		}
		return this;
	}
};

Tween.prototype.init.prototype = Tween.prototype;

Tween.propHooks = {
	_default: {
		get: function( tween ) {
			var result;

			// Use a property on the element directly when it is not a DOM element,
			// or when there is no matching style property that exists.
			if ( tween.elem.nodeType !== 1 ||
				tween.elem[ tween.prop ] != null && tween.elem.style[ tween.prop ] == null ) {
				return tween.elem[ tween.prop ];
			}

			// Passing an empty string as a 3rd parameter to .css will automatically
			// attempt a parseFloat and fallback to a string if the parse fails.
			// Simple values such as "10px" are parsed to Float;
			// complex values such as "rotate(1rad)" are returned as-is.
			result = jQuery.css( tween.elem, tween.prop, "" );

			// Empty strings, null, undefined and "auto" are converted to 0.
			return !result || result === "auto" ? 0 : result;
		},
		set: function( tween ) {

			// Use step hook for back compat.
			// Use cssHook if its there.
			// Use .style if available and use plain properties where available.
			if ( jQuery.fx.step[ tween.prop ] ) {
				jQuery.fx.step[ tween.prop ]( tween );
			} else if ( tween.elem.nodeType === 1 &&
				( tween.elem.style[ jQuery.cssProps[ tween.prop ] ] != null ||
					jQuery.cssHooks[ tween.prop ] ) ) {
				jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
			} else {
				tween.elem[ tween.prop ] = tween.now;
			}
		}
	}
};

// Support: IE <=9 only
// Panic based approach to setting things on disconnected nodes
Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
	set: function( tween ) {
		if ( tween.elem.nodeType && tween.elem.parentNode ) {
			tween.elem[ tween.prop ] = tween.now;
		}
	}
};

jQuery.easing = {
	linear: function( p ) {
		return p;
	},
	swing: function( p ) {
		return 0.5 - Math.cos( p * Math.PI ) / 2;
	},
	_default: "swing"
};

jQuery.fx = Tween.prototype.init;

// Back compat <1.8 extension point
jQuery.fx.step = {};




var
	fxNow, inProgress,
	rfxtypes = /^(?:toggle|show|hide)$/,
	rrun = /queueHooks$/;

function schedule() {
	if ( inProgress ) {
		if ( document.hidden === false && window.requestAnimationFrame ) {
			window.requestAnimationFrame( schedule );
		} else {
			window.setTimeout( schedule, jQuery.fx.interval );
		}

		jQuery.fx.tick();
	}
}

// Animations created synchronously will run synchronously
function createFxNow() {
	window.setTimeout( function() {
		fxNow = undefined;
	} );
	return ( fxNow = jQuery.now() );
}

// Generate parameters to create a standard animation
function genFx( type, includeWidth ) {
	var which,
		i = 0,
		attrs = { height: type };

	// If we include width, step value is 1 to do all cssExpand values,
	// otherwise step value is 2 to skip over Left and Right
	includeWidth = includeWidth ? 1 : 0;
	for ( ; i < 4; i += 2 - includeWidth ) {
		which = cssExpand[ i ];
		attrs[ "margin" + which ] = attrs[ "padding" + which ] = type;
	}

	if ( includeWidth ) {
		attrs.opacity = attrs.width = type;
	}

	return attrs;
}

function createTween( value, prop, animation ) {
	var tween,
		collection = ( Animation.tweeners[ prop ] || [] ).concat( Animation.tweeners[ "*" ] ),
		index = 0,
		length = collection.length;
	for ( ; index < length; index++ ) {
		if ( ( tween = collection[ index ].call( animation, prop, value ) ) ) {

			// We're done with this property
			return tween;
		}
	}
}

function defaultPrefilter( elem, props, opts ) {
	var prop, value, toggle, hooks, oldfire, propTween, restoreDisplay, display,
		isBox = "width" in props || "height" in props,
		anim = this,
		orig = {},
		style = elem.style,
		hidden = elem.nodeType && isHiddenWithinTree( elem ),
		dataShow = dataPriv.get( elem, "fxshow" );

	// Queue-skipping animations hijack the fx hooks
	if ( !opts.queue ) {
		hooks = jQuery._queueHooks( elem, "fx" );
		if ( hooks.unqueued == null ) {
			hooks.unqueued = 0;
			oldfire = hooks.empty.fire;
			hooks.empty.fire = function() {
				if ( !hooks.unqueued ) {
					oldfire();
				}
			};
		}
		hooks.unqueued++;

		anim.always( function() {

			// Ensure the complete handler is called before this completes
			anim.always( function() {
				hooks.unqueued--;
				if ( !jQuery.queue( elem, "fx" ).length ) {
					hooks.empty.fire();
				}
			} );
		} );
	}

	// Detect show/hide animations
	for ( prop in props ) {
		value = props[ prop ];
		if ( rfxtypes.test( value ) ) {
			delete props[ prop ];
			toggle = toggle || value === "toggle";
			if ( value === ( hidden ? "hide" : "show" ) ) {

				// Pretend to be hidden if this is a "show" and
				// there is still data from a stopped show/hide
				if ( value === "show" && dataShow && dataShow[ prop ] !== undefined ) {
					hidden = true;

				// Ignore all other no-op show/hide data
				} else {
					continue;
				}
			}
			orig[ prop ] = dataShow && dataShow[ prop ] || jQuery.style( elem, prop );
		}
	}

	// Bail out if this is a no-op like .hide().hide()
	propTween = !jQuery.isEmptyObject( props );
	if ( !propTween && jQuery.isEmptyObject( orig ) ) {
		return;
	}

	// Restrict "overflow" and "display" styles during box animations
	if ( isBox && elem.nodeType === 1 ) {

		// Support: IE <=9 - 11, Edge 12 - 13
		// Record all 3 overflow attributes because IE does not infer the shorthand
		// from identically-valued overflowX and overflowY
		opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];

		// Identify a display type, preferring old show/hide data over the CSS cascade
		restoreDisplay = dataShow && dataShow.display;
		if ( restoreDisplay == null ) {
			restoreDisplay = dataPriv.get( elem, "display" );
		}
		display = jQuery.css( elem, "display" );
		if ( display === "none" ) {
			if ( restoreDisplay ) {
				display = restoreDisplay;
			} else {

				// Get nonempty value(s) by temporarily forcing visibility
				showHide( [ elem ], true );
				restoreDisplay = elem.style.display || restoreDisplay;
				display = jQuery.css( elem, "display" );
				showHide( [ elem ] );
			}
		}

		// Animate inline elements as inline-block
		if ( display === "inline" || display === "inline-block" && restoreDisplay != null ) {
			if ( jQuery.css( elem, "float" ) === "none" ) {

				// Restore the original display value at the end of pure show/hide animations
				if ( !propTween ) {
					anim.done( function() {
						style.display = restoreDisplay;
					} );
					if ( restoreDisplay == null ) {
						display = style.display;
						restoreDisplay = display === "none" ? "" : display;
					}
				}
				style.display = "inline-block";
			}
		}
	}

	if ( opts.overflow ) {
		style.overflow = "hidden";
		anim.always( function() {
			style.overflow = opts.overflow[ 0 ];
			style.overflowX = opts.overflow[ 1 ];
			style.overflowY = opts.overflow[ 2 ];
		} );
	}

	// Implement show/hide animations
	propTween = false;
	for ( prop in orig ) {

		// General show/hide setup for this element animation
		if ( !propTween ) {
			if ( dataShow ) {
				if ( "hidden" in dataShow ) {
					hidden = dataShow.hidden;
				}
			} else {
				dataShow = dataPriv.access( elem, "fxshow", { display: restoreDisplay } );
			}

			// Store hidden/visible for toggle so `.stop().toggle()` "reverses"
			if ( toggle ) {
				dataShow.hidden = !hidden;
			}

			// Show elements before animating them
			if ( hidden ) {
				showHide( [ elem ], true );
			}

			/* eslint-disable no-loop-func */

			anim.done( function() {

			/* eslint-enable no-loop-func */

				// The final step of a "hide" animation is actually hiding the element
				if ( !hidden ) {
					showHide( [ elem ] );
				}
				dataPriv.remove( elem, "fxshow" );
				for ( prop in orig ) {
					jQuery.style( elem, prop, orig[ prop ] );
				}
			} );
		}

		// Per-property setup
		propTween = createTween( hidden ? dataShow[ prop ] : 0, prop, anim );
		if ( !( prop in dataShow ) ) {
			dataShow[ prop ] = propTween.start;
			if ( hidden ) {
				propTween.end = propTween.start;
				propTween.start = 0;
			}
		}
	}
}

function propFilter( props, specialEasing ) {
	var index, name, easing, value, hooks;

	// camelCase, specialEasing and expand cssHook pass
	for ( index in props ) {
		name = jQuery.camelCase( index );
		easing = specialEasing[ name ];
		value = props[ index ];
		if ( Array.isArray( value ) ) {
			easing = value[ 1 ];
			value = props[ index ] = value[ 0 ];
		}

		if ( index !== name ) {
			props[ name ] = value;
			delete props[ index ];
		}

		hooks = jQuery.cssHooks[ name ];
		if ( hooks && "expand" in hooks ) {
			value = hooks.expand( value );
			delete props[ name ];

			// Not quite $.extend, this won't overwrite existing keys.
			// Reusing 'index' because we have the correct "name"
			for ( index in value ) {
				if ( !( index in props ) ) {
					props[ index ] = value[ index ];
					specialEasing[ index ] = easing;
				}
			}
		} else {
			specialEasing[ name ] = easing;
		}
	}
}

function Animation( elem, properties, options ) {
	var result,
		stopped,
		index = 0,
		length = Animation.prefilters.length,
		deferred = jQuery.Deferred().always( function() {

			// Don't match elem in the :animated selector
			delete tick.elem;
		} ),
		tick = function() {
			if ( stopped ) {
				return false;
			}
			var currentTime = fxNow || createFxNow(),
				remaining = Math.max( 0, animation.startTime + animation.duration - currentTime ),

				// Support: Android 2.3 only
				// Archaic crash bug won't allow us to use `1 - ( 0.5 || 0 )` (#12497)
				temp = remaining / animation.duration || 0,
				percent = 1 - temp,
				index = 0,
				length = animation.tweens.length;

			for ( ; index < length; index++ ) {
				animation.tweens[ index ].run( percent );
			}

			deferred.notifyWith( elem, [ animation, percent, remaining ] );

			// If there's more to do, yield
			if ( percent < 1 && length ) {
				return remaining;
			}

			// If this was an empty animation, synthesize a final progress notification
			if ( !length ) {
				deferred.notifyWith( elem, [ animation, 1, 0 ] );
			}

			// Resolve the animation and report its conclusion
			deferred.resolveWith( elem, [ animation ] );
			return false;
		},
		animation = deferred.promise( {
			elem: elem,
			props: jQuery.extend( {}, properties ),
			opts: jQuery.extend( true, {
				specialEasing: {},
				easing: jQuery.easing._default
			}, options ),
			originalProperties: properties,
			originalOptions: options,
			startTime: fxNow || createFxNow(),
			duration: options.duration,
			tweens: [],
			createTween: function( prop, end ) {
				var tween = jQuery.Tween( elem, animation.opts, prop, end,
						animation.opts.specialEasing[ prop ] || animation.opts.easing );
				animation.tweens.push( tween );
				return tween;
			},
			stop: function( gotoEnd ) {
				var index = 0,

					// If we are going to the end, we want to run all the tweens
					// otherwise we skip this part
					length = gotoEnd ? animation.tweens.length : 0;
				if ( stopped ) {
					return this;
				}
				stopped = true;
				for ( ; index < length; index++ ) {
					animation.tweens[ index ].run( 1 );
				}

				// Resolve when we played the last frame; otherwise, reject
				if ( gotoEnd ) {
					deferred.notifyWith( elem, [ animation, 1, 0 ] );
					deferred.resolveWith( elem, [ animation, gotoEnd ] );
				} else {
					deferred.rejectWith( elem, [ animation, gotoEnd ] );
				}
				return this;
			}
		} ),
		props = animation.props;

	propFilter( props, animation.opts.specialEasing );

	for ( ; index < length; index++ ) {
		result = Animation.prefilters[ index ].call( animation, elem, props, animation.opts );
		if ( result ) {
			if ( jQuery.isFunction( result.stop ) ) {
				jQuery._queueHooks( animation.elem, animation.opts.queue ).stop =
					jQuery.proxy( result.stop, result );
			}
			return result;
		}
	}

	jQuery.map( props, createTween, animation );

	if ( jQuery.isFunction( animation.opts.start ) ) {
		animation.opts.start.call( elem, animation );
	}

	// Attach callbacks from options
	animation
		.progress( animation.opts.progress )
		.done( animation.opts.done, animation.opts.complete )
		.fail( animation.opts.fail )
		.always( animation.opts.always );

	jQuery.fx.timer(
		jQuery.extend( tick, {
			elem: elem,
			anim: animation,
			queue: animation.opts.queue
		} )
	);

	return animation;
}

jQuery.Animation = jQuery.extend( Animation, {

	tweeners: {
		"*": [ function( prop, value ) {
			var tween = this.createTween( prop, value );
			adjustCSS( tween.elem, prop, rcssNum.exec( value ), tween );
			return tween;
		} ]
	},

	tweener: function( props, callback ) {
		if ( jQuery.isFunction( props ) ) {
			callback = props;
			props = [ "*" ];
		} else {
			props = props.match( rnothtmlwhite );
		}

		var prop,
			index = 0,
			length = props.length;

		for ( ; index < length; index++ ) {
			prop = props[ index ];
			Animation.tweeners[ prop ] = Animation.tweeners[ prop ] || [];
			Animation.tweeners[ prop ].unshift( callback );
		}
	},

	prefilters: [ defaultPrefilter ],

	prefilter: function( callback, prepend ) {
		if ( prepend ) {
			Animation.prefilters.unshift( callback );
		} else {
			Animation.prefilters.push( callback );
		}
	}
} );

jQuery.speed = function( speed, easing, fn ) {
	var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
		complete: fn || !fn && easing ||
			jQuery.isFunction( speed ) && speed,
		duration: speed,
		easing: fn && easing || easing && !jQuery.isFunction( easing ) && easing
	};

	// Go to the end state if fx are off
	if ( jQuery.fx.off ) {
		opt.duration = 0;

	} else {
		if ( typeof opt.duration !== "number" ) {
			if ( opt.duration in jQuery.fx.speeds ) {
				opt.duration = jQuery.fx.speeds[ opt.duration ];

			} else {
				opt.duration = jQuery.fx.speeds._default;
			}
		}
	}

	// Normalize opt.queue - true/undefined/null -> "fx"
	if ( opt.queue == null || opt.queue === true ) {
		opt.queue = "fx";
	}

	// Queueing
	opt.old = opt.complete;

	opt.complete = function() {
		if ( jQuery.isFunction( opt.old ) ) {
			opt.old.call( this );
		}

		if ( opt.queue ) {
			jQuery.dequeue( this, opt.queue );
		}
	};

	return opt;
};

jQuery.fn.extend( {
	fadeTo: function( speed, to, easing, callback ) {

		// Show any hidden elements after setting opacity to 0
		return this.filter( isHiddenWithinTree ).css( "opacity", 0 ).show()

			// Animate to the value specified
			.end().animate( { opacity: to }, speed, easing, callback );
	},
	animate: function( prop, speed, easing, callback ) {
		var empty = jQuery.isEmptyObject( prop ),
			optall = jQuery.speed( speed, easing, callback ),
			doAnimation = function() {

				// Operate on a copy of prop so per-property easing won't be lost
				var anim = Animation( this, jQuery.extend( {}, prop ), optall );

				// Empty animations, or finishing resolves immediately
				if ( empty || dataPriv.get( this, "finish" ) ) {
					anim.stop( true );
				}
			};
			doAnimation.finish = doAnimation;

		return empty || optall.queue === false ?
			this.each( doAnimation ) :
			this.queue( optall.queue, doAnimation );
	},
	stop: function( type, clearQueue, gotoEnd ) {
		var stopQueue = function( hooks ) {
			var stop = hooks.stop;
			delete hooks.stop;
			stop( gotoEnd );
		};

		if ( typeof type !== "string" ) {
			gotoEnd = clearQueue;
			clearQueue = type;
			type = undefined;
		}
		if ( clearQueue && type !== false ) {
			this.queue( type || "fx", [] );
		}

		return this.each( function() {
			var dequeue = true,
				index = type != null && type + "queueHooks",
				timers = jQuery.timers,
				data = dataPriv.get( this );

			if ( index ) {
				if ( data[ index ] && data[ index ].stop ) {
					stopQueue( data[ index ] );
				}
			} else {
				for ( index in data ) {
					if ( data[ index ] && data[ index ].stop && rrun.test( index ) ) {
						stopQueue( data[ index ] );
					}
				}
			}

			for ( index = timers.length; index--; ) {
				if ( timers[ index ].elem === this &&
					( type == null || timers[ index ].queue === type ) ) {

					timers[ index ].anim.stop( gotoEnd );
					dequeue = false;
					timers.splice( index, 1 );
				}
			}

			// Start the next in the queue if the last step wasn't forced.
			// Timers currently will call their complete callbacks, which
			// will dequeue but only if they were gotoEnd.
			if ( dequeue || !gotoEnd ) {
				jQuery.dequeue( this, type );
			}
		} );
	},
	finish: function( type ) {
		if ( type !== false ) {
			type = type || "fx";
		}
		return this.each( function() {
			var index,
				data = dataPriv.get( this ),
				queue = data[ type + "queue" ],
				hooks = data[ type + "queueHooks" ],
				timers = jQuery.timers,
				length = queue ? queue.length : 0;

			// Enable finishing flag on private data
			data.finish = true;

			// Empty the queue first
			jQuery.queue( this, type, [] );

			if ( hooks && hooks.stop ) {
				hooks.stop.call( this, true );
			}

			// Look for any active animations, and finish them
			for ( index = timers.length; index--; ) {
				if ( timers[ index ].elem === this && timers[ index ].queue === type ) {
					timers[ index ].anim.stop( true );
					timers.splice( index, 1 );
				}
			}

			// Look for any animations in the old queue and finish them
			for ( index = 0; index < length; index++ ) {
				if ( queue[ index ] && queue[ index ].finish ) {
					queue[ index ].finish.call( this );
				}
			}

			// Turn off finishing flag
			delete data.finish;
		} );
	}
} );

jQuery.each( [ "toggle", "show", "hide" ], function( i, name ) {
	var cssFn = jQuery.fn[ name ];
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return speed == null || typeof speed === "boolean" ?
			cssFn.apply( this, arguments ) :
			this.animate( genFx( name, true ), speed, easing, callback );
	};
} );

// Generate shortcuts for custom animations
jQuery.each( {
	slideDown: genFx( "show" ),
	slideUp: genFx( "hide" ),
	slideToggle: genFx( "toggle" ),
	fadeIn: { opacity: "show" },
	fadeOut: { opacity: "hide" },
	fadeToggle: { opacity: "toggle" }
}, function( name, props ) {
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return this.animate( props, speed, easing, callback );
	};
} );

jQuery.timers = [];
jQuery.fx.tick = function() {
	var timer,
		i = 0,
		timers = jQuery.timers;

	fxNow = jQuery.now();

	for ( ; i < timers.length; i++ ) {
		timer = timers[ i ];

		// Run the timer and safely remove it when done (allowing for external removal)
		if ( !timer() && timers[ i ] === timer ) {
			timers.splice( i--, 1 );
		}
	}

	if ( !timers.length ) {
		jQuery.fx.stop();
	}
	fxNow = undefined;
};

jQuery.fx.timer = function( timer ) {
	jQuery.timers.push( timer );
	jQuery.fx.start();
};

jQuery.fx.interval = 13;
jQuery.fx.start = function() {
	if ( inProgress ) {
		return;
	}

	inProgress = true;
	schedule();
};

jQuery.fx.stop = function() {
	inProgress = null;
};

jQuery.fx.speeds = {
	slow: 600,
	fast: 200,

	// Default speed
	_default: 400
};


// Based off of the plugin by Clint Helfers, with permission.
// https://web.archive.org/web/20100324014747/http://blindsignals.com/index.php/2009/07/jquery-delay/
jQuery.fn.delay = function( time, type ) {
	time = jQuery.fx ? jQuery.fx.speeds[ time ] || time : time;
	type = type || "fx";

	return this.queue( type, function( next, hooks ) {
		var timeout = window.setTimeout( next, time );
		hooks.stop = function() {
			window.clearTimeout( timeout );
		};
	} );
};


( function() {
	var input = document.createElement( "input" ),
		select = document.createElement( "select" ),
		opt = select.appendChild( document.createElement( "option" ) );

	input.type = "checkbox";

	// Support: Android <=4.3 only
	// Default value for a checkbox should be "on"
	support.checkOn = input.value !== "";

	// Support: IE <=11 only
	// Must access selectedIndex to make default options select
	support.optSelected = opt.selected;

	// Support: IE <=11 only
	// An input loses its value after becoming a radio
	input = document.createElement( "input" );
	input.value = "t";
	input.type = "radio";
	support.radioValue = input.value === "t";
} )();


var boolHook,
	attrHandle = jQuery.expr.attrHandle;

jQuery.fn.extend( {
	attr: function( name, value ) {
		return access( this, jQuery.attr, name, value, arguments.length > 1 );
	},

	removeAttr: function( name ) {
		return this.each( function() {
			jQuery.removeAttr( this, name );
		} );
	}
} );

jQuery.extend( {
	attr: function( elem, name, value ) {
		var ret, hooks,
			nType = elem.nodeType;

		// Don't get/set attributes on text, comment and attribute nodes
		if ( nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		// Fallback to prop when attributes are not supported
		if ( typeof elem.getAttribute === "undefined" ) {
			return jQuery.prop( elem, name, value );
		}

		// Attribute hooks are determined by the lowercase version
		// Grab necessary hook if one is defined
		if ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {
			hooks = jQuery.attrHooks[ name.toLowerCase() ] ||
				( jQuery.expr.match.bool.test( name ) ? boolHook : undefined );
		}

		if ( value !== undefined ) {
			if ( value === null ) {
				jQuery.removeAttr( elem, name );
				return;
			}

			if ( hooks && "set" in hooks &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				return ret;
			}

			elem.setAttribute( name, value + "" );
			return value;
		}

		if ( hooks && "get" in hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			return ret;
		}

		ret = jQuery.find.attr( elem, name );

		// Non-existent attributes return null, we normalize to undefined
		return ret == null ? undefined : ret;
	},

	attrHooks: {
		type: {
			set: function( elem, value ) {
				if ( !support.radioValue && value === "radio" &&
					nodeName( elem, "input" ) ) {
					var val = elem.value;
					elem.setAttribute( "type", value );
					if ( val ) {
						elem.value = val;
					}
					return value;
				}
			}
		}
	},

	removeAttr: function( elem, value ) {
		var name,
			i = 0,

			// Attribute names can contain non-HTML whitespace characters
			// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
			attrNames = value && value.match( rnothtmlwhite );

		if ( attrNames && elem.nodeType === 1 ) {
			while ( ( name = attrNames[ i++ ] ) ) {
				elem.removeAttribute( name );
			}
		}
	}
} );

// Hooks for boolean attributes
boolHook = {
	set: function( elem, value, name ) {
		if ( value === false ) {

			// Remove boolean attributes when set to false
			jQuery.removeAttr( elem, name );
		} else {
			elem.setAttribute( name, name );
		}
		return name;
	}
};

jQuery.each( jQuery.expr.match.bool.source.match( /\w+/g ), function( i, name ) {
	var getter = attrHandle[ name ] || jQuery.find.attr;

	attrHandle[ name ] = function( elem, name, isXML ) {
		var ret, handle,
			lowercaseName = name.toLowerCase();

		if ( !isXML ) {

			// Avoid an infinite loop by temporarily removing this function from the getter
			handle = attrHandle[ lowercaseName ];
			attrHandle[ lowercaseName ] = ret;
			ret = getter( elem, name, isXML ) != null ?
				lowercaseName :
				null;
			attrHandle[ lowercaseName ] = handle;
		}
		return ret;
	};
} );




var rfocusable = /^(?:input|select|textarea|button)$/i,
	rclickable = /^(?:a|area)$/i;

jQuery.fn.extend( {
	prop: function( name, value ) {
		return access( this, jQuery.prop, name, value, arguments.length > 1 );
	},

	removeProp: function( name ) {
		return this.each( function() {
			delete this[ jQuery.propFix[ name ] || name ];
		} );
	}
} );

jQuery.extend( {
	prop: function( elem, name, value ) {
		var ret, hooks,
			nType = elem.nodeType;

		// Don't get/set properties on text, comment and attribute nodes
		if ( nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		if ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {

			// Fix name and attach hooks
			name = jQuery.propFix[ name ] || name;
			hooks = jQuery.propHooks[ name ];
		}

		if ( value !== undefined ) {
			if ( hooks && "set" in hooks &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				return ret;
			}

			return ( elem[ name ] = value );
		}

		if ( hooks && "get" in hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			return ret;
		}

		return elem[ name ];
	},

	propHooks: {
		tabIndex: {
			get: function( elem ) {

				// Support: IE <=9 - 11 only
				// elem.tabIndex doesn't always return the
				// correct value when it hasn't been explicitly set
				// https://web.archive.org/web/20141116233347/http://fluidproject.org/blog/2008/01/09/getting-setting-and-removing-tabindex-values-with-javascript/
				// Use proper attribute retrieval(#12072)
				var tabindex = jQuery.find.attr( elem, "tabindex" );

				if ( tabindex ) {
					return parseInt( tabindex, 10 );
				}

				if (
					rfocusable.test( elem.nodeName ) ||
					rclickable.test( elem.nodeName ) &&
					elem.href
				) {
					return 0;
				}

				return -1;
			}
		}
	},

	propFix: {
		"for": "htmlFor",
		"class": "className"
	}
} );

// Support: IE <=11 only
// Accessing the selectedIndex property
// forces the browser to respect setting selected
// on the option
// The getter ensures a default option is selected
// when in an optgroup
// eslint rule "no-unused-expressions" is disabled for this code
// since it considers such accessions noop
if ( !support.optSelected ) {
	jQuery.propHooks.selected = {
		get: function( elem ) {

			/* eslint no-unused-expressions: "off" */

			var parent = elem.parentNode;
			if ( parent && parent.parentNode ) {
				parent.parentNode.selectedIndex;
			}
			return null;
		},
		set: function( elem ) {

			/* eslint no-unused-expressions: "off" */

			var parent = elem.parentNode;
			if ( parent ) {
				parent.selectedIndex;

				if ( parent.parentNode ) {
					parent.parentNode.selectedIndex;
				}
			}
		}
	};
}

jQuery.each( [
	"tabIndex",
	"readOnly",
	"maxLength",
	"cellSpacing",
	"cellPadding",
	"rowSpan",
	"colSpan",
	"useMap",
	"frameBorder",
	"contentEditable"
], function() {
	jQuery.propFix[ this.toLowerCase() ] = this;
} );




	// Strip and collapse whitespace according to HTML spec
	// https://html.spec.whatwg.org/multipage/infrastructure.html#strip-and-collapse-whitespace
	function stripAndCollapse( value ) {
		var tokens = value.match( rnothtmlwhite ) || [];
		return tokens.join( " " );
	}


function getClass( elem ) {
	return elem.getAttribute && elem.getAttribute( "class" ) || "";
}

jQuery.fn.extend( {
	addClass: function( value ) {
		var classes, elem, cur, curValue, clazz, j, finalValue,
			i = 0;

		if ( jQuery.isFunction( value ) ) {
			return this.each( function( j ) {
				jQuery( this ).addClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		if ( typeof value === "string" && value ) {
			classes = value.match( rnothtmlwhite ) || [];

			while ( ( elem = this[ i++ ] ) ) {
				curValue = getClass( elem );
				cur = elem.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				if ( cur ) {
					j = 0;
					while ( ( clazz = classes[ j++ ] ) ) {
						if ( cur.indexOf( " " + clazz + " " ) < 0 ) {
							cur += clazz + " ";
						}
					}

					// Only assign if different to avoid unneeded rendering.
					finalValue = stripAndCollapse( cur );
					if ( curValue !== finalValue ) {
						elem.setAttribute( "class", finalValue );
					}
				}
			}
		}

		return this;
	},

	removeClass: function( value ) {
		var classes, elem, cur, curValue, clazz, j, finalValue,
			i = 0;

		if ( jQuery.isFunction( value ) ) {
			return this.each( function( j ) {
				jQuery( this ).removeClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		if ( !arguments.length ) {
			return this.attr( "class", "" );
		}

		if ( typeof value === "string" && value ) {
			classes = value.match( rnothtmlwhite ) || [];

			while ( ( elem = this[ i++ ] ) ) {
				curValue = getClass( elem );

				// This expression is here for better compressibility (see addClass)
				cur = elem.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				if ( cur ) {
					j = 0;
					while ( ( clazz = classes[ j++ ] ) ) {

						// Remove *all* instances
						while ( cur.indexOf( " " + clazz + " " ) > -1 ) {
							cur = cur.replace( " " + clazz + " ", " " );
						}
					}

					// Only assign if different to avoid unneeded rendering.
					finalValue = stripAndCollapse( cur );
					if ( curValue !== finalValue ) {
						elem.setAttribute( "class", finalValue );
					}
				}
			}
		}

		return this;
	},

	toggleClass: function( value, stateVal ) {
		var type = typeof value;

		if ( typeof stateVal === "boolean" && type === "string" ) {
			return stateVal ? this.addClass( value ) : this.removeClass( value );
		}

		if ( jQuery.isFunction( value ) ) {
			return this.each( function( i ) {
				jQuery( this ).toggleClass(
					value.call( this, i, getClass( this ), stateVal ),
					stateVal
				);
			} );
		}

		return this.each( function() {
			var className, i, self, classNames;

			if ( type === "string" ) {

				// Toggle individual class names
				i = 0;
				self = jQuery( this );
				classNames = value.match( rnothtmlwhite ) || [];

				while ( ( className = classNames[ i++ ] ) ) {

					// Check each className given, space separated list
					if ( self.hasClass( className ) ) {
						self.removeClass( className );
					} else {
						self.addClass( className );
					}
				}

			// Toggle whole class name
			} else if ( value === undefined || type === "boolean" ) {
				className = getClass( this );
				if ( className ) {

					// Store className if set
					dataPriv.set( this, "__className__", className );
				}

				// If the element has a class name or if we're passed `false`,
				// then remove the whole classname (if there was one, the above saved it).
				// Otherwise bring back whatever was previously saved (if anything),
				// falling back to the empty string if nothing was stored.
				if ( this.setAttribute ) {
					this.setAttribute( "class",
						className || value === false ?
						"" :
						dataPriv.get( this, "__className__" ) || ""
					);
				}
			}
		} );
	},

	hasClass: function( selector ) {
		var className, elem,
			i = 0;

		className = " " + selector + " ";
		while ( ( elem = this[ i++ ] ) ) {
			if ( elem.nodeType === 1 &&
				( " " + stripAndCollapse( getClass( elem ) ) + " " ).indexOf( className ) > -1 ) {
					return true;
			}
		}

		return false;
	}
} );




var rreturn = /\r/g;

jQuery.fn.extend( {
	val: function( value ) {
		var hooks, ret, isFunction,
			elem = this[ 0 ];

		if ( !arguments.length ) {
			if ( elem ) {
				hooks = jQuery.valHooks[ elem.type ] ||
					jQuery.valHooks[ elem.nodeName.toLowerCase() ];

				if ( hooks &&
					"get" in hooks &&
					( ret = hooks.get( elem, "value" ) ) !== undefined
				) {
					return ret;
				}

				ret = elem.value;

				// Handle most common string cases
				if ( typeof ret === "string" ) {
					return ret.replace( rreturn, "" );
				}

				// Handle cases where value is null/undef or number
				return ret == null ? "" : ret;
			}

			return;
		}

		isFunction = jQuery.isFunction( value );

		return this.each( function( i ) {
			var val;

			if ( this.nodeType !== 1 ) {
				return;
			}

			if ( isFunction ) {
				val = value.call( this, i, jQuery( this ).val() );
			} else {
				val = value;
			}

			// Treat null/undefined as ""; convert numbers to string
			if ( val == null ) {
				val = "";

			} else if ( typeof val === "number" ) {
				val += "";

			} else if ( Array.isArray( val ) ) {
				val = jQuery.map( val, function( value ) {
					return value == null ? "" : value + "";
				} );
			}

			hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];

			// If set returns undefined, fall back to normal setting
			if ( !hooks || !( "set" in hooks ) || hooks.set( this, val, "value" ) === undefined ) {
				this.value = val;
			}
		} );
	}
} );

jQuery.extend( {
	valHooks: {
		option: {
			get: function( elem ) {

				var val = jQuery.find.attr( elem, "value" );
				return val != null ?
					val :

					// Support: IE <=10 - 11 only
					// option.text throws exceptions (#14686, #14858)
					// Strip and collapse whitespace
					// https://html.spec.whatwg.org/#strip-and-collapse-whitespace
					stripAndCollapse( jQuery.text( elem ) );
			}
		},
		select: {
			get: function( elem ) {
				var value, option, i,
					options = elem.options,
					index = elem.selectedIndex,
					one = elem.type === "select-one",
					values = one ? null : [],
					max = one ? index + 1 : options.length;

				if ( index < 0 ) {
					i = max;

				} else {
					i = one ? index : 0;
				}

				// Loop through all the selected options
				for ( ; i < max; i++ ) {
					option = options[ i ];

					// Support: IE <=9 only
					// IE8-9 doesn't update selected after form reset (#2551)
					if ( ( option.selected || i === index ) &&

							// Don't return options that are disabled or in a disabled optgroup
							!option.disabled &&
							( !option.parentNode.disabled ||
								!nodeName( option.parentNode, "optgroup" ) ) ) {

						// Get the specific value for the option
						value = jQuery( option ).val();

						// We don't need an array for one selects
						if ( one ) {
							return value;
						}

						// Multi-Selects return an array
						values.push( value );
					}
				}

				return values;
			},

			set: function( elem, value ) {
				var optionSet, option,
					options = elem.options,
					values = jQuery.makeArray( value ),
					i = options.length;

				while ( i-- ) {
					option = options[ i ];

					/* eslint-disable no-cond-assign */

					if ( option.selected =
						jQuery.inArray( jQuery.valHooks.option.get( option ), values ) > -1
					) {
						optionSet = true;
					}

					/* eslint-enable no-cond-assign */
				}

				// Force browsers to behave consistently when non-matching value is set
				if ( !optionSet ) {
					elem.selectedIndex = -1;
				}
				return values;
			}
		}
	}
} );

// Radios and checkboxes getter/setter
jQuery.each( [ "radio", "checkbox" ], function() {
	jQuery.valHooks[ this ] = {
		set: function( elem, value ) {
			if ( Array.isArray( value ) ) {
				return ( elem.checked = jQuery.inArray( jQuery( elem ).val(), value ) > -1 );
			}
		}
	};
	if ( !support.checkOn ) {
		jQuery.valHooks[ this ].get = function( elem ) {
			return elem.getAttribute( "value" ) === null ? "on" : elem.value;
		};
	}
} );




// Return jQuery for attributes-only inclusion


var rfocusMorph = /^(?:focusinfocus|focusoutblur)$/;

jQuery.extend( jQuery.event, {

	trigger: function( event, data, elem, onlyHandlers ) {

		var i, cur, tmp, bubbleType, ontype, handle, special,
			eventPath = [ elem || document ],
			type = hasOwn.call( event, "type" ) ? event.type : event,
			namespaces = hasOwn.call( event, "namespace" ) ? event.namespace.split( "." ) : [];

		cur = tmp = elem = elem || document;

		// Don't do events on text and comment nodes
		if ( elem.nodeType === 3 || elem.nodeType === 8 ) {
			return;
		}

		// focus/blur morphs to focusin/out; ensure we're not firing them right now
		if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
			return;
		}

		if ( type.indexOf( "." ) > -1 ) {

			// Namespaced trigger; create a regexp to match event type in handle()
			namespaces = type.split( "." );
			type = namespaces.shift();
			namespaces.sort();
		}
		ontype = type.indexOf( ":" ) < 0 && "on" + type;

		// Caller can pass in a jQuery.Event object, Object, or just an event type string
		event = event[ jQuery.expando ] ?
			event :
			new jQuery.Event( type, typeof event === "object" && event );

		// Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
		event.isTrigger = onlyHandlers ? 2 : 3;
		event.namespace = namespaces.join( "." );
		event.rnamespace = event.namespace ?
			new RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" ) :
			null;

		// Clean up the event in case it is being reused
		event.result = undefined;
		if ( !event.target ) {
			event.target = elem;
		}

		// Clone any incoming data and prepend the event, creating the handler arg list
		data = data == null ?
			[ event ] :
			jQuery.makeArray( data, [ event ] );

		// Allow special events to draw outside the lines
		special = jQuery.event.special[ type ] || {};
		if ( !onlyHandlers && special.trigger && special.trigger.apply( elem, data ) === false ) {
			return;
		}

		// Determine event propagation path in advance, per W3C events spec (#9951)
		// Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
		if ( !onlyHandlers && !special.noBubble && !jQuery.isWindow( elem ) ) {

			bubbleType = special.delegateType || type;
			if ( !rfocusMorph.test( bubbleType + type ) ) {
				cur = cur.parentNode;
			}
			for ( ; cur; cur = cur.parentNode ) {
				eventPath.push( cur );
				tmp = cur;
			}

			// Only add window if we got to document (e.g., not plain obj or detached DOM)
			if ( tmp === ( elem.ownerDocument || document ) ) {
				eventPath.push( tmp.defaultView || tmp.parentWindow || window );
			}
		}

		// Fire handlers on the event path
		i = 0;
		while ( ( cur = eventPath[ i++ ] ) && !event.isPropagationStopped() ) {

			event.type = i > 1 ?
				bubbleType :
				special.bindType || type;

			// jQuery handler
			handle = ( dataPriv.get( cur, "events" ) || {} )[ event.type ] &&
				dataPriv.get( cur, "handle" );
			if ( handle ) {
				handle.apply( cur, data );
			}

			// Native handler
			handle = ontype && cur[ ontype ];
			if ( handle && handle.apply && acceptData( cur ) ) {
				event.result = handle.apply( cur, data );
				if ( event.result === false ) {
					event.preventDefault();
				}
			}
		}
		event.type = type;

		// If nobody prevented the default action, do it now
		if ( !onlyHandlers && !event.isDefaultPrevented() ) {

			if ( ( !special._default ||
				special._default.apply( eventPath.pop(), data ) === false ) &&
				acceptData( elem ) ) {

				// Call a native DOM method on the target with the same name as the event.
				// Don't do default actions on window, that's where global variables be (#6170)
				if ( ontype && jQuery.isFunction( elem[ type ] ) && !jQuery.isWindow( elem ) ) {

					// Don't re-trigger an onFOO event when we call its FOO() method
					tmp = elem[ ontype ];

					if ( tmp ) {
						elem[ ontype ] = null;
					}

					// Prevent re-triggering of the same event, since we already bubbled it above
					jQuery.event.triggered = type;
					elem[ type ]();
					jQuery.event.triggered = undefined;

					if ( tmp ) {
						elem[ ontype ] = tmp;
					}
				}
			}
		}

		return event.result;
	},

	// Piggyback on a donor event to simulate a different one
	// Used only for `focus(in | out)` events
	simulate: function( type, elem, event ) {
		var e = jQuery.extend(
			new jQuery.Event(),
			event,
			{
				type: type,
				isSimulated: true
			}
		);

		jQuery.event.trigger( e, null, elem );
	}

} );

jQuery.fn.extend( {

	trigger: function( type, data ) {
		return this.each( function() {
			jQuery.event.trigger( type, data, this );
		} );
	},
	triggerHandler: function( type, data ) {
		var elem = this[ 0 ];
		if ( elem ) {
			return jQuery.event.trigger( type, data, elem, true );
		}
	}
} );


jQuery.each( ( "blur focus focusin focusout resize scroll click dblclick " +
	"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
	"change select submit keydown keypress keyup contextmenu" ).split( " " ),
	function( i, name ) {

	// Handle event binding
	jQuery.fn[ name ] = function( data, fn ) {
		return arguments.length > 0 ?
			this.on( name, null, data, fn ) :
			this.trigger( name );
	};
} );

jQuery.fn.extend( {
	hover: function( fnOver, fnOut ) {
		return this.mouseenter( fnOver ).mouseleave( fnOut || fnOver );
	}
} );




support.focusin = "onfocusin" in window;


// Support: Firefox <=44
// Firefox doesn't have focus(in | out) events
// Related ticket - https://bugzilla.mozilla.org/show_bug.cgi?id=687787
//
// Support: Chrome <=48 - 49, Safari <=9.0 - 9.1
// focus(in | out) events fire after focus & blur events,
// which is spec violation - http://www.w3.org/TR/DOM-Level-3-Events/#events-focusevent-event-order
// Related ticket - https://bugs.chromium.org/p/chromium/issues/detail?id=449857
if ( !support.focusin ) {
	jQuery.each( { focus: "focusin", blur: "focusout" }, function( orig, fix ) {

		// Attach a single capturing handler on the document while someone wants focusin/focusout
		var handler = function( event ) {
			jQuery.event.simulate( fix, event.target, jQuery.event.fix( event ) );
		};

		jQuery.event.special[ fix ] = {
			setup: function() {
				var doc = this.ownerDocument || this,
					attaches = dataPriv.access( doc, fix );

				if ( !attaches ) {
					doc.addEventListener( orig, handler, true );
				}
				dataPriv.access( doc, fix, ( attaches || 0 ) + 1 );
			},
			teardown: function() {
				var doc = this.ownerDocument || this,
					attaches = dataPriv.access( doc, fix ) - 1;

				if ( !attaches ) {
					doc.removeEventListener( orig, handler, true );
					dataPriv.remove( doc, fix );

				} else {
					dataPriv.access( doc, fix, attaches );
				}
			}
		};
	} );
}
var location = window.location;

var nonce = jQuery.now();

var rquery = ( /\?/ );



// Cross-browser xml parsing
jQuery.parseXML = function( data ) {
	var xml;
	if ( !data || typeof data !== "string" ) {
		return null;
	}

	// Support: IE 9 - 11 only
	// IE throws on parseFromString with invalid input.
	try {
		xml = ( new window.DOMParser() ).parseFromString( data, "text/xml" );
	} catch ( e ) {
		xml = undefined;
	}

	if ( !xml || xml.getElementsByTagName( "parsererror" ).length ) {
		jQuery.error( "Invalid XML: " + data );
	}
	return xml;
};


var
	rbracket = /\[\]$/,
	rCRLF = /\r?\n/g,
	rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
	rsubmittable = /^(?:input|select|textarea|keygen)/i;

function buildParams( prefix, obj, traditional, add ) {
	var name;

	if ( Array.isArray( obj ) ) {

		// Serialize array item.
		jQuery.each( obj, function( i, v ) {
			if ( traditional || rbracket.test( prefix ) ) {

				// Treat each array item as a scalar.
				add( prefix, v );

			} else {

				// Item is non-scalar (array or object), encode its numeric index.
				buildParams(
					prefix + "[" + ( typeof v === "object" && v != null ? i : "" ) + "]",
					v,
					traditional,
					add
				);
			}
		} );

	} else if ( !traditional && jQuery.type( obj ) === "object" ) {

		// Serialize object item.
		for ( name in obj ) {
			buildParams( prefix + "[" + name + "]", obj[ name ], traditional, add );
		}

	} else {

		// Serialize scalar item.
		add( prefix, obj );
	}
}

// Serialize an array of form elements or a set of
// key/values into a query string
jQuery.param = function( a, traditional ) {
	var prefix,
		s = [],
		add = function( key, valueOrFunction ) {

			// If value is a function, invoke it and use its return value
			var value = jQuery.isFunction( valueOrFunction ) ?
				valueOrFunction() :
				valueOrFunction;

			s[ s.length ] = encodeURIComponent( key ) + "=" +
				encodeURIComponent( value == null ? "" : value );
		};

	// If an array was passed in, assume that it is an array of form elements.
	if ( Array.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {

		// Serialize the form elements
		jQuery.each( a, function() {
			add( this.name, this.value );
		} );

	} else {

		// If traditional, encode the "old" way (the way 1.3.2 or older
		// did it), otherwise encode params recursively.
		for ( prefix in a ) {
			buildParams( prefix, a[ prefix ], traditional, add );
		}
	}

	// Return the resulting serialization
	return s.join( "&" );
};

jQuery.fn.extend( {
	serialize: function() {
		return jQuery.param( this.serializeArray() );
	},
	serializeArray: function() {
		return this.map( function() {

			// Can add propHook for "elements" to filter or add form elements
			var elements = jQuery.prop( this, "elements" );
			return elements ? jQuery.makeArray( elements ) : this;
		} )
		.filter( function() {
			var type = this.type;

			// Use .is( ":disabled" ) so that fieldset[disabled] works
			return this.name && !jQuery( this ).is( ":disabled" ) &&
				rsubmittable.test( this.nodeName ) && !rsubmitterTypes.test( type ) &&
				( this.checked || !rcheckableType.test( type ) );
		} )
		.map( function( i, elem ) {
			var val = jQuery( this ).val();

			if ( val == null ) {
				return null;
			}

			if ( Array.isArray( val ) ) {
				return jQuery.map( val, function( val ) {
					return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
				} );
			}

			return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
		} ).get();
	}
} );


var
	r20 = /%20/g,
	rhash = /#.*$/,
	rantiCache = /([?&])_=[^&]*/,
	rheaders = /^(.*?):[ \t]*([^\r\n]*)$/mg,

	// #7653, #8125, #8152: local protocol detection
	rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
	rnoContent = /^(?:GET|HEAD)$/,
	rprotocol = /^\/\//,

	/* Prefilters
	 * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
	 * 2) These are called:
	 *    - BEFORE asking for a transport
	 *    - AFTER param serialization (s.data is a string if s.processData is true)
	 * 3) key is the dataType
	 * 4) the catchall symbol "*" can be used
	 * 5) execution will start with transport dataType and THEN continue down to "*" if needed
	 */
	prefilters = {},

	/* Transports bindings
	 * 1) key is the dataType
	 * 2) the catchall symbol "*" can be used
	 * 3) selection will start with transport dataType and THEN go to "*" if needed
	 */
	transports = {},

	// Avoid comment-prolog char sequence (#10098); must appease lint and evade compression
	allTypes = "*/".concat( "*" ),

	// Anchor tag for parsing the document origin
	originAnchor = document.createElement( "a" );
	originAnchor.href = location.href;

// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
function addToPrefiltersOrTransports( structure ) {

	// dataTypeExpression is optional and defaults to "*"
	return function( dataTypeExpression, func ) {

		if ( typeof dataTypeExpression !== "string" ) {
			func = dataTypeExpression;
			dataTypeExpression = "*";
		}

		var dataType,
			i = 0,
			dataTypes = dataTypeExpression.toLowerCase().match( rnothtmlwhite ) || [];

		if ( jQuery.isFunction( func ) ) {

			// For each dataType in the dataTypeExpression
			while ( ( dataType = dataTypes[ i++ ] ) ) {

				// Prepend if requested
				if ( dataType[ 0 ] === "+" ) {
					dataType = dataType.slice( 1 ) || "*";
					( structure[ dataType ] = structure[ dataType ] || [] ).unshift( func );

				// Otherwise append
				} else {
					( structure[ dataType ] = structure[ dataType ] || [] ).push( func );
				}
			}
		}
	};
}

// Base inspection function for prefilters and transports
function inspectPrefiltersOrTransports( structure, options, originalOptions, jqXHR ) {

	var inspected = {},
		seekingTransport = ( structure === transports );

	function inspect( dataType ) {
		var selected;
		inspected[ dataType ] = true;
		jQuery.each( structure[ dataType ] || [], function( _, prefilterOrFactory ) {
			var dataTypeOrTransport = prefilterOrFactory( options, originalOptions, jqXHR );
			if ( typeof dataTypeOrTransport === "string" &&
				!seekingTransport && !inspected[ dataTypeOrTransport ] ) {

				options.dataTypes.unshift( dataTypeOrTransport );
				inspect( dataTypeOrTransport );
				return false;
			} else if ( seekingTransport ) {
				return !( selected = dataTypeOrTransport );
			}
		} );
		return selected;
	}

	return inspect( options.dataTypes[ 0 ] ) || !inspected[ "*" ] && inspect( "*" );
}

// A special extend for ajax options
// that takes "flat" options (not to be deep extended)
// Fixes #9887
function ajaxExtend( target, src ) {
	var key, deep,
		flatOptions = jQuery.ajaxSettings.flatOptions || {};

	for ( key in src ) {
		if ( src[ key ] !== undefined ) {
			( flatOptions[ key ] ? target : ( deep || ( deep = {} ) ) )[ key ] = src[ key ];
		}
	}
	if ( deep ) {
		jQuery.extend( true, target, deep );
	}

	return target;
}

/* Handles responses to an ajax request:
 * - finds the right dataType (mediates between content-type and expected dataType)
 * - returns the corresponding response
 */
function ajaxHandleResponses( s, jqXHR, responses ) {

	var ct, type, finalDataType, firstDataType,
		contents = s.contents,
		dataTypes = s.dataTypes;

	// Remove auto dataType and get content-type in the process
	while ( dataTypes[ 0 ] === "*" ) {
		dataTypes.shift();
		if ( ct === undefined ) {
			ct = s.mimeType || jqXHR.getResponseHeader( "Content-Type" );
		}
	}

	// Check if we're dealing with a known content-type
	if ( ct ) {
		for ( type in contents ) {
			if ( contents[ type ] && contents[ type ].test( ct ) ) {
				dataTypes.unshift( type );
				break;
			}
		}
	}

	// Check to see if we have a response for the expected dataType
	if ( dataTypes[ 0 ] in responses ) {
		finalDataType = dataTypes[ 0 ];
	} else {

		// Try convertible dataTypes
		for ( type in responses ) {
			if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[ 0 ] ] ) {
				finalDataType = type;
				break;
			}
			if ( !firstDataType ) {
				firstDataType = type;
			}
		}

		// Or just use first one
		finalDataType = finalDataType || firstDataType;
	}

	// If we found a dataType
	// We add the dataType to the list if needed
	// and return the corresponding response
	if ( finalDataType ) {
		if ( finalDataType !== dataTypes[ 0 ] ) {
			dataTypes.unshift( finalDataType );
		}
		return responses[ finalDataType ];
	}
}

/* Chain conversions given the request and the original response
 * Also sets the responseXXX fields on the jqXHR instance
 */
function ajaxConvert( s, response, jqXHR, isSuccess ) {
	var conv2, current, conv, tmp, prev,
		converters = {},

		// Work with a copy of dataTypes in case we need to modify it for conversion
		dataTypes = s.dataTypes.slice();

	// Create converters map with lowercased keys
	if ( dataTypes[ 1 ] ) {
		for ( conv in s.converters ) {
			converters[ conv.toLowerCase() ] = s.converters[ conv ];
		}
	}

	current = dataTypes.shift();

	// Convert to each sequential dataType
	while ( current ) {

		if ( s.responseFields[ current ] ) {
			jqXHR[ s.responseFields[ current ] ] = response;
		}

		// Apply the dataFilter if provided
		if ( !prev && isSuccess && s.dataFilter ) {
			response = s.dataFilter( response, s.dataType );
		}

		prev = current;
		current = dataTypes.shift();

		if ( current ) {

			// There's only work to do if current dataType is non-auto
			if ( current === "*" ) {

				current = prev;

			// Convert response if prev dataType is non-auto and differs from current
			} else if ( prev !== "*" && prev !== current ) {

				// Seek a direct converter
				conv = converters[ prev + " " + current ] || converters[ "* " + current ];

				// If none found, seek a pair
				if ( !conv ) {
					for ( conv2 in converters ) {

						// If conv2 outputs current
						tmp = conv2.split( " " );
						if ( tmp[ 1 ] === current ) {

							// If prev can be converted to accepted input
							conv = converters[ prev + " " + tmp[ 0 ] ] ||
								converters[ "* " + tmp[ 0 ] ];
							if ( conv ) {

								// Condense equivalence converters
								if ( conv === true ) {
									conv = converters[ conv2 ];

								// Otherwise, insert the intermediate dataType
								} else if ( converters[ conv2 ] !== true ) {
									current = tmp[ 0 ];
									dataTypes.unshift( tmp[ 1 ] );
								}
								break;
							}
						}
					}
				}

				// Apply converter (if not an equivalence)
				if ( conv !== true ) {

					// Unless errors are allowed to bubble, catch and return them
					if ( conv && s.throws ) {
						response = conv( response );
					} else {
						try {
							response = conv( response );
						} catch ( e ) {
							return {
								state: "parsererror",
								error: conv ? e : "No conversion from " + prev + " to " + current
							};
						}
					}
				}
			}
		}
	}

	return { state: "success", data: response };
}

jQuery.extend( {

	// Counter for holding the number of active queries
	active: 0,

	// Last-Modified header cache for next request
	lastModified: {},
	etag: {},

	ajaxSettings: {
		url: location.href,
		type: "GET",
		isLocal: rlocalProtocol.test( location.protocol ),
		global: true,
		processData: true,
		async: true,
		contentType: "application/x-www-form-urlencoded; charset=UTF-8",

		/*
		timeout: 0,
		data: null,
		dataType: null,
		username: null,
		password: null,
		cache: null,
		throws: false,
		traditional: false,
		headers: {},
		*/

		accepts: {
			"*": allTypes,
			text: "text/plain",
			html: "text/html",
			xml: "application/xml, text/xml",
			json: "application/json, text/javascript"
		},

		contents: {
			xml: /\bxml\b/,
			html: /\bhtml/,
			json: /\bjson\b/
		},

		responseFields: {
			xml: "responseXML",
			text: "responseText",
			json: "responseJSON"
		},

		// Data converters
		// Keys separate source (or catchall "*") and destination types with a single space
		converters: {

			// Convert anything to text
			"* text": String,

			// Text to html (true = no transformation)
			"text html": true,

			// Evaluate text as a json expression
			"text json": JSON.parse,

			// Parse text as xml
			"text xml": jQuery.parseXML
		},

		// For options that shouldn't be deep extended:
		// you can add your own custom options here if
		// and when you create one that shouldn't be
		// deep extended (see ajaxExtend)
		flatOptions: {
			url: true,
			context: true
		}
	},

	// Creates a full fledged settings object into target
	// with both ajaxSettings and settings fields.
	// If target is omitted, writes into ajaxSettings.
	ajaxSetup: function( target, settings ) {
		return settings ?

			// Building a settings object
			ajaxExtend( ajaxExtend( target, jQuery.ajaxSettings ), settings ) :

			// Extending ajaxSettings
			ajaxExtend( jQuery.ajaxSettings, target );
	},

	ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
	ajaxTransport: addToPrefiltersOrTransports( transports ),

	// Main method
	ajax: function( url, options ) {

		// If url is an object, simulate pre-1.5 signature
		if ( typeof url === "object" ) {
			options = url;
			url = undefined;
		}

		// Force options to be an object
		options = options || {};

		var transport,

			// URL without anti-cache param
			cacheURL,

			// Response headers
			responseHeadersString,
			responseHeaders,

			// timeout handle
			timeoutTimer,

			// Url cleanup var
			urlAnchor,

			// Request state (becomes false upon send and true upon completion)
			completed,

			// To know if global events are to be dispatched
			fireGlobals,

			// Loop variable
			i,

			// uncached part of the url
			uncached,

			// Create the final options object
			s = jQuery.ajaxSetup( {}, options ),

			// Callbacks context
			callbackContext = s.context || s,

			// Context for global events is callbackContext if it is a DOM node or jQuery collection
			globalEventContext = s.context &&
				( callbackContext.nodeType || callbackContext.jquery ) ?
					jQuery( callbackContext ) :
					jQuery.event,

			// Deferreds
			deferred = jQuery.Deferred(),
			completeDeferred = jQuery.Callbacks( "once memory" ),

			// Status-dependent callbacks
			statusCode = s.statusCode || {},

			// Headers (they are sent all at once)
			requestHeaders = {},
			requestHeadersNames = {},

			// Default abort message
			strAbort = "canceled",

			// Fake xhr
			jqXHR = {
				readyState: 0,

				// Builds headers hashtable if needed
				getResponseHeader: function( key ) {
					var match;
					if ( completed ) {
						if ( !responseHeaders ) {
							responseHeaders = {};
							while ( ( match = rheaders.exec( responseHeadersString ) ) ) {
								responseHeaders[ match[ 1 ].toLowerCase() ] = match[ 2 ];
							}
						}
						match = responseHeaders[ key.toLowerCase() ];
					}
					return match == null ? null : match;
				},

				// Raw string
				getAllResponseHeaders: function() {
					return completed ? responseHeadersString : null;
				},

				// Caches the header
				setRequestHeader: function( name, value ) {
					if ( completed == null ) {
						name = requestHeadersNames[ name.toLowerCase() ] =
							requestHeadersNames[ name.toLowerCase() ] || name;
						requestHeaders[ name ] = value;
					}
					return this;
				},

				// Overrides response content-type header
				overrideMimeType: function( type ) {
					if ( completed == null ) {
						s.mimeType = type;
					}
					return this;
				},

				// Status-dependent callbacks
				statusCode: function( map ) {
					var code;
					if ( map ) {
						if ( completed ) {

							// Execute the appropriate callbacks
							jqXHR.always( map[ jqXHR.status ] );
						} else {

							// Lazy-add the new callbacks in a way that preserves old ones
							for ( code in map ) {
								statusCode[ code ] = [ statusCode[ code ], map[ code ] ];
							}
						}
					}
					return this;
				},

				// Cancel the request
				abort: function( statusText ) {
					var finalText = statusText || strAbort;
					if ( transport ) {
						transport.abort( finalText );
					}
					done( 0, finalText );
					return this;
				}
			};

		// Attach deferreds
		deferred.promise( jqXHR );

		// Add protocol if not provided (prefilters might expect it)
		// Handle falsy url in the settings object (#10093: consistency with old signature)
		// We also use the url parameter if available
		s.url = ( ( url || s.url || location.href ) + "" )
			.replace( rprotocol, location.protocol + "//" );

		// Alias method option to type as per ticket #12004
		s.type = options.method || options.type || s.method || s.type;

		// Extract dataTypes list
		s.dataTypes = ( s.dataType || "*" ).toLowerCase().match( rnothtmlwhite ) || [ "" ];

		// A cross-domain request is in order when the origin doesn't match the current origin.
		if ( s.crossDomain == null ) {
			urlAnchor = document.createElement( "a" );

			// Support: IE <=8 - 11, Edge 12 - 13
			// IE throws exception on accessing the href property if url is malformed,
			// e.g. http://example.com:80x/
			try {
				urlAnchor.href = s.url;

				// Support: IE <=8 - 11 only
				// Anchor's host property isn't correctly set when s.url is relative
				urlAnchor.href = urlAnchor.href;
				s.crossDomain = originAnchor.protocol + "//" + originAnchor.host !==
					urlAnchor.protocol + "//" + urlAnchor.host;
			} catch ( e ) {

				// If there is an error parsing the URL, assume it is crossDomain,
				// it can be rejected by the transport if it is invalid
				s.crossDomain = true;
			}
		}

		// Convert data if not already a string
		if ( s.data && s.processData && typeof s.data !== "string" ) {
			s.data = jQuery.param( s.data, s.traditional );
		}

		// Apply prefilters
		inspectPrefiltersOrTransports( prefilters, s, options, jqXHR );

		// If request was aborted inside a prefilter, stop there
		if ( completed ) {
			return jqXHR;
		}

		// We can fire global events as of now if asked to
		// Don't fire events if jQuery.event is undefined in an AMD-usage scenario (#15118)
		fireGlobals = jQuery.event && s.global;

		// Watch for a new set of requests
		if ( fireGlobals && jQuery.active++ === 0 ) {
			jQuery.event.trigger( "ajaxStart" );
		}

		// Uppercase the type
		s.type = s.type.toUpperCase();

		// Determine if request has content
		s.hasContent = !rnoContent.test( s.type );

		// Save the URL in case we're toying with the If-Modified-Since
		// and/or If-None-Match header later on
		// Remove hash to simplify url manipulation
		cacheURL = s.url.replace( rhash, "" );

		// More options handling for requests with no content
		if ( !s.hasContent ) {

			// Remember the hash so we can put it back
			uncached = s.url.slice( cacheURL.length );

			// If data is available, append data to url
			if ( s.data ) {
				cacheURL += ( rquery.test( cacheURL ) ? "&" : "?" ) + s.data;

				// #9682: remove data so that it's not used in an eventual retry
				delete s.data;
			}

			// Add or update anti-cache param if needed
			if ( s.cache === false ) {
				cacheURL = cacheURL.replace( rantiCache, "$1" );
				uncached = ( rquery.test( cacheURL ) ? "&" : "?" ) + "_=" + ( nonce++ ) + uncached;
			}

			// Put hash and anti-cache on the URL that will be requested (gh-1732)
			s.url = cacheURL + uncached;

		// Change '%20' to '+' if this is encoded form body content (gh-2658)
		} else if ( s.data && s.processData &&
			( s.contentType || "" ).indexOf( "application/x-www-form-urlencoded" ) === 0 ) {
			s.data = s.data.replace( r20, "+" );
		}

		// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
		if ( s.ifModified ) {
			if ( jQuery.lastModified[ cacheURL ] ) {
				jqXHR.setRequestHeader( "If-Modified-Since", jQuery.lastModified[ cacheURL ] );
			}
			if ( jQuery.etag[ cacheURL ] ) {
				jqXHR.setRequestHeader( "If-None-Match", jQuery.etag[ cacheURL ] );
			}
		}

		// Set the correct header, if data is being sent
		if ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
			jqXHR.setRequestHeader( "Content-Type", s.contentType );
		}

		// Set the Accepts header for the server, depending on the dataType
		jqXHR.setRequestHeader(
			"Accept",
			s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[ 0 ] ] ?
				s.accepts[ s.dataTypes[ 0 ] ] +
					( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
				s.accepts[ "*" ]
		);

		// Check for headers option
		for ( i in s.headers ) {
			jqXHR.setRequestHeader( i, s.headers[ i ] );
		}

		// Allow custom headers/mimetypes and early abort
		if ( s.beforeSend &&
			( s.beforeSend.call( callbackContext, jqXHR, s ) === false || completed ) ) {

			// Abort if not done already and return
			return jqXHR.abort();
		}

		// Aborting is no longer a cancellation
		strAbort = "abort";

		// Install callbacks on deferreds
		completeDeferred.add( s.complete );
		jqXHR.done( s.success );
		jqXHR.fail( s.error );

		// Get transport
		transport = inspectPrefiltersOrTransports( transports, s, options, jqXHR );

		// If no transport, we auto-abort
		if ( !transport ) {
			done( -1, "No Transport" );
		} else {
			jqXHR.readyState = 1;

			// Send global event
			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
			}

			// If request was aborted inside ajaxSend, stop there
			if ( completed ) {
				return jqXHR;
			}

			// Timeout
			if ( s.async && s.timeout > 0 ) {
				timeoutTimer = window.setTimeout( function() {
					jqXHR.abort( "timeout" );
				}, s.timeout );
			}

			try {
				completed = false;
				transport.send( requestHeaders, done );
			} catch ( e ) {

				// Rethrow post-completion exceptions
				if ( completed ) {
					throw e;
				}

				// Propagate others as results
				done( -1, e );
			}
		}

		// Callback for when everything is done
		function done( status, nativeStatusText, responses, headers ) {
			var isSuccess, success, error, response, modified,
				statusText = nativeStatusText;

			// Ignore repeat invocations
			if ( completed ) {
				return;
			}

			completed = true;

			// Clear timeout if it exists
			if ( timeoutTimer ) {
				window.clearTimeout( timeoutTimer );
			}

			// Dereference transport for early garbage collection
			// (no matter how long the jqXHR object will be used)
			transport = undefined;

			// Cache response headers
			responseHeadersString = headers || "";

			// Set readyState
			jqXHR.readyState = status > 0 ? 4 : 0;

			// Determine if successful
			isSuccess = status >= 200 && status < 300 || status === 304;

			// Get response data
			if ( responses ) {
				response = ajaxHandleResponses( s, jqXHR, responses );
			}

			// Convert no matter what (that way responseXXX fields are always set)
			response = ajaxConvert( s, response, jqXHR, isSuccess );

			// If successful, handle type chaining
			if ( isSuccess ) {

				// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
				if ( s.ifModified ) {
					modified = jqXHR.getResponseHeader( "Last-Modified" );
					if ( modified ) {
						jQuery.lastModified[ cacheURL ] = modified;
					}
					modified = jqXHR.getResponseHeader( "etag" );
					if ( modified ) {
						jQuery.etag[ cacheURL ] = modified;
					}
				}

				// if no content
				if ( status === 204 || s.type === "HEAD" ) {
					statusText = "nocontent";

				// if not modified
				} else if ( status === 304 ) {
					statusText = "notmodified";

				// If we have data, let's convert it
				} else {
					statusText = response.state;
					success = response.data;
					error = response.error;
					isSuccess = !error;
				}
			} else {

				// Extract error from statusText and normalize for non-aborts
				error = statusText;
				if ( status || !statusText ) {
					statusText = "error";
					if ( status < 0 ) {
						status = 0;
					}
				}
			}

			// Set data for the fake xhr object
			jqXHR.status = status;
			jqXHR.statusText = ( nativeStatusText || statusText ) + "";

			// Success/Error
			if ( isSuccess ) {
				deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
			} else {
				deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
			}

			// Status-dependent callbacks
			jqXHR.statusCode( statusCode );
			statusCode = undefined;

			if ( fireGlobals ) {
				globalEventContext.trigger( isSuccess ? "ajaxSuccess" : "ajaxError",
					[ jqXHR, s, isSuccess ? success : error ] );
			}

			// Complete
			completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );

			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );

				// Handle the global AJAX counter
				if ( !( --jQuery.active ) ) {
					jQuery.event.trigger( "ajaxStop" );
				}
			}
		}

		return jqXHR;
	},

	getJSON: function( url, data, callback ) {
		return jQuery.get( url, data, callback, "json" );
	},

	getScript: function( url, callback ) {
		return jQuery.get( url, undefined, callback, "script" );
	}
} );

jQuery.each( [ "get", "post" ], function( i, method ) {
	jQuery[ method ] = function( url, data, callback, type ) {

		// Shift arguments if data argument was omitted
		if ( jQuery.isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = undefined;
		}

		// The url can be an options object (which then must have .url)
		return jQuery.ajax( jQuery.extend( {
			url: url,
			type: method,
			dataType: type,
			data: data,
			success: callback
		}, jQuery.isPlainObject( url ) && url ) );
	};
} );


jQuery._evalUrl = function( url ) {
	return jQuery.ajax( {
		url: url,

		// Make this explicit, since user can override this through ajaxSetup (#11264)
		type: "GET",
		dataType: "script",
		cache: true,
		async: false,
		global: false,
		"throws": true
	} );
};


jQuery.fn.extend( {
	wrapAll: function( html ) {
		var wrap;

		if ( this[ 0 ] ) {
			if ( jQuery.isFunction( html ) ) {
				html = html.call( this[ 0 ] );
			}

			// The elements to wrap the target around
			wrap = jQuery( html, this[ 0 ].ownerDocument ).eq( 0 ).clone( true );

			if ( this[ 0 ].parentNode ) {
				wrap.insertBefore( this[ 0 ] );
			}

			wrap.map( function() {
				var elem = this;

				while ( elem.firstElementChild ) {
					elem = elem.firstElementChild;
				}

				return elem;
			} ).append( this );
		}

		return this;
	},

	wrapInner: function( html ) {
		if ( jQuery.isFunction( html ) ) {
			return this.each( function( i ) {
				jQuery( this ).wrapInner( html.call( this, i ) );
			} );
		}

		return this.each( function() {
			var self = jQuery( this ),
				contents = self.contents();

			if ( contents.length ) {
				contents.wrapAll( html );

			} else {
				self.append( html );
			}
		} );
	},

	wrap: function( html ) {
		var isFunction = jQuery.isFunction( html );

		return this.each( function( i ) {
			jQuery( this ).wrapAll( isFunction ? html.call( this, i ) : html );
		} );
	},

	unwrap: function( selector ) {
		this.parent( selector ).not( "body" ).each( function() {
			jQuery( this ).replaceWith( this.childNodes );
		} );
		return this;
	}
} );


jQuery.expr.pseudos.hidden = function( elem ) {
	return !jQuery.expr.pseudos.visible( elem );
};
jQuery.expr.pseudos.visible = function( elem ) {
	return !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length );
};




jQuery.ajaxSettings.xhr = function() {
	try {
		return new window.XMLHttpRequest();
	} catch ( e ) {}
};

var xhrSuccessStatus = {

		// File protocol always yields status code 0, assume 200
		0: 200,

		// Support: IE <=9 only
		// #1450: sometimes IE returns 1223 when it should be 204
		1223: 204
	},
	xhrSupported = jQuery.ajaxSettings.xhr();

support.cors = !!xhrSupported && ( "withCredentials" in xhrSupported );
support.ajax = xhrSupported = !!xhrSupported;

jQuery.ajaxTransport( function( options ) {
	var callback, errorCallback;

	// Cross domain only allowed if supported through XMLHttpRequest
	if ( support.cors || xhrSupported && !options.crossDomain ) {
		return {
			send: function( headers, complete ) {
				var i,
					xhr = options.xhr();

				xhr.open(
					options.type,
					options.url,
					options.async,
					options.username,
					options.password
				);

				// Apply custom fields if provided
				if ( options.xhrFields ) {
					for ( i in options.xhrFields ) {
						xhr[ i ] = options.xhrFields[ i ];
					}
				}

				// Override mime type if needed
				if ( options.mimeType && xhr.overrideMimeType ) {
					xhr.overrideMimeType( options.mimeType );
				}

				// X-Requested-With header
				// For cross-domain requests, seeing as conditions for a preflight are
				// akin to a jigsaw puzzle, we simply never set it to be sure.
				// (it can always be set on a per-request basis or even using ajaxSetup)
				// For same-domain requests, won't change header if already provided.
				if ( !options.crossDomain && !headers[ "X-Requested-With" ] ) {
					headers[ "X-Requested-With" ] = "XMLHttpRequest";
				}

				// Set headers
				for ( i in headers ) {
					xhr.setRequestHeader( i, headers[ i ] );
				}

				// Callback
				callback = function( type ) {
					return function() {
						if ( callback ) {
							callback = errorCallback = xhr.onload =
								xhr.onerror = xhr.onabort = xhr.onreadystatechange = null;

							if ( type === "abort" ) {
								xhr.abort();
							} else if ( type === "error" ) {

								// Support: IE <=9 only
								// On a manual native abort, IE9 throws
								// errors on any property access that is not readyState
								if ( typeof xhr.status !== "number" ) {
									complete( 0, "error" );
								} else {
									complete(

										// File: protocol always yields status 0; see #8605, #14207
										xhr.status,
										xhr.statusText
									);
								}
							} else {
								complete(
									xhrSuccessStatus[ xhr.status ] || xhr.status,
									xhr.statusText,

									// Support: IE <=9 only
									// IE9 has no XHR2 but throws on binary (trac-11426)
									// For XHR2 non-text, let the caller handle it (gh-2498)
									( xhr.responseType || "text" ) !== "text"  ||
									typeof xhr.responseText !== "string" ?
										{ binary: xhr.response } :
										{ text: xhr.responseText },
									xhr.getAllResponseHeaders()
								);
							}
						}
					};
				};

				// Listen to events
				xhr.onload = callback();
				errorCallback = xhr.onerror = callback( "error" );

				// Support: IE 9 only
				// Use onreadystatechange to replace onabort
				// to handle uncaught aborts
				if ( xhr.onabort !== undefined ) {
					xhr.onabort = errorCallback;
				} else {
					xhr.onreadystatechange = function() {

						// Check readyState before timeout as it changes
						if ( xhr.readyState === 4 ) {

							// Allow onerror to be called first,
							// but that will not handle a native abort
							// Also, save errorCallback to a variable
							// as xhr.onerror cannot be accessed
							window.setTimeout( function() {
								if ( callback ) {
									errorCallback();
								}
							} );
						}
					};
				}

				// Create the abort callback
				callback = callback( "abort" );

				try {

					// Do send the request (this may raise an exception)
					xhr.send( options.hasContent && options.data || null );
				} catch ( e ) {

					// #14683: Only rethrow if this hasn't been notified as an error yet
					if ( callback ) {
						throw e;
					}
				}
			},

			abort: function() {
				if ( callback ) {
					callback();
				}
			}
		};
	}
} );




// Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)
jQuery.ajaxPrefilter( function( s ) {
	if ( s.crossDomain ) {
		s.contents.script = false;
	}
} );

// Install script dataType
jQuery.ajaxSetup( {
	accepts: {
		script: "text/javascript, application/javascript, " +
			"application/ecmascript, application/x-ecmascript"
	},
	contents: {
		script: /\b(?:java|ecma)script\b/
	},
	converters: {
		"text script": function( text ) {
			jQuery.globalEval( text );
			return text;
		}
	}
} );

// Handle cache's special case and crossDomain
jQuery.ajaxPrefilter( "script", function( s ) {
	if ( s.cache === undefined ) {
		s.cache = false;
	}
	if ( s.crossDomain ) {
		s.type = "GET";
	}
} );

// Bind script tag hack transport
jQuery.ajaxTransport( "script", function( s ) {

	// This transport only deals with cross domain requests
	if ( s.crossDomain ) {
		var script, callback;
		return {
			send: function( _, complete ) {
				script = jQuery( "<script>" ).prop( {
					charset: s.scriptCharset,
					src: s.url
				} ).on(
					"load error",
					callback = function( evt ) {
						script.remove();
						callback = null;
						if ( evt ) {
							complete( evt.type === "error" ? 404 : 200, evt.type );
						}
					}
				);

				// Use native DOM manipulation to avoid our domManip AJAX trickery
				document.head.appendChild( script[ 0 ] );
			},
			abort: function() {
				if ( callback ) {
					callback();
				}
			}
		};
	}
} );




var oldCallbacks = [],
	rjsonp = /(=)\?(?=&|$)|\?\?/;

// Default jsonp settings
jQuery.ajaxSetup( {
	jsonp: "callback",
	jsonpCallback: function() {
		var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce++ ) );
		this[ callback ] = true;
		return callback;
	}
} );

// Detect, normalize options and install callbacks for jsonp requests
jQuery.ajaxPrefilter( "json jsonp", function( s, originalSettings, jqXHR ) {

	var callbackName, overwritten, responseContainer,
		jsonProp = s.jsonp !== false && ( rjsonp.test( s.url ) ?
			"url" :
			typeof s.data === "string" &&
				( s.contentType || "" )
					.indexOf( "application/x-www-form-urlencoded" ) === 0 &&
				rjsonp.test( s.data ) && "data"
		);

	// Handle iff the expected data type is "jsonp" or we have a parameter to set
	if ( jsonProp || s.dataTypes[ 0 ] === "jsonp" ) {

		// Get callback name, remembering preexisting value associated with it
		callbackName = s.jsonpCallback = jQuery.isFunction( s.jsonpCallback ) ?
			s.jsonpCallback() :
			s.jsonpCallback;

		// Insert callback into url or form data
		if ( jsonProp ) {
			s[ jsonProp ] = s[ jsonProp ].replace( rjsonp, "$1" + callbackName );
		} else if ( s.jsonp !== false ) {
			s.url += ( rquery.test( s.url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
		}

		// Use data converter to retrieve json after script execution
		s.converters[ "script json" ] = function() {
			if ( !responseContainer ) {
				jQuery.error( callbackName + " was not called" );
			}
			return responseContainer[ 0 ];
		};

		// Force json dataType
		s.dataTypes[ 0 ] = "json";

		// Install callback
		overwritten = window[ callbackName ];
		window[ callbackName ] = function() {
			responseContainer = arguments;
		};

		// Clean-up function (fires after converters)
		jqXHR.always( function() {

			// If previous value didn't exist - remove it
			if ( overwritten === undefined ) {
				jQuery( window ).removeProp( callbackName );

			// Otherwise restore preexisting value
			} else {
				window[ callbackName ] = overwritten;
			}

			// Save back as free
			if ( s[ callbackName ] ) {

				// Make sure that re-using the options doesn't screw things around
				s.jsonpCallback = originalSettings.jsonpCallback;

				// Save the callback name for future use
				oldCallbacks.push( callbackName );
			}

			// Call if it was a function and we have a response
			if ( responseContainer && jQuery.isFunction( overwritten ) ) {
				overwritten( responseContainer[ 0 ] );
			}

			responseContainer = overwritten = undefined;
		} );

		// Delegate to script
		return "script";
	}
} );




// Support: Safari 8 only
// In Safari 8 documents created via document.implementation.createHTMLDocument
// collapse sibling forms: the second one becomes a child of the first one.
// Because of that, this security measure has to be disabled in Safari 8.
// https://bugs.webkit.org/show_bug.cgi?id=137337
support.createHTMLDocument = ( function() {
	var body = document.implementation.createHTMLDocument( "" ).body;
	body.innerHTML = "<form></form><form></form>";
	return body.childNodes.length === 2;
} )();


// Argument "data" should be string of html
// context (optional): If specified, the fragment will be created in this context,
// defaults to document
// keepScripts (optional): If true, will include scripts passed in the html string
jQuery.parseHTML = function( data, context, keepScripts ) {
	if ( typeof data !== "string" ) {
		return [];
	}
	if ( typeof context === "boolean" ) {
		keepScripts = context;
		context = false;
	}

	var base, parsed, scripts;

	if ( !context ) {

		// Stop scripts or inline event handlers from being executed immediately
		// by using document.implementation
		if ( support.createHTMLDocument ) {
			context = document.implementation.createHTMLDocument( "" );

			// Set the base href for the created document
			// so any parsed elements with URLs
			// are based on the document's URL (gh-2965)
			base = context.createElement( "base" );
			base.href = document.location.href;
			context.head.appendChild( base );
		} else {
			context = document;
		}
	}

	parsed = rsingleTag.exec( data );
	scripts = !keepScripts && [];

	// Single tag
	if ( parsed ) {
		return [ context.createElement( parsed[ 1 ] ) ];
	}

	parsed = buildFragment( [ data ], context, scripts );

	if ( scripts && scripts.length ) {
		jQuery( scripts ).remove();
	}

	return jQuery.merge( [], parsed.childNodes );
};


/**
 * Load a url into a page
 */
jQuery.fn.load = function( url, params, callback ) {
	var selector, type, response,
		self = this,
		off = url.indexOf( " " );

	if ( off > -1 ) {
		selector = stripAndCollapse( url.slice( off ) );
		url = url.slice( 0, off );
	}

	// If it's a function
	if ( jQuery.isFunction( params ) ) {

		// We assume that it's the callback
		callback = params;
		params = undefined;

	// Otherwise, build a param string
	} else if ( params && typeof params === "object" ) {
		type = "POST";
	}

	// If we have elements to modify, make the request
	if ( self.length > 0 ) {
		jQuery.ajax( {
			url: url,

			// If "type" variable is undefined, then "GET" method will be used.
			// Make value of this field explicit since
			// user can override it through ajaxSetup method
			type: type || "GET",
			dataType: "html",
			data: params
		} ).done( function( responseText ) {

			// Save response for use in complete callback
			response = arguments;

			self.html( selector ?

				// If a selector was specified, locate the right elements in a dummy div
				// Exclude scripts to avoid IE 'Permission Denied' errors
				jQuery( "<div>" ).append( jQuery.parseHTML( responseText ) ).find( selector ) :

				// Otherwise use the full result
				responseText );

		// If the request succeeds, this function gets "data", "status", "jqXHR"
		// but they are ignored because response was set above.
		// If it fails, this function gets "jqXHR", "status", "error"
		} ).always( callback && function( jqXHR, status ) {
			self.each( function() {
				callback.apply( this, response || [ jqXHR.responseText, status, jqXHR ] );
			} );
		} );
	}

	return this;
};




// Attach a bunch of functions for handling common AJAX events
jQuery.each( [
	"ajaxStart",
	"ajaxStop",
	"ajaxComplete",
	"ajaxError",
	"ajaxSuccess",
	"ajaxSend"
], function( i, type ) {
	jQuery.fn[ type ] = function( fn ) {
		return this.on( type, fn );
	};
} );




jQuery.expr.pseudos.animated = function( elem ) {
	return jQuery.grep( jQuery.timers, function( fn ) {
		return elem === fn.elem;
	} ).length;
};




jQuery.offset = {
	setOffset: function( elem, options, i ) {
		var curPosition, curLeft, curCSSTop, curTop, curOffset, curCSSLeft, calculatePosition,
			position = jQuery.css( elem, "position" ),
			curElem = jQuery( elem ),
			props = {};

		// Set position first, in-case top/left are set even on static elem
		if ( position === "static" ) {
			elem.style.position = "relative";
		}

		curOffset = curElem.offset();
		curCSSTop = jQuery.css( elem, "top" );
		curCSSLeft = jQuery.css( elem, "left" );
		calculatePosition = ( position === "absolute" || position === "fixed" ) &&
			( curCSSTop + curCSSLeft ).indexOf( "auto" ) > -1;

		// Need to be able to calculate position if either
		// top or left is auto and position is either absolute or fixed
		if ( calculatePosition ) {
			curPosition = curElem.position();
			curTop = curPosition.top;
			curLeft = curPosition.left;

		} else {
			curTop = parseFloat( curCSSTop ) || 0;
			curLeft = parseFloat( curCSSLeft ) || 0;
		}

		if ( jQuery.isFunction( options ) ) {

			// Use jQuery.extend here to allow modification of coordinates argument (gh-1848)
			options = options.call( elem, i, jQuery.extend( {}, curOffset ) );
		}

		if ( options.top != null ) {
			props.top = ( options.top - curOffset.top ) + curTop;
		}
		if ( options.left != null ) {
			props.left = ( options.left - curOffset.left ) + curLeft;
		}

		if ( "using" in options ) {
			options.using.call( elem, props );

		} else {
			curElem.css( props );
		}
	}
};

jQuery.fn.extend( {
	offset: function( options ) {

		// Preserve chaining for setter
		if ( arguments.length ) {
			return options === undefined ?
				this :
				this.each( function( i ) {
					jQuery.offset.setOffset( this, options, i );
				} );
		}

		var doc, docElem, rect, win,
			elem = this[ 0 ];

		if ( !elem ) {
			return;
		}

		// Return zeros for disconnected and hidden (display: none) elements (gh-2310)
		// Support: IE <=11 only
		// Running getBoundingClientRect on a
		// disconnected node in IE throws an error
		if ( !elem.getClientRects().length ) {
			return { top: 0, left: 0 };
		}

		rect = elem.getBoundingClientRect();

		doc = elem.ownerDocument;
		docElem = doc.documentElement;
		win = doc.defaultView;

		return {
			top: rect.top + win.pageYOffset - docElem.clientTop,
			left: rect.left + win.pageXOffset - docElem.clientLeft
		};
	},

	position: function() {
		if ( !this[ 0 ] ) {
			return;
		}

		var offsetParent, offset,
			elem = this[ 0 ],
			parentOffset = { top: 0, left: 0 };

		// Fixed elements are offset from window (parentOffset = {top:0, left: 0},
		// because it is its only offset parent
		if ( jQuery.css( elem, "position" ) === "fixed" ) {

			// Assume getBoundingClientRect is there when computed position is fixed
			offset = elem.getBoundingClientRect();

		} else {

			// Get *real* offsetParent
			offsetParent = this.offsetParent();

			// Get correct offsets
			offset = this.offset();
			if ( !nodeName( offsetParent[ 0 ], "html" ) ) {
				parentOffset = offsetParent.offset();
			}

			// Add offsetParent borders
			parentOffset = {
				top: parentOffset.top + jQuery.css( offsetParent[ 0 ], "borderTopWidth", true ),
				left: parentOffset.left + jQuery.css( offsetParent[ 0 ], "borderLeftWidth", true )
			};
		}

		// Subtract parent offsets and element margins
		return {
			top: offset.top - parentOffset.top - jQuery.css( elem, "marginTop", true ),
			left: offset.left - parentOffset.left - jQuery.css( elem, "marginLeft", true )
		};
	},

	// This method will return documentElement in the following cases:
	// 1) For the element inside the iframe without offsetParent, this method will return
	//    documentElement of the parent window
	// 2) For the hidden or detached element
	// 3) For body or html element, i.e. in case of the html node - it will return itself
	//
	// but those exceptions were never presented as a real life use-cases
	// and might be considered as more preferable results.
	//
	// This logic, however, is not guaranteed and can change at any point in the future
	offsetParent: function() {
		return this.map( function() {
			var offsetParent = this.offsetParent;

			while ( offsetParent && jQuery.css( offsetParent, "position" ) === "static" ) {
				offsetParent = offsetParent.offsetParent;
			}

			return offsetParent || documentElement;
		} );
	}
} );

// Create scrollLeft and scrollTop methods
jQuery.each( { scrollLeft: "pageXOffset", scrollTop: "pageYOffset" }, function( method, prop ) {
	var top = "pageYOffset" === prop;

	jQuery.fn[ method ] = function( val ) {
		return access( this, function( elem, method, val ) {

			// Coalesce documents and windows
			var win;
			if ( jQuery.isWindow( elem ) ) {
				win = elem;
			} else if ( elem.nodeType === 9 ) {
				win = elem.defaultView;
			}

			if ( val === undefined ) {
				return win ? win[ prop ] : elem[ method ];
			}

			if ( win ) {
				win.scrollTo(
					!top ? val : win.pageXOffset,
					top ? val : win.pageYOffset
				);

			} else {
				elem[ method ] = val;
			}
		}, method, val, arguments.length );
	};
} );

// Support: Safari <=7 - 9.1, Chrome <=37 - 49
// Add the top/left cssHooks using jQuery.fn.position
// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
// Blink bug: https://bugs.chromium.org/p/chromium/issues/detail?id=589347
// getComputedStyle returns percent when specified for top/left/bottom/right;
// rather than make the css module depend on the offset module, just check for it here
jQuery.each( [ "top", "left" ], function( i, prop ) {
	jQuery.cssHooks[ prop ] = addGetHookIf( support.pixelPosition,
		function( elem, computed ) {
			if ( computed ) {
				computed = curCSS( elem, prop );

				// If curCSS returns percentage, fallback to offset
				return rnumnonpx.test( computed ) ?
					jQuery( elem ).position()[ prop ] + "px" :
					computed;
			}
		}
	);
} );


// Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
jQuery.each( { Height: "height", Width: "width" }, function( name, type ) {
	jQuery.each( { padding: "inner" + name, content: type, "": "outer" + name },
		function( defaultExtra, funcName ) {

		// Margin is only for outerHeight, outerWidth
		jQuery.fn[ funcName ] = function( margin, value ) {
			var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
				extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );

			return access( this, function( elem, type, value ) {
				var doc;

				if ( jQuery.isWindow( elem ) ) {

					// $( window ).outerWidth/Height return w/h including scrollbars (gh-1729)
					return funcName.indexOf( "outer" ) === 0 ?
						elem[ "inner" + name ] :
						elem.document.documentElement[ "client" + name ];
				}

				// Get document width or height
				if ( elem.nodeType === 9 ) {
					doc = elem.documentElement;

					// Either scroll[Width/Height] or offset[Width/Height] or client[Width/Height],
					// whichever is greatest
					return Math.max(
						elem.body[ "scroll" + name ], doc[ "scroll" + name ],
						elem.body[ "offset" + name ], doc[ "offset" + name ],
						doc[ "client" + name ]
					);
				}

				return value === undefined ?

					// Get width or height on the element, requesting but not forcing parseFloat
					jQuery.css( elem, type, extra ) :

					// Set width or height on the element
					jQuery.style( elem, type, value, extra );
			}, type, chainable ? margin : undefined, chainable );
		};
	} );
} );


jQuery.fn.extend( {

	bind: function( types, data, fn ) {
		return this.on( types, null, data, fn );
	},
	unbind: function( types, fn ) {
		return this.off( types, null, fn );
	},

	delegate: function( selector, types, data, fn ) {
		return this.on( types, selector, data, fn );
	},
	undelegate: function( selector, types, fn ) {

		// ( namespace ) or ( selector, types [, fn] )
		return arguments.length === 1 ?
			this.off( selector, "**" ) :
			this.off( types, selector || "**", fn );
	}
} );

jQuery.holdReady = function( hold ) {
	if ( hold ) {
		jQuery.readyWait++;
	} else {
		jQuery.ready( true );
	}
};
jQuery.isArray = Array.isArray;
jQuery.parseJSON = JSON.parse;
jQuery.nodeName = nodeName;




// Register as a named AMD module, since jQuery can be concatenated with other
// files that may use define, but not via a proper concatenation script that
// understands anonymous AMD modules. A named AMD is safest and most robust
// way to register. Lowercase jquery is used because AMD module names are
// derived from file names, and jQuery is normally delivered in a lowercase
// file name. Do this after creating the global so that if an AMD module wants
// to call noConflict to hide this version of jQuery, it will work.

// Note that for maximum portability, libraries that are not jQuery should
// declare themselves as anonymous modules, and avoid setting a global if an
// AMD loader is present. jQuery is a special case. For more information, see
// https://github.com/jrburke/requirejs/wiki/Updating-existing-libraries#wiki-anon

if ( typeof define === "function" && define.amd ) {
	define( "jquery", [], function() {
		return jQuery;
	} );
}




var

	// Map over jQuery in case of overwrite
	_jQuery = window.jQuery,

	// Map over the $ in case of overwrite
	_$ = window.$;

jQuery.noConflict = function( deep ) {
	if ( window.$ === jQuery ) {
		window.$ = _$;
	}

	if ( deep && window.jQuery === jQuery ) {
		window.jQuery = _jQuery;
	}

	return jQuery;
};

// Expose jQuery and $ identifiers, even in AMD
// (#7102#comment:10, https://github.com/jquery/jquery/pull/557)
// and CommonJS for browser emulators (#13566)
if ( !noGlobal ) {
	window.jQuery = window.$ = jQuery;
}




return jQuery;
} );

},{}],6:[function(require,module,exports){
/*!
 * Select2 4.0.3
 * https://select2.github.io
 *
 * Released under the MIT license
 * https://github.com/select2/select2/blob/master/LICENSE.md
 */
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS
    factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function (jQuery) {
  // This is needed so we can catch the AMD loader configuration and use it
  // The inner file should be wrapped (by `banner.start.js`) in a function that
  // returns the AMD loader references.
  var S2 =
(function () {
  // Restore the Select2 AMD loader so it can be used
  // Needed mostly in the language files, where the loader is not inserted
  if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
    var S2 = jQuery.fn.select2.amd;
  }
var S2;(function () { if (!S2 || !S2.requirejs) {
if (!S2) { S2 = {}; } else { require = S2; }
/**
 * @license almond 0.3.1 Copyright (c) 2011-2014, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/jrburke/almond for details
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*jslint sloppy: true */
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
    var main, req, makeMap, handlers,
        defined = {},
        waiting = {},
        config = {},
        defining = {},
        hasOwn = Object.prototype.hasOwnProperty,
        aps = [].slice,
        jsSuffixRegExp = /\.js$/;

    function hasProp(obj, prop) {
        return hasOwn.call(obj, prop);
    }

    /**
     * Given a relative module name, like ./something, normalize it to
     * a real name that can be mapped to a path.
     * @param {String} name the relative name
     * @param {String} baseName a real name that the name arg is relative
     * to.
     * @returns {String} normalized name
     */
    function normalize(name, baseName) {
        var nameParts, nameSegment, mapValue, foundMap, lastIndex,
            foundI, foundStarMap, starI, i, j, part,
            baseParts = baseName && baseName.split("/"),
            map = config.map,
            starMap = (map && map['*']) || {};

        //Adjust any relative paths.
        if (name && name.charAt(0) === ".") {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                name = name.split('/');
                lastIndex = name.length - 1;

                // Node .js allowance:
                if (config.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
                    name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
                }

                //Lop off the last part of baseParts, so that . matches the
                //"directory" and not name of the baseName's module. For instance,
                //baseName of "one/two/three", maps to "one/two/three.js", but we
                //want the directory, "one/two" for this normalization.
                name = baseParts.slice(0, baseParts.length - 1).concat(name);

                //start trimDots
                for (i = 0; i < name.length; i += 1) {
                    part = name[i];
                    if (part === ".") {
                        name.splice(i, 1);
                        i -= 1;
                    } else if (part === "..") {
                        if (i === 1 && (name[2] === '..' || name[0] === '..')) {
                            //End of the line. Keep at least one non-dot
                            //path segment at the front so it can be mapped
                            //correctly to disk. Otherwise, there is likely
                            //no path mapping for a path starting with '..'.
                            //This can still fail, but catches the most reasonable
                            //uses of ..
                            break;
                        } else if (i > 0) {
                            name.splice(i - 1, 2);
                            i -= 2;
                        }
                    }
                }
                //end trimDots

                name = name.join("/");
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        //Apply map config if available.
        if ((baseParts || starMap) && map) {
            nameParts = name.split('/');

            for (i = nameParts.length; i > 0; i -= 1) {
                nameSegment = nameParts.slice(0, i).join("/");

                if (baseParts) {
                    //Find the longest baseName segment match in the config.
                    //So, do joins on the biggest to smallest lengths of baseParts.
                    for (j = baseParts.length; j > 0; j -= 1) {
                        mapValue = map[baseParts.slice(0, j).join('/')];

                        //baseName segment has  config, find if it has one for
                        //this name.
                        if (mapValue) {
                            mapValue = mapValue[nameSegment];
                            if (mapValue) {
                                //Match, update name to the new value.
                                foundMap = mapValue;
                                foundI = i;
                                break;
                            }
                        }
                    }
                }

                if (foundMap) {
                    break;
                }

                //Check for a star map match, but just hold on to it,
                //if there is a shorter segment match later in a matching
                //config, then favor over this star map.
                if (!foundStarMap && starMap && starMap[nameSegment]) {
                    foundStarMap = starMap[nameSegment];
                    starI = i;
                }
            }

            if (!foundMap && foundStarMap) {
                foundMap = foundStarMap;
                foundI = starI;
            }

            if (foundMap) {
                nameParts.splice(0, foundI, foundMap);
                name = nameParts.join('/');
            }
        }

        return name;
    }

    function makeRequire(relName, forceSync) {
        return function () {
            //A version of a require function that passes a moduleName
            //value for items that may need to
            //look up paths relative to the moduleName
            var args = aps.call(arguments, 0);

            //If first arg is not require('string'), and there is only
            //one arg, it is the array form without a callback. Insert
            //a null so that the following concat is correct.
            if (typeof args[0] !== 'string' && args.length === 1) {
                args.push(null);
            }
            return req.apply(undef, args.concat([relName, forceSync]));
        };
    }

    function makeNormalize(relName) {
        return function (name) {
            return normalize(name, relName);
        };
    }

    function makeLoad(depName) {
        return function (value) {
            defined[depName] = value;
        };
    }

    function callDep(name) {
        if (hasProp(waiting, name)) {
            var args = waiting[name];
            delete waiting[name];
            defining[name] = true;
            main.apply(undef, args);
        }

        if (!hasProp(defined, name) && !hasProp(defining, name)) {
            throw new Error('No ' + name);
        }
        return defined[name];
    }

    //Turns a plugin!resource to [plugin, resource]
    //with the plugin being undefined if the name
    //did not have a plugin prefix.
    function splitPrefix(name) {
        var prefix,
            index = name ? name.indexOf('!') : -1;
        if (index > -1) {
            prefix = name.substring(0, index);
            name = name.substring(index + 1, name.length);
        }
        return [prefix, name];
    }

    /**
     * Makes a name map, normalizing the name, and using a plugin
     * for normalization if necessary. Grabs a ref to plugin
     * too, as an optimization.
     */
    makeMap = function (name, relName) {
        var plugin,
            parts = splitPrefix(name),
            prefix = parts[0];

        name = parts[1];

        if (prefix) {
            prefix = normalize(prefix, relName);
            plugin = callDep(prefix);
        }

        //Normalize according
        if (prefix) {
            if (plugin && plugin.normalize) {
                name = plugin.normalize(name, makeNormalize(relName));
            } else {
                name = normalize(name, relName);
            }
        } else {
            name = normalize(name, relName);
            parts = splitPrefix(name);
            prefix = parts[0];
            name = parts[1];
            if (prefix) {
                plugin = callDep(prefix);
            }
        }

        //Using ridiculous property names for space reasons
        return {
            f: prefix ? prefix + '!' + name : name, //fullName
            n: name,
            pr: prefix,
            p: plugin
        };
    };

    function makeConfig(name) {
        return function () {
            return (config && config.config && config.config[name]) || {};
        };
    }

    handlers = {
        require: function (name) {
            return makeRequire(name);
        },
        exports: function (name) {
            var e = defined[name];
            if (typeof e !== 'undefined') {
                return e;
            } else {
                return (defined[name] = {});
            }
        },
        module: function (name) {
            return {
                id: name,
                uri: '',
                exports: defined[name],
                config: makeConfig(name)
            };
        }
    };

    main = function (name, deps, callback, relName) {
        var cjsModule, depName, ret, map, i,
            args = [],
            callbackType = typeof callback,
            usingExports;

        //Use name if no relName
        relName = relName || name;

        //Call the callback to define the module, if necessary.
        if (callbackType === 'undefined' || callbackType === 'function') {
            //Pull out the defined dependencies and pass the ordered
            //values to the callback.
            //Default to [require, exports, module] if no deps
            deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
            for (i = 0; i < deps.length; i += 1) {
                map = makeMap(deps[i], relName);
                depName = map.f;

                //Fast path CommonJS standard dependencies.
                if (depName === "require") {
                    args[i] = handlers.require(name);
                } else if (depName === "exports") {
                    //CommonJS module spec 1.1
                    args[i] = handlers.exports(name);
                    usingExports = true;
                } else if (depName === "module") {
                    //CommonJS module spec 1.1
                    cjsModule = args[i] = handlers.module(name);
                } else if (hasProp(defined, depName) ||
                           hasProp(waiting, depName) ||
                           hasProp(defining, depName)) {
                    args[i] = callDep(depName);
                } else if (map.p) {
                    map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
                    args[i] = defined[depName];
                } else {
                    throw new Error(name + ' missing ' + depName);
                }
            }

            ret = callback ? callback.apply(defined[name], args) : undefined;

            if (name) {
                //If setting exports via "module" is in play,
                //favor that over return value and exports. After that,
                //favor a non-undefined return value over exports use.
                if (cjsModule && cjsModule.exports !== undef &&
                        cjsModule.exports !== defined[name]) {
                    defined[name] = cjsModule.exports;
                } else if (ret !== undef || !usingExports) {
                    //Use the return value from the function.
                    defined[name] = ret;
                }
            }
        } else if (name) {
            //May just be an object definition for the module. Only
            //worry about defining if have a module name.
            defined[name] = callback;
        }
    };

    requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
        if (typeof deps === "string") {
            if (handlers[deps]) {
                //callback in this case is really relName
                return handlers[deps](callback);
            }
            //Just return the module wanted. In this scenario, the
            //deps arg is the module name, and second arg (if passed)
            //is just the relName.
            //Normalize module name, if it contains . or ..
            return callDep(makeMap(deps, callback).f);
        } else if (!deps.splice) {
            //deps is a config object, not an array.
            config = deps;
            if (config.deps) {
                req(config.deps, config.callback);
            }
            if (!callback) {
                return;
            }

            if (callback.splice) {
                //callback is an array, which means it is a dependency list.
                //Adjust args if there are dependencies
                deps = callback;
                callback = relName;
                relName = null;
            } else {
                deps = undef;
            }
        }

        //Support require(['a'])
        callback = callback || function () {};

        //If relName is a function, it is an errback handler,
        //so remove it.
        if (typeof relName === 'function') {
            relName = forceSync;
            forceSync = alt;
        }

        //Simulate async callback;
        if (forceSync) {
            main(undef, deps, callback, relName);
        } else {
            //Using a non-zero value because of concern for what old browsers
            //do, and latest browsers "upgrade" to 4 if lower value is used:
            //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
            //If want a value immediately, use require('id') instead -- something
            //that works in almond on the global level, but not guaranteed and
            //unlikely to work in other AMD implementations.
            setTimeout(function () {
                main(undef, deps, callback, relName);
            }, 4);
        }

        return req;
    };

    /**
     * Just drops the config on the floor, but returns req in case
     * the config return value is used.
     */
    req.config = function (cfg) {
        return req(cfg);
    };

    /**
     * Expose module registry for debugging and tooling
     */
    requirejs._defined = defined;

    define = function (name, deps, callback) {
        if (typeof name !== 'string') {
            throw new Error('See almond README: incorrect module build, no module name');
        }

        //This module may not have dependencies
        if (!deps.splice) {
            //deps is not an array, so probably means
            //an object literal or factory function for
            //the value. Adjust args.
            callback = deps;
            deps = [];
        }

        if (!hasProp(defined, name) && !hasProp(waiting, name)) {
            waiting[name] = [name, deps, callback];
        }
    };

    define.amd = {
        jQuery: true
    };
}());

S2.requirejs = requirejs;S2.require = require;S2.define = define;
}
}());
S2.define("almond", function(){});

/* global jQuery:false, $:false */
S2.define('jquery',[],function () {
  var _$ = jQuery || $;

  if (_$ == null && console && console.error) {
    console.error(
      'Select2: An instance of jQuery or a jQuery-compatible library was not ' +
      'found. Make sure that you are including jQuery before Select2 on your ' +
      'web page.'
    );
  }

  return _$;
});

S2.define('select2/utils',[
  'jquery'
], function ($) {
  var Utils = {};

  Utils.Extend = function (ChildClass, SuperClass) {
    var __hasProp = {}.hasOwnProperty;

    function BaseConstructor () {
      this.constructor = ChildClass;
    }

    for (var key in SuperClass) {
      if (__hasProp.call(SuperClass, key)) {
        ChildClass[key] = SuperClass[key];
      }
    }

    BaseConstructor.prototype = SuperClass.prototype;
    ChildClass.prototype = new BaseConstructor();
    ChildClass.__super__ = SuperClass.prototype;

    return ChildClass;
  };

  function getMethods (theClass) {
    var proto = theClass.prototype;

    var methods = [];

    for (var methodName in proto) {
      var m = proto[methodName];

      if (typeof m !== 'function') {
        continue;
      }

      if (methodName === 'constructor') {
        continue;
      }

      methods.push(methodName);
    }

    return methods;
  }

  Utils.Decorate = function (SuperClass, DecoratorClass) {
    var decoratedMethods = getMethods(DecoratorClass);
    var superMethods = getMethods(SuperClass);

    function DecoratedClass () {
      var unshift = Array.prototype.unshift;

      var argCount = DecoratorClass.prototype.constructor.length;

      var calledConstructor = SuperClass.prototype.constructor;

      if (argCount > 0) {
        unshift.call(arguments, SuperClass.prototype.constructor);

        calledConstructor = DecoratorClass.prototype.constructor;
      }

      calledConstructor.apply(this, arguments);
    }

    DecoratorClass.displayName = SuperClass.displayName;

    function ctr () {
      this.constructor = DecoratedClass;
    }

    DecoratedClass.prototype = new ctr();

    for (var m = 0; m < superMethods.length; m++) {
        var superMethod = superMethods[m];

        DecoratedClass.prototype[superMethod] =
          SuperClass.prototype[superMethod];
    }

    var calledMethod = function (methodName) {
      // Stub out the original method if it's not decorating an actual method
      var originalMethod = function () {};

      if (methodName in DecoratedClass.prototype) {
        originalMethod = DecoratedClass.prototype[methodName];
      }

      var decoratedMethod = DecoratorClass.prototype[methodName];

      return function () {
        var unshift = Array.prototype.unshift;

        unshift.call(arguments, originalMethod);

        return decoratedMethod.apply(this, arguments);
      };
    };

    for (var d = 0; d < decoratedMethods.length; d++) {
      var decoratedMethod = decoratedMethods[d];

      DecoratedClass.prototype[decoratedMethod] = calledMethod(decoratedMethod);
    }

    return DecoratedClass;
  };

  var Observable = function () {
    this.listeners = {};
  };

  Observable.prototype.on = function (event, callback) {
    this.listeners = this.listeners || {};

    if (event in this.listeners) {
      this.listeners[event].push(callback);
    } else {
      this.listeners[event] = [callback];
    }
  };

  Observable.prototype.trigger = function (event) {
    var slice = Array.prototype.slice;
    var params = slice.call(arguments, 1);

    this.listeners = this.listeners || {};

    // Params should always come in as an array
    if (params == null) {
      params = [];
    }

    // If there are no arguments to the event, use a temporary object
    if (params.length === 0) {
      params.push({});
    }

    // Set the `_type` of the first object to the event
    params[0]._type = event;

    if (event in this.listeners) {
      this.invoke(this.listeners[event], slice.call(arguments, 1));
    }

    if ('*' in this.listeners) {
      this.invoke(this.listeners['*'], arguments);
    }
  };

  Observable.prototype.invoke = function (listeners, params) {
    for (var i = 0, len = listeners.length; i < len; i++) {
      listeners[i].apply(this, params);
    }
  };

  Utils.Observable = Observable;

  Utils.generateChars = function (length) {
    var chars = '';

    for (var i = 0; i < length; i++) {
      var randomChar = Math.floor(Math.random() * 36);
      chars += randomChar.toString(36);
    }

    return chars;
  };

  Utils.bind = function (func, context) {
    return function () {
      func.apply(context, arguments);
    };
  };

  Utils._convertData = function (data) {
    for (var originalKey in data) {
      var keys = originalKey.split('-');

      var dataLevel = data;

      if (keys.length === 1) {
        continue;
      }

      for (var k = 0; k < keys.length; k++) {
        var key = keys[k];

        // Lowercase the first letter
        // By default, dash-separated becomes camelCase
        key = key.substring(0, 1).toLowerCase() + key.substring(1);

        if (!(key in dataLevel)) {
          dataLevel[key] = {};
        }

        if (k == keys.length - 1) {
          dataLevel[key] = data[originalKey];
        }

        dataLevel = dataLevel[key];
      }

      delete data[originalKey];
    }

    return data;
  };

  Utils.hasScroll = function (index, el) {
    // Adapted from the function created by @ShadowScripter
    // and adapted by @BillBarry on the Stack Exchange Code Review website.
    // The original code can be found at
    // http://codereview.stackexchange.com/q/13338
    // and was designed to be used with the Sizzle selector engine.

    var $el = $(el);
    var overflowX = el.style.overflowX;
    var overflowY = el.style.overflowY;

    //Check both x and y declarations
    if (overflowX === overflowY &&
        (overflowY === 'hidden' || overflowY === 'visible')) {
      return false;
    }

    if (overflowX === 'scroll' || overflowY === 'scroll') {
      return true;
    }

    return ($el.innerHeight() < el.scrollHeight ||
      $el.innerWidth() < el.scrollWidth);
  };

  Utils.escapeMarkup = function (markup) {
    var replaceMap = {
      '\\': '&#92;',
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      '\'': '&#39;',
      '/': '&#47;'
    };

    // Do not try to escape the markup if it's not a string
    if (typeof markup !== 'string') {
      return markup;
    }

    return String(markup).replace(/[&<>"'\/\\]/g, function (match) {
      return replaceMap[match];
    });
  };

  // Append an array of jQuery nodes to a given element.
  Utils.appendMany = function ($element, $nodes) {
    // jQuery 1.7.x does not support $.fn.append() with an array
    // Fall back to a jQuery object collection using $.fn.add()
    if ($.fn.jquery.substr(0, 3) === '1.7') {
      var $jqNodes = $();

      $.map($nodes, function (node) {
        $jqNodes = $jqNodes.add(node);
      });

      $nodes = $jqNodes;
    }

    $element.append($nodes);
  };

  return Utils;
});

S2.define('select2/results',[
  'jquery',
  './utils'
], function ($, Utils) {
  function Results ($element, options, dataAdapter) {
    this.$element = $element;
    this.data = dataAdapter;
    this.options = options;

    Results.__super__.constructor.call(this);
  }

  Utils.Extend(Results, Utils.Observable);

  Results.prototype.render = function () {
    var $results = $(
      '<ul class="select2-results__options" role="tree"></ul>'
    );

    if (this.options.get('multiple')) {
      $results.attr('aria-multiselectable', 'true');
    }

    this.$results = $results;

    return $results;
  };

  Results.prototype.clear = function () {
    this.$results.empty();
  };

  Results.prototype.displayMessage = function (params) {
    var escapeMarkup = this.options.get('escapeMarkup');

    this.clear();
    this.hideLoading();

    var $message = $(
      '<li role="treeitem" aria-live="assertive"' +
      ' class="select2-results__option"></li>'
    );

    var message = this.options.get('translations').get(params.message);

    $message.append(
      escapeMarkup(
        message(params.args)
      )
    );

    $message[0].className += ' select2-results__message';

    this.$results.append($message);
  };

  Results.prototype.hideMessages = function () {
    this.$results.find('.select2-results__message').remove();
  };

  Results.prototype.append = function (data) {
    this.hideLoading();

    var $options = [];

    if (data.results == null || data.results.length === 0) {
      if (this.$results.children().length === 0) {
        this.trigger('results:message', {
          message: 'noResults'
        });
      }

      return;
    }

    data.results = this.sort(data.results);

    for (var d = 0; d < data.results.length; d++) {
      var item = data.results[d];

      var $option = this.option(item);

      $options.push($option);
    }

    this.$results.append($options);
  };

  Results.prototype.position = function ($results, $dropdown) {
    var $resultsContainer = $dropdown.find('.select2-results');
    $resultsContainer.append($results);
  };

  Results.prototype.sort = function (data) {
    var sorter = this.options.get('sorter');

    return sorter(data);
  };

  Results.prototype.highlightFirstItem = function () {
    var $options = this.$results
      .find('.select2-results__option[aria-selected]');

    var $selected = $options.filter('[aria-selected=true]');

    // Check if there are any selected options
    if ($selected.length > 0) {
      // If there are selected options, highlight the first
      $selected.first().trigger('mouseenter');
    } else {
      // If there are no selected options, highlight the first option
      // in the dropdown
      $options.first().trigger('mouseenter');
    }

    this.ensureHighlightVisible();
  };

  Results.prototype.setClasses = function () {
    var self = this;

    this.data.current(function (selected) {
      var selectedIds = $.map(selected, function (s) {
        return s.id.toString();
      });

      var $options = self.$results
        .find('.select2-results__option[aria-selected]');

      $options.each(function () {
        var $option = $(this);

        var item = $.data(this, 'data');

        // id needs to be converted to a string when comparing
        var id = '' + item.id;

        if ((item.element != null && item.element.selected) ||
            (item.element == null && $.inArray(id, selectedIds) > -1)) {
          $option.attr('aria-selected', 'true');
        } else {
          $option.attr('aria-selected', 'false');
        }
      });

    });
  };

  Results.prototype.showLoading = function (params) {
    this.hideLoading();

    var loadingMore = this.options.get('translations').get('searching');

    var loading = {
      disabled: true,
      loading: true,
      text: loadingMore(params)
    };
    var $loading = this.option(loading);
    $loading.className += ' loading-results';

    this.$results.prepend($loading);
  };

  Results.prototype.hideLoading = function () {
    this.$results.find('.loading-results').remove();
  };

  Results.prototype.option = function (data) {
    var option = document.createElement('li');
    option.className = 'select2-results__option';

    var attrs = {
      'role': 'treeitem',
      'aria-selected': 'false'
    };

    if (data.disabled) {
      delete attrs['aria-selected'];
      attrs['aria-disabled'] = 'true';
    }

    if (data.id == null) {
      delete attrs['aria-selected'];
    }

    if (data._resultId != null) {
      option.id = data._resultId;
    }

    if (data.title) {
      option.title = data.title;
    }

    if (data.children) {
      attrs.role = 'group';
      attrs['aria-label'] = data.text;
      delete attrs['aria-selected'];
    }

    for (var attr in attrs) {
      var val = attrs[attr];

      option.setAttribute(attr, val);
    }

    if (data.children) {
      var $option = $(option);

      var label = document.createElement('strong');
      label.className = 'select2-results__group';

      var $label = $(label);
      this.template(data, label);

      var $children = [];

      for (var c = 0; c < data.children.length; c++) {
        var child = data.children[c];

        var $child = this.option(child);

        $children.push($child);
      }

      var $childrenContainer = $('<ul></ul>', {
        'class': 'select2-results__options select2-results__options--nested'
      });

      $childrenContainer.append($children);

      $option.append(label);
      $option.append($childrenContainer);
    } else {
      this.template(data, option);
    }

    $.data(option, 'data', data);

    return option;
  };

  Results.prototype.bind = function (container, $container) {
    var self = this;

    var id = container.id + '-results';

    this.$results.attr('id', id);

    container.on('results:all', function (params) {
      self.clear();
      self.append(params.data);

      if (container.isOpen()) {
        self.setClasses();
        self.highlightFirstItem();
      }
    });

    container.on('results:append', function (params) {
      self.append(params.data);

      if (container.isOpen()) {
        self.setClasses();
      }
    });

    container.on('query', function (params) {
      self.hideMessages();
      self.showLoading(params);
    });

    container.on('select', function () {
      if (!container.isOpen()) {
        return;
      }

      self.setClasses();
      self.highlightFirstItem();
    });

    container.on('unselect', function () {
      if (!container.isOpen()) {
        return;
      }

      self.setClasses();
      self.highlightFirstItem();
    });

    container.on('open', function () {
      // When the dropdown is open, aria-expended="true"
      self.$results.attr('aria-expanded', 'true');
      self.$results.attr('aria-hidden', 'false');

      self.setClasses();
      self.ensureHighlightVisible();
    });

    container.on('close', function () {
      // When the dropdown is closed, aria-expended="false"
      self.$results.attr('aria-expanded', 'false');
      self.$results.attr('aria-hidden', 'true');
      self.$results.removeAttr('aria-activedescendant');
    });

    container.on('results:toggle', function () {
      var $highlighted = self.getHighlightedResults();

      if ($highlighted.length === 0) {
        return;
      }

      $highlighted.trigger('mouseup');
    });

    container.on('results:select', function () {
      var $highlighted = self.getHighlightedResults();

      if ($highlighted.length === 0) {
        return;
      }

      var data = $highlighted.data('data');

      if ($highlighted.attr('aria-selected') == 'true') {
        self.trigger('close', {});
      } else {
        self.trigger('select', {
          data: data
        });
      }
    });

    container.on('results:previous', function () {
      var $highlighted = self.getHighlightedResults();

      var $options = self.$results.find('[aria-selected]');

      var currentIndex = $options.index($highlighted);

      // If we are already at te top, don't move further
      if (currentIndex === 0) {
        return;
      }

      var nextIndex = currentIndex - 1;

      // If none are highlighted, highlight the first
      if ($highlighted.length === 0) {
        nextIndex = 0;
      }

      var $next = $options.eq(nextIndex);

      $next.trigger('mouseenter');

      var currentOffset = self.$results.offset().top;
      var nextTop = $next.offset().top;
      var nextOffset = self.$results.scrollTop() + (nextTop - currentOffset);

      if (nextIndex === 0) {
        self.$results.scrollTop(0);
      } else if (nextTop - currentOffset < 0) {
        self.$results.scrollTop(nextOffset);
      }
    });

    container.on('results:next', function () {
      var $highlighted = self.getHighlightedResults();

      var $options = self.$results.find('[aria-selected]');

      var currentIndex = $options.index($highlighted);

      var nextIndex = currentIndex + 1;

      // If we are at the last option, stay there
      if (nextIndex >= $options.length) {
        return;
      }

      var $next = $options.eq(nextIndex);

      $next.trigger('mouseenter');

      var currentOffset = self.$results.offset().top +
        self.$results.outerHeight(false);
      var nextBottom = $next.offset().top + $next.outerHeight(false);
      var nextOffset = self.$results.scrollTop() + nextBottom - currentOffset;

      if (nextIndex === 0) {
        self.$results.scrollTop(0);
      } else if (nextBottom > currentOffset) {
        self.$results.scrollTop(nextOffset);
      }
    });

    container.on('results:focus', function (params) {
      params.element.addClass('select2-results__option--highlighted');
    });

    container.on('results:message', function (params) {
      self.displayMessage(params);
    });

    if ($.fn.mousewheel) {
      this.$results.on('mousewheel', function (e) {
        var top = self.$results.scrollTop();

        var bottom = self.$results.get(0).scrollHeight - top + e.deltaY;

        var isAtTop = e.deltaY > 0 && top - e.deltaY <= 0;
        var isAtBottom = e.deltaY < 0 && bottom <= self.$results.height();

        if (isAtTop) {
          self.$results.scrollTop(0);

          e.preventDefault();
          e.stopPropagation();
        } else if (isAtBottom) {
          self.$results.scrollTop(
            self.$results.get(0).scrollHeight - self.$results.height()
          );

          e.preventDefault();
          e.stopPropagation();
        }
      });
    }

    this.$results.on('mouseup', '.select2-results__option[aria-selected]',
      function (evt) {
      var $this = $(this);

      var data = $this.data('data');

      if ($this.attr('aria-selected') === 'true') {
        if (self.options.get('multiple')) {
          self.trigger('unselect', {
            originalEvent: evt,
            data: data
          });
        } else {
          self.trigger('close', {});
        }

        return;
      }

      self.trigger('select', {
        originalEvent: evt,
        data: data
      });
    });

    this.$results.on('mouseenter', '.select2-results__option[aria-selected]',
      function (evt) {
      var data = $(this).data('data');

      self.getHighlightedResults()
          .removeClass('select2-results__option--highlighted');

      self.trigger('results:focus', {
        data: data,
        element: $(this)
      });
    });
  };

  Results.prototype.getHighlightedResults = function () {
    var $highlighted = this.$results
    .find('.select2-results__option--highlighted');

    return $highlighted;
  };

  Results.prototype.destroy = function () {
    this.$results.remove();
  };

  Results.prototype.ensureHighlightVisible = function () {
    var $highlighted = this.getHighlightedResults();

    if ($highlighted.length === 0) {
      return;
    }

    var $options = this.$results.find('[aria-selected]');

    var currentIndex = $options.index($highlighted);

    var currentOffset = this.$results.offset().top;
    var nextTop = $highlighted.offset().top;
    var nextOffset = this.$results.scrollTop() + (nextTop - currentOffset);

    var offsetDelta = nextTop - currentOffset;
    nextOffset -= $highlighted.outerHeight(false) * 2;

    if (currentIndex <= 2) {
      this.$results.scrollTop(0);
    } else if (offsetDelta > this.$results.outerHeight() || offsetDelta < 0) {
      this.$results.scrollTop(nextOffset);
    }
  };

  Results.prototype.template = function (result, container) {
    var template = this.options.get('templateResult');
    var escapeMarkup = this.options.get('escapeMarkup');

    var content = template(result, container);

    if (content == null) {
      container.style.display = 'none';
    } else if (typeof content === 'string') {
      container.innerHTML = escapeMarkup(content);
    } else {
      $(container).append(content);
    }
  };

  return Results;
});

S2.define('select2/keys',[

], function () {
  var KEYS = {
    BACKSPACE: 8,
    TAB: 9,
    ENTER: 13,
    SHIFT: 16,
    CTRL: 17,
    ALT: 18,
    ESC: 27,
    SPACE: 32,
    PAGE_UP: 33,
    PAGE_DOWN: 34,
    END: 35,
    HOME: 36,
    LEFT: 37,
    UP: 38,
    RIGHT: 39,
    DOWN: 40,
    DELETE: 46
  };

  return KEYS;
});

S2.define('select2/selection/base',[
  'jquery',
  '../utils',
  '../keys'
], function ($, Utils, KEYS) {
  function BaseSelection ($element, options) {
    this.$element = $element;
    this.options = options;

    BaseSelection.__super__.constructor.call(this);
  }

  Utils.Extend(BaseSelection, Utils.Observable);

  BaseSelection.prototype.render = function () {
    var $selection = $(
      '<span class="select2-selection" role="combobox" ' +
      ' aria-haspopup="true" aria-expanded="false">' +
      '</span>'
    );

    this._tabindex = 0;

    if (this.$element.data('old-tabindex') != null) {
      this._tabindex = this.$element.data('old-tabindex');
    } else if (this.$element.attr('tabindex') != null) {
      this._tabindex = this.$element.attr('tabindex');
    }

    $selection.attr('title', this.$element.attr('title'));
    $selection.attr('tabindex', this._tabindex);

    this.$selection = $selection;

    return $selection;
  };

  BaseSelection.prototype.bind = function (container, $container) {
    var self = this;

    var id = container.id + '-container';
    var resultsId = container.id + '-results';

    this.container = container;

    this.$selection.on('focus', function (evt) {
      self.trigger('focus', evt);
    });

    this.$selection.on('blur', function (evt) {
      self._handleBlur(evt);
    });

    this.$selection.on('keydown', function (evt) {
      self.trigger('keypress', evt);

      if (evt.which === KEYS.SPACE) {
        evt.preventDefault();
      }
    });

    container.on('results:focus', function (params) {
      self.$selection.attr('aria-activedescendant', params.data._resultId);
    });

    container.on('selection:update', function (params) {
      self.update(params.data);
    });

    container.on('open', function () {
      // When the dropdown is open, aria-expanded="true"
      self.$selection.attr('aria-expanded', 'true');
      self.$selection.attr('aria-owns', resultsId);

      self._attachCloseHandler(container);
    });

    container.on('close', function () {
      // When the dropdown is closed, aria-expanded="false"
      self.$selection.attr('aria-expanded', 'false');
      self.$selection.removeAttr('aria-activedescendant');
      self.$selection.removeAttr('aria-owns');

      self.$selection.focus();

      self._detachCloseHandler(container);
    });

    container.on('enable', function () {
      self.$selection.attr('tabindex', self._tabindex);
    });

    container.on('disable', function () {
      self.$selection.attr('tabindex', '-1');
    });
  };

  BaseSelection.prototype._handleBlur = function (evt) {
    var self = this;

    // This needs to be delayed as the active element is the body when the tab
    // key is pressed, possibly along with others.
    window.setTimeout(function () {
      // Don't trigger `blur` if the focus is still in the selection
      if (
        (document.activeElement == self.$selection[0]) ||
        ($.contains(self.$selection[0], document.activeElement))
      ) {
        return;
      }

      self.trigger('blur', evt);
    }, 1);
  };

  BaseSelection.prototype._attachCloseHandler = function (container) {
    var self = this;

    $(document.body).on('mousedown.select2.' + container.id, function (e) {
      var $target = $(e.target);

      var $select = $target.closest('.select2');

      var $all = $('.select2.select2-container--open');

      $all.each(function () {
        var $this = $(this);

        if (this == $select[0]) {
          return;
        }

        var $element = $this.data('element');

        $element.select2('close');
      });
    });
  };

  BaseSelection.prototype._detachCloseHandler = function (container) {
    $(document.body).off('mousedown.select2.' + container.id);
  };

  BaseSelection.prototype.position = function ($selection, $container) {
    var $selectionContainer = $container.find('.selection');
    $selectionContainer.append($selection);
  };

  BaseSelection.prototype.destroy = function () {
    this._detachCloseHandler(this.container);
  };

  BaseSelection.prototype.update = function (data) {
    throw new Error('The `update` method must be defined in child classes.');
  };

  return BaseSelection;
});

S2.define('select2/selection/single',[
  'jquery',
  './base',
  '../utils',
  '../keys'
], function ($, BaseSelection, Utils, KEYS) {
  function SingleSelection () {
    SingleSelection.__super__.constructor.apply(this, arguments);
  }

  Utils.Extend(SingleSelection, BaseSelection);

  SingleSelection.prototype.render = function () {
    var $selection = SingleSelection.__super__.render.call(this);

    $selection.addClass('select2-selection--single');

    $selection.html(
      '<span class="select2-selection__rendered"></span>' +
      '<span class="select2-selection__arrow" role="presentation">' +
        '<b role="presentation"></b>' +
      '</span>'
    );

    return $selection;
  };

  SingleSelection.prototype.bind = function (container, $container) {
    var self = this;

    SingleSelection.__super__.bind.apply(this, arguments);

    var id = container.id + '-container';

    this.$selection.find('.select2-selection__rendered').attr('id', id);
    this.$selection.attr('aria-labelledby', id);

    this.$selection.on('mousedown', function (evt) {
      // Only respond to left clicks
      if (evt.which !== 1) {
        return;
      }

      self.trigger('toggle', {
        originalEvent: evt
      });
    });

    this.$selection.on('focus', function (evt) {
      // User focuses on the container
    });

    this.$selection.on('blur', function (evt) {
      // User exits the container
    });

    container.on('focus', function (evt) {
      if (!container.isOpen()) {
        self.$selection.focus();
      }
    });

    container.on('selection:update', function (params) {
      self.update(params.data);
    });
  };

  SingleSelection.prototype.clear = function () {
    this.$selection.find('.select2-selection__rendered').empty();
  };

  SingleSelection.prototype.display = function (data, container) {
    var template = this.options.get('templateSelection');
    var escapeMarkup = this.options.get('escapeMarkup');

    return escapeMarkup(template(data, container));
  };

  SingleSelection.prototype.selectionContainer = function () {
    return $('<span></span>');
  };

  SingleSelection.prototype.update = function (data) {
    if (data.length === 0) {
      this.clear();
      return;
    }

    var selection = data[0];

    var $rendered = this.$selection.find('.select2-selection__rendered');
    var formatted = this.display(selection, $rendered);

    $rendered.empty().append(formatted);
    $rendered.prop('title', selection.title || selection.text);
  };

  return SingleSelection;
});

S2.define('select2/selection/multiple',[
  'jquery',
  './base',
  '../utils'
], function ($, BaseSelection, Utils) {
  function MultipleSelection ($element, options) {
    MultipleSelection.__super__.constructor.apply(this, arguments);
  }

  Utils.Extend(MultipleSelection, BaseSelection);

  MultipleSelection.prototype.render = function () {
    var $selection = MultipleSelection.__super__.render.call(this);

    $selection.addClass('select2-selection--multiple');

    $selection.html(
      '<ul class="select2-selection__rendered"></ul>'
    );

    return $selection;
  };

  MultipleSelection.prototype.bind = function (container, $container) {
    var self = this;

    MultipleSelection.__super__.bind.apply(this, arguments);

    this.$selection.on('click', function (evt) {
      self.trigger('toggle', {
        originalEvent: evt
      });
    });

    this.$selection.on(
      'click',
      '.select2-selection__choice__remove',
      function (evt) {
        // Ignore the event if it is disabled
        if (self.options.get('disabled')) {
          return;
        }

        var $remove = $(this);
        var $selection = $remove.parent();

        var data = $selection.data('data');

        self.trigger('unselect', {
          originalEvent: evt,
          data: data
        });
      }
    );
  };

  MultipleSelection.prototype.clear = function () {
    this.$selection.find('.select2-selection__rendered').empty();
  };

  MultipleSelection.prototype.display = function (data, container) {
    var template = this.options.get('templateSelection');
    var escapeMarkup = this.options.get('escapeMarkup');

    return escapeMarkup(template(data, container));
  };

  MultipleSelection.prototype.selectionContainer = function () {
    var $container = $(
      '<li class="select2-selection__choice">' +
        '<span class="select2-selection__choice__remove" role="presentation">' +
          '&times;' +
        '</span>' +
      '</li>'
    );

    return $container;
  };

  MultipleSelection.prototype.update = function (data) {
    this.clear();

    if (data.length === 0) {
      return;
    }

    var $selections = [];

    for (var d = 0; d < data.length; d++) {
      var selection = data[d];

      var $selection = this.selectionContainer();
      var formatted = this.display(selection, $selection);

      $selection.append(formatted);
      $selection.prop('title', selection.title || selection.text);

      $selection.data('data', selection);

      $selections.push($selection);
    }

    var $rendered = this.$selection.find('.select2-selection__rendered');

    Utils.appendMany($rendered, $selections);
  };

  return MultipleSelection;
});

S2.define('select2/selection/placeholder',[
  '../utils'
], function (Utils) {
  function Placeholder (decorated, $element, options) {
    this.placeholder = this.normalizePlaceholder(options.get('placeholder'));

    decorated.call(this, $element, options);
  }

  Placeholder.prototype.normalizePlaceholder = function (_, placeholder) {
    if (typeof placeholder === 'string') {
      placeholder = {
        id: '',
        text: placeholder
      };
    }

    return placeholder;
  };

  Placeholder.prototype.createPlaceholder = function (decorated, placeholder) {
    var $placeholder = this.selectionContainer();

    $placeholder.html(this.display(placeholder));
    $placeholder.addClass('select2-selection__placeholder')
                .removeClass('select2-selection__choice');

    return $placeholder;
  };

  Placeholder.prototype.update = function (decorated, data) {
    var singlePlaceholder = (
      data.length == 1 && data[0].id != this.placeholder.id
    );
    var multipleSelections = data.length > 1;

    if (multipleSelections || singlePlaceholder) {
      return decorated.call(this, data);
    }

    this.clear();

    var $placeholder = this.createPlaceholder(this.placeholder);

    this.$selection.find('.select2-selection__rendered').append($placeholder);
  };

  return Placeholder;
});

S2.define('select2/selection/allowClear',[
  'jquery',
  '../keys'
], function ($, KEYS) {
  function AllowClear () { }

  AllowClear.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    if (this.placeholder == null) {
      if (this.options.get('debug') && window.console && console.error) {
        console.error(
          'Select2: The `allowClear` option should be used in combination ' +
          'with the `placeholder` option.'
        );
      }
    }

    this.$selection.on('mousedown', '.select2-selection__clear',
      function (evt) {
        self._handleClear(evt);
    });

    container.on('keypress', function (evt) {
      self._handleKeyboardClear(evt, container);
    });
  };

  AllowClear.prototype._handleClear = function (_, evt) {
    // Ignore the event if it is disabled
    if (this.options.get('disabled')) {
      return;
    }

    var $clear = this.$selection.find('.select2-selection__clear');

    // Ignore the event if nothing has been selected
    if ($clear.length === 0) {
      return;
    }

    evt.stopPropagation();

    var data = $clear.data('data');

    for (var d = 0; d < data.length; d++) {
      var unselectData = {
        data: data[d]
      };

      // Trigger the `unselect` event, so people can prevent it from being
      // cleared.
      this.trigger('unselect', unselectData);

      // If the event was prevented, don't clear it out.
      if (unselectData.prevented) {
        return;
      }
    }

    this.$element.val(this.placeholder.id).trigger('change');

    this.trigger('toggle', {});
  };

  AllowClear.prototype._handleKeyboardClear = function (_, evt, container) {
    if (container.isOpen()) {
      return;
    }

    if (evt.which == KEYS.DELETE || evt.which == KEYS.BACKSPACE) {
      this._handleClear(evt);
    }
  };

  AllowClear.prototype.update = function (decorated, data) {
    decorated.call(this, data);

    if (this.$selection.find('.select2-selection__placeholder').length > 0 ||
        data.length === 0) {
      return;
    }

    var $remove = $(
      '<span class="select2-selection__clear">' +
        '&times;' +
      '</span>'
    );
    $remove.data('data', data);

    this.$selection.find('.select2-selection__rendered').prepend($remove);
  };

  return AllowClear;
});

S2.define('select2/selection/search',[
  'jquery',
  '../utils',
  '../keys'
], function ($, Utils, KEYS) {
  function Search (decorated, $element, options) {
    decorated.call(this, $element, options);
  }

  Search.prototype.render = function (decorated) {
    var $search = $(
      '<li class="select2-search select2-search--inline">' +
        '<input class="select2-search__field" type="search" tabindex="-1"' +
        ' autocomplete="off" autocorrect="off" autocapitalize="off"' +
        ' spellcheck="false" role="textbox" aria-autocomplete="list" />' +
      '</li>'
    );

    this.$searchContainer = $search;
    this.$search = $search.find('input');

    var $rendered = decorated.call(this);

    this._transferTabIndex();

    return $rendered;
  };

  Search.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    container.on('open', function () {
      self.$search.trigger('focus');
    });

    container.on('close', function () {
      self.$search.val('');
      self.$search.removeAttr('aria-activedescendant');
      self.$search.trigger('focus');
    });

    container.on('enable', function () {
      self.$search.prop('disabled', false);

      self._transferTabIndex();
    });

    container.on('disable', function () {
      self.$search.prop('disabled', true);
    });

    container.on('focus', function (evt) {
      self.$search.trigger('focus');
    });

    container.on('results:focus', function (params) {
      self.$search.attr('aria-activedescendant', params.id);
    });

    this.$selection.on('focusin', '.select2-search--inline', function (evt) {
      self.trigger('focus', evt);
    });

    this.$selection.on('focusout', '.select2-search--inline', function (evt) {
      self._handleBlur(evt);
    });

    this.$selection.on('keydown', '.select2-search--inline', function (evt) {
      evt.stopPropagation();

      self.trigger('keypress', evt);

      self._keyUpPrevented = evt.isDefaultPrevented();

      var key = evt.which;

      if (key === KEYS.BACKSPACE && self.$search.val() === '') {
        var $previousChoice = self.$searchContainer
          .prev('.select2-selection__choice');

        if ($previousChoice.length > 0) {
          var item = $previousChoice.data('data');

          self.searchRemoveChoice(item);

          evt.preventDefault();
        }
      }
    });

    // Try to detect the IE version should the `documentMode` property that
    // is stored on the document. This is only implemented in IE and is
    // slightly cleaner than doing a user agent check.
    // This property is not available in Edge, but Edge also doesn't have
    // this bug.
    var msie = document.documentMode;
    var disableInputEvents = msie && msie <= 11;

    // Workaround for browsers which do not support the `input` event
    // This will prevent double-triggering of events for browsers which support
    // both the `keyup` and `input` events.
    this.$selection.on(
      'input.searchcheck',
      '.select2-search--inline',
      function (evt) {
        // IE will trigger the `input` event when a placeholder is used on a
        // search box. To get around this issue, we are forced to ignore all
        // `input` events in IE and keep using `keyup`.
        if (disableInputEvents) {
          self.$selection.off('input.search input.searchcheck');
          return;
        }

        // Unbind the duplicated `keyup` event
        self.$selection.off('keyup.search');
      }
    );

    this.$selection.on(
      'keyup.search input.search',
      '.select2-search--inline',
      function (evt) {
        // IE will trigger the `input` event when a placeholder is used on a
        // search box. To get around this issue, we are forced to ignore all
        // `input` events in IE and keep using `keyup`.
        if (disableInputEvents && evt.type === 'input') {
          self.$selection.off('input.search input.searchcheck');
          return;
        }

        var key = evt.which;

        // We can freely ignore events from modifier keys
        if (key == KEYS.SHIFT || key == KEYS.CTRL || key == KEYS.ALT) {
          return;
        }

        // Tabbing will be handled during the `keydown` phase
        if (key == KEYS.TAB) {
          return;
        }

        self.handleSearch(evt);
      }
    );
  };

  /**
   * This method will transfer the tabindex attribute from the rendered
   * selection to the search box. This allows for the search box to be used as
   * the primary focus instead of the selection container.
   *
   * @private
   */
  Search.prototype._transferTabIndex = function (decorated) {
    this.$search.attr('tabindex', this.$selection.attr('tabindex'));
    this.$selection.attr('tabindex', '-1');
  };

  Search.prototype.createPlaceholder = function (decorated, placeholder) {
    this.$search.attr('placeholder', placeholder.text);
  };

  Search.prototype.update = function (decorated, data) {
    var searchHadFocus = this.$search[0] == document.activeElement;

    this.$search.attr('placeholder', '');

    decorated.call(this, data);

    this.$selection.find('.select2-selection__rendered')
                   .append(this.$searchContainer);

    this.resizeSearch();
    if (searchHadFocus) {
      this.$search.focus();
    }
  };

  Search.prototype.handleSearch = function () {
    this.resizeSearch();

    if (!this._keyUpPrevented) {
      var input = this.$search.val();

      this.trigger('query', {
        term: input
      });
    }

    this._keyUpPrevented = false;
  };

  Search.prototype.searchRemoveChoice = function (decorated, item) {
    this.trigger('unselect', {
      data: item
    });

    this.$search.val(item.text);
    this.handleSearch();
  };

  Search.prototype.resizeSearch = function () {
    this.$search.css('width', '25px');

    var width = '';

    if (this.$search.attr('placeholder') !== '') {
      width = this.$selection.find('.select2-selection__rendered').innerWidth();
    } else {
      var minimumWidth = this.$search.val().length + 1;

      width = (minimumWidth * 0.75) + 'em';
    }

    this.$search.css('width', width);
  };

  return Search;
});

S2.define('select2/selection/eventRelay',[
  'jquery'
], function ($) {
  function EventRelay () { }

  EventRelay.prototype.bind = function (decorated, container, $container) {
    var self = this;
    var relayEvents = [
      'open', 'opening',
      'close', 'closing',
      'select', 'selecting',
      'unselect', 'unselecting'
    ];

    var preventableEvents = ['opening', 'closing', 'selecting', 'unselecting'];

    decorated.call(this, container, $container);

    container.on('*', function (name, params) {
      // Ignore events that should not be relayed
      if ($.inArray(name, relayEvents) === -1) {
        return;
      }

      // The parameters should always be an object
      params = params || {};

      // Generate the jQuery event for the Select2 event
      var evt = $.Event('select2:' + name, {
        params: params
      });

      self.$element.trigger(evt);

      // Only handle preventable events if it was one
      if ($.inArray(name, preventableEvents) === -1) {
        return;
      }

      params.prevented = evt.isDefaultPrevented();
    });
  };

  return EventRelay;
});

S2.define('select2/translation',[
  'jquery',
  'require'
], function ($, require) {
  function Translation (dict) {
    this.dict = dict || {};
  }

  Translation.prototype.all = function () {
    return this.dict;
  };

  Translation.prototype.get = function (key) {
    return this.dict[key];
  };

  Translation.prototype.extend = function (translation) {
    this.dict = $.extend({}, translation.all(), this.dict);
  };

  // Static functions

  Translation._cache = {};

  Translation.loadPath = function (path) {
    if (!(path in Translation._cache)) {
      var translations = require(path);

      Translation._cache[path] = translations;
    }

    return new Translation(Translation._cache[path]);
  };

  return Translation;
});

S2.define('select2/diacritics',[

], function () {
  var diacritics = {
    '\u24B6': 'A',
    '\uFF21': 'A',
    '\u00C0': 'A',
    '\u00C1': 'A',
    '\u00C2': 'A',
    '\u1EA6': 'A',
    '\u1EA4': 'A',
    '\u1EAA': 'A',
    '\u1EA8': 'A',
    '\u00C3': 'A',
    '\u0100': 'A',
    '\u0102': 'A',
    '\u1EB0': 'A',
    '\u1EAE': 'A',
    '\u1EB4': 'A',
    '\u1EB2': 'A',
    '\u0226': 'A',
    '\u01E0': 'A',
    '\u00C4': 'A',
    '\u01DE': 'A',
    '\u1EA2': 'A',
    '\u00C5': 'A',
    '\u01FA': 'A',
    '\u01CD': 'A',
    '\u0200': 'A',
    '\u0202': 'A',
    '\u1EA0': 'A',
    '\u1EAC': 'A',
    '\u1EB6': 'A',
    '\u1E00': 'A',
    '\u0104': 'A',
    '\u023A': 'A',
    '\u2C6F': 'A',
    '\uA732': 'AA',
    '\u00C6': 'AE',
    '\u01FC': 'AE',
    '\u01E2': 'AE',
    '\uA734': 'AO',
    '\uA736': 'AU',
    '\uA738': 'AV',
    '\uA73A': 'AV',
    '\uA73C': 'AY',
    '\u24B7': 'B',
    '\uFF22': 'B',
    '\u1E02': 'B',
    '\u1E04': 'B',
    '\u1E06': 'B',
    '\u0243': 'B',
    '\u0182': 'B',
    '\u0181': 'B',
    '\u24B8': 'C',
    '\uFF23': 'C',
    '\u0106': 'C',
    '\u0108': 'C',
    '\u010A': 'C',
    '\u010C': 'C',
    '\u00C7': 'C',
    '\u1E08': 'C',
    '\u0187': 'C',
    '\u023B': 'C',
    '\uA73E': 'C',
    '\u24B9': 'D',
    '\uFF24': 'D',
    '\u1E0A': 'D',
    '\u010E': 'D',
    '\u1E0C': 'D',
    '\u1E10': 'D',
    '\u1E12': 'D',
    '\u1E0E': 'D',
    '\u0110': 'D',
    '\u018B': 'D',
    '\u018A': 'D',
    '\u0189': 'D',
    '\uA779': 'D',
    '\u01F1': 'DZ',
    '\u01C4': 'DZ',
    '\u01F2': 'Dz',
    '\u01C5': 'Dz',
    '\u24BA': 'E',
    '\uFF25': 'E',
    '\u00C8': 'E',
    '\u00C9': 'E',
    '\u00CA': 'E',
    '\u1EC0': 'E',
    '\u1EBE': 'E',
    '\u1EC4': 'E',
    '\u1EC2': 'E',
    '\u1EBC': 'E',
    '\u0112': 'E',
    '\u1E14': 'E',
    '\u1E16': 'E',
    '\u0114': 'E',
    '\u0116': 'E',
    '\u00CB': 'E',
    '\u1EBA': 'E',
    '\u011A': 'E',
    '\u0204': 'E',
    '\u0206': 'E',
    '\u1EB8': 'E',
    '\u1EC6': 'E',
    '\u0228': 'E',
    '\u1E1C': 'E',
    '\u0118': 'E',
    '\u1E18': 'E',
    '\u1E1A': 'E',
    '\u0190': 'E',
    '\u018E': 'E',
    '\u24BB': 'F',
    '\uFF26': 'F',
    '\u1E1E': 'F',
    '\u0191': 'F',
    '\uA77B': 'F',
    '\u24BC': 'G',
    '\uFF27': 'G',
    '\u01F4': 'G',
    '\u011C': 'G',
    '\u1E20': 'G',
    '\u011E': 'G',
    '\u0120': 'G',
    '\u01E6': 'G',
    '\u0122': 'G',
    '\u01E4': 'G',
    '\u0193': 'G',
    '\uA7A0': 'G',
    '\uA77D': 'G',
    '\uA77E': 'G',
    '\u24BD': 'H',
    '\uFF28': 'H',
    '\u0124': 'H',
    '\u1E22': 'H',
    '\u1E26': 'H',
    '\u021E': 'H',
    '\u1E24': 'H',
    '\u1E28': 'H',
    '\u1E2A': 'H',
    '\u0126': 'H',
    '\u2C67': 'H',
    '\u2C75': 'H',
    '\uA78D': 'H',
    '\u24BE': 'I',
    '\uFF29': 'I',
    '\u00CC': 'I',
    '\u00CD': 'I',
    '\u00CE': 'I',
    '\u0128': 'I',
    '\u012A': 'I',
    '\u012C': 'I',
    '\u0130': 'I',
    '\u00CF': 'I',
    '\u1E2E': 'I',
    '\u1EC8': 'I',
    '\u01CF': 'I',
    '\u0208': 'I',
    '\u020A': 'I',
    '\u1ECA': 'I',
    '\u012E': 'I',
    '\u1E2C': 'I',
    '\u0197': 'I',
    '\u24BF': 'J',
    '\uFF2A': 'J',
    '\u0134': 'J',
    '\u0248': 'J',
    '\u24C0': 'K',
    '\uFF2B': 'K',
    '\u1E30': 'K',
    '\u01E8': 'K',
    '\u1E32': 'K',
    '\u0136': 'K',
    '\u1E34': 'K',
    '\u0198': 'K',
    '\u2C69': 'K',
    '\uA740': 'K',
    '\uA742': 'K',
    '\uA744': 'K',
    '\uA7A2': 'K',
    '\u24C1': 'L',
    '\uFF2C': 'L',
    '\u013F': 'L',
    '\u0139': 'L',
    '\u013D': 'L',
    '\u1E36': 'L',
    '\u1E38': 'L',
    '\u013B': 'L',
    '\u1E3C': 'L',
    '\u1E3A': 'L',
    '\u0141': 'L',
    '\u023D': 'L',
    '\u2C62': 'L',
    '\u2C60': 'L',
    '\uA748': 'L',
    '\uA746': 'L',
    '\uA780': 'L',
    '\u01C7': 'LJ',
    '\u01C8': 'Lj',
    '\u24C2': 'M',
    '\uFF2D': 'M',
    '\u1E3E': 'M',
    '\u1E40': 'M',
    '\u1E42': 'M',
    '\u2C6E': 'M',
    '\u019C': 'M',
    '\u24C3': 'N',
    '\uFF2E': 'N',
    '\u01F8': 'N',
    '\u0143': 'N',
    '\u00D1': 'N',
    '\u1E44': 'N',
    '\u0147': 'N',
    '\u1E46': 'N',
    '\u0145': 'N',
    '\u1E4A': 'N',
    '\u1E48': 'N',
    '\u0220': 'N',
    '\u019D': 'N',
    '\uA790': 'N',
    '\uA7A4': 'N',
    '\u01CA': 'NJ',
    '\u01CB': 'Nj',
    '\u24C4': 'O',
    '\uFF2F': 'O',
    '\u00D2': 'O',
    '\u00D3': 'O',
    '\u00D4': 'O',
    '\u1ED2': 'O',
    '\u1ED0': 'O',
    '\u1ED6': 'O',
    '\u1ED4': 'O',
    '\u00D5': 'O',
    '\u1E4C': 'O',
    '\u022C': 'O',
    '\u1E4E': 'O',
    '\u014C': 'O',
    '\u1E50': 'O',
    '\u1E52': 'O',
    '\u014E': 'O',
    '\u022E': 'O',
    '\u0230': 'O',
    '\u00D6': 'O',
    '\u022A': 'O',
    '\u1ECE': 'O',
    '\u0150': 'O',
    '\u01D1': 'O',
    '\u020C': 'O',
    '\u020E': 'O',
    '\u01A0': 'O',
    '\u1EDC': 'O',
    '\u1EDA': 'O',
    '\u1EE0': 'O',
    '\u1EDE': 'O',
    '\u1EE2': 'O',
    '\u1ECC': 'O',
    '\u1ED8': 'O',
    '\u01EA': 'O',
    '\u01EC': 'O',
    '\u00D8': 'O',
    '\u01FE': 'O',
    '\u0186': 'O',
    '\u019F': 'O',
    '\uA74A': 'O',
    '\uA74C': 'O',
    '\u01A2': 'OI',
    '\uA74E': 'OO',
    '\u0222': 'OU',
    '\u24C5': 'P',
    '\uFF30': 'P',
    '\u1E54': 'P',
    '\u1E56': 'P',
    '\u01A4': 'P',
    '\u2C63': 'P',
    '\uA750': 'P',
    '\uA752': 'P',
    '\uA754': 'P',
    '\u24C6': 'Q',
    '\uFF31': 'Q',
    '\uA756': 'Q',
    '\uA758': 'Q',
    '\u024A': 'Q',
    '\u24C7': 'R',
    '\uFF32': 'R',
    '\u0154': 'R',
    '\u1E58': 'R',
    '\u0158': 'R',
    '\u0210': 'R',
    '\u0212': 'R',
    '\u1E5A': 'R',
    '\u1E5C': 'R',
    '\u0156': 'R',
    '\u1E5E': 'R',
    '\u024C': 'R',
    '\u2C64': 'R',
    '\uA75A': 'R',
    '\uA7A6': 'R',
    '\uA782': 'R',
    '\u24C8': 'S',
    '\uFF33': 'S',
    '\u1E9E': 'S',
    '\u015A': 'S',
    '\u1E64': 'S',
    '\u015C': 'S',
    '\u1E60': 'S',
    '\u0160': 'S',
    '\u1E66': 'S',
    '\u1E62': 'S',
    '\u1E68': 'S',
    '\u0218': 'S',
    '\u015E': 'S',
    '\u2C7E': 'S',
    '\uA7A8': 'S',
    '\uA784': 'S',
    '\u24C9': 'T',
    '\uFF34': 'T',
    '\u1E6A': 'T',
    '\u0164': 'T',
    '\u1E6C': 'T',
    '\u021A': 'T',
    '\u0162': 'T',
    '\u1E70': 'T',
    '\u1E6E': 'T',
    '\u0166': 'T',
    '\u01AC': 'T',
    '\u01AE': 'T',
    '\u023E': 'T',
    '\uA786': 'T',
    '\uA728': 'TZ',
    '\u24CA': 'U',
    '\uFF35': 'U',
    '\u00D9': 'U',
    '\u00DA': 'U',
    '\u00DB': 'U',
    '\u0168': 'U',
    '\u1E78': 'U',
    '\u016A': 'U',
    '\u1E7A': 'U',
    '\u016C': 'U',
    '\u00DC': 'U',
    '\u01DB': 'U',
    '\u01D7': 'U',
    '\u01D5': 'U',
    '\u01D9': 'U',
    '\u1EE6': 'U',
    '\u016E': 'U',
    '\u0170': 'U',
    '\u01D3': 'U',
    '\u0214': 'U',
    '\u0216': 'U',
    '\u01AF': 'U',
    '\u1EEA': 'U',
    '\u1EE8': 'U',
    '\u1EEE': 'U',
    '\u1EEC': 'U',
    '\u1EF0': 'U',
    '\u1EE4': 'U',
    '\u1E72': 'U',
    '\u0172': 'U',
    '\u1E76': 'U',
    '\u1E74': 'U',
    '\u0244': 'U',
    '\u24CB': 'V',
    '\uFF36': 'V',
    '\u1E7C': 'V',
    '\u1E7E': 'V',
    '\u01B2': 'V',
    '\uA75E': 'V',
    '\u0245': 'V',
    '\uA760': 'VY',
    '\u24CC': 'W',
    '\uFF37': 'W',
    '\u1E80': 'W',
    '\u1E82': 'W',
    '\u0174': 'W',
    '\u1E86': 'W',
    '\u1E84': 'W',
    '\u1E88': 'W',
    '\u2C72': 'W',
    '\u24CD': 'X',
    '\uFF38': 'X',
    '\u1E8A': 'X',
    '\u1E8C': 'X',
    '\u24CE': 'Y',
    '\uFF39': 'Y',
    '\u1EF2': 'Y',
    '\u00DD': 'Y',
    '\u0176': 'Y',
    '\u1EF8': 'Y',
    '\u0232': 'Y',
    '\u1E8E': 'Y',
    '\u0178': 'Y',
    '\u1EF6': 'Y',
    '\u1EF4': 'Y',
    '\u01B3': 'Y',
    '\u024E': 'Y',
    '\u1EFE': 'Y',
    '\u24CF': 'Z',
    '\uFF3A': 'Z',
    '\u0179': 'Z',
    '\u1E90': 'Z',
    '\u017B': 'Z',
    '\u017D': 'Z',
    '\u1E92': 'Z',
    '\u1E94': 'Z',
    '\u01B5': 'Z',
    '\u0224': 'Z',
    '\u2C7F': 'Z',
    '\u2C6B': 'Z',
    '\uA762': 'Z',
    '\u24D0': 'a',
    '\uFF41': 'a',
    '\u1E9A': 'a',
    '\u00E0': 'a',
    '\u00E1': 'a',
    '\u00E2': 'a',
    '\u1EA7': 'a',
    '\u1EA5': 'a',
    '\u1EAB': 'a',
    '\u1EA9': 'a',
    '\u00E3': 'a',
    '\u0101': 'a',
    '\u0103': 'a',
    '\u1EB1': 'a',
    '\u1EAF': 'a',
    '\u1EB5': 'a',
    '\u1EB3': 'a',
    '\u0227': 'a',
    '\u01E1': 'a',
    '\u00E4': 'a',
    '\u01DF': 'a',
    '\u1EA3': 'a',
    '\u00E5': 'a',
    '\u01FB': 'a',
    '\u01CE': 'a',
    '\u0201': 'a',
    '\u0203': 'a',
    '\u1EA1': 'a',
    '\u1EAD': 'a',
    '\u1EB7': 'a',
    '\u1E01': 'a',
    '\u0105': 'a',
    '\u2C65': 'a',
    '\u0250': 'a',
    '\uA733': 'aa',
    '\u00E6': 'ae',
    '\u01FD': 'ae',
    '\u01E3': 'ae',
    '\uA735': 'ao',
    '\uA737': 'au',
    '\uA739': 'av',
    '\uA73B': 'av',
    '\uA73D': 'ay',
    '\u24D1': 'b',
    '\uFF42': 'b',
    '\u1E03': 'b',
    '\u1E05': 'b',
    '\u1E07': 'b',
    '\u0180': 'b',
    '\u0183': 'b',
    '\u0253': 'b',
    '\u24D2': 'c',
    '\uFF43': 'c',
    '\u0107': 'c',
    '\u0109': 'c',
    '\u010B': 'c',
    '\u010D': 'c',
    '\u00E7': 'c',
    '\u1E09': 'c',
    '\u0188': 'c',
    '\u023C': 'c',
    '\uA73F': 'c',
    '\u2184': 'c',
    '\u24D3': 'd',
    '\uFF44': 'd',
    '\u1E0B': 'd',
    '\u010F': 'd',
    '\u1E0D': 'd',
    '\u1E11': 'd',
    '\u1E13': 'd',
    '\u1E0F': 'd',
    '\u0111': 'd',
    '\u018C': 'd',
    '\u0256': 'd',
    '\u0257': 'd',
    '\uA77A': 'd',
    '\u01F3': 'dz',
    '\u01C6': 'dz',
    '\u24D4': 'e',
    '\uFF45': 'e',
    '\u00E8': 'e',
    '\u00E9': 'e',
    '\u00EA': 'e',
    '\u1EC1': 'e',
    '\u1EBF': 'e',
    '\u1EC5': 'e',
    '\u1EC3': 'e',
    '\u1EBD': 'e',
    '\u0113': 'e',
    '\u1E15': 'e',
    '\u1E17': 'e',
    '\u0115': 'e',
    '\u0117': 'e',
    '\u00EB': 'e',
    '\u1EBB': 'e',
    '\u011B': 'e',
    '\u0205': 'e',
    '\u0207': 'e',
    '\u1EB9': 'e',
    '\u1EC7': 'e',
    '\u0229': 'e',
    '\u1E1D': 'e',
    '\u0119': 'e',
    '\u1E19': 'e',
    '\u1E1B': 'e',
    '\u0247': 'e',
    '\u025B': 'e',
    '\u01DD': 'e',
    '\u24D5': 'f',
    '\uFF46': 'f',
    '\u1E1F': 'f',
    '\u0192': 'f',
    '\uA77C': 'f',
    '\u24D6': 'g',
    '\uFF47': 'g',
    '\u01F5': 'g',
    '\u011D': 'g',
    '\u1E21': 'g',
    '\u011F': 'g',
    '\u0121': 'g',
    '\u01E7': 'g',
    '\u0123': 'g',
    '\u01E5': 'g',
    '\u0260': 'g',
    '\uA7A1': 'g',
    '\u1D79': 'g',
    '\uA77F': 'g',
    '\u24D7': 'h',
    '\uFF48': 'h',
    '\u0125': 'h',
    '\u1E23': 'h',
    '\u1E27': 'h',
    '\u021F': 'h',
    '\u1E25': 'h',
    '\u1E29': 'h',
    '\u1E2B': 'h',
    '\u1E96': 'h',
    '\u0127': 'h',
    '\u2C68': 'h',
    '\u2C76': 'h',
    '\u0265': 'h',
    '\u0195': 'hv',
    '\u24D8': 'i',
    '\uFF49': 'i',
    '\u00EC': 'i',
    '\u00ED': 'i',
    '\u00EE': 'i',
    '\u0129': 'i',
    '\u012B': 'i',
    '\u012D': 'i',
    '\u00EF': 'i',
    '\u1E2F': 'i',
    '\u1EC9': 'i',
    '\u01D0': 'i',
    '\u0209': 'i',
    '\u020B': 'i',
    '\u1ECB': 'i',
    '\u012F': 'i',
    '\u1E2D': 'i',
    '\u0268': 'i',
    '\u0131': 'i',
    '\u24D9': 'j',
    '\uFF4A': 'j',
    '\u0135': 'j',
    '\u01F0': 'j',
    '\u0249': 'j',
    '\u24DA': 'k',
    '\uFF4B': 'k',
    '\u1E31': 'k',
    '\u01E9': 'k',
    '\u1E33': 'k',
    '\u0137': 'k',
    '\u1E35': 'k',
    '\u0199': 'k',
    '\u2C6A': 'k',
    '\uA741': 'k',
    '\uA743': 'k',
    '\uA745': 'k',
    '\uA7A3': 'k',
    '\u24DB': 'l',
    '\uFF4C': 'l',
    '\u0140': 'l',
    '\u013A': 'l',
    '\u013E': 'l',
    '\u1E37': 'l',
    '\u1E39': 'l',
    '\u013C': 'l',
    '\u1E3D': 'l',
    '\u1E3B': 'l',
    '\u017F': 'l',
    '\u0142': 'l',
    '\u019A': 'l',
    '\u026B': 'l',
    '\u2C61': 'l',
    '\uA749': 'l',
    '\uA781': 'l',
    '\uA747': 'l',
    '\u01C9': 'lj',
    '\u24DC': 'm',
    '\uFF4D': 'm',
    '\u1E3F': 'm',
    '\u1E41': 'm',
    '\u1E43': 'm',
    '\u0271': 'm',
    '\u026F': 'm',
    '\u24DD': 'n',
    '\uFF4E': 'n',
    '\u01F9': 'n',
    '\u0144': 'n',
    '\u00F1': 'n',
    '\u1E45': 'n',
    '\u0148': 'n',
    '\u1E47': 'n',
    '\u0146': 'n',
    '\u1E4B': 'n',
    '\u1E49': 'n',
    '\u019E': 'n',
    '\u0272': 'n',
    '\u0149': 'n',
    '\uA791': 'n',
    '\uA7A5': 'n',
    '\u01CC': 'nj',
    '\u24DE': 'o',
    '\uFF4F': 'o',
    '\u00F2': 'o',
    '\u00F3': 'o',
    '\u00F4': 'o',
    '\u1ED3': 'o',
    '\u1ED1': 'o',
    '\u1ED7': 'o',
    '\u1ED5': 'o',
    '\u00F5': 'o',
    '\u1E4D': 'o',
    '\u022D': 'o',
    '\u1E4F': 'o',
    '\u014D': 'o',
    '\u1E51': 'o',
    '\u1E53': 'o',
    '\u014F': 'o',
    '\u022F': 'o',
    '\u0231': 'o',
    '\u00F6': 'o',
    '\u022B': 'o',
    '\u1ECF': 'o',
    '\u0151': 'o',
    '\u01D2': 'o',
    '\u020D': 'o',
    '\u020F': 'o',
    '\u01A1': 'o',
    '\u1EDD': 'o',
    '\u1EDB': 'o',
    '\u1EE1': 'o',
    '\u1EDF': 'o',
    '\u1EE3': 'o',
    '\u1ECD': 'o',
    '\u1ED9': 'o',
    '\u01EB': 'o',
    '\u01ED': 'o',
    '\u00F8': 'o',
    '\u01FF': 'o',
    '\u0254': 'o',
    '\uA74B': 'o',
    '\uA74D': 'o',
    '\u0275': 'o',
    '\u01A3': 'oi',
    '\u0223': 'ou',
    '\uA74F': 'oo',
    '\u24DF': 'p',
    '\uFF50': 'p',
    '\u1E55': 'p',
    '\u1E57': 'p',
    '\u01A5': 'p',
    '\u1D7D': 'p',
    '\uA751': 'p',
    '\uA753': 'p',
    '\uA755': 'p',
    '\u24E0': 'q',
    '\uFF51': 'q',
    '\u024B': 'q',
    '\uA757': 'q',
    '\uA759': 'q',
    '\u24E1': 'r',
    '\uFF52': 'r',
    '\u0155': 'r',
    '\u1E59': 'r',
    '\u0159': 'r',
    '\u0211': 'r',
    '\u0213': 'r',
    '\u1E5B': 'r',
    '\u1E5D': 'r',
    '\u0157': 'r',
    '\u1E5F': 'r',
    '\u024D': 'r',
    '\u027D': 'r',
    '\uA75B': 'r',
    '\uA7A7': 'r',
    '\uA783': 'r',
    '\u24E2': 's',
    '\uFF53': 's',
    '\u00DF': 's',
    '\u015B': 's',
    '\u1E65': 's',
    '\u015D': 's',
    '\u1E61': 's',
    '\u0161': 's',
    '\u1E67': 's',
    '\u1E63': 's',
    '\u1E69': 's',
    '\u0219': 's',
    '\u015F': 's',
    '\u023F': 's',
    '\uA7A9': 's',
    '\uA785': 's',
    '\u1E9B': 's',
    '\u24E3': 't',
    '\uFF54': 't',
    '\u1E6B': 't',
    '\u1E97': 't',
    '\u0165': 't',
    '\u1E6D': 't',
    '\u021B': 't',
    '\u0163': 't',
    '\u1E71': 't',
    '\u1E6F': 't',
    '\u0167': 't',
    '\u01AD': 't',
    '\u0288': 't',
    '\u2C66': 't',
    '\uA787': 't',
    '\uA729': 'tz',
    '\u24E4': 'u',
    '\uFF55': 'u',
    '\u00F9': 'u',
    '\u00FA': 'u',
    '\u00FB': 'u',
    '\u0169': 'u',
    '\u1E79': 'u',
    '\u016B': 'u',
    '\u1E7B': 'u',
    '\u016D': 'u',
    '\u00FC': 'u',
    '\u01DC': 'u',
    '\u01D8': 'u',
    '\u01D6': 'u',
    '\u01DA': 'u',
    '\u1EE7': 'u',
    '\u016F': 'u',
    '\u0171': 'u',
    '\u01D4': 'u',
    '\u0215': 'u',
    '\u0217': 'u',
    '\u01B0': 'u',
    '\u1EEB': 'u',
    '\u1EE9': 'u',
    '\u1EEF': 'u',
    '\u1EED': 'u',
    '\u1EF1': 'u',
    '\u1EE5': 'u',
    '\u1E73': 'u',
    '\u0173': 'u',
    '\u1E77': 'u',
    '\u1E75': 'u',
    '\u0289': 'u',
    '\u24E5': 'v',
    '\uFF56': 'v',
    '\u1E7D': 'v',
    '\u1E7F': 'v',
    '\u028B': 'v',
    '\uA75F': 'v',
    '\u028C': 'v',
    '\uA761': 'vy',
    '\u24E6': 'w',
    '\uFF57': 'w',
    '\u1E81': 'w',
    '\u1E83': 'w',
    '\u0175': 'w',
    '\u1E87': 'w',
    '\u1E85': 'w',
    '\u1E98': 'w',
    '\u1E89': 'w',
    '\u2C73': 'w',
    '\u24E7': 'x',
    '\uFF58': 'x',
    '\u1E8B': 'x',
    '\u1E8D': 'x',
    '\u24E8': 'y',
    '\uFF59': 'y',
    '\u1EF3': 'y',
    '\u00FD': 'y',
    '\u0177': 'y',
    '\u1EF9': 'y',
    '\u0233': 'y',
    '\u1E8F': 'y',
    '\u00FF': 'y',
    '\u1EF7': 'y',
    '\u1E99': 'y',
    '\u1EF5': 'y',
    '\u01B4': 'y',
    '\u024F': 'y',
    '\u1EFF': 'y',
    '\u24E9': 'z',
    '\uFF5A': 'z',
    '\u017A': 'z',
    '\u1E91': 'z',
    '\u017C': 'z',
    '\u017E': 'z',
    '\u1E93': 'z',
    '\u1E95': 'z',
    '\u01B6': 'z',
    '\u0225': 'z',
    '\u0240': 'z',
    '\u2C6C': 'z',
    '\uA763': 'z',
    '\u0386': '\u0391',
    '\u0388': '\u0395',
    '\u0389': '\u0397',
    '\u038A': '\u0399',
    '\u03AA': '\u0399',
    '\u038C': '\u039F',
    '\u038E': '\u03A5',
    '\u03AB': '\u03A5',
    '\u038F': '\u03A9',
    '\u03AC': '\u03B1',
    '\u03AD': '\u03B5',
    '\u03AE': '\u03B7',
    '\u03AF': '\u03B9',
    '\u03CA': '\u03B9',
    '\u0390': '\u03B9',
    '\u03CC': '\u03BF',
    '\u03CD': '\u03C5',
    '\u03CB': '\u03C5',
    '\u03B0': '\u03C5',
    '\u03C9': '\u03C9',
    '\u03C2': '\u03C3'
  };

  return diacritics;
});

S2.define('select2/data/base',[
  '../utils'
], function (Utils) {
  function BaseAdapter ($element, options) {
    BaseAdapter.__super__.constructor.call(this);
  }

  Utils.Extend(BaseAdapter, Utils.Observable);

  BaseAdapter.prototype.current = function (callback) {
    throw new Error('The `current` method must be defined in child classes.');
  };

  BaseAdapter.prototype.query = function (params, callback) {
    throw new Error('The `query` method must be defined in child classes.');
  };

  BaseAdapter.prototype.bind = function (container, $container) {
    // Can be implemented in subclasses
  };

  BaseAdapter.prototype.destroy = function () {
    // Can be implemented in subclasses
  };

  BaseAdapter.prototype.generateResultId = function (container, data) {
    var id = container.id + '-result-';

    id += Utils.generateChars(4);

    if (data.id != null) {
      id += '-' + data.id.toString();
    } else {
      id += '-' + Utils.generateChars(4);
    }
    return id;
  };

  return BaseAdapter;
});

S2.define('select2/data/select',[
  './base',
  '../utils',
  'jquery'
], function (BaseAdapter, Utils, $) {
  function SelectAdapter ($element, options) {
    this.$element = $element;
    this.options = options;

    SelectAdapter.__super__.constructor.call(this);
  }

  Utils.Extend(SelectAdapter, BaseAdapter);

  SelectAdapter.prototype.current = function (callback) {
    var data = [];
    var self = this;

    this.$element.find(':selected').each(function () {
      var $option = $(this);

      var option = self.item($option);

      data.push(option);
    });

    callback(data);
  };

  SelectAdapter.prototype.select = function (data) {
    var self = this;

    data.selected = true;

    // If data.element is a DOM node, use it instead
    if ($(data.element).is('option')) {
      data.element.selected = true;

      this.$element.trigger('change');

      return;
    }

    if (this.$element.prop('multiple')) {
      this.current(function (currentData) {
        var val = [];

        data = [data];
        data.push.apply(data, currentData);

        for (var d = 0; d < data.length; d++) {
          var id = data[d].id;

          if ($.inArray(id, val) === -1) {
            val.push(id);
          }
        }

        self.$element.val(val);
        self.$element.trigger('change');
      });
    } else {
      var val = data.id;

      this.$element.val(val);
      this.$element.trigger('change');
    }
  };

  SelectAdapter.prototype.unselect = function (data) {
    var self = this;

    if (!this.$element.prop('multiple')) {
      return;
    }

    data.selected = false;

    if ($(data.element).is('option')) {
      data.element.selected = false;

      this.$element.trigger('change');

      return;
    }

    this.current(function (currentData) {
      var val = [];

      for (var d = 0; d < currentData.length; d++) {
        var id = currentData[d].id;

        if (id !== data.id && $.inArray(id, val) === -1) {
          val.push(id);
        }
      }

      self.$element.val(val);

      self.$element.trigger('change');
    });
  };

  SelectAdapter.prototype.bind = function (container, $container) {
    var self = this;

    this.container = container;

    container.on('select', function (params) {
      self.select(params.data);
    });

    container.on('unselect', function (params) {
      self.unselect(params.data);
    });
  };

  SelectAdapter.prototype.destroy = function () {
    // Remove anything added to child elements
    this.$element.find('*').each(function () {
      // Remove any custom data set by Select2
      $.removeData(this, 'data');
    });
  };

  SelectAdapter.prototype.query = function (params, callback) {
    var data = [];
    var self = this;

    var $options = this.$element.children();

    $options.each(function () {
      var $option = $(this);

      if (!$option.is('option') && !$option.is('optgroup')) {
        return;
      }

      var option = self.item($option);

      var matches = self.matches(params, option);

      if (matches !== null) {
        data.push(matches);
      }
    });

    callback({
      results: data
    });
  };

  SelectAdapter.prototype.addOptions = function ($options) {
    Utils.appendMany(this.$element, $options);
  };

  SelectAdapter.prototype.option = function (data) {
    var option;

    if (data.children) {
      option = document.createElement('optgroup');
      option.label = data.text;
    } else {
      option = document.createElement('option');

      if (option.textContent !== undefined) {
        option.textContent = data.text;
      } else {
        option.innerText = data.text;
      }
    }

    if (data.id) {
      option.value = data.id;
    }

    if (data.disabled) {
      option.disabled = true;
    }

    if (data.selected) {
      option.selected = true;
    }

    if (data.title) {
      option.title = data.title;
    }

    var $option = $(option);

    var normalizedData = this._normalizeItem(data);
    normalizedData.element = option;

    // Override the option's data with the combined data
    $.data(option, 'data', normalizedData);

    return $option;
  };

  SelectAdapter.prototype.item = function ($option) {
    var data = {};

    data = $.data($option[0], 'data');

    if (data != null) {
      return data;
    }

    if ($option.is('option')) {
      data = {
        id: $option.val(),
        text: $option.text(),
        disabled: $option.prop('disabled'),
        selected: $option.prop('selected'),
        title: $option.prop('title')
      };
    } else if ($option.is('optgroup')) {
      data = {
        text: $option.prop('label'),
        children: [],
        title: $option.prop('title')
      };

      var $children = $option.children('option');
      var children = [];

      for (var c = 0; c < $children.length; c++) {
        var $child = $($children[c]);

        var child = this.item($child);

        children.push(child);
      }

      data.children = children;
    }

    data = this._normalizeItem(data);
    data.element = $option[0];

    $.data($option[0], 'data', data);

    return data;
  };

  SelectAdapter.prototype._normalizeItem = function (item) {
    if (!$.isPlainObject(item)) {
      item = {
        id: item,
        text: item
      };
    }

    item = $.extend({}, {
      text: ''
    }, item);

    var defaults = {
      selected: false,
      disabled: false
    };

    if (item.id != null) {
      item.id = item.id.toString();
    }

    if (item.text != null) {
      item.text = item.text.toString();
    }

    if (item._resultId == null && item.id && this.container != null) {
      item._resultId = this.generateResultId(this.container, item);
    }

    return $.extend({}, defaults, item);
  };

  SelectAdapter.prototype.matches = function (params, data) {
    var matcher = this.options.get('matcher');

    return matcher(params, data);
  };

  return SelectAdapter;
});

S2.define('select2/data/array',[
  './select',
  '../utils',
  'jquery'
], function (SelectAdapter, Utils, $) {
  function ArrayAdapter ($element, options) {
    var data = options.get('data') || [];

    ArrayAdapter.__super__.constructor.call(this, $element, options);

    this.addOptions(this.convertToOptions(data));
  }

  Utils.Extend(ArrayAdapter, SelectAdapter);

  ArrayAdapter.prototype.select = function (data) {
    var $option = this.$element.find('option').filter(function (i, elm) {
      return elm.value == data.id.toString();
    });

    if ($option.length === 0) {
      $option = this.option(data);

      this.addOptions($option);
    }

    ArrayAdapter.__super__.select.call(this, data);
  };

  ArrayAdapter.prototype.convertToOptions = function (data) {
    var self = this;

    var $existing = this.$element.find('option');
    var existingIds = $existing.map(function () {
      return self.item($(this)).id;
    }).get();

    var $options = [];

    // Filter out all items except for the one passed in the argument
    function onlyItem (item) {
      return function () {
        return $(this).val() == item.id;
      };
    }

    for (var d = 0; d < data.length; d++) {
      var item = this._normalizeItem(data[d]);

      // Skip items which were pre-loaded, only merge the data
      if ($.inArray(item.id, existingIds) >= 0) {
        var $existingOption = $existing.filter(onlyItem(item));

        var existingData = this.item($existingOption);
        var newData = $.extend(true, {}, item, existingData);

        var $newOption = this.option(newData);

        $existingOption.replaceWith($newOption);

        continue;
      }

      var $option = this.option(item);

      if (item.children) {
        var $children = this.convertToOptions(item.children);

        Utils.appendMany($option, $children);
      }

      $options.push($option);
    }

    return $options;
  };

  return ArrayAdapter;
});

S2.define('select2/data/ajax',[
  './array',
  '../utils',
  'jquery'
], function (ArrayAdapter, Utils, $) {
  function AjaxAdapter ($element, options) {
    this.ajaxOptions = this._applyDefaults(options.get('ajax'));

    if (this.ajaxOptions.processResults != null) {
      this.processResults = this.ajaxOptions.processResults;
    }

    AjaxAdapter.__super__.constructor.call(this, $element, options);
  }

  Utils.Extend(AjaxAdapter, ArrayAdapter);

  AjaxAdapter.prototype._applyDefaults = function (options) {
    var defaults = {
      data: function (params) {
        return $.extend({}, params, {
          q: params.term
        });
      },
      transport: function (params, success, failure) {
        var $request = $.ajax(params);

        $request.then(success);
        $request.fail(failure);

        return $request;
      }
    };

    return $.extend({}, defaults, options, true);
  };

  AjaxAdapter.prototype.processResults = function (results) {
    return results;
  };

  AjaxAdapter.prototype.query = function (params, callback) {
    var matches = [];
    var self = this;

    if (this._request != null) {
      // JSONP requests cannot always be aborted
      if ($.isFunction(this._request.abort)) {
        this._request.abort();
      }

      this._request = null;
    }

    var options = $.extend({
      type: 'GET'
    }, this.ajaxOptions);

    if (typeof options.url === 'function') {
      options.url = options.url.call(this.$element, params);
    }

    if (typeof options.data === 'function') {
      options.data = options.data.call(this.$element, params);
    }

    function request () {
      var $request = options.transport(options, function (data) {
        var results = self.processResults(data, params);

        if (self.options.get('debug') && window.console && console.error) {
          // Check to make sure that the response included a `results` key.
          if (!results || !results.results || !$.isArray(results.results)) {
            console.error(
              'Select2: The AJAX results did not return an array in the ' +
              '`results` key of the response.'
            );
          }
        }

        callback(results);
      }, function () {
        // Attempt to detect if a request was aborted
        // Only works if the transport exposes a status property
        if ($request.status && $request.status === '0') {
          return;
        }

        self.trigger('results:message', {
          message: 'errorLoading'
        });
      });

      self._request = $request;
    }

    if (this.ajaxOptions.delay && params.term != null) {
      if (this._queryTimeout) {
        window.clearTimeout(this._queryTimeout);
      }

      this._queryTimeout = window.setTimeout(request, this.ajaxOptions.delay);
    } else {
      request();
    }
  };

  return AjaxAdapter;
});

S2.define('select2/data/tags',[
  'jquery'
], function ($) {
  function Tags (decorated, $element, options) {
    var tags = options.get('tags');

    var createTag = options.get('createTag');

    if (createTag !== undefined) {
      this.createTag = createTag;
    }

    var insertTag = options.get('insertTag');

    if (insertTag !== undefined) {
        this.insertTag = insertTag;
    }

    decorated.call(this, $element, options);

    if ($.isArray(tags)) {
      for (var t = 0; t < tags.length; t++) {
        var tag = tags[t];
        var item = this._normalizeItem(tag);

        var $option = this.option(item);

        this.$element.append($option);
      }
    }
  }

  Tags.prototype.query = function (decorated, params, callback) {
    var self = this;

    this._removeOldTags();

    if (params.term == null || params.page != null) {
      decorated.call(this, params, callback);
      return;
    }

    function wrapper (obj, child) {
      var data = obj.results;

      for (var i = 0; i < data.length; i++) {
        var option = data[i];

        var checkChildren = (
          option.children != null &&
          !wrapper({
            results: option.children
          }, true)
        );

        var checkText = option.text === params.term;

        if (checkText || checkChildren) {
          if (child) {
            return false;
          }

          obj.data = data;
          callback(obj);

          return;
        }
      }

      if (child) {
        return true;
      }

      var tag = self.createTag(params);

      if (tag != null) {
        var $option = self.option(tag);
        $option.attr('data-select2-tag', true);

        self.addOptions([$option]);

        self.insertTag(data, tag);
      }

      obj.results = data;

      callback(obj);
    }

    decorated.call(this, params, wrapper);
  };

  Tags.prototype.createTag = function (decorated, params) {
    var term = $.trim(params.term);

    if (term === '') {
      return null;
    }

    return {
      id: term,
      text: term
    };
  };

  Tags.prototype.insertTag = function (_, data, tag) {
    data.unshift(tag);
  };

  Tags.prototype._removeOldTags = function (_) {
    var tag = this._lastTag;

    var $options = this.$element.find('option[data-select2-tag]');

    $options.each(function () {
      if (this.selected) {
        return;
      }

      $(this).remove();
    });
  };

  return Tags;
});

S2.define('select2/data/tokenizer',[
  'jquery'
], function ($) {
  function Tokenizer (decorated, $element, options) {
    var tokenizer = options.get('tokenizer');

    if (tokenizer !== undefined) {
      this.tokenizer = tokenizer;
    }

    decorated.call(this, $element, options);
  }

  Tokenizer.prototype.bind = function (decorated, container, $container) {
    decorated.call(this, container, $container);

    this.$search =  container.dropdown.$search || container.selection.$search ||
      $container.find('.select2-search__field');
  };

  Tokenizer.prototype.query = function (decorated, params, callback) {
    var self = this;

    function createAndSelect (data) {
      // Normalize the data object so we can use it for checks
      var item = self._normalizeItem(data);

      // Check if the data object already exists as a tag
      // Select it if it doesn't
      var $existingOptions = self.$element.find('option').filter(function () {
        return $(this).val() === item.id;
      });

      // If an existing option wasn't found for it, create the option
      if (!$existingOptions.length) {
        var $option = self.option(item);
        $option.attr('data-select2-tag', true);

        self._removeOldTags();
        self.addOptions([$option]);
      }

      // Select the item, now that we know there is an option for it
      select(item);
    }

    function select (data) {
      self.trigger('select', {
        data: data
      });
    }

    params.term = params.term || '';

    var tokenData = this.tokenizer(params, this.options, createAndSelect);

    if (tokenData.term !== params.term) {
      // Replace the search term if we have the search box
      if (this.$search.length) {
        this.$search.val(tokenData.term);
        this.$search.focus();
      }

      params.term = tokenData.term;
    }

    decorated.call(this, params, callback);
  };

  Tokenizer.prototype.tokenizer = function (_, params, options, callback) {
    var separators = options.get('tokenSeparators') || [];
    var term = params.term;
    var i = 0;

    var createTag = this.createTag || function (params) {
      return {
        id: params.term,
        text: params.term
      };
    };

    while (i < term.length) {
      var termChar = term[i];

      if ($.inArray(termChar, separators) === -1) {
        i++;

        continue;
      }

      var part = term.substr(0, i);
      var partParams = $.extend({}, params, {
        term: part
      });

      var data = createTag(partParams);

      if (data == null) {
        i++;
        continue;
      }

      callback(data);

      // Reset the term to not include the tokenized portion
      term = term.substr(i + 1) || '';
      i = 0;
    }

    return {
      term: term
    };
  };

  return Tokenizer;
});

S2.define('select2/data/minimumInputLength',[

], function () {
  function MinimumInputLength (decorated, $e, options) {
    this.minimumInputLength = options.get('minimumInputLength');

    decorated.call(this, $e, options);
  }

  MinimumInputLength.prototype.query = function (decorated, params, callback) {
    params.term = params.term || '';

    if (params.term.length < this.minimumInputLength) {
      this.trigger('results:message', {
        message: 'inputTooShort',
        args: {
          minimum: this.minimumInputLength,
          input: params.term,
          params: params
        }
      });

      return;
    }

    decorated.call(this, params, callback);
  };

  return MinimumInputLength;
});

S2.define('select2/data/maximumInputLength',[

], function () {
  function MaximumInputLength (decorated, $e, options) {
    this.maximumInputLength = options.get('maximumInputLength');

    decorated.call(this, $e, options);
  }

  MaximumInputLength.prototype.query = function (decorated, params, callback) {
    params.term = params.term || '';

    if (this.maximumInputLength > 0 &&
        params.term.length > this.maximumInputLength) {
      this.trigger('results:message', {
        message: 'inputTooLong',
        args: {
          maximum: this.maximumInputLength,
          input: params.term,
          params: params
        }
      });

      return;
    }

    decorated.call(this, params, callback);
  };

  return MaximumInputLength;
});

S2.define('select2/data/maximumSelectionLength',[

], function (){
  function MaximumSelectionLength (decorated, $e, options) {
    this.maximumSelectionLength = options.get('maximumSelectionLength');

    decorated.call(this, $e, options);
  }

  MaximumSelectionLength.prototype.query =
    function (decorated, params, callback) {
      var self = this;

      this.current(function (currentData) {
        var count = currentData != null ? currentData.length : 0;
        if (self.maximumSelectionLength > 0 &&
          count >= self.maximumSelectionLength) {
          self.trigger('results:message', {
            message: 'maximumSelected',
            args: {
              maximum: self.maximumSelectionLength
            }
          });
          return;
        }
        decorated.call(self, params, callback);
      });
  };

  return MaximumSelectionLength;
});

S2.define('select2/dropdown',[
  'jquery',
  './utils'
], function ($, Utils) {
  function Dropdown ($element, options) {
    this.$element = $element;
    this.options = options;

    Dropdown.__super__.constructor.call(this);
  }

  Utils.Extend(Dropdown, Utils.Observable);

  Dropdown.prototype.render = function () {
    var $dropdown = $(
      '<span class="select2-dropdown">' +
        '<span class="select2-results"></span>' +
      '</span>'
    );

    $dropdown.attr('dir', this.options.get('dir'));

    this.$dropdown = $dropdown;

    return $dropdown;
  };

  Dropdown.prototype.bind = function () {
    // Should be implemented in subclasses
  };

  Dropdown.prototype.position = function ($dropdown, $container) {
    // Should be implmented in subclasses
  };

  Dropdown.prototype.destroy = function () {
    // Remove the dropdown from the DOM
    this.$dropdown.remove();
  };

  return Dropdown;
});

S2.define('select2/dropdown/search',[
  'jquery',
  '../utils'
], function ($, Utils) {
  function Search () { }

  Search.prototype.render = function (decorated) {
    var $rendered = decorated.call(this);

    var $search = $(
      '<span class="select2-search select2-search--dropdown">' +
        '<input class="select2-search__field" type="search" tabindex="-1"' +
        ' autocomplete="off" autocorrect="off" autocapitalize="off"' +
        ' spellcheck="false" role="textbox" />' +
      '</span>'
    );

    this.$searchContainer = $search;
    this.$search = $search.find('input');

    $rendered.prepend($search);

    return $rendered;
  };

  Search.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    this.$search.on('keydown', function (evt) {
      self.trigger('keypress', evt);

      self._keyUpPrevented = evt.isDefaultPrevented();
    });

    // Workaround for browsers which do not support the `input` event
    // This will prevent double-triggering of events for browsers which support
    // both the `keyup` and `input` events.
    this.$search.on('input', function (evt) {
      // Unbind the duplicated `keyup` event
      $(this).off('keyup');
    });

    this.$search.on('keyup input', function (evt) {
      self.handleSearch(evt);
    });

    container.on('open', function () {
      self.$search.attr('tabindex', 0);

      self.$search.focus();

      window.setTimeout(function () {
        self.$search.focus();
      }, 0);
    });

    container.on('close', function () {
      self.$search.attr('tabindex', -1);

      self.$search.val('');
    });

    container.on('focus', function () {
      if (container.isOpen()) {
        self.$search.focus();
      }
    });

    container.on('results:all', function (params) {
      if (params.query.term == null || params.query.term === '') {
        var showSearch = self.showSearch(params);

        if (showSearch) {
          self.$searchContainer.removeClass('select2-search--hide');
        } else {
          self.$searchContainer.addClass('select2-search--hide');
        }
      }
    });
  };

  Search.prototype.handleSearch = function (evt) {
    if (!this._keyUpPrevented) {
      var input = this.$search.val();

      this.trigger('query', {
        term: input
      });
    }

    this._keyUpPrevented = false;
  };

  Search.prototype.showSearch = function (_, params) {
    return true;
  };

  return Search;
});

S2.define('select2/dropdown/hidePlaceholder',[

], function () {
  function HidePlaceholder (decorated, $element, options, dataAdapter) {
    this.placeholder = this.normalizePlaceholder(options.get('placeholder'));

    decorated.call(this, $element, options, dataAdapter);
  }

  HidePlaceholder.prototype.append = function (decorated, data) {
    data.results = this.removePlaceholder(data.results);

    decorated.call(this, data);
  };

  HidePlaceholder.prototype.normalizePlaceholder = function (_, placeholder) {
    if (typeof placeholder === 'string') {
      placeholder = {
        id: '',
        text: placeholder
      };
    }

    return placeholder;
  };

  HidePlaceholder.prototype.removePlaceholder = function (_, data) {
    var modifiedData = data.slice(0);

    for (var d = data.length - 1; d >= 0; d--) {
      var item = data[d];

      if (this.placeholder.id === item.id) {
        modifiedData.splice(d, 1);
      }
    }

    return modifiedData;
  };

  return HidePlaceholder;
});

S2.define('select2/dropdown/infiniteScroll',[
  'jquery'
], function ($) {
  function InfiniteScroll (decorated, $element, options, dataAdapter) {
    this.lastParams = {};

    decorated.call(this, $element, options, dataAdapter);

    this.$loadingMore = this.createLoadingMore();
    this.loading = false;
  }

  InfiniteScroll.prototype.append = function (decorated, data) {
    this.$loadingMore.remove();
    this.loading = false;

    decorated.call(this, data);

    if (this.showLoadingMore(data)) {
      this.$results.append(this.$loadingMore);
    }
  };

  InfiniteScroll.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    container.on('query', function (params) {
      self.lastParams = params;
      self.loading = true;
    });

    container.on('query:append', function (params) {
      self.lastParams = params;
      self.loading = true;
    });

    this.$results.on('scroll', function () {
      var isLoadMoreVisible = $.contains(
        document.documentElement,
        self.$loadingMore[0]
      );

      if (self.loading || !isLoadMoreVisible) {
        return;
      }

      var currentOffset = self.$results.offset().top +
        self.$results.outerHeight(false);
      var loadingMoreOffset = self.$loadingMore.offset().top +
        self.$loadingMore.outerHeight(false);

      if (currentOffset + 50 >= loadingMoreOffset) {
        self.loadMore();
      }
    });
  };

  InfiniteScroll.prototype.loadMore = function () {
    this.loading = true;

    var params = $.extend({}, {page: 1}, this.lastParams);

    params.page++;

    this.trigger('query:append', params);
  };

  InfiniteScroll.prototype.showLoadingMore = function (_, data) {
    return data.pagination && data.pagination.more;
  };

  InfiniteScroll.prototype.createLoadingMore = function () {
    var $option = $(
      '<li ' +
      'class="select2-results__option select2-results__option--load-more"' +
      'role="treeitem" aria-disabled="true"></li>'
    );

    var message = this.options.get('translations').get('loadingMore');

    $option.html(message(this.lastParams));

    return $option;
  };

  return InfiniteScroll;
});

S2.define('select2/dropdown/attachBody',[
  'jquery',
  '../utils'
], function ($, Utils) {
  function AttachBody (decorated, $element, options) {
    this.$dropdownParent = options.get('dropdownParent') || $(document.body);

    decorated.call(this, $element, options);
  }

  AttachBody.prototype.bind = function (decorated, container, $container) {
    var self = this;

    var setupResultsEvents = false;

    decorated.call(this, container, $container);

    container.on('open', function () {
      self._showDropdown();
      self._attachPositioningHandler(container);

      if (!setupResultsEvents) {
        setupResultsEvents = true;

        container.on('results:all', function () {
          self._positionDropdown();
          self._resizeDropdown();
        });

        container.on('results:append', function () {
          self._positionDropdown();
          self._resizeDropdown();
        });
      }
    });

    container.on('close', function () {
      self._hideDropdown();
      self._detachPositioningHandler(container);
    });

    this.$dropdownContainer.on('mousedown', function (evt) {
      evt.stopPropagation();
    });
  };

  AttachBody.prototype.destroy = function (decorated) {
    decorated.call(this);

    this.$dropdownContainer.remove();
  };

  AttachBody.prototype.position = function (decorated, $dropdown, $container) {
    // Clone all of the container classes
    $dropdown.attr('class', $container.attr('class'));

    $dropdown.removeClass('select2');
    $dropdown.addClass('select2-container--open');

    $dropdown.css({
      position: 'absolute',
      top: -999999
    });

    this.$container = $container;
  };

  AttachBody.prototype.render = function (decorated) {
    var $container = $('<span></span>');

    var $dropdown = decorated.call(this);
    $container.append($dropdown);

    this.$dropdownContainer = $container;

    return $container;
  };

  AttachBody.prototype._hideDropdown = function (decorated) {
    this.$dropdownContainer.detach();
  };

  AttachBody.prototype._attachPositioningHandler =
      function (decorated, container) {
    var self = this;

    var scrollEvent = 'scroll.select2.' + container.id;
    var resizeEvent = 'resize.select2.' + container.id;
    var orientationEvent = 'orientationchange.select2.' + container.id;

    var $watchers = this.$container.parents().filter(Utils.hasScroll);
    $watchers.each(function () {
      $(this).data('select2-scroll-position', {
        x: $(this).scrollLeft(),
        y: $(this).scrollTop()
      });
    });

    $watchers.on(scrollEvent, function (ev) {
      var position = $(this).data('select2-scroll-position');
      $(this).scrollTop(position.y);
    });

    $(window).on(scrollEvent + ' ' + resizeEvent + ' ' + orientationEvent,
      function (e) {
      self._positionDropdown();
      self._resizeDropdown();
    });
  };

  AttachBody.prototype._detachPositioningHandler =
      function (decorated, container) {
    var scrollEvent = 'scroll.select2.' + container.id;
    var resizeEvent = 'resize.select2.' + container.id;
    var orientationEvent = 'orientationchange.select2.' + container.id;

    var $watchers = this.$container.parents().filter(Utils.hasScroll);
    $watchers.off(scrollEvent);

    $(window).off(scrollEvent + ' ' + resizeEvent + ' ' + orientationEvent);
  };

  AttachBody.prototype._positionDropdown = function () {
    var $window = $(window);

    var isCurrentlyAbove = this.$dropdown.hasClass('select2-dropdown--above');
    var isCurrentlyBelow = this.$dropdown.hasClass('select2-dropdown--below');

    var newDirection = null;

    var offset = this.$container.offset();

    offset.bottom = offset.top + this.$container.outerHeight(false);

    var container = {
      height: this.$container.outerHeight(false)
    };

    container.top = offset.top;
    container.bottom = offset.top + container.height;

    var dropdown = {
      height: this.$dropdown.outerHeight(false)
    };

    var viewport = {
      top: $window.scrollTop(),
      bottom: $window.scrollTop() + $window.height()
    };

    var enoughRoomAbove = viewport.top < (offset.top - dropdown.height);
    var enoughRoomBelow = viewport.bottom > (offset.bottom + dropdown.height);

    var css = {
      left: offset.left,
      top: container.bottom
    };

    // Determine what the parent element is to use for calciulating the offset
    var $offsetParent = this.$dropdownParent;

    // For statically positoned elements, we need to get the element
    // that is determining the offset
    if ($offsetParent.css('position') === 'static') {
      $offsetParent = $offsetParent.offsetParent();
    }

    var parentOffset = $offsetParent.offset();

    css.top -= parentOffset.top;
    css.left -= parentOffset.left;

    if (!isCurrentlyAbove && !isCurrentlyBelow) {
      newDirection = 'below';
    }

    if (!enoughRoomBelow && enoughRoomAbove && !isCurrentlyAbove) {
      newDirection = 'above';
    } else if (!enoughRoomAbove && enoughRoomBelow && isCurrentlyAbove) {
      newDirection = 'below';
    }

    if (newDirection == 'above' ||
      (isCurrentlyAbove && newDirection !== 'below')) {
      css.top = container.top - parentOffset.top - dropdown.height;
    }

    if (newDirection != null) {
      this.$dropdown
        .removeClass('select2-dropdown--below select2-dropdown--above')
        .addClass('select2-dropdown--' + newDirection);
      this.$container
        .removeClass('select2-container--below select2-container--above')
        .addClass('select2-container--' + newDirection);
    }

    this.$dropdownContainer.css(css);
  };

  AttachBody.prototype._resizeDropdown = function () {
    var css = {
      width: this.$container.outerWidth(false) + 'px'
    };

    if (this.options.get('dropdownAutoWidth')) {
      css.minWidth = css.width;
      css.position = 'relative';
      css.width = 'auto';
    }

    this.$dropdown.css(css);
  };

  AttachBody.prototype._showDropdown = function (decorated) {
    this.$dropdownContainer.appendTo(this.$dropdownParent);

    this._positionDropdown();
    this._resizeDropdown();
  };

  return AttachBody;
});

S2.define('select2/dropdown/minimumResultsForSearch',[

], function () {
  function countResults (data) {
    var count = 0;

    for (var d = 0; d < data.length; d++) {
      var item = data[d];

      if (item.children) {
        count += countResults(item.children);
      } else {
        count++;
      }
    }

    return count;
  }

  function MinimumResultsForSearch (decorated, $element, options, dataAdapter) {
    this.minimumResultsForSearch = options.get('minimumResultsForSearch');

    if (this.minimumResultsForSearch < 0) {
      this.minimumResultsForSearch = Infinity;
    }

    decorated.call(this, $element, options, dataAdapter);
  }

  MinimumResultsForSearch.prototype.showSearch = function (decorated, params) {
    if (countResults(params.data.results) < this.minimumResultsForSearch) {
      return false;
    }

    return decorated.call(this, params);
  };

  return MinimumResultsForSearch;
});

S2.define('select2/dropdown/selectOnClose',[

], function () {
  function SelectOnClose () { }

  SelectOnClose.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    container.on('close', function (params) {
      self._handleSelectOnClose(params);
    });
  };

  SelectOnClose.prototype._handleSelectOnClose = function (_, params) {
    if (params && params.originalSelect2Event != null) {
      var event = params.originalSelect2Event;

      // Don't select an item if the close event was triggered from a select or
      // unselect event
      if (event._type === 'select' || event._type === 'unselect') {
        return;
      }
    }

    var $highlightedResults = this.getHighlightedResults();

    // Only select highlighted results
    if ($highlightedResults.length < 1) {
      return;
    }

    var data = $highlightedResults.data('data');

    // Don't re-select already selected resulte
    if (
      (data.element != null && data.element.selected) ||
      (data.element == null && data.selected)
    ) {
      return;
    }

    this.trigger('select', {
        data: data
    });
  };

  return SelectOnClose;
});

S2.define('select2/dropdown/closeOnSelect',[

], function () {
  function CloseOnSelect () { }

  CloseOnSelect.prototype.bind = function (decorated, container, $container) {
    var self = this;

    decorated.call(this, container, $container);

    container.on('select', function (evt) {
      self._selectTriggered(evt);
    });

    container.on('unselect', function (evt) {
      self._selectTriggered(evt);
    });
  };

  CloseOnSelect.prototype._selectTriggered = function (_, evt) {
    var originalEvent = evt.originalEvent;

    // Don't close if the control key is being held
    if (originalEvent && originalEvent.ctrlKey) {
      return;
    }

    this.trigger('close', {
      originalEvent: originalEvent,
      originalSelect2Event: evt
    });
  };

  return CloseOnSelect;
});

S2.define('select2/i18n/en',[],function () {
  // English
  return {
    errorLoading: function () {
      return 'The results could not be loaded.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Please delete ' + overChars + ' character';

      if (overChars != 1) {
        message += 's';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Please enter ' + remainingChars + ' or more characters';

      return message;
    },
    loadingMore: function () {
      return 'Loading more results…';
    },
    maximumSelected: function (args) {
      var message = 'You can only select ' + args.maximum + ' item';

      if (args.maximum != 1) {
        message += 's';
      }

      return message;
    },
    noResults: function () {
      return 'No results found';
    },
    searching: function () {
      return 'Searching…';
    }
  };
});

S2.define('select2/defaults',[
  'jquery',
  'require',

  './results',

  './selection/single',
  './selection/multiple',
  './selection/placeholder',
  './selection/allowClear',
  './selection/search',
  './selection/eventRelay',

  './utils',
  './translation',
  './diacritics',

  './data/select',
  './data/array',
  './data/ajax',
  './data/tags',
  './data/tokenizer',
  './data/minimumInputLength',
  './data/maximumInputLength',
  './data/maximumSelectionLength',

  './dropdown',
  './dropdown/search',
  './dropdown/hidePlaceholder',
  './dropdown/infiniteScroll',
  './dropdown/attachBody',
  './dropdown/minimumResultsForSearch',
  './dropdown/selectOnClose',
  './dropdown/closeOnSelect',

  './i18n/en'
], function ($, require,

             ResultsList,

             SingleSelection, MultipleSelection, Placeholder, AllowClear,
             SelectionSearch, EventRelay,

             Utils, Translation, DIACRITICS,

             SelectData, ArrayData, AjaxData, Tags, Tokenizer,
             MinimumInputLength, MaximumInputLength, MaximumSelectionLength,

             Dropdown, DropdownSearch, HidePlaceholder, InfiniteScroll,
             AttachBody, MinimumResultsForSearch, SelectOnClose, CloseOnSelect,

             EnglishTranslation) {
  function Defaults () {
    this.reset();
  }

  Defaults.prototype.apply = function (options) {
    options = $.extend(true, {}, this.defaults, options);

    if (options.dataAdapter == null) {
      if (options.ajax != null) {
        options.dataAdapter = AjaxData;
      } else if (options.data != null) {
        options.dataAdapter = ArrayData;
      } else {
        options.dataAdapter = SelectData;
      }

      if (options.minimumInputLength > 0) {
        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          MinimumInputLength
        );
      }

      if (options.maximumInputLength > 0) {
        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          MaximumInputLength
        );
      }

      if (options.maximumSelectionLength > 0) {
        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          MaximumSelectionLength
        );
      }

      if (options.tags) {
        options.dataAdapter = Utils.Decorate(options.dataAdapter, Tags);
      }

      if (options.tokenSeparators != null || options.tokenizer != null) {
        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          Tokenizer
        );
      }

      if (options.query != null) {
        var Query = require(options.amdBase + 'compat/query');

        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          Query
        );
      }

      if (options.initSelection != null) {
        var InitSelection = require(options.amdBase + 'compat/initSelection');

        options.dataAdapter = Utils.Decorate(
          options.dataAdapter,
          InitSelection
        );
      }
    }

    if (options.resultsAdapter == null) {
      options.resultsAdapter = ResultsList;

      if (options.ajax != null) {
        options.resultsAdapter = Utils.Decorate(
          options.resultsAdapter,
          InfiniteScroll
        );
      }

      if (options.placeholder != null) {
        options.resultsAdapter = Utils.Decorate(
          options.resultsAdapter,
          HidePlaceholder
        );
      }

      if (options.selectOnClose) {
        options.resultsAdapter = Utils.Decorate(
          options.resultsAdapter,
          SelectOnClose
        );
      }
    }

    if (options.dropdownAdapter == null) {
      if (options.multiple) {
        options.dropdownAdapter = Dropdown;
      } else {
        var SearchableDropdown = Utils.Decorate(Dropdown, DropdownSearch);

        options.dropdownAdapter = SearchableDropdown;
      }

      if (options.minimumResultsForSearch !== 0) {
        options.dropdownAdapter = Utils.Decorate(
          options.dropdownAdapter,
          MinimumResultsForSearch
        );
      }

      if (options.closeOnSelect) {
        options.dropdownAdapter = Utils.Decorate(
          options.dropdownAdapter,
          CloseOnSelect
        );
      }

      if (
        options.dropdownCssClass != null ||
        options.dropdownCss != null ||
        options.adaptDropdownCssClass != null
      ) {
        var DropdownCSS = require(options.amdBase + 'compat/dropdownCss');

        options.dropdownAdapter = Utils.Decorate(
          options.dropdownAdapter,
          DropdownCSS
        );
      }

      options.dropdownAdapter = Utils.Decorate(
        options.dropdownAdapter,
        AttachBody
      );
    }

    if (options.selectionAdapter == null) {
      if (options.multiple) {
        options.selectionAdapter = MultipleSelection;
      } else {
        options.selectionAdapter = SingleSelection;
      }

      // Add the placeholder mixin if a placeholder was specified
      if (options.placeholder != null) {
        options.selectionAdapter = Utils.Decorate(
          options.selectionAdapter,
          Placeholder
        );
      }

      if (options.allowClear) {
        options.selectionAdapter = Utils.Decorate(
          options.selectionAdapter,
          AllowClear
        );
      }

      if (options.multiple) {
        options.selectionAdapter = Utils.Decorate(
          options.selectionAdapter,
          SelectionSearch
        );
      }

      if (
        options.containerCssClass != null ||
        options.containerCss != null ||
        options.adaptContainerCssClass != null
      ) {
        var ContainerCSS = require(options.amdBase + 'compat/containerCss');

        options.selectionAdapter = Utils.Decorate(
          options.selectionAdapter,
          ContainerCSS
        );
      }

      options.selectionAdapter = Utils.Decorate(
        options.selectionAdapter,
        EventRelay
      );
    }

    if (typeof options.language === 'string') {
      // Check if the language is specified with a region
      if (options.language.indexOf('-') > 0) {
        // Extract the region information if it is included
        var languageParts = options.language.split('-');
        var baseLanguage = languageParts[0];

        options.language = [options.language, baseLanguage];
      } else {
        options.language = [options.language];
      }
    }

    if ($.isArray(options.language)) {
      var languages = new Translation();
      options.language.push('en');

      var languageNames = options.language;

      for (var l = 0; l < languageNames.length; l++) {
        var name = languageNames[l];
        var language = {};

        try {
          // Try to load it with the original name
          language = Translation.loadPath(name);
        } catch (e) {
          try {
            // If we couldn't load it, check if it wasn't the full path
            name = this.defaults.amdLanguageBase + name;
            language = Translation.loadPath(name);
          } catch (ex) {
            // The translation could not be loaded at all. Sometimes this is
            // because of a configuration problem, other times this can be
            // because of how Select2 helps load all possible translation files.
            if (options.debug && window.console && console.warn) {
              console.warn(
                'Select2: The language file for "' + name + '" could not be ' +
                'automatically loaded. A fallback will be used instead.'
              );
            }

            continue;
          }
        }

        languages.extend(language);
      }

      options.translations = languages;
    } else {
      var baseTranslation = Translation.loadPath(
        this.defaults.amdLanguageBase + 'en'
      );
      var customTranslation = new Translation(options.language);

      customTranslation.extend(baseTranslation);

      options.translations = customTranslation;
    }

    return options;
  };

  Defaults.prototype.reset = function () {
    function stripDiacritics (text) {
      // Used 'uni range + named function' from http://jsperf.com/diacritics/18
      function match(a) {
        return DIACRITICS[a] || a;
      }

      return text.replace(/[^\u0000-\u007E]/g, match);
    }

    function matcher (params, data) {
      // Always return the object if there is nothing to compare
      if ($.trim(params.term) === '') {
        return data;
      }

      // Do a recursive check for options with children
      if (data.children && data.children.length > 0) {
        // Clone the data object if there are children
        // This is required as we modify the object to remove any non-matches
        var match = $.extend(true, {}, data);

        // Check each child of the option
        for (var c = data.children.length - 1; c >= 0; c--) {
          var child = data.children[c];

          var matches = matcher(params, child);

          // If there wasn't a match, remove the object in the array
          if (matches == null) {
            match.children.splice(c, 1);
          }
        }

        // If any children matched, return the new object
        if (match.children.length > 0) {
          return match;
        }

        // If there were no matching children, check just the plain object
        return matcher(params, match);
      }

      var original = stripDiacritics(data.text).toUpperCase();
      var term = stripDiacritics(params.term).toUpperCase();

      // Check if the text contains the term
      if (original.indexOf(term) > -1) {
        return data;
      }

      // If it doesn't contain the term, don't return anything
      return null;
    }

    this.defaults = {
      amdBase: './',
      amdLanguageBase: './i18n/',
      closeOnSelect: true,
      debug: false,
      dropdownAutoWidth: false,
      escapeMarkup: Utils.escapeMarkup,
      language: EnglishTranslation,
      matcher: matcher,
      minimumInputLength: 0,
      maximumInputLength: 0,
      maximumSelectionLength: 0,
      minimumResultsForSearch: 0,
      selectOnClose: false,
      sorter: function (data) {
        return data;
      },
      templateResult: function (result) {
        return result.text;
      },
      templateSelection: function (selection) {
        return selection.text;
      },
      theme: 'default',
      width: 'resolve'
    };
  };

  Defaults.prototype.set = function (key, value) {
    var camelKey = $.camelCase(key);

    var data = {};
    data[camelKey] = value;

    var convertedData = Utils._convertData(data);

    $.extend(this.defaults, convertedData);
  };

  var defaults = new Defaults();

  return defaults;
});

S2.define('select2/options',[
  'require',
  'jquery',
  './defaults',
  './utils'
], function (require, $, Defaults, Utils) {
  function Options (options, $element) {
    this.options = options;

    if ($element != null) {
      this.fromElement($element);
    }

    this.options = Defaults.apply(this.options);

    if ($element && $element.is('input')) {
      var InputCompat = require(this.get('amdBase') + 'compat/inputData');

      this.options.dataAdapter = Utils.Decorate(
        this.options.dataAdapter,
        InputCompat
      );
    }
  }

  Options.prototype.fromElement = function ($e) {
    var excludedData = ['select2'];

    if (this.options.multiple == null) {
      this.options.multiple = $e.prop('multiple');
    }

    if (this.options.disabled == null) {
      this.options.disabled = $e.prop('disabled');
    }

    if (this.options.language == null) {
      if ($e.prop('lang')) {
        this.options.language = $e.prop('lang').toLowerCase();
      } else if ($e.closest('[lang]').prop('lang')) {
        this.options.language = $e.closest('[lang]').prop('lang');
      }
    }

    if (this.options.dir == null) {
      if ($e.prop('dir')) {
        this.options.dir = $e.prop('dir');
      } else if ($e.closest('[dir]').prop('dir')) {
        this.options.dir = $e.closest('[dir]').prop('dir');
      } else {
        this.options.dir = 'ltr';
      }
    }

    $e.prop('disabled', this.options.disabled);
    $e.prop('multiple', this.options.multiple);

    if ($e.data('select2Tags')) {
      if (this.options.debug && window.console && console.warn) {
        console.warn(
          'Select2: The `data-select2-tags` attribute has been changed to ' +
          'use the `data-data` and `data-tags="true"` attributes and will be ' +
          'removed in future versions of Select2.'
        );
      }

      $e.data('data', $e.data('select2Tags'));
      $e.data('tags', true);
    }

    if ($e.data('ajaxUrl')) {
      if (this.options.debug && window.console && console.warn) {
        console.warn(
          'Select2: The `data-ajax-url` attribute has been changed to ' +
          '`data-ajax--url` and support for the old attribute will be removed' +
          ' in future versions of Select2.'
        );
      }

      $e.attr('ajax--url', $e.data('ajaxUrl'));
      $e.data('ajax--url', $e.data('ajaxUrl'));
    }

    var dataset = {};

    // Prefer the element's `dataset` attribute if it exists
    // jQuery 1.x does not correctly handle data attributes with multiple dashes
    if ($.fn.jquery && $.fn.jquery.substr(0, 2) == '1.' && $e[0].dataset) {
      dataset = $.extend(true, {}, $e[0].dataset, $e.data());
    } else {
      dataset = $e.data();
    }

    var data = $.extend(true, {}, dataset);

    data = Utils._convertData(data);

    for (var key in data) {
      if ($.inArray(key, excludedData) > -1) {
        continue;
      }

      if ($.isPlainObject(this.options[key])) {
        $.extend(this.options[key], data[key]);
      } else {
        this.options[key] = data[key];
      }
    }

    return this;
  };

  Options.prototype.get = function (key) {
    return this.options[key];
  };

  Options.prototype.set = function (key, val) {
    this.options[key] = val;
  };

  return Options;
});

S2.define('select2/core',[
  'jquery',
  './options',
  './utils',
  './keys'
], function ($, Options, Utils, KEYS) {
  var Select2 = function ($element, options) {
    if ($element.data('select2') != null) {
      $element.data('select2').destroy();
    }

    this.$element = $element;

    this.id = this._generateId($element);

    options = options || {};

    this.options = new Options(options, $element);

    Select2.__super__.constructor.call(this);

    // Set up the tabindex

    var tabindex = $element.attr('tabindex') || 0;
    $element.data('old-tabindex', tabindex);
    $element.attr('tabindex', '-1');

    // Set up containers and adapters

    var DataAdapter = this.options.get('dataAdapter');
    this.dataAdapter = new DataAdapter($element, this.options);

    var $container = this.render();

    this._placeContainer($container);

    var SelectionAdapter = this.options.get('selectionAdapter');
    this.selection = new SelectionAdapter($element, this.options);
    this.$selection = this.selection.render();

    this.selection.position(this.$selection, $container);

    var DropdownAdapter = this.options.get('dropdownAdapter');
    this.dropdown = new DropdownAdapter($element, this.options);
    this.$dropdown = this.dropdown.render();

    this.dropdown.position(this.$dropdown, $container);

    var ResultsAdapter = this.options.get('resultsAdapter');
    this.results = new ResultsAdapter($element, this.options, this.dataAdapter);
    this.$results = this.results.render();

    this.results.position(this.$results, this.$dropdown);

    // Bind events

    var self = this;

    // Bind the container to all of the adapters
    this._bindAdapters();

    // Register any DOM event handlers
    this._registerDomEvents();

    // Register any internal event handlers
    this._registerDataEvents();
    this._registerSelectionEvents();
    this._registerDropdownEvents();
    this._registerResultsEvents();
    this._registerEvents();

    // Set the initial state
    this.dataAdapter.current(function (initialData) {
      self.trigger('selection:update', {
        data: initialData
      });
    });

    // Hide the original select
    $element.addClass('select2-hidden-accessible');
    $element.attr('aria-hidden', 'true');

    // Synchronize any monitored attributes
    this._syncAttributes();

    $element.data('select2', this);
  };

  Utils.Extend(Select2, Utils.Observable);

  Select2.prototype._generateId = function ($element) {
    var id = '';

    if ($element.attr('id') != null) {
      id = $element.attr('id');
    } else if ($element.attr('name') != null) {
      id = $element.attr('name') + '-' + Utils.generateChars(2);
    } else {
      id = Utils.generateChars(4);
    }

    id = id.replace(/(:|\.|\[|\]|,)/g, '');
    id = 'select2-' + id;

    return id;
  };

  Select2.prototype._placeContainer = function ($container) {
    $container.insertAfter(this.$element);

    var width = this._resolveWidth(this.$element, this.options.get('width'));

    if (width != null) {
      $container.css('width', width);
    }
  };

  Select2.prototype._resolveWidth = function ($element, method) {
    var WIDTH = /^width:(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc))/i;

    if (method == 'resolve') {
      var styleWidth = this._resolveWidth($element, 'style');

      if (styleWidth != null) {
        return styleWidth;
      }

      return this._resolveWidth($element, 'element');
    }

    if (method == 'element') {
      var elementWidth = $element.outerWidth(false);

      if (elementWidth <= 0) {
        return 'auto';
      }

      return elementWidth + 'px';
    }

    if (method == 'style') {
      var style = $element.attr('style');

      if (typeof(style) !== 'string') {
        return null;
      }

      var attrs = style.split(';');

      for (var i = 0, l = attrs.length; i < l; i = i + 1) {
        var attr = attrs[i].replace(/\s/g, '');
        var matches = attr.match(WIDTH);

        if (matches !== null && matches.length >= 1) {
          return matches[1];
        }
      }

      return null;
    }

    return method;
  };

  Select2.prototype._bindAdapters = function () {
    this.dataAdapter.bind(this, this.$container);
    this.selection.bind(this, this.$container);

    this.dropdown.bind(this, this.$container);
    this.results.bind(this, this.$container);
  };

  Select2.prototype._registerDomEvents = function () {
    var self = this;

    this.$element.on('change.select2', function () {
      self.dataAdapter.current(function (data) {
        self.trigger('selection:update', {
          data: data
        });
      });
    });

    this.$element.on('focus.select2', function (evt) {
      self.trigger('focus', evt);
    });

    this._syncA = Utils.bind(this._syncAttributes, this);
    this._syncS = Utils.bind(this._syncSubtree, this);

    if (this.$element[0].attachEvent) {
      this.$element[0].attachEvent('onpropertychange', this._syncA);
    }

    var observer = window.MutationObserver ||
      window.WebKitMutationObserver ||
      window.MozMutationObserver
    ;

    if (observer != null) {
      this._observer = new observer(function (mutations) {
        $.each(mutations, self._syncA);
        $.each(mutations, self._syncS);
      });
      this._observer.observe(this.$element[0], {
        attributes: true,
        childList: true,
        subtree: false
      });
    } else if (this.$element[0].addEventListener) {
      this.$element[0].addEventListener(
        'DOMAttrModified',
        self._syncA,
        false
      );
      this.$element[0].addEventListener(
        'DOMNodeInserted',
        self._syncS,
        false
      );
      this.$element[0].addEventListener(
        'DOMNodeRemoved',
        self._syncS,
        false
      );
    }
  };

  Select2.prototype._registerDataEvents = function () {
    var self = this;

    this.dataAdapter.on('*', function (name, params) {
      self.trigger(name, params);
    });
  };

  Select2.prototype._registerSelectionEvents = function () {
    var self = this;
    var nonRelayEvents = ['toggle', 'focus'];

    this.selection.on('toggle', function () {
      self.toggleDropdown();
    });

    this.selection.on('focus', function (params) {
      self.focus(params);
    });

    this.selection.on('*', function (name, params) {
      if ($.inArray(name, nonRelayEvents) !== -1) {
        return;
      }

      self.trigger(name, params);
    });
  };

  Select2.prototype._registerDropdownEvents = function () {
    var self = this;

    this.dropdown.on('*', function (name, params) {
      self.trigger(name, params);
    });
  };

  Select2.prototype._registerResultsEvents = function () {
    var self = this;

    this.results.on('*', function (name, params) {
      self.trigger(name, params);
    });
  };

  Select2.prototype._registerEvents = function () {
    var self = this;

    this.on('open', function () {
      self.$container.addClass('select2-container--open');
    });

    this.on('close', function () {
      self.$container.removeClass('select2-container--open');
    });

    this.on('enable', function () {
      self.$container.removeClass('select2-container--disabled');
    });

    this.on('disable', function () {
      self.$container.addClass('select2-container--disabled');
    });

    this.on('blur', function () {
      self.$container.removeClass('select2-container--focus');
    });

    this.on('query', function (params) {
      if (!self.isOpen()) {
        self.trigger('open', {});
      }

      this.dataAdapter.query(params, function (data) {
        self.trigger('results:all', {
          data: data,
          query: params
        });
      });
    });

    this.on('query:append', function (params) {
      this.dataAdapter.query(params, function (data) {
        self.trigger('results:append', {
          data: data,
          query: params
        });
      });
    });

    this.on('keypress', function (evt) {
      var key = evt.which;

      if (self.isOpen()) {
        if (key === KEYS.ESC || key === KEYS.TAB ||
            (key === KEYS.UP && evt.altKey)) {
          self.close();

          evt.preventDefault();
        } else if (key === KEYS.ENTER) {
          self.trigger('results:select', {});

          evt.preventDefault();
        } else if ((key === KEYS.SPACE && evt.ctrlKey)) {
          self.trigger('results:toggle', {});

          evt.preventDefault();
        } else if (key === KEYS.UP) {
          self.trigger('results:previous', {});

          evt.preventDefault();
        } else if (key === KEYS.DOWN) {
          self.trigger('results:next', {});

          evt.preventDefault();
        }
      } else {
        if (key === KEYS.ENTER || key === KEYS.SPACE ||
            (key === KEYS.DOWN && evt.altKey)) {
          self.open();

          evt.preventDefault();
        }
      }
    });
  };

  Select2.prototype._syncAttributes = function () {
    this.options.set('disabled', this.$element.prop('disabled'));

    if (this.options.get('disabled')) {
      if (this.isOpen()) {
        this.close();
      }

      this.trigger('disable', {});
    } else {
      this.trigger('enable', {});
    }
  };

  Select2.prototype._syncSubtree = function (evt, mutations) {
    var changed = false;
    var self = this;

    // Ignore any mutation events raised for elements that aren't options or
    // optgroups. This handles the case when the select element is destroyed
    if (
      evt && evt.target && (
        evt.target.nodeName !== 'OPTION' && evt.target.nodeName !== 'OPTGROUP'
      )
    ) {
      return;
    }

    if (!mutations) {
      // If mutation events aren't supported, then we can only assume that the
      // change affected the selections
      changed = true;
    } else if (mutations.addedNodes && mutations.addedNodes.length > 0) {
      for (var n = 0; n < mutations.addedNodes.length; n++) {
        var node = mutations.addedNodes[n];

        if (node.selected) {
          changed = true;
        }
      }
    } else if (mutations.removedNodes && mutations.removedNodes.length > 0) {
      changed = true;
    }

    // Only re-pull the data if we think there is a change
    if (changed) {
      this.dataAdapter.current(function (currentData) {
        self.trigger('selection:update', {
          data: currentData
        });
      });
    }
  };

  /**
   * Override the trigger method to automatically trigger pre-events when
   * there are events that can be prevented.
   */
  Select2.prototype.trigger = function (name, args) {
    var actualTrigger = Select2.__super__.trigger;
    var preTriggerMap = {
      'open': 'opening',
      'close': 'closing',
      'select': 'selecting',
      'unselect': 'unselecting'
    };

    if (args === undefined) {
      args = {};
    }

    if (name in preTriggerMap) {
      var preTriggerName = preTriggerMap[name];
      var preTriggerArgs = {
        prevented: false,
        name: name,
        args: args
      };

      actualTrigger.call(this, preTriggerName, preTriggerArgs);

      if (preTriggerArgs.prevented) {
        args.prevented = true;

        return;
      }
    }

    actualTrigger.call(this, name, args);
  };

  Select2.prototype.toggleDropdown = function () {
    if (this.options.get('disabled')) {
      return;
    }

    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  };

  Select2.prototype.open = function () {
    if (this.isOpen()) {
      return;
    }

    this.trigger('query', {});
  };

  Select2.prototype.close = function () {
    if (!this.isOpen()) {
      return;
    }

    this.trigger('close', {});
  };

  Select2.prototype.isOpen = function () {
    return this.$container.hasClass('select2-container--open');
  };

  Select2.prototype.hasFocus = function () {
    return this.$container.hasClass('select2-container--focus');
  };

  Select2.prototype.focus = function (data) {
    // No need to re-trigger focus events if we are already focused
    if (this.hasFocus()) {
      return;
    }

    this.$container.addClass('select2-container--focus');
    this.trigger('focus', {});
  };

  Select2.prototype.enable = function (args) {
    if (this.options.get('debug') && window.console && console.warn) {
      console.warn(
        'Select2: The `select2("enable")` method has been deprecated and will' +
        ' be removed in later Select2 versions. Use $element.prop("disabled")' +
        ' instead.'
      );
    }

    if (args == null || args.length === 0) {
      args = [true];
    }

    var disabled = !args[0];

    this.$element.prop('disabled', disabled);
  };

  Select2.prototype.data = function () {
    if (this.options.get('debug') &&
        arguments.length > 0 && window.console && console.warn) {
      console.warn(
        'Select2: Data can no longer be set using `select2("data")`. You ' +
        'should consider setting the value instead using `$element.val()`.'
      );
    }

    var data = [];

    this.dataAdapter.current(function (currentData) {
      data = currentData;
    });

    return data;
  };

  Select2.prototype.val = function (args) {
    if (this.options.get('debug') && window.console && console.warn) {
      console.warn(
        'Select2: The `select2("val")` method has been deprecated and will be' +
        ' removed in later Select2 versions. Use $element.val() instead.'
      );
    }

    if (args == null || args.length === 0) {
      return this.$element.val();
    }

    var newVal = args[0];

    if ($.isArray(newVal)) {
      newVal = $.map(newVal, function (obj) {
        return obj.toString();
      });
    }

    this.$element.val(newVal).trigger('change');
  };

  Select2.prototype.destroy = function () {
    this.$container.remove();

    if (this.$element[0].detachEvent) {
      this.$element[0].detachEvent('onpropertychange', this._syncA);
    }

    if (this._observer != null) {
      this._observer.disconnect();
      this._observer = null;
    } else if (this.$element[0].removeEventListener) {
      this.$element[0]
        .removeEventListener('DOMAttrModified', this._syncA, false);
      this.$element[0]
        .removeEventListener('DOMNodeInserted', this._syncS, false);
      this.$element[0]
        .removeEventListener('DOMNodeRemoved', this._syncS, false);
    }

    this._syncA = null;
    this._syncS = null;

    this.$element.off('.select2');
    this.$element.attr('tabindex', this.$element.data('old-tabindex'));

    this.$element.removeClass('select2-hidden-accessible');
    this.$element.attr('aria-hidden', 'false');
    this.$element.removeData('select2');

    this.dataAdapter.destroy();
    this.selection.destroy();
    this.dropdown.destroy();
    this.results.destroy();

    this.dataAdapter = null;
    this.selection = null;
    this.dropdown = null;
    this.results = null;
  };

  Select2.prototype.render = function () {
    var $container = $(
      '<span class="select2 select2-container">' +
        '<span class="selection"></span>' +
        '<span class="dropdown-wrapper" aria-hidden="true"></span>' +
      '</span>'
    );

    $container.attr('dir', this.options.get('dir'));

    this.$container = $container;

    this.$container.addClass('select2-container--' + this.options.get('theme'));

    $container.data('element', this.$element);

    return $container;
  };

  return Select2;
});

S2.define('jquery-mousewheel',[
  'jquery'
], function ($) {
  // Used to shim jQuery.mousewheel for non-full builds.
  return $;
});

S2.define('jquery.select2',[
  'jquery',
  'jquery-mousewheel',

  './select2/core',
  './select2/defaults'
], function ($, _, Select2, Defaults) {
  if ($.fn.select2 == null) {
    // All methods that should return the element
    var thisMethods = ['open', 'close', 'destroy'];

    $.fn.select2 = function (options) {
      options = options || {};

      if (typeof options === 'object') {
        this.each(function () {
          var instanceOptions = $.extend(true, {}, options);

          var instance = new Select2($(this), instanceOptions);
        });

        return this;
      } else if (typeof options === 'string') {
        var ret;
        var args = Array.prototype.slice.call(arguments, 1);

        this.each(function () {
          var instance = $(this).data('select2');

          if (instance == null && window.console && console.error) {
            console.error(
              'The select2(\'' + options + '\') method was called on an ' +
              'element that is not using Select2.'
            );
          }

          ret = instance[options].apply(instance, args);
        });

        // Check if we should be returning `this`
        if ($.inArray(options, thisMethods) > -1) {
          return this;
        }

        return ret;
      } else {
        throw new Error('Invalid arguments for Select2: ' + options);
      }
    };
  }

  if ($.fn.select2.defaults == null) {
    $.fn.select2.defaults = Defaults;
  }

  return Select2;
});

  // Return the AMD loader configuration so it can be used outside of this file
  return {
    define: S2.define,
    require: S2.require
  };
}());

  // Autoload the jQuery bindings
  // We know that all of the modules exist above this, so we're safe
  var select2 = S2.require('jquery.select2');

  // Hold the AMD module references on the jQuery function that was just loaded
  // This allows Select2 to use the internal loader outside of this file, such
  // as in the language files.
  jQuery.fn.select2.amd = S2;

  // Return the Select2 instance for anyone who is importing it.
  return select2;
}));

},{"jquery":5}],7:[function(require,module,exports){
var beeps = {};

beeps.success = new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");
beeps.success2 = new Audio("data:audio/wav;base64,UklGRpZmAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YXJmAAABAP3/JgA8ADsAVQDHAM8AoQAeAbUBSwEFAY4BwwH7AJcAEwHdAFb/Tf8vACz/0f1X/tv+8v1I/Xr+QP/C/bf9TgCaADb/VAAxAtIB2wAaAvMDNAJNAGkCwAJT/3f+zf9q/jz7t/qh/Mf6yfZq+JT6ePdq9qL5ovrS+P34K/3g/gT8Hf6DAy0C3v8sA5EFXQPMAAAD5ATN/wr9JwFK/y/5t/li/Of5lPWr9qT6efcB9DP6vv3Z+VX7kAGtA9ABSAPfCW0KwQVhCiAQTAvoB88LQg2RCLkE2wfDBw3/H/4IBMv/RvnS+yT/Pfzs+OD9YQNN/nP95AddCmUFSwh2DnUOKwuEDnoV6hDYCQkQjxIwCc8FNgmYBjf+WPt4AD39TvLA9C37PfRa8G/3dPqg9ab0//0HA9H7Af6/CdsH8QFZCCcOlAmWAwQHDQuDAAP5cQDB/RLwYO+E9GrvH+Zb5oztk+hj4CrqlvFb6Rbq/PUn+tv2ivmDBRsIUf9iBSMR+gliAqwIeAtrAwz8CgALAWryO+75+IrzxeYo6k3wa+wd577t9PgT9LbwjQHfCbgCVQenE+oVZBEOFRUhqh39EJQY7iAfFDgMdRHCD/wDV/2oBJgD6vJs8wIApPjq70T6FQO5/sn7QwkaFRIM9wpeHYsfNxWCHAUogSPOGMYbDCR/F98IahGyERD96/bq/m76/erL59rybO5A4HbqCPj57ATp2fnCAoj8cfxpDBMScQU/CvQcWxUKBv4MKhK8BI/3C/z4/cro3d1K7LHnkNHL0oTd2NeGzWPVweRj3lvWd+vK+iLwKPRYCEoMbwOOB0oYsxWDA2QK6RhAB+D3DgEGAaPuCOUF71TvMtrP17PqZOPw1Ljj2fOc7s7qRP1fD8QHQwWCH0QnoBe3Hxcyji2BHtci9S+rJOUPyhguH00F8foTCC4FW/GF7QH8Avpv6DHzMAr3/k33nA0aHDcTthC6I9ItVyDGIa46EDURH9ol1C+uIcQPbRMSFwwBWO/Y/n/+x+Eu3yjuFOkG2mXgNvHa62rfAPP6B6T6d/gFDusUWQm8C+cdjB3NCBwKlhpmCGHztfvl/APnN9n04uLjS83+xIPY/tSSwcrME99D2e/Rm+Mc9/Hy1+4cBykT/AEjBqgaqhjeB1QKAxc/Dbj3l/3JBxbveOD27bPuetsX1vHjeuRk1SDdEPfJ8CPmK/wKD2AJmQeGG5snuxxDG5QzYzR4HaoiuzGtJsoT/xZvHR4M//jlBZ4MgfG/67L8Dfq56+7xcQTfAv/2TQdwH2oTUAyTItMs0SFOIjc0/DXnIlkgPjI9JgkNMBOwF4MCo/LV+vP8Pudw27PtBO761ordd/Ao6z/hje+LAlT+KPasCaIZtAgYCKMdMh0MC28JPRTXCwD2BPc4Ae3pd9bS4mHk2c/6xp7TkNWBxZbH399T3WzOWeFa99nzB/G7ArMPnQaQAuUY0h73BjQICxhZDoT70PwyBBb1KeGt6o71jd3W0mDkgeU32JLdoPF588boefaUESEM3gNXGrknsh3MHLYu1jMCI5sfGTOeLMoRlhbgH4YNVvwABK8JXfdK6hv6hP9W6nvutASOAp/4FQWQGIgVBAx4HdUwTSIFHhA0hTZ6JEshmi3VJzoRqw5AGrkGeO8W+oz9oOj93Zbp3usA25PYae4P7/zcfusnApr91/Y3BgwVEA1wBtMZ2iKbCl8FCBVzDcP4l/YB/d/uN9n+3Q/pQdLWwsjTNNaVxvTHtNq03SjSOdyq+GL49+xsAJ4QLwe+AzkVqRzmDA4GxBYRFVL6T/tBB9X22uMu6rnyGeMO08zgnOuL2OvZuPJZ9DrqxPVxDFsOFwazFEcqvR/6F3guezXsJM8hmC+uLJoX1xOQIRwTbPkyAyUMJflx7IP3r/297/br0QGUBx/1u//lF6QU1wx5GxAsHCb7HcguLjulJK0caS4GKRcT1w4sFu4JdPOP9ML/j+yl2SboV+y62jfYnumO7fjgPeY5AHIBO/IWA/4V2wzxBoQWOR/YDmUDuhGgE2D3lfJE/1zwiNpD3bjlsdYtxRXPJNqXx9fC7NrD3rbSgNz59FH58u/6+l0RdQr9/g4UFh8ZDmEHeRTpFO0AGvoWB2T9ueFM6ZH2WuRo1YzgWOoC30faEu+g+RDpC/HSDUcPqAasE0ImwiKBGlYpXDnUKPcdDzAjL/8ZfxVUHk8V4f9R/yQNof7y5/z1AADr73XsOP5sBaT5NvvxEtQYcgkWF1ct4CX5HSQsJzdEKUUdFil1LWAT4wndF/4LlfMM82r8+O+03NDhSu1h3JXRY+ie7urfBeVa+2gAYvZM/kIUqRDKAQIU2SAEDt4D/w+oEY/84fHH/A72udjY2avoW9hHxvLNpNf8y1nEBdap4uPTDdig9oT6aO88+skNIQwqA3gP6CCFEuACKxWOGIwCDvxOBcT+3um25yL1ger/0lnfX+5A4A3bVO0L+GruWfDyCJ4TGAXODkMoySMWGn4oqzd3Lecg3ip+M+0d/xBvIDsZugDQ//AK4ADT7QDyFgAD9I3mhPyUB2X3vvglD1sXCQ7eE70pFSoYGlMo/TkbKuYceCdgK7QXAAsCFHcQ6vJG7sL/3fF32wfgrulX3uPTw+GC71zhs906+s4BxvTU/LkQtBABBloOLR54Evn/cA5iFOP8h/IW+471IN/a2CjlGd50xC7K1tr+zJbDUtRC4ZHZ59k28B396O969HYPHw4yAt4OVR5qFTAIrhCdGvoHp/egBlAEYOvh55TzCO3w2gzeuu075sjXw+si/OTutO8xBx0SxgkNDqIjLSghGWUlcTxTLxkgZyv3MgMiZBTkHJIdfwMu+6gNbQSd7c3xG/7D9arq8facBSv5BfIKDk8atQxbEtAmDSnhHbkkdTavLvQZ1iTkLk8Y0gqmEocOwPdn75X6HPVU2ufZhevq3lTRyN9k7C3jUd9E8yICaPZj9ikR7BJnA6ILbBsfFJMEYgn1EnoCqu5O+nr6Md9M2EPkaN75ySXJMNfg0VrBRtGY5vza1tcb7vr6BPM99fEJaBFfA/IJCyL4GFgHJBGbGmMLnfwEBLQHI/A244D2YvPz2+XeY+0f6NbccOhg+tjziuv4BBcWLglRDAMisCdoHjQlqjf6M14gwCioNyIkeBRJHTAdTgi9/YYIBggU8JrslwBF+DnoFvS7Af35DfX2B+cYtQ9gDDom9SuIG8MiGjR5LuUd6iBOK74dwgdbD0wT6PjS7Yb4kPOb3QjZu+U74hDQ19k376Hj1Ns68a7/9Pd693cK9xLiA/wECx7ZF7sBTAh/EuwDYvLC9kn6zuSu1JDk9uOtySvI+daS0nXG9dAd5MvfldQS6lv/S/Pe8jMJ6RCQBk4KHx0VHZgJvQ15HwsPuvzSBdUHtvMV6Ifztvai4TvbZvDz7Hbb5uf4+SP12O5fAHgTgg0/COsgjC2vHjcjtzYrNXgltSfQM1IqlxR4GpsjIgrs+wQJGwjF89DtGvuD+rbnMOzVA1b8hPHsBdMWMBCkDa8fuyq1HrMcWDT8MjAbFB8lKwEeNwpZDJYRNv6F6Qz1Kvh+3OfVl+RG4THSm9c56enmf9pa638DoPiu82cIGxDzBOkFUxdWGHUEdAM8FZ8HqvBz9pv6ZOff1yXgruNIz2LEIde62KLGUdCa47HfddfS5hD71Peu8FcGWBazBjAIRh0MHg4OFw4VGwwV8//QAYENLfiT53D1dPiy5YHeGewm77/f/eIw/A/5nuut/sgSRw4kCxQeviw1IxQfxjYyPHokJiZgNV4rbRjDGQ4g/A/W+40FVw6n9NzqePo2+BPoXOso/sn+bPJf/0gZ4xE5Cf4dryn6HksdHy6QMSIfTRr6KnIhugexC4IRw/336mLwifQP4U/ScOG25b3Pt9Nt6BHmANyg6En9aft/8VgBwBPCBY8BuhUXGNkGgwOOD2QK9vMM8pz+Lev+1PbfvuST0T3HrNSR2oXMR8vI4l/kTtRM5Cj7O/gX80oDIRMZDBsGcRtmJS4OSgwWHk0X/wJzAlsLT/9M65XyHf806SbcSe2k8FDiEeRA97n6Hu/l+FMUlBIGCbod1SznI7YhUTNpOnsq3SPFNBoylxcdGDci2BHn/usDwAop+lTpefSs+xTnz+c0/or+YvOE/fISVxPaCZkXvSsoIO0XICzdMTAgwBkBJSIi0AxaBkcRFAF85gbu+PSC4bXThN204jbU2c9s5Z3qydhJ5Hr8Vfmb8Hb+EQ92CIn/0xARHWQGTf9REAwLDfaQ8lL6xO672Frbm+io1WHFhtad3DDNCMym3hbkAtln39T6EP2X77EBdRTkDKMIDBmUIhAV9AxlHEMdQAM6As4PpAI/717zuvxJ7+berump9Vfj79+194T7BvDU+GgQiRX7DPUXuC5rKOsdSzLNPI0sgyUCMtMxjB0RFkEi1Bc9/MEBmQzO+croDfHN+FLs++XM+Y0CrPD793ES2RJpCSEVmybNIRYYoSbcNAkgFxTKJb4jeAxTBWEMpQFe6qnoXvUF5ZvOX9us4/nT689M4Uno+txC3z336Pvt7Nf62g5MBxYALA6iGG4Lmf/5C54QO/YS79f85fDj2u7bfOYG3CfLNtIg30rPCMeP3rHlYNm737b2/Pyz9Fz9tBRDEpIEtxe/JtMXnw5jGo0dEAx4A74PmQrY7sjxwwDn8XThmuli84roKeH48q3/QPD19MESnxaWDOgX/SteKiAh+y0zPyUwWiExMt80Bh9SF/sfmRhpAg/++wnt/Nnj/+49+z7sA+Zx9qP/3PRP9RwN3RUdBqsPbCYmIaYXeCMcL1ojmRbtH44lVAxH/5kM8QIL6ufnaPFv5tbSPNYB5NbW0cml36vpudp33GTyhPr98Jf1WQvBCxX7LApTG8sLFgB3ClUOCvzz73z53/YX25zZ3uvi30LMFdIL3XPTqsrC2Ybon9sW2uv2Av/c9Az+iRKaE1QLxhbPKE8erg1UHYskCxGUB98PcwsO9njwF/zL9DXdFeMD8drlU96t7SP7+vSP9vkLhBgRD2QVZivbKpYhVyqcNxgu2CAmJxctzhqWDN4WnxJy/dn3jQEl/GbsJu/i+h70eOnS+JkDgvoJ+agJDxIRCSUMyBtfHToRTRlxKFYhzBXCG4MiXxT/CQkPTA1D+jPzwPu28wziQN9J5o/cVtKB2oDh1dhj1tXoJvJ17dfxcgT3CQwFdg78Gp4XfgylESAVBAcQ+bH5h/Q34D/ZONyR1JbDkMKny6fI8cLKzebdpNyb4b3zLwBLAM0EehPXF4ISqxS7HiUXIgwwDzwQYQUo+0r/Q/8q9AXvZfrq+sHwkfXd/Zn9Vvp1A3wN0QszCF0TcRwgFYQYgCFzIiceFSP3Lfsuhyi+LPo2Pi4aKRQtYyq/H5cY2RnNFKUGWf2s/7f00er77jbvkeqg6OnxAvv3/O7+TA9vF1YVIiBnKHsoFiM3IacfBhbqBKf9+PXC31nWG9Ebx7e7NbbNuH26CbfTvbbPaNGo2I7nA/HD92v89APECVoHFgPjCS0Bp/U09mDwyOh84QvgsOHQ3s/Yz+M16SDj0eqa8Db02vbS+osBwQV6AVIHjA/QB6YMUhHkEPES/BUYHWck4SK+JdEyUC7IL4Y1QjIeMGYrQCfII0cZpgsMC0X/ZPWz9y7yrfCf8X71dv61BvIIXRj/IgIkIzLjN0M7pDwJNzMxzisLG44OlAS17Mzjg9mpy+rEob2TujW/ZcAxxo3Zytw55oL0Fft3BhEM+AwKEZIQKQgXC6oAJfXT9ILoxeE83Q3XZ9fn2ZPUCd5d5oTjze1B8H30Z/yS/ZT/AQVB/xH/EAQk+oj/ugAK/WEDbgUyCG0SBxXXFr4k/CJmKM0upSnXLGApOyCcHHMUZQPm/4byL+aa51je3N3h4FvhHer89uj6cAuPGTQdTi4PM5Q3Ij23NfEt8yrlGzsO6wMD63nj4tgMyWXF+b53uz/C8sXDzDvibucb83kDTgnBF8MeXB0yIEMgDRgTGrUP0gT5BEX27fBZ7zfpTemB7LPo9PF0+jH4mwNJBKwHuBH5ES0S0hWID2MNrxCcBeoJIglmAkwIowlQDKkVFBiqGMEldCQTKJotUidKKmollRr2FWANG/s89TLnsdlw23TQ7c2Jz6rORNcr41/n8vdlB/4K2RulIKMkOypIImAb0hdhCOv5GfAM2PvPvsUQtgmzJaz/qSGxGrWjvCbSUtil5FL2l/y3DNAUDBUsGoQbGxQDFv4MmwJNAwT1mfEs8t/sCO5E8bnuOvliAikAVw0aDxQTXh6BHuUfTSS2HyYe1iHTF24cAhvLEjYZlBrlHQMnQCnkKS03NDd/OyFCyjqHPQA59C29KMUf+A2UBt73W+qf7Gvf2Nqq26/ZyeEn7M3vHP6WCycODR+KI1Mm3ys3I60cwBcCB0L3xezf1JrLmb/ArgmsdqNUoLumB6spsuHErMm11Njl6+qY+l8CXgOPCQILRQMuBDz8VPFH8o3kIOE24analNz036XdJecg8TvvEPwk/7wDLw+FDukQThYfE94RHRajDckS0xPsDJIUFBawGdQiTCVKJ1Q1EDeLPAlF2T39QKM91DN1L28mcxawD6sBt/R393vqnOY46QzoB/HW+8sAGw7IGpIdUS8TNP81XTz7M9ctlihXGZMKav+F53HettK5wNm+Erb0sTu4Sb2Uw5HTBtje4XHyBfYjBfsLHgvSD60PPQiRCPcA5vQF9pbo9ONx4gTa9ts+3ovbhuKD6+bp/vTP9pj6AAZaA1wFLgmtBEACawWb/AYBEwNf/LgEAgX6B2gQpxI3Ft4jiSXnKQYyxyrYLrAqgiDkHE0TAgQk/XvweeNE5YvYTNZg2mPZ2uP27sr1pwR/E9wXaSkMLuovPzhTMNIqPCZPGSkMcwGb6g3i1dgQx+/FlL7MvFvE0sirzirfGubA8IACSwYzFvEdURyzINgf3hl2GsgSLAemCUz8Svc19+ruuPAJ8hPwwvYH/579xghfCsIMWxgjFToWdBjrEngPMxHkB5wKTgvTAU8J7whtCmkSIRU8GZUl1Cb6KAMw6iexK9MmFRosFgQMl/uF8hXm1tcA2GXKc8ZsyqjH6NEb3MXi/vG4AdAGoxbwGsMcZSbSHVUYDxRMBrP4IO7n2A3PFca1tO2zLq22rDu1h7fmvUHPGNhr4x722frNCxgVrRSZG5UbuRbHFt4PJAbyCNv7Wvfi+UbyKfWi9q313v26Bi0GoBK7FnoYZiSHIcgjEic9Is0fhCGGGeYbWxxvEasYJhhoGRkjfyYTK3c2ajgxOxlDlzqNPZk5FSwWKBwdIA00A4b1neb/5f7XhdIW1uzRg9ur5IfqPfjSBbIJzRfJHKMeHyjOHiQZHRSwBDb21eoa1vTKdcDord+tTaeTpH6r26wosyfD5Mri1ILmWuqU+hoEqwMGDCAMrwbHBQQADPdx+a/sfefY6UXh1+TQ5kflEe1f9v72VAPTBykJmhVrEksV5RmQFUIUQBaFD2gSzRSBCy4TcBKEFFUgqSPtKEQ0PjeFO/xEDD0WQF897zAKLqcisxTAC9H9/e6o7lziJN4U40/fGerW82P6wwfyEwYXUiVaLM4tuDdwLs0oYiS8FakIsPzt5+LcB9PQv0vBHbu/tcm77rz/w0XSatkF4o3ysfUYBToOYwvpEioRZwusCksFL/xj/ejwAOpX683gAORq5fLiWOm08WHzOv3k/3YAEg35CB4Kpw3aB7gFFAYq/+IA4gOD+l8BHwFGA5wPNRIDGA4jKyaIKbYxtyoBLoQr8R26G9QQugJA+eHrWd103PXRFM6B1FjRAd2050nvP/4qCyMQFB/rJlAofTPoKoUlsSJNFU0KqP746mLg89gbyJrJrcOUvvjFFscSzz7eA+fl8NMBagYaFiIg8By1IwwhqxxFHdUX4g6XD4gDV/zY/gT14vez+F32Fv4dBkMHbxACE00SCB+KGjga8hxoFXsSNhGcCewJTgqM/ocEOwXaBrMSZRRDGosk6ya5KBgv5yfYKmsnJRfzFIMJlPlM7v/fsdHgz9bEEb/ixIzAUcuw1QjcQets+A7/CA37E7gVSSHvGLoTExGvAtH3LuzR2VfPWMjTuA+5NLLordK2Obegv0DPV9l05A728PvbC+sX3hUuHl4dZhp4GwcWzQ2FDpIDpvz3AJb4+/tC/Tb8VgaBDs4PnxlYHgEe/yqIJrQmYCvVI0UiDCEhGs0ZChkzDYQTOxWXFgMjRSR4KmU02zaSOZNAzTkiO0M4kCgIJjkZ8Ahy/RvuYeBI3rXSlctx0L/K29Qd3j/iNfAw/P8Bzg4UFn0X7SJ6GpQUwBGnARP2f+mS12PNBMV9s1WyD6sFpaKta62KtUnEEM1E18bnNO1+/GAIbQUHEHIQjwz9DOQGIv9G//v0ie0u8ZLnTuuM7nztDvde/vIASgqND2AQexzJF1sYXR5hF8wWqhUND5cOpw+DB+AOzhDyEWof5yCQJ8YxGDUROVlB5TtZPK07HS05KpodWQ68BBb2GOnD5qjczdYK3fHXVuKc6+XvEP/HCSAPExyoJBcmxTHJKWwj3CEaEvEHm/tf6lLgsNcNxTPDXr3etQC+v70+xkfU4Nsy5TH0u/k3ByASXw4pGAQXLRKEEoILtwP3ArL4W/Cs8mTnNutI7w7sH/Q4+vf89wSzCCYIbhO+DuENohIICmMIKgU4/Z78xv5B+BD/wQDQAIoOkg9eFucgcyNiJzIu4yjXKhUqIhkNFwgLgfsm89/kedg41VHMo8e/zvXJ0dOB3iTkRfXsAJYI1RW3HmchFy10JqYgOSCKEQEKk/8z7+HkW9zXy97JFMUXv47IXcgj0U7g9+jl868DKQoNF/Ij6CDAKesn7CIkJMccrBV9FF8KBAKYBJr7LQDKArD+Tgh+DdIPuBcNG0cZ2SPPH4wdkyHoFnoTZQ4RBsoFlAY7/rMCaQRoA/0QcRGsFxIisiNpJigraCYDJ5sk8BEYD2MDyPKb6QHae82QyU/ABLo/v4y4m8EyzUzRqeJj79n3sQQqDfUPUhuhFbMP3Q8IARH6ru/F3h/U68qBu5y5ObVYr4a6G7pjwp3S7tvS6JL41/9RDtwcphpCJUkl1x/HIYYaTxQRE8gJDgLLBj4AbAS4B1QEkA9MFREYVCDrJGckWi7RKpcoNC7UIpkfQBxHFd0V6xVBDZMQdBKREbMfKiA1JgIxDjK1NXo7HzdLNfUyuCGAHrsS/wHq+Lnnx9vQ1zLOI8bVyDvC3cpc1cbX7uh985L66wYgD/0RgRxNF7gQ/BHsAlf66+0P3HPRFsfhtk60H7BTqI2yELKdubHJedES3SrrPfMJAXsOUAxvF0cY+RHdE9oLswVABMf7CPUq+U7xefTn+af1YQBFBiAJXhHrFbAW+x9lHJ8ZRB94FBwTexHkCkIM9gzBBsAKkg3BDPsaLhxAIm8tvy6QNIY6qjZRNbQzkSRJImoX/QbY/7rvjuQk4KLWUM9a013PJ9dr4t7l9PbcALcHIxTiHM8grCrKJvIgZiOoFGALZf9X7SPjo9i3yc3FLML/uj3DvsKqym7aRuAz66D46wAEDc4YrRaqHjceOReWGBQQCQo4CJsA8fnG+gbz8/Rj+R/1Gf5eAvAEHwzaDg0PPRarEtUNEREWBxkFQQLb+oz7a/u39oD6W/2Y/HcJWwufEGYcMR7CIpUmbiO7Ig8hyRJ3D7YFd/VQ7m3fkdSNzhnF8r+OxEvC98li1grbz+zZ+JcBxg57F90cTSbpJPggXiInFDgM9QHK8GTnvd1L0RrNM8qDxYfNZc5u1n3lDO1++iEIcBGPHRQpaCi/LsUt9SZpKH0gshs2Gr8S3AtaDE8H8giSDH0INhEWFdgWLB3UHgkeIiJfHlEZLBz9EmsPpwuqA/cCdgB8+p36P/uU+qwENwYPDPQXDRqrHtohNCDYH3kdJRBoCZv+C+4f5PbT/MdRwaq5C7YguXC5lcERzZHSU+LR7QH22ADKB/cMNxX3FWcTSBQlCoQDzPrI7NPjYNj/ypbDsr2Qt0u8aL2ow7DSAt7U7U/98giDFc4ffyA/JJIiEBvcGEARkA01DSYJbgdEC+UL0xBwFoIVyBwHICogdSPdI0sjqCVQJIojiigyJXYk1CP6HgweKRrxEr4O5Qu7CfAOdBEhGBMjhSg6MAI2uDdQOPszbiZXGoQKo/YA52PT0sT3vPO1obMQuLq7mcNqza3SBd6y5e3pU/Cs9DL5GgBvA48EngjGBYkCTP0M9ArtluK51dfL9MMAvtG/YcFhybLZiueW+LkJARiCJb4usy89MO4riSJKHFoTNg2LCpIHtwejC/8N4xLdF3QWmxdeFacPdQv8BcoASv6V+9P6Nv8HAHQBbwG5/YD7HPZG7v7nO+Mr4CTjCOgh8h8BpA0vGwwnYS9INV41Ay1ZImYTmgFH8rvhqdYH0nvP2NEx2njkmfFF/rIG1hDLFqcYjhqPG0cdFCCaIfEiMCe9JxMmKSIhGl0QlQGu74/eWs9Vw8+81LlNvm/KJdlL6/X7SQneFGAbXhpkFhYOkQKH+Jrueukj6tTsmfJr+8UD9QzZFTAaiR2OHJcXWxIjDcYJZQiUCBUL9Q+SFLsZHh3XHIcaTxQ0C1sBh/j68vLwefHd9yEELxGIHvQpqTLPNhky9yR4E6b8XuPWy6S1KaVInEGZAJ0Ep0KzQcCJzKjVcd1G4jXjfuMf5Pbl+Opp8vr6+QXUD98VcxmGGxQaJRJMBTP3QOsh45req9+86Dj3iAmMHwc2sElBWTFitGL/XH5RYEJrMqEjehk3FYYVCxpXIU4pgDENN/w2fjJTKQIcTw2X/6r0O+yi5gnm2+oG8Uz0D/WZ88zupOa92wDQd8WNvj68S8BczILdufDCA9gUfSN+LX4vASlgHP0J4/Td4GjP4MLpvE28BcFszS/eYu59/PgGNw4PEjMRog7CDPAKywkfC1oPMRYLHT0gECDNHNwTRgWh8q/dOspiu06wo6oBr9y7S83R4EX0mgUGFJscMh2/GHUPuAI29gvsoOfh6BnuO/d6A3sQFh7gK0E1qDjLNuQwoylJI84dfhoSGoYbXyBAKX8xOjb1N+I1Qi/2JFoYBgxFAif73fgt/00LthlKKB41Lz9dQ0k/hjIOH5UGP+uO0Me45KYinKKYaJwmpmSy376SysDTb9oo3i/ebNx428LcnOCs5iDv7vmkBPsLNhFoFEISYgqr/i/wKuIU2HfSNNEV143jLfVHC4Qh3DSKRKZNXE5SSf4+rDCwIRgTNQgeAxcDDwd1DmQXqh8AJuAnCSWrHWcRPwPc9tPrleK73vHfD+PT5yPtGvA08bPvDOqa4X/XEs6wyMLHT8sn1trm3PkUDSQfni81PEVBsDypMKAf5gtW+Anmg9gZ0enOI9Rn4czxawGbDwsb2CKYJsAl+CH3HBsYyhXCFjEaYiCzJnsp2CjLJSUeNRBn/aroBdV0xFa4K7L+tDq/Ec6x4CHz1AOuEXgZMhrHFXIMgf/C8iLopuHT4Nbk5exg+AgG+RRmIn0qGS7ZLRMoDR/2FsMQSguVB6kHlAwHFYUcdCB2IlshSBueEcgFd/ot8YLqA+ir7CL4HgaDFJAhpivpMOwucCQ7E2n8H+KTyDuxdZ8glkmTZ5UMnuOrpLrGyB7Vpd6Q5G3mM+YD5/Po6Ov88Of4sQN4DoEX1B7uI/EkbR9nFLUGIfqR8L3pceZF6iD2Twg7HlMze0Z0VtNfIWEXXN1RZkIUMPYe8hJlDaQM5A+AFpUevSaMLagwwS5QJ2Ib+wzm/vDyMOoZ5SnihOJt54Xshu5J78ntXuj839HVBc1Qx9TEZcbmzhLexe+HArwV2ybDMtw22zJyKAIY2wJQ7QvaJ8r9v6K9tcJKzmbdeuy9+qUG4g4sE6cSlA4mCtsGkAQZBMEGsAxcEjkVfxXmE1EOeAIO8hDfGszSvBuyeKzCrjS4jMbS2GHsCf9YDygavh1oG2IU0wlx/vHz8uyw61/vLPclA3ISnSIQMWE86kLaQkk9ejWjLcUlKx5JGrwa7x5dJostwzHcMxczfC0zJDEYEAtu/x/3cfP89n4Bjw6qG9InBjLRN4I2Gi07HOAFtOsB0Zy5b6iTnNOVpZa0nxStuLpLyMjUOt6+497lCOak5cXlGues6ifxtfq9BQEQnxc+HLQcOBcEDej/4vG05QbdD9hA2kzlQPZRCsoeTDGvQD9KKUyGRxo9Pi1WHFUNeAG6+mX50fuJAdQJyRLeGtMfvx+TGgUQSwLm9SDsPOOF3KzZq9pI31zl2elH7fbuK+xN5kjfL9j80uvQLdLL2VrodPp5DrIiSjRxQVBI70ZqPW0tNRmvA3nun9101PHRwtVP4Mnuxf3iC08YYCGVJRYktx6sGHATqA97DlcQURRgGEYbhhxPG0EWowuo+9focNb0xhC8brU3tBO6Ccd02c3svv5YDv0YwxylGoAUHQon/fLwSeg+5dXnYe+H+wULuxoyKVI0PToUO1o2jixEIU0XiQ/OCpMJowtDEeEXmhueHZgduxhTDzcDfvZx7Bfm7uIf5bnt5/iPBRQTIx7MJI8l/h0pD/D6RuIzypW0JaHAk3eOppCQmYanfLeUx8TWD+NB7CPxyvFu8bTxu/Kf9Vz89QZZEoEcLiWaK24udStsIm8VQQd/+mbxDe1f77P4gQerGqAuu0BYUEhab1xEV0pM3DwIK64ZAQxYBDEBnQG1BigPpRjyIGkmdCc2I4oZ3gy+ANz0Yenx4Evch9vu34Hmd+pW7RvvBO0g6CLhGtmW0sHOnc7h1IXi9fMZBzkb3SzVOS5AMD7HNGclZQ/o9g7iotFHxqDB3cPizHDaxOhl9uoClQtmD74ONgpsBbcBUP7D+177QP6PAk0GlAjWCNEFCP1H7xjfDM/NwGO1b63uqzSz7sAO0z7nL/ttDe4a4iHrInseoBQNCHT8zfMH8W30G/wnCJMXFSh9N3ZE00zFTk9Jtz6iND0sHiRLHvIbwRw5IVUn0SrwLFktBiiAHiIT5Abc+/ryye0D7nX0df6iChIYeCPsKjos4yV7GM4FD++j1RK9k6humliTupNSnGCqy7n0yDvYmeVz7p7yVvKi8Jfvae/h8RX4JQFuC5sVLR6IJDsneyPXGUkM8vys727mH+Hw4L/nePRoBSoYJypKOW9CY0S+Pyw2/yc1F5kHAfq27zbr0uvk8DH6zwQdDjgVaBgAF/gQTgW7983r1+CF2NrUndUk2v7gAufz7O3yRPTO8DvrZeR73lPbWtwr4xHwwQCLE+Mn/TnpR2pQnFCqRxk3kiJODfz4sObq2SHU+9TL3GfpW/erBEoQLxntHXAejRprFOMNRAdiAngBywNFB/cKug2dDsUM8wWU+sjrw9qTys29mLV6s5m4G8RD1YPpMf1fDysdTCS7JDsfABX2B937N/ME72/w3vbiAfUQaSGIMI08UEN6Q6s+qzWoKmogoBaMDsAJsgg+CwsQVBM5FD8UfxA4CB3+6/J36C7gptqz2ezeXeg59GYC8Q5FF+MaBBgFDlH9JubTzAC2aKIilO6Nzo/JmMWm+bfnyhndLexj9i38hf3i/GD9hv5WAIkFag52GHIiryvAMr82ijSFK1kfNhITBjD8XPWx8w35BgRYE2Ul/jUURMpNw1CuTddE/zY1JU8S4wFf9hnxuvBX9Xz+RgmBE/wbvyHOIZAaRA67ADb0Aen9387akdlF3ZHkp+sO8cX14vYV89TsuuXp39jc99zu4fvsD/yeDTQhMTNuQA5HK0V8PJ8u+BrSA4XtPtoyy6XC1MEPyKvS896r6/r3UAIPCJgJRgZv/035vfNl7jvs7O2a8c/12vlr/OP9m/oB8Kjh49Evw8q3WbA7ruCzh8Cn0VvmcvyeEFwgRilgK8gn5h9gFbgKMgID/b/9oQMqDkgd5S2bPHRID1H2VBhSmEmjPgg0TCmhH1oZ6BYMGAYbRx2tHuYfzR3yFggNMgEF9V3qHOPt4PTklO3w+OQGORSQHj8k3SGIF4UGgPAh2JLArqzanbeVdZUSnnStt76+zzngou6T+Gb+tQAtAHr/7f4n/9kCwQl1EqgbAyRHKaUrNSr/IuQXTQpI/Hrw7OdO5ODmxe9K/DALrRtAKsM0dTkuN34uSiCiDmz9re654uXb7dpU30DpQ/Y0A7QNCxScFOQPSAb5+cTuceQ82xnWodbm2yrj6+oU8oj4Bfym+jf3VPPP7sjrBux68Bz6gwhoGTUsZj3wSV9SxVQ1T+JBWy6mF/MAzuxY3EDSYc8q0wXdcunt9mQEmA8DFokWlxKUC7wDz/sJ9TvyEPMs9iT7DAF0BU8GvwLD+YHskt2Ez5fDTrvRuB29XMjB2OLsJgKtFOQiXCsHLpMr3CNgGGwMrAIr/Kz6d/54B+gUnSPuMdM+9Ec4S2RI0j8jM08mHRq0DnEFFgBv/yMBrgPSBWwHuwaq/zT1Derc3p/Vnc+wzazQt9h85Nnz3wMED5AUxROxC6b9F+o100a9j6qSm+qT7pUdn1OtXb7d0GLjP/SVASkKIA59DgEOpw1CDb4PwhX+HIMkACz8Mik4PzkVND8pfxvSDQQCyviA8yr0uPquBZwUfCWgNLk/IESDQQQ5jytHGqIH7vbm6W7i+eAR5s3xGP/jCggVVBzdHiwbYBLpBij7CvC+5nXhaeCM47PpZ/AV9nH7o/+r/0L8Zvct8mDuCe3Z77D3eAObEbYhtjIJQbFJzErlQ7g2PCNGCwfzod1Ky6G9I7hzu7/EWtH33gPs1/YU/ZT+5fvI9S3v8ujB4mLfYeDI5MfqnfC+9Br3WvZf8GvmY9rOzZnDJr2ou6rALcy02xfuXwLYFVEm/jERN7w1iS+KJREabRCKCX0GPgjmDpwbhyuxOuBHElLuVn1UmUtXP2UyjCQkFgML9QTIAtMDXgboCKsKEgqxBTz+D/Ue6/DhMtt42O7aGONS73f99QriFdMcwB0AGBoMyfoq5n7R1L+Assmqy6kzsCK9fs3t3onw9gA7DYoUbxd+FqUT5A9VDKsKjgsMD/YUMBxSIhQmTyZ8IZwYrwwr/xDyNect4Dve3uE+6iP2aASCEb8agh+ZHsoXdwwJ/uzu2OGo14bRstEV2ILjxvFTAHsN3BdsHVkdShgUD6cEt/ok8dzpHedu6HvsmPIV+rsBUgjdCwIMOAoMB64DFALVAkUGew1FGHUlyTKHPQ5EUEVvQGw0JyI6DK/13+Dkzr7C+L3Ov97HDNTJ4cXvAvxgBNMH+QZfAh37PfP564/nIOfM6S/vH/a8/AkCBgUqBNv/+fi48BjpnuNg4Wfjw+n88/oBjBKGITUtbDXEOIM2gS9YJU8ZDw7YBL7+Zf4zBKIOxBtIKS81tD09QdY++jbOKawZAwkQ+Xnrx+Fq3MLaQtz1383jsuYW56bkpOCH26jWudM905XVWNsU5Zrxy/4LCrURzBQ6EisKmP3a7UHdd860wqG7W7tvwRDNqtzv7WH/tw8MHRglPCgDJwAivhsNFWcP3QxXDVkQKBVQGmYewiBlINwbMxMTCDn9JfTr7DzpEera7gb3uQGPDH8VsBoGG50WQg4xA5b2d+oY4X3bAdx/41vww/+sD4UebCqjMbMz6DBlKWoeEhL1BUT8zPWk8qnzc/im/icEAQotDzYRvBB+DlwLYghqBmkGdQmRDxcXUh+GJhwqpykiJPMY3QhL9U7gjMwivHSw1apgq7+xOb0PzGvblehq8sr35vgT9qbvkuhx4qzco9kT2/Dg9ehn8Rn5Of/qAkwDRgEw/dT3u/PP8RPyB/Yd/lAJ6hW9IW4rEjM9Nyo2nzARKHEdshKECToDWAKDB2QQHBzxKOA0JD51QzZDbDz8L4Eg7w/t/uvv4+SH3XraxtuC4DfmNuu97uDv7e5r7DDpO+ZH5NTkDel+8ZD8ewj8Em8a8x2CHPUVRAth/fztKN820+DLN8rqzVvX4eW/9fYEYxOaHygncSmFJywh0xi9EBsJ0QPjAfsClgY+C4kPExJ3EkAPVwjy/mX0pupA44Pe5txV37zlku69+KAB9gYoCAAFDf6T9Lrpcd+J11nSBNIL2enlZfXdBWgVCCJbKtktkSxkJvgbxg/UA1/5QPJJ7xrwFPT/+bsAAggzD1cTWBSAE08Rxw7yDHQM8w5OFBwbvCI0KsEufy8MLCMjfhTjARXuVtsvyx+/17iouB2+VckB2FDnvfTt/ggFmAZcBEX+G/ZO7k/nfeMv5B3pqfDg+JoA2AYcC3gMxQq7BloBl/yS+f34k/trAbIKexbNIZ4qKjHzNNQzRi6XJcAagA+vBZn+K/ym/88H3hKUH5Mr3TTPOUE5GzPJJ18Y1gZq9f3lpdkT0SvNp83V0ZrX0NzP4NDiheKs4B/eB9wf29XbVN+/5jbxtPxqB3oPxBNcExsO1ARr+Mjpq9tl0N7IicZKyqHTaeEf8TgB+RCbHqkncCu7KsElLh5gFssORQkoBwUIZQsuEAAVoBiaGr8ZXBRuC6IB8/iJ8SPsJuoE7H/xx/mDA48MaxJNFNoRowu6AjD4Nu2u42bdXdwN4s3tpPy4DBwc6CitMZY1rTTELrIkYBhlCx0A7/fe8snxEfWK+lcAeAbrDFwRgRKIEQIP3wtRCfgHRAlkDRUTHRrgIJsluiY2IxwaIgyr+irn0dMYw522+q4LrV2xf7tnyZ3YMebd8Kf3Dfpw+C7z3+v55I3eGdru2XPef+XC7fD18/wjAsIEkgTcARD9qvgS9hL1Dffh/BsGBhH3G7sl0y0+MwE04S+UKAAfvBRxC48E5QG1BGkMPRfzI14wdzobQYVCAj6CM/QkyxSYA2DzpubX3SfZJ9kl3c/if+gG7cDvfPA878zsA+py58Dmf+n670D5DwRnDowWTxtfG5wW6g3MAWXz8uTZ2HzQH82IzobVbOLI8ecAaw9IHGAlGyk4KDAjQxumEkgKpQNRAE8A/QJwBzAMFBDkEc4QBgwUBG/64PDL6PTiud8Z4JDksOuj9JT9sQMhBqAEKv/09gHt4+KJ2vnUgtMA2NDidPFsASwRdR4UKDEtQy3GKIsfwxN+B+j7IfNX7qjtjfC29RT8kAOAC28RRhTGFFcT7hCkDmUNhA5XEusXnx5wJb8q7izrKh8k0RcvB2n0FeLj0e7EA72uuuy9MccQ1Szk6PEH/VYEXwdJBlUBrflU8XLpHeQS47TmTO0q9U39cATfCa8MGA29CuQF4gBT/Y37cvxdAKoHaxEIHBsl7CvbML0xti1XJoYc3xEACEEAqfxA/n4EjQ7qGh0nZjEEOGM5GDVrK3MdnQzk+lnqldxF0tHM/cs8z93Ugtpz3/XiWeTs4/LhqN8W3hHeTuDW5XLux/gMA58LGRFXEtIOXgd4/OnuEuGT1V7NQsmmysLR890U7bP8LAyUGjQlbCrkKicnHyABGMIP4QhlBVgF4QdSDIkRNxZnGT8aUheFEFUHd/7Q9pLw1ezI7Gzww/ZV/xYIoA7hEQgRZQzMBCz7tvDp5t/flN1K4e/qtfhrCP0XgCVoL9U0cjUUMfgn6BvSDk4Cnfgg8nfvavFL9jH8cQJgCWUPNBKHEgcR7Q37Ct0ICwm/C0UQDhZnHIAh9CNXIowbuA/3/7nt+9ofyvy8A7Q5sG+yW7o/x0/WKuRx7273EfuO+kb2Ru/V59fgMtsY2fTbGOIf6rrydPrCANsESAZbBXYBzfy8+fH3H/jT+0oD7Az0FjkgBiiSLisxyS7tKFUglxYsDckFSgKSA4wJKxMvH7YrnTaCPuhBeT/eNocpqhnCCAr4yOlo3x3Zedd02snf0OVY62LvnvHL8TLwju2P6tvoKuq07mX2DwC+CQISsheYGdIW0Q9YBer3DurK3cLUyM+jz7fUPt/Y7Rf9XAvHGPYiLSh5KIgkaR22FK4LxwM9/y7+5v/6AwUJ1Q30EIcR+Q4YCZgALvel7u3nL+Ou4UDkq+kn8ZT5MgDfA+gDGwAy+TLwW+aJ3YTXVNX112PghO3p/LsM5hqjJRss0y2mKrAiYhfxCvb+kvS67WrrEu238dv3Kf96B9kOahMwFfsU5hJPEF4OhA7lEBUVzhr/IEEmJinHKE0klhrTC1X6iehz2DXL18G2vUq/QcZI0v/gD+8i+7YD3QfrB+QD7PxX9LfrxOTg4efjduns8Ez5RgHkB0IMEQ5xDegJPgU9AWf+hv3S/0oFXw0BFw0gzyYoLLouhSybJgQe2RP0CeMBlv30/c0CfgvYFh8jKC4SNik5xjbbLiMiERKDAKPvqeCe1GXNCssazX7Suthn3v7iveWD5oflWeMS4U/gj+Ep5e/r+fSr/kcHVQ0rEMQO+wiw/3bzGuZ42qzRZczsy0/Rm9tm6Z/40weaFlUiAym6KsonjyGJGeEQKAlNBPgCfgR/CNQNLxOAF+QZJRl4FK4MBAQs/Ef19+8t7vXvlPSw+8MDiQrDDooPwgxeBsr9+vMl6nDiId//4Kfo0vShAwMTKiFCLB0zOjVWMp0qWh94EqcFkvpT8hTuTu6E8n745v41BvgMfhFDE98SjRAuDX4KuAn7ChoO0hJ2GIEdgCBMIOkbmBK2BMrzB+JZ0abDtrlotMm0z7qXxaTT2OH47eP2xPtn/P34dfLP6lbj49wJ2frZIN9q5vXueffW/j0E+wZuBzAFBAFA/d36m/lc++cA7Aj3EdUalyImKUAtMS3AKEkhLxj1DkgHBAMUA3cHsA/RGjUnmzJhO1VA+D9COVAtMR6DDez8mO1U4YnZT9bn1xjdeePO6eTuV/Kz8yPzKfHj7YPrlus77v7zMfxGBZsNsRO3FskVzRAHCBX80+634jXZBdNX0c/Ubd1w6mf5hQfxFDMgwCZrKGMl2h5iFggNjASv/k38If2LAK8F+AprD7MR8xDJDM4FJf1u9OjsH+ce5MrkauiE7uz1uvwWAXICdAAP+yHzzunD4D7aMtdx2PHeY+rA+CQIwhalIlQqUC2mKzklwhpEDg4C1/Yk7ujpL+oM7vbzP/vIAwEMBhJaFSYW2xQkErUPCw8YEMsSShfiHBEiXyUJJjMjQBwREMX/qO4E34zRNseAwXbBjsaN0Drenewg+YkCKwhLCSoGAAC098TuHed74k3ikOaA7ZD1Ff6mBT0Lfw44DzENRAkMBYkBWf/f/4QDxQk8Ev8a2SE0J8wqyCqAJgYfzRUBDIUDZf6a/S0BeAjAEpUeICosM/c3bDc7MTEmVxcEBsz0OuX+18HOsMpYy/7Pddbd3GHifuZ86IzoAed15ADjk+Om5TLq2fGe+s0CdgksDSwNQwnwAYH3HOti31bWEtDHzVDREtqD5tX0tQNKEtgeBCcAKmko9CIrG2kSIwomBEUBlwHyBEEK+w8gFcMYJRqcF0cRTgmVAVT6y/M48I3wSPOy+On/zwZ+C1ENJAyKB/X/9/aD7XXl8eBf4RLnmPFc/z8Omxz5KPIwSjQJM8osiyLhFdII7PxA80XtnuvB7q30MPuXAkYKMBCFEy4UvxKmDzwMqAroCoQMKRDuFMMZIh3mHQ8bABSFCIL5rOhn2ErKh7/UuH+3w7u+xFzRbt8m7AL2E/zQ/Wz7mPX07fnlDt852uzYQtwA4y7rDfRm/O0CDQfCCPwH+gQhAQb+l/uM+1H/ngWZDc0VMB3EI44oMCqrJ64hnhm2ENoI+AMPAz0GGg0cF74iYy46OFE+vT9jO/UwryJ2EtsB/PFn5ATbD9bh1UXa2uC158TtjfJ29f/1vfT58evu5O0a76vyP/k+AfUIDA/HEhYTwQ8oCVj/VfO+5y3eRddW1FrWSt2i6Hb2NwQ3EdccmSRYJ1wllh9lFwIOPgW2/hD7fPom/RECBQimDZcRzxK6EMoLmwRe/GL0S+2I6Cnnf+ha7CbySfjy/AP/YP7I+oj0puyV5EHeJdvF25jgAur89i0FDRMkH3cnGispKtUkSRsrD8QCEPd+7ebn+Oby6aXv3/aN/9EIfRCQFaQXLhcCFR8SQRDJD5MQ8xKZFoYaqB3pHlkdhxjnDzsDnvS85mfaXNA3yjXJ68zQ1FjgG+0K+VoC+geoCR4HKgEM+QrwIOj24j7hvOPc6frx6vrRAy0LCxCoEnwS6w8DDLYH2wMGAhMDQQaZCzYSBxjFHHQgzCH2H+4a8xNLDLIFqgHqAH4DNwnaER0cWibiLtYzGDQmL1klwBfQB/r2NOf+2abQA8zvy8/PrdZk3pnle+tf75vwqu+H7T7r8unj6U3rRO8l9T37sgC0BO0FzAMe/134re+G5rjeSNkW12LZK+CK6tv2UwNsD1wa/yEiJbUjbx7PFkoOfAZ7AEf9Bv31/00F5wvdEqkYNRzQHKQZrROfDN8EQ/1o94L04PNy9Tn58/1JAroErgSDAtv9nfct8bzr6+iK6bftpfX0AIANihnpI9oqlC0nLEsmqxwBEU8E9PdV7o3oyOZt6UHvvPYF//EH7Q8/FUMXkxZZFAURqA19C1kKmwr4CysOaxC+EZoQGAy+BCr76+8e5PHYJdD4yobJR8zi0m3cYuey8Zb5aP6W/9f80/YD7yrnp+Dc27nZyNu04cjpOPMR/acF+QuiDy8RGhB/DPgHyQPCAAEAmgH5BHYJQA74Ek4X/hkWGmgXsxKnDaAJfAfhB6cK2A9xF7wgwykdMXI1ITZoMk0paBzjDaL+Xu9r4gXaC9ZI1lraP+G26Sjy4fhu/T//i/6Z/FD59/UZ9OnzsvVC+bb9EQK1BUcHxgU4AjH9bfYE7/fnueK94HDideff7/36HAbuD6EYoh7oIOIeIBk7EeIIEgHB+v/2h/Yq+UT+CwVxDN4StBaoF68VGBEhCocBZPnM8gfuuOuv66ntNfEu9cf3X/hP97nzp+6U6fPlteQA5pzpN/CD+isGkBF4Gy4iFSX4I7IeChYcCxf/Z/Or6cvju+Ko5Wjr/vIq/EAGbQ8YFmkZxBl4GHEVBhKLDxQOhQ0+Du4P2hFxE0ETdRBsC3sDbfkF7yDl2tyP1xjWVNjr3bvm+fDs+tYCswceCbYG3QAm+fDwQ+l747jgJ+KD50zvZ/hKAi0LrxGZFXkXnRZDE0EOFglGBYEDiwOSBWUJ4A3bETcVqhcIGH8VyhCtC2oH0gSUBIgG3Aq2EV4aECOAKgUvly+6Kzwj+xadCO34fOla3KvSqM1tza3QGdeT3yXoK+819I32UPbP9CbyUu+87Qrt6+0X8XH16vnl/REAg//l/ML4E/MU7HvljuBq3qvfl+Ty7I33bgLHDEQWjh0IITggdxt8FMsMNgUO/zH7XfrX/NcBighLEF4XYBzJHvgdtBlNE6kLdwNI/Gb3tPT88531CPny/O3//QB3AKP92vjT86fvRO137WvwbfbB//gKDhbJH+AmAConKVIk5RskERwF3/iW7uXnNeX75lDsqPMu/EsF5w1qFMAXAxiBFlwTNQ8LDPsJuwjGCPYJ1gtVDV8Nkwp5BTT+2/SC6ibgc9ey0TrPXdBE1Tbdseac8K74zf3F//b9rvhZ8c3p4OKn3Ynaxtqt32LncfCq+goERgvmD3ASixK+DyQLVwY7AgAABQAbAtIFHgp5DswSRRYNGOgWLRPwDiYLyQiXCIYKmg4CFV4d9iWrLfUyqzRtMicrdR+sEeQC4PNc5mbcANfy1enYK9+M52nw2vcz/S0ASwDy/vP77vcl9ezzOPS49o/61/60Ah8F6wSrAhH/kPmz8tnrO+Zg48TjX+c+7iD43AKdDDQV7htxH6we2hmmErsKvAID/Hf3x/WE9xL8ZgIqCm0RZRaHGPUXZxQ+DjUGi/3o9Q3wUuz06vHryO6O8sT1Lfcv9yr13PA77Irow+ZZ5wzqPO/E96kCnA2oF10fNSMsIyAfrRejDQACZvYg7P7kP+Lm4x/pPPD5+BgDvQxUFNkYQBqCGfYWERPmD94NXgwdDCgNEA/IEHoR1A9MDIgG4v3u82bqBeLn2wbZ5dkH3mjll+4z+JsAKwaTCFoHcAJu+6HzzOth5b3hV+FN5YrsVvVJ/9sITxA3FfoXOxiSFfsQlQugBq4DfgIqAxkGSApBDqwRlRQ6FngVrhEpDToJZwZ1BbAGFQq6D10XiR8MJ7Mshi73K/YkGxq4DMj9b+6o4DXWv8/VzQnQk9XM3ajmYu4h9Hj3M/gU94v0JPH07pbtHO3y7qHy7vYP+/39pf4r/Vv68fWy7ybpAeQN4e/gbuRc6wT1Sf8pCaUSehoHH4MfzBuOFTsOrAYTAJT70/kQ+3H/uwWJDUoVOhvUHnUfthzRFo0PZQdj/yT5RfVG88bzbPb5+XH9UP+s/27+lvoR9kHylO/v7srwevVD/WUH8RG/G7Aj/yc4KIEkTR11E/0H/Psh8XHpneWk5dzp6fA2+WwCYwvDEjoXjRipF/0UvhDZDDUKDggoB6UHLAnlCp0LMApwBtEAEfmM77Ll0dxY1uPSp9IF1pTc/uQo7rX2mPxy/8D+XPqr82jsTOV439vb8Nrz3crki+2d97kB2gmAD9gS9hMIEr4N0Aj3A2kAE//d/7gCjAarCgUP8hKMFfwVnRP5D6gMKwo6CWwKkg32ElYaWiLUKfwv2zLUMT8sFSIiFeQGVviJ6rbfJNmI1jDYYt145YDumfa3/HgAnwGmAB3+Dvpe9lj0gvOe9Lv3uvut/8ACuQOkAiUAFPwR9pbv9+lL5onl2udT7dn1wP8xCbcR9RhrHekdKhrVE24MmASM/XP4E/aR9iP6FwCpB5cPkRXGGG0ZOhfzEYsK7wF2+avyxO0M6+TqyuwP8HLzj/Vf9pf1ivJm7vTq7ejT6K3qx+7O9YD/5AmqE/QbOSE5IkIfBxkIEPQEbfn37vHm7uIb4wnnjO3t9c7/vwkLEpgXAhoQGgkYThR0EL0NtQtsCtIKUww2Dk8PxA53DE4IiAFV+Bfv8OZB4F/c5du+3rLkzux/9R/+kATfB6MHuQNi/QT2W+5u5wHjv+EL5CvqefI3/FgGqg54FB8YZBmYF3oTNA6MCGIEEgJ/AVsD+QbKCksOfRHiE10ULRJhDukKFwiPBgoHfgktDsMUQxyDI5Ap1iy7KycmvxxxED8CN/Mz5fPZgNIuz/vPUdTg2xblPu258+/3lvn9+NH2cPNd8GXuCe1O7TbwFvQo+Jv7Pv0L/Tn7APjs8rbshOcC5Ozi7uRy6tzyYPy8Bb4O8xakHEgeuxtgFq8PTwhnAXH8DfpN+oD9OAPMChcTwhlBHlwg6h4EGiQTVwv6Aov7ZfZH86jyWPRs9+f6eP2n/kv++Pse+J/07PGQ8ITx//Rd+2wEUQ6yF9ofgSXzJmAkbh6cFd8KGP/o83HrmuZ65RPoNu5b9mj/fgiDEAkWhRhyGCgWUxIMDqYK4Qf1BcIFsgZiCLkJVQnjBqUCY/wP9M/qKeIQ26bWO9Up12fcs+Pw60z03/qi/hL/nPu+9dfuzueS4WTdwttT3eDiAuuZ9BL/EgiODuYS4BTnEzoQVQspBrgB/f5Z/kkArwNbB08LYg/HElQUWxOcENENhAsvCo0KDQ2AEcEXEB83JnQsdTC1MMssWiR9GPYKxPzi7lrjttsH2EPYItw/43bsDPXI+3AAhgJqAhsAZ/w9+D/1YvMF8zv13fim/BgAHAIcAqkA3f32+Obygu1M6YPnrei77NXzxvwABk4OeRXdGsscQBrEFBwOgQYc/5z5k/Y79rj41P33BE8NQxSLGG4afRlJFXMOOgaJ/c/1xO+T60fqW+v17WPxCvSW9bf14vOM8F7tQet56rHrxe5p9OT8iAbjDxUYTx67IOse2RnmEaoHafy28RvpAuTW4p3lZ+sy85j8mAZ5D/sViBlfGtQYdxWIERcOcAtKCcoI3gmRCy4Ndg0zDFkJSgR1/LXz2evx5O/fP97X337kUOst8077KQJMBkUHkATr/jn4z/Cd6XnkVuJ44zDo0+8O+W8DrAw7E8YXFho6GZ8VnRDLCsUFawJiAO0A6gOKBwILSg5CEdsSDBJVDzUMtgnGB4IHRQnjDJsSWBk4IEMmbSrAKtom+x7fE40GCvjZ6ffdsNUo0aHQ5tNd2j3j+uvo8vX3iPrC+uP4mPUj8nnviO1l7A7uqPFn9Rv5k/tq/ML7p/m19UPwJetb51DlB+b86Rvx1fmPAhgLHRNjGXAcOBvKFs4Q3Qm8Akv9U/rs+RP8EwEgCIgQFRhIHXAgmiDUHGEW1Q6RBnj+Ivi/89HxjvID9VX4TvtU/cr9pPzP+br2VfSN8pfyE/X4+cYB8wroEwAcQiL1JIwjGB9pF0kNHQLZ9qbt9+fA5RfnJ+zP86H8vwURDosUKhj+GDUXmxM2Dz4L9QctBe8DsAQEBoUHEAjoBgMEOf8Y+K3veucN4K/aNdjc2MDc0OIM6uLx4Pg2/af+nfyB90DxYOq+4xjf5dxv3Xbh1+j18VH8HgZeDW8SbhVfFWgSqw1MCEEDcf9O/eH9wwBKBPUHAwz2D34S5xI+EfcO4Aw3C/UKnwwaEHEVBBzCItoobi3CLjIs6yV1G3YO3AAu8xnnjd7e2eTYoNvD4VjqXvOm+vv/FgOlA/YBcP4l+l32xPMl8vbyHPbC+Vv9JgA2AdcAIv9w+zb2J/Gz7MHp2em+7IbyXProAv4KHhKsF7Aa2Rl+FWAPRwi0AMH6Ovcn9rX3FvyyAr8KjhLvF9YaMRsDGPkRVQqfAS75Y/Lz7CnqVeoh7FDvhPLZ9N/1SfU887rwz+6J7f7tQfBh9Ej72gNzDGIUyhoQHkYdgBkQE98JRP/Q9OjrEObK40flQOqB8Uj6vgPWDAkUoxgvGg4ZyhWnEXgN7wkcB2cFsgUBB7QI/AkgCrYIeAW9/1D4R/Gx6hLlNOJb4jXlGup98GL30/0TAoYDRQIT/jv4rvHj6sXlR+OR4//mz+2F9lUADAqcEa8WqRmUGaoW9REpDAAGPgEw/h/9zf7YATYFzwh+DFYPohAyEK4O9gxVC4YKkgsFDucR8hZ0HJIhayV/JtYjIB4jFZMJ/fxj8EDlAN0w2PfWl9kh387mY+/C9jv8mP9EAJ/+X/tA9xnz1O+f7Vbtf+/C8mX2J/rI/Lj9M/1m+yz4avTP8Anud+1r76LzvvmgAFoHfg1TEjEVahWlEqsNxgejAbz8yfkn+cT6Iv+DBf4MuxTaGnEePh+IHPoW2w9xB1T+iPa68CztMew07dHvb/OD9nn4gPmL+Xv4H/fX9Yn1IvcI+pL+xwSdCwwSIxfQGSAZ6xU9EFEIUP9N9nTu/uix5s7neOyA81X7lAPZC7sSThezGGoXORTDD5wKJAbpArwAIAD6AMMCNwXbBtkGJgXTAbX8Gfdj8S7sFOkY6BbpGuyf8NL16vpq/ov//v4T/CD3kvFK7Bzo8uUm5jTpk+/49+EALQowEmYX9RnsGVkX0hLlDO4F4P8f/Ez6q/rK/AQA4QPmB30LSQ7nD/IPGA/ADeoMew3+Dh8RNBQWGOEblR5VHzIdUBiWEI0G5ftD8Y7nGOCU22ra4Nz+4YvoN/BP98H8BQCNAND+jPss90jyIe586zHqNOux7Vzx4vXM+QD8rvyg/Hn7CPkE9qfzsvJb87316/kj/0kE5giuDE4PYRD6DgMLPwatAe39nftK+xv9aAGaB3UO6RWcHJAgrCGkH6ga6hPvC5kC7vm888fv8+0x7pnwSfQL+OH6KP38/kT/iP5w/bf8eP1P/xsCZgbBC+UQ2hQoF+UWERQaDw4ISgCe+JvxS+z06bbqnu4s9Wf8zQMxC7kR6xUfF7cVjhLYDSgI5QI6/5v8TPug+279agAHA8kDzwKnACP9dfgM8+DtcOqd6Cro2+kT7QnxKPUe+ED5L/km98XywO1m6QPmW+ST5Drn9Oz99Hf9jAbWDmMUDhdQFy8VFhExC1EE7f2F+X33l/dm+WP8fwASBX8JaQ1REHoRMhFqEM8PNhBmEfcSWBXHGCUcnx6pH2YewRo1FOoKFAGP94LuFOd84jjhPOPt5yfuYPW7/FUCwQWgBjwFEQKA/fb3/PLT7zjuje7T8FL00fgZ/a//qQDiAPz/v/28+uH3VfZ69tH32fpu/2YElwi4CwsOIw/nDfcJGQVrAGz8jPnL+DX66/2LAxcKZhFRGFEcNB1JG9EWVxArCJv+ufX97kLq8efK56zpRu0M8Qn0sPbP+JX5//go+O738fi1+vH8yQDgBf4KCw+sERIS+g+YC0UF/f289gjw1Ool6MrotuwH8wb6aQFkCZYQqRWrFw0XbRQuEOEKtwX+AU7/1f0E/tv/6wLtBXAHaQdFBioDx/4D+iz1ZPFR76bu2e/M8pf2cvqa/R7/Pv/G/cD54PRY8FLs2emJ6d3rFfGL+LwAigkPEr0XhBr1GgMZ/xQ3DxUIJgFi/JD5ifjW+cX8ggCEBHsIOQwiD0UQ9A/6DhAO/Q29DtYPthGWFK0XChojGxkaTxYLEFIHw/029ObqOuMp3kPcs93r4Znnd+7H9Yv7Tf+FAGb/iPxF+DfzsO6a64rpYOli6+Pui/My+EP7zfx//S79gPvS+EX2s/R89Jv1vfhF/fEBMAbPCdMMsg6BDmkLJAf8AjH/ify1+wj9hgDxBVAMiRPBGnAfLSFKIFIcMxaADpQFnvyX9bjw3u1m7R3vivJw9sr5pPwS/yoAvv/e/gH+Gf5V/zABfgQ1CQ4O+RGoFFsVbROQD3kJPgIw+0D0eO5T60TrL+6l83b6ewHNCJYPUhRvFtUVMxPaDlsJ0QOy/7j8qPpg+vH74f76AaQDfANFArD/o/vA9prxbe3l6nnpEOqG7M7vZfN49kn4xvjE9z70q+996/HnxeWA5XTnAOwW8x373AO6DA0TMRYsF9gVahIRDUkGn/+K+oL3cva893r6Lv6lAkcHygtuD2YRrhEUEXIQaBAhER8S0BOaFqMZAxxuHfscPxopFSkNwwPT+hjyUOr75OXi9+Ot5xztoPP4+jEBAQWaBuEFMgPr/oX5RPSO8FXuku0Y73Dy4faZ++D+jgB2AWMBrP8//XL6Lvhx9//3//nP/UACMwZUCcgLdA1IDXsKGgbdAdn9uvp9+XD6bP0yAjEIHg9eFlob6hzwG2AYpRLvCt0Btvhe8enrTuhe57PozOut7yvzSfYC+XP6V/q0+U/5ufn/+oX8Ov+qA18ITQw2D4AQMA+3C3sG4v87+bryGO226XfpX+zD8Ub4JP+2Bj0OpBN/FrIWsRTcEK8LdQZaAnf/UP3H/FH+PAGvBNEGlwdIB1QFfgEO/ZT4SfRU8czvCvAU8jP1jvid+8T9f/67/eD6cfY/8mLuauuO6ivsXPDL9pn+5gatD1AWlBmsGpEZJBbgEAoK9gJt/e354/cu+Nn6bv5MAmkGmwo4Dj0QaxC+D+4OaQ6TDjAPXxCxElQVixfzGMgYAxbeEHUJkwCh97HumObt4EXevN7c4djm7ewW9J/6rP6DAC4A0/3R+ef0IPCU7Cnq+egD6kbtwvHD9ob6y/wN/nL+aP1B+8z4ufag9eH14ve++wQA0gNPB24KzAxgDYYL4AcSBG4Abv00/Bv9+f96BGgKMRF7GB4ejCCEIKgdFRjnEFEIY/+49yXyWe7j7P7t7fDU9I34Afzs/roA1wAsAIL/Cf+G/8AAEwMNB2oLLg8UEpgTfhJTD1UK+gNa/cb2qPDY7AXsB+5x8pn4Yv9dBk4NiRJWFZ4VjBOdD1UKxAQ7AOr8b/pi+X36PP2xABQDzgNfA6kBVf7w+Tb1q/BS7TnrtupY7ADv/PHy9DL3NPi191n1UvFe7eDpUOen5g7oyOvN8VT5mwFJCoYRaxXhFkkWexOdDi8IZgG+++f3/PVR9rP4HPw7AAYF5Ak9DvMQ2BGhES0RxBDjEIwRlRKyFE4XcRkMG1UbZBlDFbwOOga3/Xf1fe2H567k3OSX5zTsK/IS+cH/LQQ+BlIGLQRQABz7qfVq8bnuOe3Z7anw8/T7+ez9TwDNAVsCZgFT/+f8Vfq9+HL4ivlq/FQACgQGB6gJmAstDG8K5QYJAzz/5fsy+q36F/0kAZgGFA0WFPoZhxxsHLwZpBSMDdQEqfvJ87DtU+lU5/fnbupH7ifyvPX5+Bb7lPsc+7T6sPpR+1z8K/68AREGsQnKDLEOQg6VCyYHhwFS+x31Ue9M6zfqNuyq8Jv2Hv07BKMLtBE0FUYW5hR5EZgMQAfSAn7///za+778f/8bAwEGbQcFCA4HEAQCANT7j/fD81PxnPDO8Sn0D/fl+Uv8n/1d/Wv70ffm80zwA+2J64js5O9r9ZH8mwQLDVEUphhHGucZNxdpEvUL0QS+/nP6zvcm9wX5WvwgAE0E5QgMDesPxBB1ENcPJw/DDu4Oew8ZEUwTMxW3FjQXbhUqEd8KAAO6+ljyHOrJ44vgEuBA4mLm1+t/8iH51/0zAKkA7/5J+232f/GR7b/q/ugj6cHr7e8B9XT5WfxY/lL/5f5H/SD77fg393n2Zfdh+jH+qAHMBPUHsArhCwkLSAgABbQBi/7G/E39jP98A7MIGA8cFiUciB9YIJwe3xkVE+YKFwL7+bvzPO/o7Dvtje8m80b3IPuJ/gwB0AGCAeoAXAASALUAHgI3BSkJmwyMD5QRcBH5DtYKYAVj/035KPON7hjtPe7M8SD3ff1ABPIKhxAJFDsV4hNeEFEL0wX1AEP9ZvrZ+F75vftC/0UC2QNABGgDwQD+/K/4FfQB8CftrutJ7E7upPBg8+T1Zvds98D1s/IN777r3uiw56Loj+uo8H33Yv/YB0kPGhRGFlwWUBT7D98JHAMg/Zz48vVG9Tn3PPoJ/qwC+AfPDE4Q3xEMEs0RZxH1EBYRoBEdEz0VCxeoGHkZcBgcFcEPeAhqAK341fBA6rLmI+bn56vr9vBP9wv++wKWBYAGHgWBAZ78MPeQ8kPvXe0T7VDvNfMf+Kz82P/nAQ8DtwI2ARb/tvxW+jz5aPlZ+7b+CwLtBIIHzgneCgoKagcIBJsAJf37+gT78PxJABEFKQv2EfwXfRsmHJIaURbUD6IHhf469qHvn+rI56rnkOnN7PvwAPW0+Hb7f/xr/BT88fv/+4/8mP0ZAOwDSQdOCrwMEw0yC3EHuQJG/ZX3x/E17WTrbewA8EP1VfsLAiEJWg9nE4oVCBX6EW0NHghsA8P/8PxG+5z75f1fAbkE+AY6CDsIEQaLArH+nvpr9ivzh/Gr8WjznPUh+Lr6jvzQ/IL7zPh79SXy1+637Cvt3O969NL6WwKUCvcR/RZPGagZzheOE5INogYpAET7/feb9sb3xfpH/lgCDQfBC1APzxDtEHkQzg8VD7EO0w7LD4sRIBOGFIAVoBRHEd4LBwV4/c71uu3F5ujipuHx4j/mAOsa8Z33xvyH/8wA8P+j/Pn3+/LG7pnrVOmx6JPqbe488yP4w/tS/vb/JAAR/0z9KvsA+ZP3jvdY+a384/+kAsYFrQhxClMKhwi9BccCv/93/X79Yv+RAhAHCw3GE+8Z+x1yH9ceWRsjFWUNyQR8/JT1d/Bl7fDsm+6m8b71DPrz/QcBYQJ7AhYCggHXAMYAlgGkA/cGGQrqDF4PEhBcDu4KVAYMAZT7sPV78EDuuO5S8dn1uPs2AqoIVA4rEj0U/hPzEDkMzwbKAcv9mPqg+Jb4j/rZ/TYBtQPdBOIEIgPy/zX86vdy8+nvrO0n7UzuDvAk8rz0tfZE9132OvRg8Wnut+sH6pLq6uz58PX2MP4PBk0NmBJHFekVoRT3EGIL3gSj/o/5IPbf9Or1tPgz/KEA9AUqC1MPmRFHEjYSqRHKECkQIxCPEK8R8BIyFDkV1RRvEkoOtAjtARz7NvTO7cfpaOg06fTrPvCv9Xn7ZwAxA1wEtgOdAC78OveB8vLuqewO7KbtOvG59Z36zv6nAYIDDQRkA90Bf/+//KH65flU+i38vf4UAYUD4AV5B9EH3wbFBDgCz//5/dL9aP8OAhEGLgvyECsW5Rk3G0Ma+hZVEUEKKgIg+jLzCO776k3qout97tfym/ek+93+zABeASkBeQCK/xL/Mf/6/9kBRQSYBsQIrQnFCI4GdgOl/0378Pb68uPw7vAA89327/tdAbYGpAtkD0oRERGFDoIK5gUtAT39kPo/+Xr5XPtr/i0CkAWlB3cItgd2BVcCPv6L+XP1oPIE8a/wafHk8gP12/a696j34vYS9c3y/fDj71HwQfKd9Zr6zgAtByINyBFQFJEU2xJyD6IKyAS2/o35Ffa09HD13vdt+wAARwVcCnMORRF/EnMSjRELENIOJw6SDVUN1A2bDkwPFg+KDcQKEwf4AXP8G/cx8pHu2uwC7Qjvg/Kw9hf7Lv/eAcICxgEP/zD75fZ98uDuyeyJ7DDua/Gi9az6e//3AhMFOQZCBhMFvgKO/9P8O/uO+uD6OPw0/j8ALALRA/QEOwVTBIkCJgE+AEMAbwHIAyMHUwu3D88T+RZlGE0X8RPIDo4IfwHS+RHzIe4961/qRusR7pLyofe2+/D+SAF3AkYCOAHD/8P+Sv68/R/+kv9jAQkD5QO9A74CEQGs/pr7hvj59Vb0/PNO9XT41/wnARAFFQmsDGYOEQ7TC4wIrwRrAKj8Xfq4+Uf6+vvQ/tUC3gadCf4KQAv2CUUHZQPX/mb6BPeR9C7zBfMd9Mr1bfeR+DX5bvmS+AX32/VI9ZX1+Pba+Tn+cQO1CKMNuhE/FGgUjxImD9sKogXA/3b67faF9bn1qfc5++f/BAXFCagNpBAxEiQS8RAJD1ENFAzBCrwJnAkYCn0KWAo7CTEHRQQ4AJ37Avec8jHvKu2w7CDu8/BW9NP3cfsV/vf+E/5v+wP4HPQL8LXs0+qQ6grs9e788gn4QP0IAXkD9QSNBckEeQJ3/6f8ofpl+U/5Xvom/Af+FgAgAvAD/wTXBLwDzAJXAoYCmwPaBRAJ2QzcEKQU2xeFGR4ZdRarEdILTQU4/nX3a/KI73/uPe/Z8ST2QvuZ/9QCawXKBsQGlAXKAzYCNAGCAH8ArAFSA9YEwwXtBSMFuQNrAYr+o/va+M32GPb09mP5Kv1PARsFrAjQC4oNQw0VC7sH3QNm/3f72/jp9zf4kPkt/AEAHgT3BjcIZwhgB+4EGgFV/Lz3CvQi8TPv2u6b7xXxwPL28+L0PPW89F/zVvII8pHyAfSe9qT6qP/CBKgJ6A2zEF8R5A/wDAoJJQSR/m75zvUy9Gj0Zfa9+Tb+nAPMCC8NpRC7EiATPhKaEPwOug2BDGALIguFC/gLCgxpC/0JyQc8BMr/lvui9xf0zPEu8Vvy7fQd+HP79/7IAdECOgIBAKr81fiC9JrwVu7m7SXvtvGF9U36h/+LA/8Fmwc+CHwHUgUzAgr/qPwg+zL6yvp3/Dv+4f+VATUDSgQmBAoDAQJeATwB5QHRA7UGMQraDYQRrRR5Fv8VVhPhDmEJ/gK7+8n0kO9K7M/qRuuP7X7xhfb3+lv+GAHEAuYC3AFiABH/MP5W/e785f1+/wkBNgKxAlgCQgFm/+v8S/rZ99b18fSL9fj3u/u+/10DIwe0CuAMOA3FC+oIZQU+AWH92Prr+Sv6aPvh/ZYB1wUcCesKxwsmC9MITgUCAXj8iPiL9WXzuvJy88X0a/bi9+/4e/lB+Sf4K/eS9mr2Tvew+XP9EALqBpkLzg/CEoITQhKND7sLAgddAd37+PcE9qL1+vYl+m/+ZAMvCFkMvQ/TESYSKBFfD5MN8AtzChUJhwi7CBoJOAmzCFEH/gSmAZn9eflm9Yjx+u7p7a3u4PDC8832L/oW/UX+5/0U/AH5T/VD8c/tq+sb6wXsVO4C8rj2//tgADMDGwUNBrAF1APzAAv+fvvC+cz4Z/n5+qb8e/6oALsCTQS0BDIEfwMlAwADogOMBWUIqAs6D80SBxYlGD0YWBZ2Ej0NJwdWAIf5JPS38CjvSO9s8R71Bfqi/isCFwXxBj0HPAaPBNACYQFbAMb/WQC7AS8DWgQEBdAE4QMhAr7/Qf3U+m/4EPdc9yL5SfwIAIoD7AYTCvgLUgz+CjwIjgRPAFj8hPlK+FX4Pflo++z+EgN8BkYI+ghrCHoGCQOa/uf5wPVw8t7v0+5J70/w2/Fj85r0X/VU9Yb0p/NR84DzevS29iT6ff4pA9kHFQw7D3AQhA8hDcwJcAUMAMz61vau9Ez0t/XJ+PH85AEiB7MLlQ8qEvQSSRLTEBEPiw0RDM0KCQosCoQKwwqaCs4JJwhXBYUBov3++Vv2ffNC8t7y0fR991z6p/2eAA0CzAFJAIj95vm79cTxK+9c7hfvIPF79AT5J/6yArMFoge4CFAIhwapA20AmP18+x/6+Plh+/H8fv5SAC4CnAMZBIkDuAI6AugBIgKuA1AGRQmEDOQPDhM7FWMVYROcD8MK6AQD/v/2cPG77bnrkuta7cbwXfUX+rv92gDoAmgDhgIPAaf/cv5Q/Yj80fwX/on/3gDLAQECcQH3/wP+0Pui+Xb38PXu9a336/qX/vQBawXcCEkLFwxKCzEJ5gX/ARX+Wfsq+jz6BvsY/XQAmwRGCKIK9gv6CzAKBAftAoX+Pvq19if0x/IN8/TzafUi94X4c/mc+QX5QvjG94v33vey+ff8+ABhBdYJ/A09EYsSyRGQD1wMIwjfAj/9BfmW9qz1efYs+Tn91wGZBvcKqw5OEf8RQxGkD9IN8QsuCr8IvQeNB7UH9gf0BzAHhwW7Ajz/j/vf9xL06fBN727vE/F18/z1G/kM/KL9nf1B/OL5fPaL8uXuheyy6zbs8e0q8Zj1tfpk/9UCHQVjBmsG7QRVAlb/i/w2+s74ufgD+oP7Av0r/4QBbwNnBGEE/gO7A6AD3QNJBesHwQrPDSkRVBS8FlQXBxbnEmUO1whrArH7B/Yr8v/vqO8v8W70z/iO/WQBlgToBowHzgY8BYIDuwFTAF7/b/9jAKkB6gIBBFYE2wOIAqsAmf52/CL6Rvji9xf5r/vq/iYCXgViCH8KMQtjClYIHgUWARv9DPqm+G74BPnI+uT95AGIBQsITQlTCb4HyAS7ACD8rffs8+vwRe8t783v9vC78kb0X/W79W711fSH9KD0MfXk9uD5k/3GASIGVQq7DWwPAQ8ZDUMKiQaSAUb8Dfhk9XH0T/UA+N37bABgBRoKXQ53EbASTxL4EEwPgQ28C1YKUgn3CBkJZgmqCWAJSAgpBu0CeP8r/Lr4f/WT83zz5fQD93D5bvxr/yQBSgE2ABH+6vr19gbzH/Dv7k/vyPCv8+f31PyKAR8FiAfjCP0IhgcABdIBrP4Y/E76hPlm+s37KP3p/gwB0QLIA8wDYQP5ArQCrALDA/0FnAhTC2YObRHdE4oUKRMTEM8LjgY5AF35d/Nm79HsFuxv7Vfwc/T/+Pn8UwDcAsgDJQPMAUAA3P5p/WH8LPzz/Cn+b/++AHsBXAFTAMv+Ef0v+y75Z/e99q/3SPqP/aUA1QMcB5kJxAqDCgAJTAaeAtf+2vuM+kj63Pp4/IT/bgMsBwYK3AuSDFQLeAjHBH0AA/wL+Pv0JfPU8l/zdPQx9vD3NPm8+af5OfnS+KL4r/j2+aT8HAD+AxQIKwyRD2MRHhFlD7EMAgk+BOD+Qvpn9wX2RPZx+CP8iQADBXEJag2LEOARXRH0DyQOKwwaCnUIQAelBoQGsQYEB+cGxgWRA5wAX/0e+n/2KfMD8WbwTvE781n1GPj7+tD8GP04/F76bve28xHwbO1x7Jfs2+2M8KH0g/k8/hMC3wSaBvUG0wWOA7AArf3v+hb5Y/gy+YT61vu9/UcAhgL1A2wEcwRSBD0EWgRYBZgHGQq1DJwPqhI7FUoWaxUAEyYPFgosBL/91feS8+fwD/Ae8e3z3/dJ/GMA0wOHBsYHOwfPBREEFQJYABr/yv41/0QAWgGzAq8DwQPIAlcBqf/s/dX7yvna+Fb5Lvvv/eEA6APWBgMJ/wm8CUQImQXmAQz+0/o0+cX4Bfly+iL96QCdBIgHTwnsCfAIPgamAkH+mfl49QHy0e8+74rvTfAC8uLzTvX+9R/27vWm9Zf1AfZQ99v58f");
beeps.error = new Audio("data:audio/wav;base64,UklGRpD2AABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YWz2AAABpv+lAKYBpv6lA6b9pQKm/6UApgCmAKYBpv6lA6b8pQOm/6X/pQOm/KUEpvylBKb9pQKm/6X/pQOm/aUCpv+lAKYApgGm/6UBpv+lAab/pQGmAKb/pQKm/qUBpgCm/6UBpgCm/6UCpv2lAqYApv+lAqb9pQOm/aUDpv6lAKYCpvylBKb+pQCmAab/pf+lA6b9pQKm/6UApgCmAKYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUDpv+l/6UCpv+l/6UCpv6lAaYApv+lAqb+pQKm/lkAWgNa/FkEWvxZBFr8WQRa/VkBWgFa/VkEWv1ZAVoBWv1ZBFr8WQNa/lkBWv9ZAVr/WQJa/lkBWgBa/1kDWvxZBFr9WQJa/1kAWgBaAVr/WQBaAFoBWv5ZA1r9WQFaAVr+WQFaAFoAWgBaAFoAWgBaAKYApv+lA6b8pQSm/aUBpgGm/aUFpvqlBqb6pQam/KUBpgGm/aUEpv2lAaYBpv6lAqb+pQKm/6UBpv+lAKYBpv+lAab+pQKm/6UBpv+lAKYApgCmAab/pQCmAab+pQOm/aUCpv+lAab/pQGm/6UApgGm/6UBpgCm/qUCpv6lAqb/pQCmAKYApv+lAqb9pQOm/qUBpgCm/6UBpgCm/6UCpv2lA6b+pQGmAKb/pQGmAKb/pQGm/6UBpv+lAqb9pQOm/qUBpv+lAqb9pQSm/KUDpv6lAqb+pQKm/qUBpgCmAKYApgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQGm/6UBpv+lAaYApv+lAab/pQGmAKb/pQKm/aUDpv6lAqb+pQKm/qUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQGm/6UBpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b+pQCmAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lA6b/pf+lAqb9pQOm/qUBpgCm/6UCpv6lAaYApgCmAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgFa/lkBWgBaAFoAWgFa/VkEWv1ZAVoBWv5ZAVoBWv5ZAVoAWv9ZAlr+WQFa/1kBWgBaAFr/WQFa/1kCWv5ZAVoAWv9ZAlr+WQFaAFr/WQJa/lkBWv9ZAVoAWv9ZAVr/WQFaAFr/WQFa/1kBWgBa/1kBWv+lAab/pQGm/6UBpv+lAab+pQOm/qUBpgCm/6UBpgCmAKYApgCm/6UCpv6lAqb+pQKm/qUBpgCmAKYApgGm/qUBpgGm/6UBpv+lAKYBpv+lAKYBpv+lAKYBpv6lAqb/pf+lA6b9pQGmAab9pQSm/aUCpv6lAqb+pQKm/6UApgCmAab+pQKm/qUCpv+lAKYApv+lAqb/pf+lAqb+pQKm/qUBpgCmAKYBpv2lBKb8pQOm/6X/pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpv6lAqb/pQCmAKb/pQKm/aUFpvqlBab8pQOm/qUDpvylBKb8pQSm/aUCpv+lAKYApgCmAKYApgCmAKYApgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQGmAKYApgCmAKYApgGm/qUCpv6lAqb/pQGm/qUCpv6lAqb/pQCmAab+pQKm/qUDpv2lAqb/pQCmAKYApgCmAab/pf+lAqb+pQOm/aUCpv6lAqb/pQGm/6X/pQKm/6UBpv+lAKYApgCmAab+pQOm/aUCpv+lAKYBpv+lAKYApgGm/6UBpv6lAaYBpv+lAab+pQKm/6UApgCmAKb/pQKm/aUDWv1ZA1r9WQJa/1kAWgJa/VkDWv1ZAloBWv1ZBFr8WQNa/1kAWgBaAFoAWgBaAFr/WQJa/lkBWv9ZAVoAWgBaAFr/WQJa/VkEWv1ZAVoAWv9ZAVoBWv5ZAVoAWv5ZBFr8WQJaAFr+WQNa/lkAWgFa/1kBpv+lAab/pQKm/aUDpv6lAaYApv+lAqb+pQKm/aUDpv6lAab/pQGm/6UBpv+lAKYBpv+lAqb+pQCmAaYApv+lAqb9pQOm/qUCpv2lA6b+pQGmAKb/pQGmAKb/pQGm/6UBpgCm/6UBpv+lAab/pQKm/aUCpgCm/qUDpv2lA6b+pQGm/qUDpv2lA6b+pQCmAqb9pQOm/aUEpvylA6b+pQCmAqb+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/6UBpgCm/6UBpv+lAKYCpv2lA6b9pQKm/6UBpgCm/6UBpv+lAaYApv+lAKYBpgCm/6UApgCmAKYBpv6lAaYApv+lAqb+pQKm/aUEpvylBKb8pQOm/qUCpv+lAKb/pQGmAKYBpv6lAqb+pQKm/6UApgGm/qUDpv2lAaYBpv+lAKYBpv2lBKb+pQCmAqb8pQSm/qUBpv+lAab+pQOm/aUCpv+lAKYBpv6lAqb+pQKm/6UApgCm/6UCpv6lAaYApv+lAqb+pQGmAKYApgCm/6UCpv6lA6b8pQSm/KUEpv2lAaYBpv6lA6b8pQSm/KUFpvulBKb9pQKmAKb/pQCmAab/pQGm/6UApgGm/6UBWv5ZAloAWv5ZAlr/WQBaAVr+WQFaAVr+WQNa/FkEWvxZA1r+WQJa/lkCWv1ZA1r+WQJa/VkDWv5ZAVoAWgBaAFoAWv9ZAlr+WQJa/1n/WQNa/FkDWv9ZAFoAWgFa/VkEWvxZA1r/WQBa/1kCWv5ZAlr/pQCmAKYApgCmAKYApgCmAab9pQOm/aUDpv+l/6UBpv+lAqb9pQOm/aUDpv+lAKb/pQGm/6UCpv+lAKb/pQGmAKYApgGm/aUDpv6lAqb+pQKm/qUBpgCm/6UBpgCm/6UCpv2lAqYApv+lAqb+pQGmAKYApgCmAKYApv+lAqb+pQKm/qUBpgCmAKYApgCm/6UCpv+lAKYBpv2lBKb+pQCmAab/pQCmAaYApv6lA6b9pQGmAqb9pQOm/aUCpv+lAaYApv+lAab/pQGm/6UCpvylBab8pQKm/6UBpv+lAab/pQCmAab/pQGm/6UBpv6lA6b8pQSm/aUCpv+l/6UBpgCmAKYApgCm/6UCpv6lAaYBpv6lAaYBpv6lAqb/pQCmAKYBpv6lA6b9pQKm/qUDpv2lAqb+pQKm/6UBpv6lAqb+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAab+pQOm/aUBpgGm/qUCpv+lAKYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/aUEpvylBKb+pf+lA6b8pQSm/aUCpgCm/6UBpv6lAqb/pQGm/6UApgCmAFoAWgFa/lkCWv9Z/1kDWvxZBFr9WQJa/1kBWv9ZAFoCWv1ZA1r+WQFaAFoAWv9ZAlr+WQFaAFoAWgBaAFr/WQFaAFoAWv9ZAVr/WQFaAFr/WQFa/1kAWgJa/VkDWv1ZA1r9WQNa/VkDWv5ZAFoCWv1ZA6b+pQCmAqb+pQGmAKb/pQGmAKYApgCmAKYApv+lAqb+pQKm/6X/pQGmAKb/pQGmAKb/pQKm/qUApgKm/qUDpv2lAqb+pQKm/qUDpvylBKb8pQSm/KUDpv6lAqb/pQCm/6UCpv6lA6b8pQSm/KUEpvylBKb9pQGmAKYApgCmAKb/pQKm/aUEpvulBab8pQKm/6UApgKm/aUCpv+lAKYBpv+lAKYApgGm/qUCpv6lAqb+pQKm/aUDpv+lAKYApv+lAaYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYBpv+lAKYApgCmAab/pQCmAKYBpv6lA6b8pQSm/KUDpv+lAKYBpv6lAqb/pf+lA6b9pQKm/6X/pQOm/KUFpvqlBab9pQKm/6UBpv6lAqb/pQCmAab+pQKm/qUBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv2lA6b9pQOm/qUApgGm/6UApgGm/qUDpv6lAKYBpv6lA6b9pQOm/qUApgKm/aUEpv2lAaYApv+lA6b8pQSm/KUDpv+l/6UCpv6lAaYApgCm/6UCpv6lAqb/pQCm/6UCpv6lA6b9pQGmAKYApv9ZAlr9WQRa/FkDWv1ZA1r9WQRa/FkDWv5ZAFoDWvxZA1r+WQBaAlr+WQFaAFr+WQNa/VkDWv5ZAVr/WQFa/1kBWgBa/1kBWv9ZAVr/WQJa/FkEWv1ZAlr/WQFa/lkCWv9ZAFoBWv5ZA1r9WQNa/VkBWgGm/6UBpv+lAKYBpv+lAKYBpv+lAab/pQCmAaYApv+lAKYBpv6lBKb8pQKm/6UBpv6lA6b+pQCmAab/pQGm/6UCpv2lA6b+pQCmAqb9pQOm/aUCpv+lAKYApgCmAKYBpv+lAKYApgGm/6UApgGm/qUDpv6l/6UCpv+lAKYApgCmAKYApgCm/6UBpgCmAKb/pQGm/6UCpv6lAab/pQKm/qUDpvylA6b/pQCmAKYApgCmAab+pQKm/qUBpgCmAKYApgCmAKb/pQKm/qUBpgGm/qUCpv+l/6UDpvylBab7pQSm/aUCpv6lAqb+pQOm/KUDpv6lAaYApgCm/6UCpv2lA6b9pQOm/qUBpv+lAKYBpgCm/6UBpv+lAKYBpgCm/6UCpvylBab7pQWm/KUCpgCm/qUDpv2lAqb/pQCmAab+pQKm/qUDpvylBKb8pQSm/aUBpgCmAKYApgCmAKYApgCmAKb/pQOm/KUDpv6lAaYApgCm/6UBpv+lAaYApv+lAqb+pQGmAKb/pQKm/6UApgCm/6UCpv6lAaYApgCm/6UCpv2lBKb9pQGmAKYBpv6lA6b8pQSm/aUBpgGm/qUCpv6lAaYAWgFa/lkDWvtZBVr+WQBaAVr+WQJa/1kBWv5ZAlr/WQBaAVr/Wf9ZA1r8WQRa/ln/WQNa/VkCWv9ZAFoBWgBa/lkCWv9ZAFoCWv1ZAlr/WQFa/1kBWv9ZAVoAWgBa/1kBWv9ZAlr+WQJa/VkDWv5ZAlr+pQGm/6UCpv2lBKb8pQOm/qUBpgCmAKYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKb/pQOm+6UGpvqlBKb/pQCmAKYApv+lAqb/pf+lA6b8pQSm/aUCpv6lA6b9pQKmAKb9pQSm/aUCpv6lAab/pQGmAKb/pQGmAKb/pQGmAKb/pQOm+6UFpvylA6b+pQCmAaYApgCm/6UBpv+lA6b8pQOm/qUBpgGm/aUDpv6lAab/pQCmAKYBpv+lAKYBpv6lAqb/pQCmAqb9pQKm/6UApgGm/6UApgGm/qUDpv2lAqb/pQCmAKYBpv6lA6b8pQOm/qUBpgCm/6UBpgCm/6UBpv+lAab/pQKm/aUEpvylA6b+pQGmAKYBpv6lAqb9pQOm/qUCpv6lAaYApv+lAaYApv+lAqb+pQGmAKYApgCm/6UCpv2lBab6pQWm/KUDpv6lAqb+pQGm/6UBpgCmAKYApv+lAqb+pQGmAKYApgGm/qUCpv6lA6b9pQKm/6UApgGm/6UApgGm/qUCpgCm/qUDpvylBKb9pQOm/aUCpv+lAKYBpv6lA6b9pQKm/qUCpv6lA6b9pQKm/qUCpv6lAqb/pQCmAFr/WQJa/1kAWv9ZAVoAWgBaAFr/WQFaAFr/WQJa/VkEWvtZBlr5WQda+lkFWv1ZAVr/WQFaAFoAWgBaAFr/WQFaAFr/WQJa/lkAWgJa/VkDWv5ZAFoBWgBa/1kBWv9ZAFoBWv9ZAFoBWv9ZAFoBWv5ZA6b9pQOm/aUCpgCm/qUDpv2lAqYApv6lA6b+pQCmAab+pQOm/qUApgGm/6UApgGm/6UBpgCm/qUDpv6lAqb+pQGm/6UBpgCmAKb/pQKm/KUFpvylA6b+pQCmAab/pQKm/aUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAKYCpvylBab6pQam/KUCpv+lAKYApgCmAab+pQOm/KUEpvylBKb9pQKm/6UApgCmAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lAqYApv+lAab/pQCmAab/pQCmAKYBpv+lAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAab+pQOm/aUCpv+lAKYBpv+lAab+pQOm/qUBpv+lAab/pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApv+lAaYApgCmAab9pQSm/KUEpv2lAaYApv+lAqb9pQSm+6UFpvulBKb+pQGm/6UBpv+lAaYApv6lA6b9pQOm/qUApgGm/6UBpv+lAKYBpv+lAab/pQCmAKYBpv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVoAWgBa/1kBWv9ZAlr/Wf9ZAlr+WQFaAVr9WQVa+lkGWvtZA1r/WQBaAFoBWv5ZAlr+WQFaAFoAWv9ZAVr/WQJa/VkDWv1ZA1r+WQFa/1kBWv9ZAVr/WQBaAVr/WQCmAab+pQKm/6UApgGm/6UApgGm/qUDpv6lAKYBpv+lAaYApv+lAqb9pQOm/qUApgGm/6UBpgCm/qUBpgCmAab/pQGm/qUCpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGmAKb/pQGmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYBpv6lAqb+pQKm/6UApgGm/qUBpgGm/qUDpvylA6b/pQCmAKb/pQKm/qUCpv6lAab/pQKm/qUBpgGm/aUEpv2lAaYApgGm/qUCpv6lAaYBpv6lAaYApv+lAqb+pQGmAKYApgCm/6UBpv+lAqb+pQCmAqb9pQOm/aUCpgCmAKYApv+lAab/pQKm/qUBpgCm/6UBpgCm/qUDpv2lA6b+pQGm/6UApgGm/6UBpgCm/6UBpv+lAKYCpv2lAqYApv6lBKb7pQSm/aUCpgCm/qUDpvylBKb9pQKm/6UBpv+lAKYApgGm/6UBpv6lAqb/pQCmAKYApgCmAKYApgCmAab/pQBaAVr+WQNa/VkDWv5ZAFoBWv5ZAlr/WQBaAVr/Wf9ZAlr+WQJa/1kAWgBaAFoAWgBaAVr9WQRa/FkEWv1ZAlr+WQJa/lkCWv9ZAFoBWv5ZAlr/WQBaAVr+WQJa/1kAWgFa/lkCWv9ZAFoBWv9ZAVr/WQGm/6UCpv2lBKb7pQWm/KUDpv6lAqb9pQOm/qUCpv+lAKb/pQKm/qUDpvylBKb9pQGmAab9pQSm/aUCpv6lAqb+pQKm/6X/pQKm/qUCpv6lAaYApgCmAKYApv+lAaYApgCmAKYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpgCm/6UBpv+lAKYCpv6lAab/pQCmAab/pQGm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQKm/6UApgGm/6UApgGm/6UBpv+lAab/pQGm/6UApgGm/6UApgCmAKYApgGm/qUCpv+l/6UDpvylBab7pQSm/aUDpv2lA6b9pQOm/aUDpv6lAab/pQGm/6UCpv6lAKYCpv2lBKb8pQKmAKYApgCm/6UCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgGm/qUCpv6lA6b8pQWm+6UEpv2lAqb/pQCmAqb8pQSm/aUCpgCm/6UBpv+lAab/pQGmAKb/pQGm/6UApgGmAKb+pQSm+6UEpv6lAKYCpv2lA6b9pQOm/qUBpgCm/6UCpv6lAab/pQKm/qUCpv6lAKYCWv5ZAlr/Wf9ZAVoAWv9ZA1r7WQVa+1kEWv5ZAVr/WQFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFaAFr+WQNa/lkBWv9ZAVr/WQFa/1kBWv9ZAVr+WQNa/VkDWvxZBFr9WQJa/1n/WQJa/1kAWgBaAFoApgCmAab9pQWm+qUGpvqlBqb6pQWm/aUBpgGm/qUBpgCmAKYApgGm/qUCpv6lAqb+pQKm/6X/pQKm/qUBpgCmAKb/pQGm/6UBpgCm/6UApgCmAab/pQGm/qUCpv+lAKYBpv+lAKYBpv6lAqb/pQCmAKYApgCmAKYApgCm/6UDpvylBab6pQWm/KUEpv2lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAab/pQGm/6UCpv2lA6b+pQCmAaYApv+lAqb9pQOm/qUBpgCm/6UCpv6lAaYApv+lAqb+pQGm/6UBpv+lAqb9pQOm/aUCpgCm/qUDpvylBKb9pQGmAKb/pQKm/qUBpgCm/6UBpgCmAKYApgCm/6UBpgCmAKYApgCm/6UBpv+lAqb+pQGmAKb/pQKm/aUDpv6lAaYApv6lBKb8pQOm/aUCpgCm/6UBpv6lAqb/pQCmAKYApgCmAKYApv+lAqb+pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQKm/aUDpv+lAKYApgCm/6UDpv2lAaYApgCmAab+pQGmAKYApgCmAKb/pQKm/1kAWgBa/1kCWv5ZAlr/Wf9ZAVoAWv9ZAlr+WQBaAVr/WQFa/1kAWgBaAFoAWgFa/VkEWvxZBFr9WQJa/lkCWv9ZAVr+WQJa/lkDWv1ZAlr/WQBaAVr/WQFa/1kBWv9ZAVr/WQBaAFoBWv5ZA1r8WQNa/6X/pQOm/KUDpv6lAqb+pQKm/qUBpgCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/qUBpgCm/6UDpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UCpvylBqb6pQSm/qUBpv+lAqb9pQKmAKYApv+lAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/qUDpvylA6b/pQCmAab+pQGmAab+pQKm/qUBpgCmAKYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b9pQKm/6UApgGm/6UApgGm/qUDpv2lAqYApv+lAab+pQOm/aUDpv2lAqYApv+lAKYBpv+lAqb+pQCmAaYApgCmAKb/pQCmAqb+pQGmAKb+pQOm/aUDpv2lA6b9pQKm/6UBpgCm/6UBpv6lBKb7pQWm+6UFpvylAqYApv6lBKb8pQKmAKb/pQKm/aUDpv2lA6b+pQGm/6UApgGm/6UBpv+lAab/pQGm/qUDpv6lAab/pQGmAKb/pQGmAKb/pQKm/qUApgGm/6UBpgCmAKb+pQKm/6UBpgCm/6UApgCmAab+pQKm/6UApgGm/qUCpv6lA6b9pQJa/1kAWgFa/lkCWv5ZAlr/WQFa/VkFWvpZB1r5WQZa+1kEWv5ZAFoBWv9ZAFoBWv9ZAVr/WQBaAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQJa/lkBWgBaAFr/WQJa/lkCWv9Z/1kCWv5ZAlr+WQFaAFoAWgCm/qUDpv2lAqYApv6lAqb/pQCmAab/pQCmAKYBpgCm/6UBpv+lAab/pQKm/aUDpv6lAaYApv+lAab/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQGm/6UBpv+lAaYApv6lA6b9pQKm/6UApgGm/6UApgGm/aUFpvqlBqb7pQOm/6X/pQOm/KUEpvylBab7pQSm/aUCpv+lAab+pQKm/6UApgGm/qUBpgGm/qUCpv+l/6UCpv+lAKYBpv6lAqb/pQCmAKYApv+lAqb+pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQKmAKb+pQSm/KUDpv6lAaYApv+lAqb+pQGmAKb/pQGmAKb/pQKm/qUBpv+lAaYApgCmAKb/pQGmAKb/pQKm/qUBpv+lAqb9pQOm/aUCpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UCpv2lA6b9pQOm/qUApgGm/6UBpv+lAKYApgGm/6UBpv6lAaYApgGm/qUCpv2lA6b+pQGmAKb/pQKm/aUEpvylA6b+WQFaAFr/WQJa/VkEWvtZBFr+WQFaAVr+WQBaAVr/WQJa/lkBWv9ZAFoBWv9ZAlr9WQJa/1kBWv9ZAVr+WQNa/VkCWv9Z/1kEWvtZBFr9WQFaAlr9WQJa/1kBWv9ZAVr+WQNa/lkBWv9ZAVr/WQFa/1kBpv+lAab/pQGm/6UBpv+lAab/pQGmAKYApv+lAKYBpgCm/6UCpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpv+lAKYBpv+lAab/pQGm/6UBpv+lAaYApgCm/6UBpv+lAab/pQGmAKb/pQGm/6UApgKm/aUDpv6lAKYBpv6lA6b9pQOm/aUBpgKm/KUFpvulBKb+pQGm/6UApgGm/6UBpv+lAKYApgCmAKYBpv6lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQGmAKYApgCmAKb/pQGmAKYApgCmAKYApgCm/6UCpv6lAqb+pQGmAKYApv+lAqb+pQKm/qUCpv6lAqb9pQSm+6UGpvqlBKb9pQOm/aUDpv6lAab/pQGm/6UCpv2lA6b9pQKmAab9pQKm/6UApgGm/6UBpv+lAab/pQCmAab/pQKm/aUDpv2lAqYApv+lAab/pQCmAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAqb9pQOm/aUCpgCm/6UBpgCm/6UBpv+lAKYCpv6lAab/pQCmAaYApgCm/6UBpv6lBKb7pQSm/qUApgGm/6UApgCmAab+pQKm/6UAWgBaAVr+WQJa/lkCWv5ZAlr/WQBaAFr/WQJa/lkCWv5ZAVoAWgBa/1kBWgBa/1kDWvtZBVr8WQNa/lkBWv9ZAVoAWv9ZAVr/WQFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFaAFr/WQJa/lkCWv5ZAlr+pQGmAab+pQKm/6X+pQSm/KUDpv6lAaYApv+lAqb+pQGmAKb/pQOm/KUDpv+l/6UCpv6lAaYApgCm/6UCpv6lAab/pQKm/qUBpgCm/qUFpvmlB6b7pQOm/6X/pQKm/qUCpv6lAaYBpv2lBKb8pQKmAab+pQKm/6X/pQOm/KUEpv6lAKYBpv6lA6b9pQOm/aUCpv+lAab/pQCmAab/pQGmAKb+pQOm/aUDpv2lAqYApv6lA6b9pQGmAqb8pQSm/aUCpv+lAab/pQCmAab/pQGmAKb/pQGmAKb/pQGmAKb+pQOm/aUCpgCm/qUCpv+lAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b9pQGmAKYApgGm/qUCpv6lAaYApgCm/6UCpv6lAqb+pQGm/6UCpv6lAqb9pQOm/qUCpv6lAqb+pQGmAab+pQKm/6X/pQKm/qUBpgCmAKYApgCm/6UCpv6lA6b8pQOm/6UApgCmAKYApv+lA6b8pQOm/6X/pQGmAKYApgCmAKb/pQKm/aUDpv6lAaYApv+lAKYCpv6lAaYApv+lAaYApv+lAqb+pQGm/6UApgGm/1kBWv9ZAFoBWv5ZA1r9WQJa/1kAWgFaAFr+WQNa/VkCWgBa/lkDWv1ZA1r8WQVa+1kEWv5ZAFoBWv9ZAVoAWv9ZAVr/WQJa/lkBWgBa/1kCWv5ZAVoAWv9ZAlr+WQJa/lkAWgJa/lkCWv5ZAVr/WQJa/qUCpv2lBKb8pQOm/qUApgGmAKb/pQGm/6UApgGm/6UBpv+lAKYBpv6lA6b8pQWm+qUGpvqlBqb8pQKm/qUBpgCmAab/pQGm/aUEpv2lAqb/pQCmAab/pQGm/qUDpv6lAaYApv+lAqb+pQGmAab+pQKm/qUCpv6lAaYApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b8pQSm/aUBpgGm/qUBpgCm/6UCpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UApgGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQKm/aUDpv6lAaYApgCmAKYApgGm/qUCpv+lAKYApgCmAKYBpv+l/6UBpgCmAab/pf+lAqb+pQKm/6X/pQKm/qUCpv6lAqb+pQGmAab9pQSm/KUDpv+lAKYApgCmAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAab+pQOm/KUEpv2lAaYBpv6lAqb+pQGmAab+pQKm/qUBpgGm/aUDpv6lAaYApv+lAab/pQKm/aUCpv+lAab/pQKm/KUEpv6lAKYCpv2lAqb/pQGm/6UBpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UBpv9ZAVr/WQJa/VkDWv5ZAFoBWgBa/lkDWv5ZAFoBWv9ZAVr/WQFa/1kAWgFa/1kAWgFa/lkCWv9ZAFoBWv5ZAlr/WQFa/lkCWv9ZAVr/WQBaAVr/WQJa/VkCWgBa/1kBWv9ZAFoCWvxZBVr6WQZa/FkCWv+lAKYApgGm/6UApgGm/qUDpv2lAqb+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAKYBpv6lA6b9pQKm/6X/pQOm/aUCpv6lA6b8pQWm+6UEpvylBab8pQOm/aUBpgCmAqb9pQOm/KUEpv2lAqYApv+lAab/pQGm/6UCpv2lA6b+pQGm/6UBpv+lAqb9pQKm/6UBpgCm/qUDpvylBab8pQKm/6UApgCmAab/pQGm/qUCpv6lAqYApv6lAqb+pQOm/KUFpvulA6YApv6lAqYApv6lA6b9pQKmAKb/pQGmAKb+pQOm/aUCpgCm/6UApgGm/6UBpv+lAab/pQKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQKm/qUCpv6lAaYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUCpv+l/6UDpvylBKb9pQGmAab+pQOm/KUEpv2lAqYApv6lAqb/pQCmAab/pQGm/6UBpv+lAKYCpv2lAqYApv+lAKYBpv6lA6b9pQOm/KUFpvulBab7pQSm/aUCpgCm/qUDpv2lA6b+pQCmAqb9pQOm/qUBpv+lAab/pQGm/6UApgGm/6UApgGm/qUCWv9ZAFoAWgBaAFoAWgFa/lkCWv5ZAlr/WQBaAFoAWgFa/1kAWgBaAFoBWv9ZAVr+WQJa/1kAWgFa/lkCWv5ZAlr+WQJa/lkBWgBaAFoAWgBa/1kCWv5ZAlr/Wf9ZA1r8WQRa/VkCWv5ZAlr/WQBaAVr+pQKm/6UApgCmAKYApgCm/6UCpv2lBKb8pQOm/aUDpv6lAaYBpv2lA6b+pQGmAKYBpv6lAqb+pQKm/qUDpv2lAqb/pf+lAqb/pQGm/qUDpvylBKb9pQKm/6UApgCmAKYApgCm/6UCpv6lAaYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/qUDpv6lAaYApv6lA6b9pQOm/aUCpv+lAab/pQCmAab+pQKm/6UBpgCm/qUCpv+lAqb9pQOm/aUDpv6lAKYBpgCm/qUCpv+lAKYCpv2lAaYBpv6lAqYApv6lA6b9pQKm/6UBpgCm/6UApgGm/qUEpvulBKb8pQWm+qUGpvulBKb9pQKm/qUDpv2lAqb/pQGm/6UBpv6lA6b9pQOm/qUApgGm/qUDpv2lA6b+pQCmAab/pQGm/6UBpv6lA6b9pQKm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQCmAab+pQOm/aUCpv+lAab/pQGm/6UBpgCmAKb/pQKm/aUEpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQGmAKb/pQKm/6X/pQKm/aUDpv6lAqb+pQGmAKb/pQKm/lkCWv1ZBFr9WQFaAFoAWv9ZA1r7WQVa/FkDWv5ZAVr/WQFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kBWgBa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQFa/1kBWgBa/1kBWv9ZAlr9WQNa/aUDpv6lAKYApgGm/6UBpv+l/6UCpv+lAKYApgGm/aUFpvqlBab9pQKm/6UApv+lA6b8pQSm/KUDpv+l/6UBpv+lAaYApgCm/6UBpv+lAab/pQGm/6UCpv6lAaYApv+lA6b8pQSm/aUCpv+lAKYApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUDpv2lAqb/pQCmAqb9pQOm/aUCpgCm/6UBpv+lAab/pQKm/KUFpvulBab8pQKmAKb+pQOm/aUCpgCm/6UApgGm/6UBpv+lAKYApgGm/qUCpv6lAqb+pQKm/qUCpv+lAab/pQCmAab+pQSm+6UEpv2lAqb/pQGm/6UApgGm/qUDpv6lAKYBpv+lAKYCpv2lA6b9pQKm/6UBpgCm/6UApgCmAKYBpgCm/qUCpv6lAqb/pQGm/qUCpv+l/6UDpvylBKb9pQCmA6b8pQSm/aUBpgGm/qUDpvylBKb9pQKm/6UBpv6lA6b8pQSm/aUCpv6lAqb+pQKm/aUEpvylA6b/pQCm/6UCpv6lAqb/pf+lAqb9pQSm/KUDpv6lAab/pQKm/qUBpgCmAKb/pQOm/KUDpv+l/1kCWv5ZAVoAWgBaAFr/WQJa/lkCWv9ZAFoAWgFa/lkDWv1ZAlr/WQFa/lkDWv1ZA1r9WQNa/VkDWv5ZAFoCWv5ZAVoAWv5ZBFr8WQRa/FkDWv5ZAVoAWgBaAFr/WQFa/1kBWgBa/1kBWv9ZAFoCWv1ZBKb7pQWm/KUCpgCm/qUEpvylAqb/pQCmAab/pQGm/6UApgCmAKYBpv+lAKYApgCmAKYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQGmAKb/pQKm/qUCpv6lAaYApgCmAKYBpv2lBKb9pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQGm/qUDpvylBab8pQOm/qUBpv+lAaYApv+lAqb9pQOm/aUDpv6lAaYApv+lAab/pQKm/qUBpgCm/6UCpv+l/6UCpv6lAqb+pQKm/qUCpv6lAaYApgCmAKb/pQKm/aUDpv6lAaYBpv2lA6b9pQSm/aUBpgCm/6UBpgCm/6UCpv6lAKYBpv6lA6b9pQKm/6UApgCmAKYBpv+lAab+pQOm/aUDpv2lAqb/pQGm/6UApgCmAKYBpv+lAKYBpv6lA6b9pQKmAKb/pQGm/6UBpv+lAab/pQGmAKb/pQCmAaYApv+lAab+pQKmAKb/pQGm/qUCpv6lA6b+pQCmAab+pQKmAKb/pQGm/6UApgGmAKb/pQGm/6UBpv9ZAlr9WQNa/lkBWgBaAFr/WQJa/VkEWvxZA1r+WQBaAlr+WQJa/lkBWv9ZAlr/WQBaAFr/WQFaAFr/WQFaAFr/WQFa/1kAWgJa/lkAWgFa/1kBWgBa/1kBWv9ZAFoBWv9ZAlr9WQJa/1kAWgJa/VkDWv6lAKYBpv+lAaYApv+lAKYApgGm/6UBpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQGm/qUDpv2lAqYApv6lA6b9pQGmAab/pQCmAab+pQKm/6UApgCmAab+pQOm/KUEpvylBKb9pQGmAKYApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b8pQSm/KUEpv2lAqb+pQKm/qUCpv6lAaYBpv6lAqb/pf+lAqb/pf+lA6b9pQKm/qUBpgCmAab+pQKm/qUCpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UCpv2lA6b+pQKm/qUBpgCmAKYApgCm/6UCpv+l/6UDpvulBab8pQOm/6UApv+lAab/pQKm/qUCpv6lAaYApv+lAqb+pQGmAKb/pQKm/aUDpv2lA6b+pQCmAqb8pQSm/qUApgGm/6UApgGm/6UApgGm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgGm/qUCpv6lAqb/pQGm/qUCpv6lAqb/pQCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+lAKYCpv2lA6b8pQWm/KUCpgCm/6UBpgCm/6UBpgGm/aUEpvylA6b+pQKm/qUCpv6lAaYAWgBaAFoAWgBaAFoAWv9ZAlr9WQRa/VkBWv9ZAVoAWgBaAFoAWgBaAFoAWgBaAFoAWgBaAFoAWgBa/1kCWv5ZAVoAWv9ZAlr+WQFaAFoAWgBaAFr/WQFaAVr+WQFa/1kBWgBaAFoAWv9ZAlr+WQJa/lkCpv+lAab/pQCmAab/pQGm/6UCpv2lA6b8pQWm/KUCpgCm/qUDpv2lAqYApv+lAqb9pQOm/qUBpgCmAKYApgCm/6UCpv6lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAab9pQWm+6UEpv2lAqb/pQGm/6UApgCmAab+pQOm/KUEpv2lAaYApv+lAqb/pf+lAab/pQGmAKYApv+lAaYApv+lAqb/pf+lAqb+pQGmAKYApv+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKb/pQKm/aUEpv2lAKYCpv2lA6b+pQGm/6UBpgCm/6UBpv6lA6b+pQGm/6UApgGm/6UBpv+lAKYBpv+lAaYApv6lA6b+pQGmAKb/pQKm/qUBpv+lAqb+pQKm/aUDpv6lAqb+pQKm/qUCpv2lBKb9pQKm/6X/pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKYBpv6lAaYBpv6lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQGmAKYApv+lAqb+pQKm/qUCpv6lAqb/pf+lAqb/pQCmAab+pQKm/6UBpv6lA6b8pQWm+6UDpgCm/qUDpvylBFr9WQJa/1n/WQJa/1kAWgBaAFoAWgBaAFoAWgFa/lkDWvxZBFr9WQJa/1kBWv9ZAFoAWgBaAFoBWv5ZAlr/WQBaAFoAWgBaAFoBWv5ZAlr+WQFaAVr+WQJa/1kAWgFa/lkCWv9ZAFoCWvxZBFr9WQFaAab/pQCmAKYBpv6lA6b8pQOm/6UApgCmAKb/pQKm/aUDpv2lA6b+pQGm/6UBpv+lAaYApv+lAqb9pQOm/qUCpv6lAab/pQKm/aUEpvulBab8pQOm/aUCpv+lAab/pQGm/aUFpvqlBqb7pQSm/aUCpv6lA6b9pQKm/6UApgGm/qUCpv+lAKYApv+lAqb+pQOm/KUDpv6lAqb+pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQCmAKYBpv6lA6b9pQKm/6UApgKm/aUDpvylBab7pQWm/KUCpgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQGm/6UBpv+lAqb9pQOm/aUCpv+lAKYBpv6lAqb/pf+lA6b8pQOm/6X/pQOm/KUEpvylA6b+pQGmAKYApgCm/6UBpv+lAaYApgCm/6UCpv2lA6b+pQKm/qUBpgCmAKYApgCm/6UBpgCm/6UCpv2lA6b+pQCmAqb+pQKm/qUBpv+lAqb+pQKm/qUBpv+lAaYApgCm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/qUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgGm/qUDpvxZBFr9WQJa/1kBWv9ZAFoBWv5ZA1r+WQBaAlr8WQVa/FkCWgBa/lkDWv1ZAloAWv5ZA1r9WQJa/1kBWv9ZAlr9WQJa/1kBWv9ZAFoBWv5ZAlr+WQJa/lkBWgBaAFoBWv5ZAlr9WQRa/VkCWgBa/lkDWvylBKb+pQCmAab+pQGmAab+pQKm/qUBpgCmAKYApgCmAKYApgCm/6UBpv+lAqb+pQGm/6UApgGmAKb/pQGm/6UApgKm/qUApgGm/qUDpv2lAqb+pQKm/6UApgCmAKYApgCmAab+pQKm/6UApgGm/qUCpv+lAKYApgCmAKYApgCmAKb/pQGmAKb/pQGm/6UBpv+lAqb8pQWm+6UEpv2lA6b9pQOm/KUEpv2lA6b+pQCmAKYApgGm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/6UApgCmAKYBpv+lAKYApgGm/qUCpv2lA6b/pQCmAKb/pQGmAKYApv+lAab/pQKm/aUDpv6lAab/pQGmAKb/pQKm/aUDpv2lBKb7pQWm+6UFpvylA6b+pQGmAKYApgCmAKYApgCmAKYApgGm/qUCpv6lAaYApgCm/6UCpv6lAaYApv+lAaYApgCmAKYApv+lAqb+pQKm/qUCpv6lAqb/pf+lAqb+pQKmAKb9pQSm/KUDpv6lAqb/pQCmAKb/pQKm/6UBpv6lA6b9pQKm/6UBpv+lAab+pQKm/6UApgGm/qUCpv6lAqb/pQCmAab9WQVa+1kDWv9Z/1kCWv5ZAVoAWgBa/1kCWv5ZAVoAWgBa/1kCWv5ZAVoAWv9ZAVoAWgBa/1kCWv5ZAVoAWv9ZAVoAWv9ZAlr9WQNa/VkDWv1ZA1r9WQJaAFr+WQNa/VkCWv9ZAVr+WQNa/VkCWv9ZAFoAWgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKb/pQGmAKYApv+lAab/pQKm/6UApgCmAKYApgGm/qUCpv+lAKYApgGm/qUCpv6lAaYBpv+lAKYApgCmAab/pQCmAab+pQSm/KUCpv+lAKYBpv+lAab/pQCmAKYApgGm/6UBpv+lAKYCpv2lBKb7pQSm/aUEpvylAqb/pQCmAqb+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/qUEpvylA6b+pQCmAaYApv6lA6b9pQKm/6UApgCmAab/pQCmAKYApgGm/6UBpv6lA6b9pQKmAKb+pQSm+6UFpvylAqb/pQGmAKb/pQCmAab+pQOm/aUCpgCm/qUDpvylBab7pQSm/aUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgGm/qUCpv+l/6UCpv6lAaYBpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv2lAqb/pQKm/aUDpv2lA6b+pQKm/aUDpv6lAaYBpv6lAaYApv+lAqb/pQCmAKb/pQKm/qUCpv6lAaYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQGm/6UBpgCmAKb/pQKm/aUEWvxZBFr9WQFaAFr/WQJa/VkEWvtZBlr6WQRa/lkBWgBaAFoAWgBa/1kCWv5ZA1r8WQRa/FkEWv1ZAlr/WQBaAVr9WQRa/VkBWgFa/VkEWv1ZAVoBWv5ZAlr+WQJa/1kAWgBa/1kCWv5ZAlr+WQFa/1kBpgCm/6UBpv+lAab/pQGm/qUCpgCm/6UBpv6lAqb/pQKm/qUBpv+lAaYApgCmAab9pQOm/qUBpgCm/6UBpgCm/6UBpv+lAaYApv+lAqb9pQKmAKb/pQGm/6UApgGm/6UBpv6lA6b+pQGm/qUCpv+lAqb+pQCmAab/pQKm/qUBpgCmAKYApgGm/qUCpv6lAqb/pQCmAKYApv+lA6b7pQWm/KUCpgGm/aUDpv2lA6b/pQCmAKb/pQGmAab+pQKm/qUCpv6lAqb9pQSm/aUCpv6lAqb/pQCmAab+pQOm/aUCpv+lAKYBpv6lAqb+pQOm/aUBpgCm/6UCpv+l/6UBpgCm/qUEpvulBab8pQKmAKb+pQOm/aUCpv+lAKYBpv6lA6b9pQOm/aUCpv+lAaYApv+lAKYApgCmAab/pQGm/6UApgGm/6UCpv6lAaYApv+lAqb+pQKm/aUEpvulBab8pQKm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgCmAab+pQOm/KUDpgCm/6UBpv+lAab/pQKm/aUDpv6lAKYCpv2lA6b9pQKm/6UBpv+lAab/pQGmAKb/pQGmAFoAWv9ZA1r7WQVa/VkBWgFa/VkDWv5ZAlr+WQJa/VkEWvxZA1r/Wf9ZAlr+WQFaAFoAWv9ZAlr+WQFaAVr+WQJa/lkBWgFa/1kAWgBaAFoAWgFa/lkCWv9ZAFoAWgBaAFoBWv5ZA1r8WQNa/lkBWgFa/6X/pQGmAKYApgCmAKYApgCm/6UBpgCmAab+pQGm/6UBpgGm/qUBpgCmAKYApgCm/6UCpv6lA6b8pQOm/qUCpv+lAab+pQKm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/6UApgCmAKYBpv6lA6b8pQSm/aUCpv6lAqb/pQCmAab9pQSm/aUCpv+lAKYBpv+lAab+pQOm/aUDpv6lAKYBpv+lAaYApv+lAqb+pQGmAab+pQKm/qUCpv+lAab+pQKm/qUDpv2lAqb/pQCmAab+pQKm/6UApgGm/aUDpv+l/6UCpv6lAaYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAKYCpv2lA6b9pQGmAab/pQCmAab+pQOm/KUEpvylBKb9pQGmAab9pQSm/KUDpv+l/6UCpv6lAqb/pQCm/6UCpv6lAqb+pQGmAKb/pQOm+6UFpvylA6b/pf+lAqb9pQOm/qUBpgCm/6UApgGm/6UCpv2lAqb/pQGm/6UBpv6lA6b+pQCmAqb8pQWm/KUCpgCmAKb/pQKm/KUFpvylA6b+pQCmAab/pQKm/qUBpv+lAaYApgCmAKb/pQGm/6UBpv9ZAlr+WQBaAlr8WQVa/VkAWgJa/VkDWv5ZAVr/WQFaAFr/WQJa/VkEWvtZBVr8WQNa/lkBWgBa/1kCWv1ZA1r+WQFaAFr/WQFaAFoAWv9ZAVr/WQJa/1kAWgBa/1kCWv9ZAVr+WQFaAVr+WQNa/FkDWgCm/aUEpvylBKb9pQKm/qUBpgCmAab+pQKm/qUBpgGm/6X/pQKm/qUCpv+l/6UCpv6lA6b9pQGmAKYBpv+lAab+pQKm/6UApgGm/aUFpvulBKb9pQGmAab+pQKm/qUCpv6lAqb9pQOm/qUCpv6lAaYApgCmAKYApgCmAab+pQKm/6UApgKm/aUCpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b8pQOm/6X/pQOm/KUDpv+l/6UCpv+lAKYBpv6lAqb/pQCmAab/pQCmAqb8pQWm+6UEpv2lAqb+pQKm/qUBpgCm/6UCpv6lAaYApgCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+lAab/pQGm/qUDpv2lA6b9pQKm/qUCpv+lAKYBpv6lAqb/pQCmAKYBpv6lA6b9pQKm/6UApgGmAKb/pQGm/6UBpgCmAKb/pQGmAKb/pQKm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQCmAab/pQGm/6UApgCmAab/pQCmAab+pQOm/aUCpv+lAKYBpv+lAab+pQOm/aUCpgCm/qUCpv+lAab+WQJa/lkCWv9Z/1kBWgBaAFoAWgBa/1kBWgFa/VkEWvxZAloBWv5ZAlr9WQNa/lkCWv5ZAVoAWgBa/1kCWv5ZAVoAWv9ZAlr/Wf5ZBFr7WQZa+1kCWgBa/1kBWv9ZAVoAWgBa/lkDWv1ZA1r+WQFa/1kCpvylBKb+pQCmAab+pQOm/aUCpv6lAqb/pQGm/qUCpv+lAKYBpv6lA6b9pQKmAKb+pQSm/KUDpv6lAaYApgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQGmAKb/pQKm/qUBpgCmAKYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUEpvylAqYBpv6lAqb+pQKm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYBpv2lA6b+pQGmAab+pQGmAKb/pQKm/qUCpv6lAaYApgCmAab9pQSm/KUEpvylA6b/pQCmAab+pQGmAab+pQKm/6UApgCmAab+pQOm/KUFpvulBab7pQSm/aUDpv6lAab/pQGm/6UCpv6lAqb+pQGm/6UBpgCm/6UCpvylBab8pQKmAKb+pQOm/aUCpv+lAKYApgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgCmAKYBpv+l/6UDpv2lAqYApv6lA6b9pQOm/VkDWv5ZAFoCWv1ZA1r9WQNa/VkEWvtZBFr+WQBaAlr9WQNa/VkCWv9ZAVr/WQFa/1kAWgJa/FkFWvtZBFr+WQFa/1kBWv5ZA1r+WQFa/1kAWgFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVr+WQNa/qUCpv2lA6b9pQOm/qUBpgCm/6UBpv+lAaYApv+lAab/pQGm/6UBpv6lAqb/pQGm/qUCpv6lAqb+pQKm/qUBpgCm/6UCpv6lAKYCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYBpv6lA6b9pQKm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAab/pQKm/aUDpv2lA6b+pQCmAab+pQOm/aUCpv+lAKYBpv6lAqb/pQGm/6UApgCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv6lAaYApgCm/6UCpv2lA6b9pQKm/6UBpv+lAab/pQCmAab/pQGm/6UBpv+lAqb9pQKm/6UApgKm/aUDpv2lAqb+pQOm/qUBpgCm/qUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv2lBKb8pQSm/KUDpv+lAKYBpv6lAqb/pQGm/qUCpv+lAKYApgCmAKYBpv+l/6UCpv6lA1r9WQFaAVr9WQVa+1kDWv9ZAFoAWgBaAFoBWv9ZAFoBWv5ZAloAWv5ZA1r9WQJaAFr+WQNa/VkDWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/1kAWgFaAFr/WQJa/VkDWv5ZAFoBWv9ZAFoBWv5ZAlr+WQFaAKYApgGm/qUBpv+lAqb/pQCmAKb/pQGmAKYBpv6lAqb+pQCmA6b9pQKm/qUBpv+lAqb+pQGmAKb/pQGmAKb/pQGmAKb/pQOm/KUDpv6lAaYBpv6lAqb+pQGmAKYApgGm/qUCpv2lBKb8pQSm/KUDpv6lAaYApgCmAKYApgCmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgGm/qUCpv6lAqb/pQCmAKYApgGm/qUCpv2lBKb9pQKm/qUCpv6lA6b8pQWm+6UEpv6lAKYBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb+pQSm+6UFpv2lAKYCpv2lA6b+pQGmAKb+pQOm/aUDpv2lA6b8pQWm/KUDpv6lAaYApgCmAKYBpv6lAqb/pQCmAab+pQKm/6UApgCmAKYApgCmAKYApv+lA6b7pQam+6UCpgGm/aUEpv2lAqb+pQGmAKYApgGm/6UApgCmAKYBpv6lAqb+pQKm/6UApgCmAKYApgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgGm/qUCpv2lBKb8pQOm/6X+pQSm/KUDpv9Z/lkEWvxZA1r+WQFaAFr/WQJa/VkDWv5ZAVoAWv5ZA1r9WQNa/lkBWv5ZA1r9WQNa/lkAWgBaAVr/WQBaAVr+WQNa/FkEWvxZBFr8WQRa/FkDWv9Z/1kDWvtZBVr8WQRa/VkBWv9ZAVoAWgBaAFoAWv+lA6b8pQSm/aUBpgGm/qUDpv2lAqb+pQKmAKb/pQGm/qUCpgCm/6UBpv6lA6b9pQOm/aUDpv2lAqb/pQGmAKb/pQCmAab/pQGm/6UApgGm/6UApgGm/6UApgGm/qUDpv2lAqb/pQGm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQKm/qUBpv+lAaYApgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpv+lAaYApgCmAKb/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UCpv6lAKYCpv6lAqb+pQGm/6UCpv6lAqb+pQGmAKYApgCmAKYApgCmAab+pQGmAKYApgCmAab9pQOm/aUDpv2lA6b9pQOm/aUCpv+lAab/pQCmAab/pQGm/6UBpv+lAKYBpv+lAaYApv+lAKYCpv2lA6b9pQKm/6UApgGm/qUCpv+l/6UCpv+l/6UDpvulBqb7pQOm/qUCpv6lAqb+pQKm/qUBpgGm/qUCpv6lAaYApgCmAKb/pQKm/qUBpgCm/6UCpv6lAqb+pQKm/qUCpv6lAqb/pQCmAKb/WQJa/lkBWgFa/FkFWvxZA1r+WQFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/1kBWv9ZAFoAWgBaAlr8WQVa+lkFWv5ZAFoBWv5ZAlr/WQFa/1kAWgFa/lkDWvxZBFr9WQJa/lkCWv5ZAlr/Wf9ZA1r8pQSm/aUCpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb/pQGm/6UApgGm/qUDpv2lA6b9pQKm/qUCpv+lAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQKm/6UApgGm/qUDpv2lAqb/pQCmAab/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv+lAab/pQCmAKYBpv+lAab/pQCmAKYBpv+lAab/pQGm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/6UBpv+lAab/pQGmAKb/pQGm/6UCpv2lAqb+pQKm/6UBpv6lAqb+pQKm/6UApgGm/6UApgKm/aUDpv6lAab/pQKm/aUEpvulBab8pQKm/6UBpv+lAqb9pQKm/6UBpgCm/6UBpv+lAaYApv6lA6b+pQGmAKb+pQOm/qUCpv6lAKYCpv6lAqb+pQKm/aUEpvylBKb9pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCm/6UCpv2lBKb8pQOm/aUDpv6lAab/pQCmAqb9pQKm/6UApgGmAKb+pQKm/6UApgKm/aUCpv+lAab/pQGm/6UApgGm/6UBpv+lAFoAWgFa/1kBWv5ZAlr+WQJa/1kAWgBaAFoAWgFa/lkDWvxZBVr8WQJaAFr+WQNa/lkBWgBa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVr/WQJa/lkBWgBa/1kCWv5ZAlr+WQJa/lkCWv5ZAqb+pQKm/6X/pQKm/qUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQCmAab+pQKm/6UApgGm/6UApgCmAKYBpv+lAKYApgCmAab+pQOm+6UGpvulA6b/pf+lAqb/pQCmAKYApv+lAqb/pf+lA6b7pQam+6UDpv6lAqb+pQOm/KUDpv+l/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv6lAqb/pf+lAqb+pQOm/aUBpgCmAKYApgCmAKYApv+lAab/pQGmAKb/pQCmAab+pQSm/KUCpv+lAab/pQKm/qUApgGm/6UApgGm/6UApgGm/qUCpv6lA6b9pQGmAab+pQKmAKb+pQOm/aUCpgCm/6UCpv2lA6b+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKYApgCmAab+pQKm/6X/pQOm/aUBpgGm/aUDpv6lAqb+pQKm/aUEpvylBKb8pQSm/aUBpgCmAKYApgGm/aUEpvylBab6pQWm/KUDpv+l/6UCpv2lA6b+pQGmAab9pQSm/KUDpgCm/aUEpvylA6b+pQKm/qUBpgCmAKb/pQJa/lkBWgBa/1kCWv5ZAlr9WQNa/lkBWgBa/1kBWgBa/lkEWvtZBVr8WQJa/1kCWv1ZBFr7WQRa/1n+WQRa+1kGWvpZBVr8WQNa/1n/WQFaAFr/WQJa/VkDWv5ZAVr/WQJa/VkEWvxZA1r+WQJa/VkEWv2lAaYBpv2lA6b+pQGmAKYApv+lAab/pQGmAKb/pQKm/qUBpv+lAab/pQKm/aUEpvqlB6b6pQWm/KUCpgCm/6UBpv+lAab/pQGm/6UApgGm/qUDpv2lA6b8pQSm/aUCpv+lAab+pQOm/aUCpv+lAKYBpv+lAab/pQCmAab+pQOm/aUCpv+lAKYApgCmAKYApgGm/qUBpgGm/qUCpv6lAaYBpv+lAKYApgCmAKYBpv+l/6UCpv6lAaYBpv6lAab/pQGmAKYBpv+lAKYApgGmAKb/pQKm/qUBpgGm/aUDpv6lAqb+pQGm/6UBpgCm/6UBpv6lA6b9pQOm/aUCpv6lA6b9pQKm/6UApgGm/6X/pQKm/qUDpv2lAaYApv+lAqb/pf+lAqb+pQGmAab+pQGmAKYApgGm/qUBpv+lA6b8pQSm/KUDpv6lAqb+pQKm/qUBpgCmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQCmAab/pQGmAKb+pQOm/aUDpv6lAKYBpv6lA6b+pQCmAab+pQKmAKb+pQOm/aUBpgGm/6UBpv6lAqb+pQNa/VkCWv5ZA1r9WQJa/1kAWgFaAFr+WQJa/lkCWv9ZAVr+WQJa/lkCWv5ZAlr+WQFaAFoAWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAVoBWv5ZAlr9WQNa/lkCWv5ZAlr+WQFa/1kCWv5ZAlr/Wf9ZAlr/WQCmAab/pQCmAKYBpv6lA6b+pf+lA6b8pQSm/qUApgCmAab+pQKm/qUCpv6lAqb+pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAKYApgCmAab+pQKm/qUCpv+lAKb/pQOm/KUFpvqlBab8pQSm/aUCpv6lAqb+pQKm/6X/pQKm/6X/pQKm/aUDpv6lAqb9pQOm/aUDpv6lAab/pQGm/6UBpv+lAKYApgGm/qUDpv2lAqYApv6lA6b9pQKmAKb/pQGmAKb+pQOm/aUDpv6lAKYCpv2lA6b9pQOm/qUCpv2lAqb/pQKm/qUBpgCm/qUEpvylAqYApv+lAqb9pQOm/aUCpgCm/qUDpv2lAqb/pQGm/6UBpv+lAKYBpv+lAaYApv6lA6b9pQOm/qUApgGm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQOm/aUDpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQCmAqb8pQSm/qUApgGm/qUCpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgGm/qUDpv2lAaYBpv6lA6b8pQSm/aUCpv6lAqb/WQFa/1kAWgFa/1kBWv9ZAVoAWv9ZAVr+WQRa/FkDWv5ZAFoCWv5ZAVoAWv9ZAlr+WQFaAFoAWv9ZAlr9WQNa/lkBWgBa/1kBWv9ZAlr9WQNa/lkBWgBa/1kBWgBaAFr/WQJa/lkCWv5ZAVoAWgBaAFoApgCm/6UBpv+lAqb+pQGm/6UApgKm/aUDpv2lA6b+pQCmAab/pQGmAKb+pQOm/aUCpv+lAKYBpv+lAKYApgCmAKYApgCmAKYApgCmAKYApv+lAqb9pQSm/aUBpgCm/6UBpgCm/6UCpv2lA6b+pQGm/6UBpgCm/6UCpv2lAqb/pQCmAKYCpvylBKb7pQWm/aUDpvylA6b9pQSm/KUEpvylAqYApv+lAqb+pQGmAKb/pQGmAKb/pQKm/aUDpv6lAKYBpv+lAab/pQGm/qUDpv2lAqYApv+lAab/pQGmAKYApgCm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQGmAKb/pQKm/qUCpv6lAqb+pQOm/aUCpv+lAKYBpv+lAab/pQGm/6UBpv+lAqb+pQKm/qUBpgCmAKYBpv6lAab/pQGmAab9pQOm/aUDpv6lAaYApv+lAaYApgCmAab+pQKm/qUCpv+lAab+pQKm/6UApgGm/qUCpv6lAqb/pf+lA6b8pQOm/6X/pQKm/qUCpv6lAaYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab/pQCmAKYBpv+lAab/pQCmAab+pQOm/aUDpv2lA1r9WQNa/lkBWgBaAFoAWgBaAFr/WQJa/lkCWv5ZAlr9WQRa/FkEWv1ZAlr+WQJa/lkDWvxZBFr8WQRa/VkBWgBaAFoBWv9ZAFoAWgBaAVr/WQFa/lkCWv5ZA1r8WQVa+lkGWvtZBFr9WQJaAFr/WQFa/6UApgGm/6UCpv2lA6b9pQKmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv6lAKYBpv+lAaYApv+lAaYApv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAqb9pQKmAKb/pQGmAKb/pQGm/6UBpv+lAqb9pQOm/aUDpv6lAKYBpv+lAab/pQGm/6UBpv+lAKYBpgCm/6UBpv+lAaYApv+lAqb+pQKm/qUBpgGm/qUCpv6lAqb/pQCmAKYApgCmAab/pQCmAKYBpv6lA6b9pQGmAqb8pQSm/aUCpv6lAqb/pQCmAab9pQSm/aUCpv+l/6UDpvylBab6pQWm/aUCpgCm/6UApgCmAaYApv+lAqb9pQOm/qUBpv+lAqb9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQKm/aUDpv+l/6UCpv6lAaYApv+lAab/pQGm/6UBpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQGm/qUDpvylBKb+pQGm/qUDpvylBKb+pQCmAqb9pQKm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQNa/VkCWv9ZAlr9WQNa/VkCWgBa/1kBWv9ZAVoAWv9ZAFoBWgBaAFr/WQBaAVr/WQJa/FkFWvtZBFr9WQJa/lkCWv5ZAlr+WQFaAFr/WQJa/lkCWv5ZAlr+WQJa/1kBWv9ZAFoBWv5ZAlr/WQBaAVr+WQKm/qUBpgGm/aUEpv2lAaYApv+lAaYApgGm/aUDpv6lAaYApgCm/6UCpv6lAab/pQGmAKb/pQKm/KUFpvylA6b9pQOm/aUDpv6lAaYApv6lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAqb9pQOm/qUCpv+l/6UCpv2lBab6pQam+qUFpv2lAaYApv+lAqb+pQGmAKb/pQKm/qUBpv+lAqb+pQGmAKb/pQGmAKb/pQGmAKb/pQGm/6UBpv+lAqb8pQWm/KUDpv2lAqYApv+lAqb8pQSm/aUDpv2lAqb/pf+lA6b8pQSm/aUCpv+l/6UCpv6lAqb+pQGmAKb/pQGm/6UBpgCm/6UCpv2lBKb7pQam+6UDpv+lAKYApgCmAKYApgCmAKb/pQKm/qUCpv2lA6b+pQGmAKb/pQGmAKb+pQOm/aUDpv2lAqb/pQCmAab/pQCmAab/pQCmAab+pQKm/6UApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAab/pQGm/qUCpv+lAab/pQCmAKYApgCmAKYApgCm/6UCpv2lBKb8pQKmAab9pQSm/KUDpv2lA6b9WQNa/lkBWv9ZAVr/WQFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVoAWv5ZBFr8WQJaAFr+WQRa/FkCWv9ZAVoAWgBa/1kBWv9ZAlr+WQFaAFr/WQJa/VkDWv1ZA1r+WQBaAVr/WQBaAFoBWv5ZA1r8pQOm/6UApgGm/qUBpgGm/6UApgCmAKYApgGm/qUBpgCmAKYApgCmAKYApv+lAqb+pQKm/qUBpv+lAqb+pQGmAKb/pQGm/6UCpv2lBKb7pQWm/KUCpgCm/6UBpgCm/6UBpgCm/6UBpgCm/6UBpv+lAab/pQGm/qUDpv2lAqb/pQCmAab/pQGm/qUDpv6lAKYCpv2lA6b+pQGmAKb/pQKm/qUBpgGm/aUEpv2lAaYApv+lAqb/pQCmAKb/pQKm/qUDpv2lAaYBpv6lAqb/pQCmAab/pf+lAqb/pQGm/6UApgCmAab+pQOm/KUEpv2lAqb/pQCmAKYApgGm/qUDpvylBKb9pQKm/qUCpv6lA6b8pQSm/KUEpv2lAqb+pQKm/6UApgGm/qUCpv6lAqb/pf+lAqb9pQOm/6X/pQKm/qUBpgCmAKYBpv+lAKYApgCmAab/pQCmAKYApgCmAab+pQKm/qUCpv6lAqb+pQOm/KUEpvylBKb9pQKm/6UApgGm/qUDpv6lAab/pQCmAaYApv+lAab+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAaYApv+lAab+pQKmAKb/pQGm/1kAWgFa/1kBWv9ZAFoBWv9ZAVr/WQBaAVr+WQNa/VkCWv5ZA1r8WQRa/FkEWv1ZAlr+WQJa/1kAWgBaAVr+WQNa/FkEWv1ZAlr/WQBaAVr/WQBaAVr/WQFa/1kAWgFa/1kAWgFa/lkCWv9ZAFoBWv9ZAFoBpv+lAaYApv+lAab/pQGm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAaYApv+lA6b7pQam+qUFpvylA6b+pQGmAKb+pQSm+6UFpvulBab8pQOm/qUBpv+lAaYApv+lA6b6pQem+qUFpvylA6b+pQGmAKYApgCmAKYApgCmAKYBpv6lA6b9pQKm/6UApgGm/6UBpv+lAab/pQGm/6UCpv6lAaYApgCmAKYBpv2lBab6pQem+aUGpvylAqb/pQGm/6UBpv+lAKYCpv6lAab/pQGmAKYApgCm/6UCpv2lA6b+pQGmAKb/pQGm/6UCpv2lA6b+pQCmAaYApv+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb/pQCmAKYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv2lA6b/pf+lAqb9pQSm/aUCpv6lAaYApgCmAab+pQKm/aUDpv+l/6UCpv6lAaYApgCmAKYApgCmAKYApgGm/qUCpv+lAKYApgCmAKYApgGm/aUDpv6lAqb+pQGm/6UCpv6lAab/pQGmAKb/pQGmAKb/pQGmAKb+pQOm/lkBWgBa/lkDWv5ZAVoAWv9ZAVoAWgBa/1kCWv5ZAVoBWv5ZAVoBWv9ZAVr/WQFa/1kCWv1ZA1r+WQFa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kCWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/lkDWv1ZA1r+WQBaAab+pQSm/KUCpv+lAab/pQKm/KUFpvulBab8pQOm/aUCpgCm/6UCpv6lAaYApv+lAqb+pQKm/qUBpv+lAqb+pQGm/6UBpv+lAqb+pQCmAqb9pQOm/qUBpv+lAqb9pQOm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYBpv6lAaYApgCm/6UCpv2lA6b/pf6lBKb7pQWm/KUDpv6lAqb9pQSm/KUCpgGm/qUCpv+l/6UCpv6lAqb/pQCmAab9pQSm/aUCpv6lAqb/pQCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQGm/qUDpvylBKb9pQKm/6UApgCm/6UCpv6lA6b8pQOm/qUBpgGm/qUCpv6lAqb/pQCmAKYBpv+lAab+pQKm/6UCpvylBab6pQam/KUCpgCm/qUDpv2lA6b9pQKm/qUDpv2lAqb+pQGmAKYApv+lAqb+pQGmAKb/pQKm/6UApgCmAKYBpv+lAKYBpv6lA6b9pQGmAab+pQKm/6X/pQKm/qUCpv+lAKYApgCmAKYBpv+lAKYApv+lA6b9pQJa/lkCWv9ZAVr/WQBaAVr+WQNa/VkCWv9ZAFoBWv5ZA1r9WQJa/1kAWgFaAFr/WQBaAVr+WQRa+1kEWv1ZAloAWv9ZAVr+WQJaAFr/WQFa/1kAWgJa/VkDWv1ZAloAWv9ZAVoAWv9ZAVr/WQFaAFr/WQGm/6UBpgCm/6UBpv+lAKYBpv+lAab/pQCmAKYApgGm/6UBpv6lAaYBpv+lAab+pQGmAKYBpv6lAaYApgCmAab+pQKm/6UApgCmAab+pQOm/aUBpgCmAKYApgGm/qUBpgCmAab+pQKm/qUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgCmAab/pQCmAKYApgGmAKb+pQOm/KUFpvulBKb9pQKmAKb/pQCmAab/pQGmAKb/pQKm/aUDpv6lAaYBpv2lA6b+pQGmAab9pQOm/qUBpgCmAKb/pQGmAKb/pQKm/aUEpvylBKb8pQOm/qUCpv+lAab+pQKm/qUCpv+lAab/pQCmAKb/pQOm/aUCpv+l/6UCpv6lAqb/pQCmAKYApgCmAKYBpv+lAKYBpv2lBab7pQSm/aUBpgCmAab+pQKm/aUEpvylBKb9pQGmAKYApgCmAab+pQGmAKYBpv6lAqb+pQKm/6UApgCmAKYApgCmAab+pQKm/qUBpgGm/qUBpgGm/qUCpv6lAaYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpv+lAaYApgCm/6UApgGmAKb/pQKm/aUDpv+l/6UCWv5ZAlr+WQNa/FkEWv1ZAVoAWgBaAFoAWgBaAFoAWgBa/1kCWv5ZA1r8WQNa/lkBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/lkDWv1ZA1r9WQNa/VkDWv1ZA1r+WQFaAFr/WQFaAFr/WQFa/1kBWgBa/1kBpv+lAaYApv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpgCmAKYApv+lAaYApgCmAKb/pQGmAKYApgCm/6UBpgCmAKb/pQKm/aUDpv6lAab/pQGm/6UApgKm/aUCpv+lAKYBpv+lAKYApgGm/6UApgCm/6UDpv2lAqb+pQGmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab+pQKm/6UApgCmAab/pQCmAab+pQKm/6X/pQOm/aUBpgGm/aUEpv6lAKYBpv+lAKYBpv+lAKYBpv6lAqb+pQOm+6UGpvulA6YApv6lA6b9pQKm/6UBpgCm/qUDpv2lA6b+pQCmAab/pQGm/6UBpv+lAab/pQGm/6UApgKm/aUDpv6lAKYBpv+lAaYApv+lAab/pQGm/6UCpv2lBKb8pQKmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv6lAKYCpv2lA6b+pQCmAqb9pQOm/aUDpv2lA6b+pQCmAaYApv6lBKb7pQWm/KUDpv6lAaYApgCmAKYApgCm/6UCpv6lAaYApv+lAqb+pQGmAKYApgGm/qUCpv6lAqb+pQKm/qUDpvylBKb8pQSm/aUDpv2lAlr/WQBaAVr/WQBaAFoAWgFa/lkCWv5ZAVoBWv5ZAlr/Wf9ZAlr/WQBaAlr8WQRa/VkDWvxZBVr7WQVa/FkCWv9ZAVoAWgBaAFr/WQFa/1kCWv1ZBFr7WQVa/FkDWv5ZAVoAWgBaAFoAWgBaAFoAWgBaAab+pQKm/qUCpv+lAKb/pQOm+6UGpvqlBKb/pQCm/6UBpv+lAaYBpv6lAab/pQGmAKb/pQKm/aUEpvulBab7pQSm/qUBpv+lAab+pQOm/aUDpv6lAKYBpv+lAqb+pQGm/qUDpv6lAqb9pQOm/qUBpgCm/6UCpv+l/6UCpv6lAqb+pQKm/aUEpv2lAaYBpv6lAqb+pQKm/qUDpv2lAaYBpv2lBKb9pQGmAab9pQOm/6X/pQOm/KUDpv6lAqb/pQCmAKb/pQKm/qUCpv6lAqb+pQGmAKYApgGm/qUCpv6lAqb/pQCmAab+pQKm/qUCpv6lAqb+pQKm/qUCpv6lAqb/pQGm/6UApgCmAab/pQKm/aUCpv+lAKYBpv+lAab+pQOm/aUCpv+lAab/pQGm/6UApgKm/aUCpv+lAab/pQGm/qUDpv6lAab/pQGm/6UBpgCm/6UCpvylBab7pQWm/KUBpgGm/qUDpvylBKb8pQSm/aUCpv+lAKYBpv6lBKb7pQSm/qUApgGmAKb/pQGmAKb/pQKm/qUCpv6lAqb+pQKm/6UApv+lAqb+pQKm/qUBpv+lAqb+pQGmAKYApgCmAKYApgBaAFoBWv5ZA1r9WQJa/1kBWv9ZAVr/WQFa/1kCWv1ZA1r+WQFaAFr/WQJa/lkCWv5ZAVoAWgFa/lkCWv5ZAVoBWv5ZAlr+WQJa/lkDWvxZBFr9WQJaAFr+WQNa/VkDWv5ZAVr/WQJa/lkBWgBa/1kCWv6lAqb+pQGmAKYApgCmAab+pQKm/qUCpv+lAab+pQKm/qUDpv2lAqb/pQCmAKYApgCmAKYBpv6lAqb+pQKm/qUDpv2lAqb/pQCmAKYBpv6lAqb+pQKm/6UApgCmAKYApgGm/qUCpv6lAqb/pf+lAqb+pQKm/6UApv+lAqb/pQCmAab+pQKm/qUCpv+l/6UDpvylBab7pQOm/qUDpv2lAqb/pQCmAab+pQOm/KUEpv2lAqb/pQCmAab+pQOm/aUCpgCm/qUDpv2lA6b+pQCmAqb8pQWm/KUCpgCm/qUDpv2lA6b+pQCmAab/pQGm/6UApgGm/6UBpv+lAKYCpv2lA6b9pQOm/qUBpv+lAab/pQGmAKb/pQGm/6UApgGmAKb/pQGm/6UApgGm/qUDpv2lA6b8pQSm/aUCpgCm/qUDpv2lAqb/pQGm/qUDpvylBab7pQOm/6UApgGm/6UBpv6lAqb/pQGm/6UBpv6lA6b9pQKm/6UApgGm/qUCpv6lAqb+pQKm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab+pQOm/aUDpv6lAab/pQGm/6UBpgCm/6UBpv+lAKYCpv2lA6b9pQJa/1kBWv9ZAVr/WQBaAVr/WQFa/1kAWgFa/1kBWv9ZAFoBWv9ZAVr+WQJa/lkDWv1ZAlr+WQNa/FkFWvpZBlr7WQNa/1kAWv9ZAlr9WQRa+1kFWvxZA1r+WQFa/1kBWgBa/1kBWv9ZAFoCWv1ZAlr/WQGmAKb+pQKm/6UCpv6lAab+pQOm/qUBpgCm/6UCpv6lAab/pQGmAab/pf+lAaYApgCmAKYApv+lA6b9pQKm/6UApgGmAKb+pQOm/qUApgKm/aUDpv6lAKYCpv2lA6b+pQGm/6UCpvylBab8pQOm/qUBpv+lAab/pQKm/qUApgKm/aUDpv+l/qUEpvylA6b/pQCmAKYApgCmAKYBpv6lA6b8pQSm/KUEpv2lAaYApgCm/6UCpv6lAKYCpv2lBKb7pQWm+6UFpv2lAKYBpgCm/6UCpv2lAqYBpv2lA6b9pQKm/6UBpv6lA6b9pQKm/6UBpv6lA6b+pQCmAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGm/6UBpv+lAaYApv+lAaYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYBpv2lBKb8pQSm/KUDpv+lAKYApv+lAaYBpv2lBKb7pQWm/KUDpv6lAaYBpv6lAqb+pQGmAab/pQGm/qUCpv+lAKYBpv+lAKYCpv2lA6b+pQGmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UCpv6lAaYBWv5ZAVoAWv9ZAlr/WQBaAFoAWgBaAVr/WQBaAVr+WQNa/FkEWv1ZAlr+WQNa/VkDWv1ZAloAWv9ZAVr/WQBaAVr/WQFa/1kAWgFa/lkDWv1ZA1r+WQBaAFoBWv9ZAlr9WQJaAFr+WQRa+lkHWvlZB1r5pQam+6UEpv2lAqb/pQCmAKYApgGm/qUCpv6lAqb/pQGm/qUCpv+lAab/pQCmAKYApgGm/6UApgGm/qUCpv+lAKYBpv6lA6b8pQSm/KUEpv2lAqb+pQKm/qUCpv6lAqb+pQKm/aUDpv6lAaYApv+lAab/pQGm/6UCpv2lA6b+pQCmAqb+pQGmAKYApv+lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYApgGm/aUEpvylAqYApv+lAaYApgCm/qUDpv2lA6b+pQGm/6UBpgCm/6UBpv+lAaYApgCm/6UBpgCm/6UDpvulBab8pQOm/6X/pQKm/aUEpv2lAaYBpv6lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b+pQCmAab/pQKm/aUDpv2lA6b+pQKm/aUEpvulBKb+pQGmAKb/pQGm/6UCpv2lBKb8pQOm/qUBpgCmAKYApgCmAKYApgCmAKYApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUCpv+lAKYCpvylBKb9pQKmAKb+pQKm/qUCpv+lAKYApgCmAKYApgCmAFoAWgBaAVr+WQJa/1kAWgFa/lkCWv9ZAVr/WQBaAFoBWv5ZA1r9WQJa/1kBWv9ZAVr/WQFaAFr/WQBaAlr9WQNa/lkAWgFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFa/1kBWv5ZAlr/WQBaAFoAWgBaAKYApv+lAqb+pQKm/aUDpv+lAKYApv+lAqb/pQCmAab9pQSm/KUDpv+lAKYApgCm/6UCpv6lAqb/pf+lA6b7pQam+6UDpv+lAKb/pQOm/KUEpv2lAqb/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgCmAab+pQOm/aUCpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCmAKb/pQKm/aUEpvylAqYApv+lAqb+pQGm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQCmAab/pQGmAKb+pQOm/aUDpv6lAKYBpv+lAab/pQGm/6UCpv6lAaYApv+lA6b8pQSm/KUDpv6lAqb+pQKm/qUBpgCm/6UCpv6lAaYApv+lAaYApv+lAqb+pQGmAKYApgGm/qUCpv6lA6b+pQGm/6UApgGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b9pQKm/6UBpgCm/6UBpv6lA6b+pQKm/aUDpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAqb9pQOm/aUCpgCmAKb/pQGm/6UBpgCm/qUDpv5ZAVoAWv5ZA1r+WQFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQFaAFr+WQRa+1kFWvtZBFr+WQBaAVr/WQBaAlr9WQJaAFr/WQJa/VkDWv5ZAlr+WQJa/VkDWv5ZAVoAWgBa/1kBWv9ZAVoAWv+lAab/pQGm/6UBpv+lAqb+pQCmAab/pQKm/6X/pQGm/6UBpgCmAKb/pQGm/6UBpv+lAab+pQOm/aUCpv+lAKYBpv+lAKYApgCmAab+pQOm/KUEpv2lAaYApgGm/6UApgGm/aUEpv2lAaYBpv6lAqb+pQGmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKb/pQKm/qUBpgCm/6UBpv+lAqb+pQGm/6UApgKm/aUDpv2lA6b9pQOm/aUCpv+lAab/pQKm/aUDpv2lA6b+pQKm/qUBpv+lAab/pQGm/6UBpgCm/qUDpv2lAqYApv6lA6b9pQKm/6X/pQOm/aUCpv+l/6UDpvylBKb9pQKm/6UApgCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQGm/6UBpgCm/6UBpgCm/6UBpgCm/6UCpv6lAaYApgCmAKb/pQKm/qUBpgCm/6UBpgCm/qUEpvulBKb9pQKm/6UApgGm/qUDpvylBKb9pQKm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAKYBpv6lAqb/Wf9ZA1r8WQRa/VkBWgFa/1kAWgFa/1kBWv9ZAVr+WQNa/VkCWv5ZAlr+WQJa/lkCWv5ZA1r8WQRa/VkCWv9ZAFoBWv9ZAFoAWgBaAVr/WQBaAFoAWgBaAFoAWgBaAVr+WQJa/1kBWv9ZAVr/WQJa/lkBpv+lAaYApgCm/6UBpv+lAaYApv+lAaYApv+lAqb+pQGmAab9pQSm/KUDpv+l/6UBpv+lAaYApv+lAab/pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpv+lAqb+pQGm/6UBpgCmAKb/pQGmAKb/pQGmAKb/pQOm+6UEpv6lAaYBpv2lA6b9pQOm/6UApgCm/6UCpv6lAqb/pQCmAab/pQCmAab/pQKm/qUCpv6lAaYBpv2lBKb8pQOm/qUApgGm/qUDpv2lAaYBpv6lAqb/pQCmAab+pQKm/6UApgKm/KUEpv6lAab/pQCmAab/pQKm/aUCpv+lAab/pQKm/KUFpvylA6b+pQGm/6UBpgCm/6UBpv+lAKYBpv6lAqb/pQCmAab+pQOm/aUDpv6lAKYCpv2lBKb8pQKmAKb/pQKm/qUBpgCm/6UCpv+lAKYApgCmAKYBpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKb/pQOm/KUDpv6lAaYApgCm/6UCpv2lBKb8pQOm/qUApgGmAKb/pQGm/6UApgGmAKb+pQSm+6UFpvylAqYApv+lAab/pQGm/6UBpv+lAKYCpv2lBKb7WQRa/lkBWgBa/1kBWv9ZAlr9WQNa/lkBWgBa/lkDWv5ZAVr/WQFa/lkDWvxZBVr7WQVa+1kDWv9ZAVr/WQFa/lkDWv1ZAloAWv9ZAlr9WQNa/lkBWgBa/1kCWv5ZAVr/WQFa/1kBWv9ZAVr/WQBaAFoApgGm/6UApgCmAKYApgGm/6UApgGm/qUDpv2lAqb/pQCmAab+pQOm/aUCpv6lA6b9pQSm+6UFpvylA6b+pQGmAab9pQSm/KUDpv6lAab/pQKm/aUCpgCm/qUDpv2lAqb/pQGm/qUDpv6lAKYBpv+lAKYCpvylBKb9pQKm/6UApgGm/qUCpv+lAKYApgGm/qUCpv6lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAKYApgGm/qUCpv+l/6UDpvylBKb9pQOm/KUEpv2lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv+lAKYApgGm/6UApv+lAqb+pQKm/qUBpv+lAqb9pQOm/qUApgKm/aUDpv6lAab/pQKm/qUBpv+lAab/pQKm/qUBpv+lAab/pQKm/qUBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lAqYApv+lAaYApv6lA6b9pQKmAKb/pQCmAab/pQCmAqb8pQWm/KUCpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb8pQam+aUFpv6lAKYBpv+lAKYBpv+lAaYApv6lA6b9pQOm/qUBpv+lAab/pQKm/VkDWv5ZAVoAWv9ZAVoAWgBaAFr/WQFaAFr/WQJa/VkEWvxZA1r+WQFaAFoBWv5ZAlr/Wf9ZA1r9WQJa/1kAWgBaAlr8WQVa+1kDWgBa/lkDWvxZBFr8WQRa/FkEWvxZA1r+WQFaAFoAWv9ZAlr+WQBaAqb+pQGmAKb/pQKm/aUDpv6lAaYBpv2lA6b+pQKm/6UApgCmAKYBpv+lAab/pQCmAKYBpv6lA6b8pQOm/6UApgCm/6UCpv6lAqb+pQGmAKb/pQKm/qUCpv6lAqb+pQKm/qUCpv+lAab+pQKm/6UApgGm/qUCpv6lA6b9pQKm/6UApgGm/6UBpv+lAKYApgGm/6UApgCmAKYApgGm/aUFpvqlBab+pf+lA6b8pQOm/6X/pQKm/6UApgCmAKb/pQOm/KUEpv2lAqb+pQKm/qUCpv6lAqb+pQKm/aUDpv2lA6b/pf+lAab/pQGmAKYApv+lAqb+pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAKYBpv6lA6b9pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAKYBpv+lAab/pQCmAab/pQGmAKb+pQOm/aUDpv6lAab/pQGm/6UCpv2lBKb7pQam+qUFpvulBKb/pf+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv+l/6UDpvxZBFr8WQNa/1kAWgFa/VkDWv5ZAlr+WQJa/lkCWv5ZAVoAWv9ZAVoAWv9ZAVr+WQJaAFr/WQFa/lkCWgBa/1kBWv9ZAFoBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/lkDWv1ZAlr/WQBaAVr/WQFa/1kBWv+lAKYApgGm/6UBpv+lAKYBpv6lA6b+pQGm/6UApgGm/6UCpv2lA6b9pQOm/qUCpv2lA6b+pQGmAKb/pQGmAKYApv+lAaYApgCmAKYApgCmAKYApgCmAab+pQGmAab9pQSm/KUDpv+lAKYApgCmAKYBpv+lAab+pQKm/6UApgCmAKYApgCmAKYApgCmAab+pQKm/qUDpvylBKb8pQSm/aUCpv+l/6UDpv2lA6b9pQKm/qUCpv+lAKYApv+lAab/pQKm/qUBpgCm/6UCpv2lBKb8pQSm/KUDpv6lAaYApgCmAKYApv+lAqb+pQKm/qUCpv2lBKb8pQOm/6X/pQKm/qUCpv6lAqb9pQSm/KUEpvylAqb/pQGmAKYApv6lA6b+pQGmAab8pQWm/KUEpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b8pQSm/aUDpv6lAKYBpv6lA6b+pQGmAKb/pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UCpv6lAaYApgCmAKb/pQKm/aUEpvylA6b9pQOm/aUEpvylA6b9pQKm/6UCpvylBab7pQOmAKb+WQJa/1kAWgFa/1kAWgFa/1kBWv9ZAFoCWv1ZA1r9WQNa/lkAWgFa/lkDWv1ZAlr+WQNa/VkCWv5ZAlr/WQFa/1kAWgFa/1kBWv9ZAVoAWv9ZAVr/WQJa/VkDWv1ZAloAWv9ZAVr/WQFa/1kBWgBa/1kCpv2lBKb8pQOm/6X/pQKm/qUBpgGm/qUCpv6lAqb/pQCmAKYApgGm/6UApgCm/6UCpv+lAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAab/pQKm/aUEpvulBqb6pQWm/KUDpv+l/6UBpgCmAKb/pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpv+lAab/pQGm/6UBpgCm/qUDpv2lA6b9pQOm/aUCpv+lAKYApgGm/qUCpv+l/6UDpv2lAqb/pQCmAqb9pQKmAKb+pQWm+aUHpvmlB6b6pQWm/KUCpv+lAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAab+pQKm/6UApgGm/qUCpv6lAqb+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApv+lAaYApgCmAKYApv+lAaYApv+lA6b8pQOm/qUBpgCmAKYApgCmAKYApgCmAKYApgCm/6UDpvylBKb8pQOm/6X/pQKm/qUCpv6lAqb+pQGmAab9pQSm/aUBpgGm/aUEpvylA6b/pf+lAqb9pQOm/qUBpv+lAaYApgCm/6UBpv+lAqb+pQGmAKYApv+lAqb9pQOm/1n/WQFa/1kBWgBa/1kBWv5ZBFr8WQNa/lkBWgBaAFoAWgBaAFoAWgBaAFoAWv9ZA1r7WQZa+lkEWv5ZAVoAWgBa/1kCWv1ZBFr8WQRa/FkEWv1ZAlr/WQBaAFoBWv9ZAVr/WQBaAVr/WQFa/1kBWv5ZA6b8pQWm+6UEpv2lAaYBpv+lAKYCpvylBKb+pQCmAqb+pQCmAqb9pQOm/qUBpv+lAqb9pQKmAKb/pQGmAKb/pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUEpvylA6b+pQKm/qUCpv2lBKb8pQOm/qUBpgCmAab9pQOm/qUBpgGm/qUCpv+lAKYApgCmAab+pQOm/aUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQCmAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAab/pQGm/qUCpv+lAab/pQCmAKYApgGm/6UApgCmAab+pQOm/KUEpv2lAqb+pQKm/6UApgCmAKYApgGm/qUCpv+l/6UCpv6lAqb/pf+lAaYBpv6lA6b8pQOm/6UApgCmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab/pQCmAab+pQKm/6UBpv+lAab+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYBpv+lAKYBpv6lAqb/pQGm/6UBpv+lAKYBpgCm/6UBpgCm/qUDpv6lAKYCpv2lA6b9pQOm/aUDpv6lAKYCpv6lAab/pQGm/6UBpgCm/lkDWv1ZAlr/WQFa/1kAWgFa/lkDWv1ZAlr/WQFa/1kAWgFa/lkDWv1ZAlr/WQBaAVr+WQNa/VkCWv9ZAVr/WQBaAVr/WQFa/1kAWgFaAFr/WQFa/lkDWv5ZAlr+WQFa/1kBWgBaAFoAWv9ZAVr/WQJa/qUCpv6lAKYCpv2lBKb8pQOm/aUCpv+lAab/pQGm/6UBpv+lAab/pQKm/qUCpv2lBKb9pQGmAab+pQOm/aUBpgGm/qUDpv2lAqb/pQGm/qUDpv2lAqb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UApgCmAqb9pQSm+6UFpvylBKb8pQOm/6UApgCm/6UBpgCmAKb/pQGm/6UBpgCm/6UBpgCm/qUEpvulBab8pQKmAKYApv+lAqb9pQOm/6UApgCmAKb/pQKm/qUCpv6lAaYBpv2lBKb9pQKm/6UApv+lA6b8pQWm+qUFpv2lAaYBpv6lAaYBpv6lA6b8pQSm/KUFpvulBKb+pQCmAab/pQGm/6UBpv+lAaYApv6lA6b9pQOm/aUDpv2lA6b9pQOm/aUDpv2lAqb/pQCmAab+pQKm/qUBpgCmAKb/pQKm/aUDpv+lAKYApgCm/6UDpvylBKb9pQGmAab9pQSm/KUEpv2lAqb+pQKm/6UBpv6lAqb/pQGm/6X/pQKm/qUCpv+l/6UCpv2lA6b/pQCm/6UBpv+lAqb/pf+lAaYApgCm/6UCpv6lAqb/pQCmAKYApgFa/VkFWvtZA1r+WQJa/lkDWvxZA1r+WQNa/FkEWvxZA1r/Wf9ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVr/WQFaAFr/WQJa/VkDWv5ZAlr+WQJa/VkEWvxZBFr8WQNa/1n/WQJa/VkEWv1ZAlr+WQGmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAab+pQKm/6X/pQOm/aUCpgCm/qUDpv2lA6b9pQOm/aUDpv2lAqb/pQCmAab/pQCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAaYApv6lA6b9pQOm/qUBpv+lAab/pQKm/aUDpv2lAqYApv+lAab/pQCmAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UCpv2lBKb7pQSm/qUBpgCm/6UApgKm/aUEpvylA6b+pQKm/6UApgCmAKYApgCmAKYApgCm/6UBpv+lAqb+pQCmAab+pQOm/qUBpv+lAab/pQGmAKb/pQKm/qUCpv2lA6b+pQKm/qUBpgCm/6UDpvylA6b/pQCmAKYBpv2lBKb+pf+lA6b8pQOm/6UBpv6lA6b8pQOm/6UApgGm/qUCpv2lBKb8pQOm/6X/pQKm/qUBpgCmAKYApgGm/aUDpv6lAaYBpv6lAaYApv6lA6b+pQGmAKYApv6lA6b9pQOm/6X/pQGm/6UBpgCm/6UBpv+lAqb+pQGm/6UBpgCmAab+pQKm/qUCWv5ZA1r9WQNa/VkCWv9ZAVr/WQJa/VkEWvtZBFr+WQJa/lkCWv5ZAVoAWgBa/1kCWv5ZAVoBWv1ZBFr8WQNa/lkBWgBaAFr/WQFa/1kBWgBa/1kCWv1ZA1r+WQJa/lkCWv1ZBFr8WQRa/FkDWv5ZAlr+pQKm/qUBpgGm/aUEpvylA6b+pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpv6lA6b8pQWm+6UEpv6lAab/pQGmAKb/pQKm/qUBpgCm/6UCpv2lBKb7pQWm/KUDpv2lA6b9pQOm/aUCpv+lAab/pQGm/6UApgGm/6UCpv2lBKb8pQKmAab8pQem+KUHpvulA6b/pf+lAqb+pQKm/qUCpv6lAaYApv+lAqb+pQGmAKYApgCmAKb/pQKm/qUDpvylBKb8pQSm/KUEpv2lAqb+pQKm/qUCpv+lAKYApgCmAKYApgGm/qUBpgCm/6UCpv2lBKb8pQSm/KUDpv6lAaYBpv6lAaYApv+lAqb+pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UApgKm/KUEpv2lAqYApv6lA6b9pQKmAKb+pQOm/aUBpgGm/qUCpv6lAqb+pQKm/6X/pQOm/aUCpv6lAqb+pQKm/qUBpgCmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAaYApgCm/6UBpv+lAaYApv6lA6b9pQOm/aUCpgCm/6UCpv6lAaYApv+lAqb/pQCmAKb/pQGmAVr+WQJa/lkBWgBaAFoAWgBaAFoAWgBaAFoBWv5ZA1r8WQRa/lkAWgJa/FkFWvtZBVr7WQVa+1kFWvxZAlr/WQBaAlr9WQNa/VkCWgBa/lkDWv1ZAlr/WQBaAVr/WQBaAFoAWgFa/lkDWv1ZAlr+WQJa/qUDpv2lAqb+pQGmAab/pQGm/qUCpv+lAab/pQGm/qUDpv6lAaYApv6lAqYApgCmAKYApv6lA6b+pQKm/qUCpv6lAaYBpv2lA6b+pQGmAKb/pQGmAKYApgCm/6UBpgGm/6UApgCmAKYBpv+lAKYBpv+lAab/pQGm/6UBpgCm/qUEpvqlB6b6pQSm/aUCpv6lAqb+pQKm/qUBpv+lAqb+pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAKYBpv+lAab+pQKm/6UApgGm/qUCpv+lAKYBpv+lAKYCpvylBab8pQKmAKb/pQGmAKYApv+lAqb9pQOm/qUBpgCmAKb/pQGm/6UCpv6lAqb9pQSm/KUEpvylBKb8pQSm/aUCpv+lAab/pQCmAqb9pQKm/6UApgKm/aUBpgGm/aUFpvulA6b/pQCmAKYApgCmAab/pQCmAab+pQOm/qUBpgCm/6UBpgCmAKb/pQKm/aUDpv6lAaYApv+lAKYBpgCmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lA6b+pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQGmAab9pQWm+qUFpvylBKb8pQRa/FkDWv5ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVoAWv9ZAlr+WQFa/1kBWgBaAFr/WQFaAFr/WQJa/lkBWgBa/1kBWgBa/1kBWv9ZAVr/WQFaAFr/WQFa/1kBWgBaAFr/WQFaAFr/WQJa/VkDWv6lAab/pQGm/6UBpv+lAKYBpv+lAab/pQCmAab/pQCmAab+pQOm/aUCpv+l/6UDpvylBab7pQOm/6UApgCmAKYApgCmAKYApv+lAqb/pf+lAab/pQGmAab+pQGm/6UBpv+lAaYApv+lAqb9pQOm/aUDpv6lAqb+pQGm/6UBpgCmAKb/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAab+pQOm/KUEpv2lA6b9pQKm/qUDpv2lA6b9pQKm/6UApgCmAab/pQCmAKb/pQOm/KUEpvylA6b/pQCmAKYApv+lAqb/pQCmAKYApgCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQCmAab/pQCmAab+pQOm/qUBpv+lAab/pQGmAKYApv+lAab/pQGmAKYApv+lAab/pQGm/6UCpvylBKb9pQOm/aUCpv6lAqYApv6lAqb+pQGmAab+pQGmAab9pQSm/aUBpgGm/qUCpv+lAKYBpv6lAqb/pQGm/6UBpv+lAqb+pQGm/6UCpv+lAKYAWv9ZAlr/WQBaAFoAWgBaAVr+WQJa/lkBWgBaAVr+WQJa/VkDWv9ZAFoAWgBaAFoBWv5ZA1r8WQVa+1kEWv5ZAFoBWv9ZAVoAWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAlr+WQJa/lkCWv9ZAFoAWgBaAFoApgGm/qUCpv+lAKYApgCmAKYBpv+lAKYApgCmAKYApgCmAKYApgCmAKb/pQGmAKYApgCm/6UBpv+lAqb9pQOm/qUBpv+lAab/pQKm/aUDpv6lAaYApv+lAaYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYBpvylBab8pQOm/qUBpv+lAqb9pQSm/KUDpv6lAaYApgCm/6UCpv6lAqb/pf+lAqb+pQKm/qUCpv2lBKb8pQOm/qUBpgCmAKb/pQKm/qUCpv6lAaYApv+lA6b8pQSm/KUDpv6lAaYApv+lAqb+pQGm/6UBpgCmAKYApv+lAaYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKb/pQGmAKb/pQKm/qUApgKm/aUDpv6lAab/pQGm/6UBpv+lAKYCpv2lA6b+pQCmAqb+pQGmAKb/pQGmAKb/pQGmAKb+pQOm/KUFpvylAqb/pQCmAKYBpv+lAKYCpvylBKb9pQKm/6UBpv+lAKYApgCmAab/pQCmAKYApgCmAab+pQKm/qUCpv6lA6b8pQOm/6UApgGm/qUCpv6lAqb/pf+lA6b9WQJa/lkCWv5ZA1r9WQJa/1kAWgFa/1kBWv5ZAlr/WQBaAVr/WQBaAVr+WQJaAFr+WQNa/VkCWgBa/1kBWv9ZAVr+WQNa/lkBWv9ZAFoAWgJa/VkDWv1ZAloAWv5ZBFr7WQVa/FkCWgFa/VkEWvxZA1r/pQCmAKYApv+lA6b8pQSm/KUDpv+lAKYApgGm/qUDpvylBab7pQWm+6UEpv6lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUDpvylBKb9pQKm/qUBpgCmAKYApgCm/6UDpvylBKb9pQKmAKb/pQCmAqb9pQSm+6UFpvulBqb5pQem+qUFpvylA6b+pQKm/qUBpgCmAab/pQCm/6UCpv6lBKb6pQam+qUFpv6l/6UDpv2lAqb+pQGmAKYBpv+l/6UCpv6lAqb/pf+lAqb/pQCmAab+pQKm/qUDpv2lAaYBpv6lA6b9pQKm/6UBpv+lAab/pQGm/6UCpvylBab7pQSm/qUApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQGm/6UBpv+lAaYApv+lAaYApv+lAqb9pQSm/KUEpv2lAqb/pQCmAKYApgGm/6UApgCm/6UDpvylBKb9pQGmAab+pQKm/6UApgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQGmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab/pQCmAKYApgGm/qUCpv6lAqb+pQKm/qUCpv+l/6UCpv+lAKYApv+lAlr+WQJa/lkBWgBa/1kBWgBa/1kCWv1ZBFr8WQNa/lkBWgFa/1kAWgBa/1kCWv9ZAFr/WQFa/1kCWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/lkDWv1ZA1r+WQFaAFr/WQFaAFr/WQFaAFr/WQFa/1kBWv9ZAqb9pQOm/qUBpgCm/6UCpv6lAaYApgCmAKYApgCmAKYApv+lAaYApgCmAKb/pQKm/aUDpv6lAaYBpv6lAaYApv+lAqb/pQCmAKYApgGm/qUDpvylBKb9pQKm/6UApgCmAKYApgGm/qUCpv6lAqb/pQCmAab+pQKm/6UApgCmAab+pQOm/KUDpv+lAKYBpv6lAqb/pQCmAKYApgGm/6UApgCmAKYApgGm/qUCpv+lAKYBpv6lAqb+pQOm/aUDpvylBKb+pQCmAab+pQOm/aUDpv2lA6b9pQOm/aUDpv+l/6UCpv2lA6b/pQCmAKb/pQKm/qUCpv6lAKYCpv6lAaYApv+lAqb9pQOm/aUDpv+l/qUDpv2lA6b+pQGm/6UCpv2lBKb8pQSm+6UFpvylBKb8pQKmAKb/pQGm/6UBpgCm/qUCpv+lAab/pQGm/6UBpv+lAKYBpv+lAab/pQCmAKYApgGm/qUDpvylBKb+pQCmAKYBpv6lAqYApv6lA6b9pQGmAab/pQCmAab+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYBpv6lAqb/pQGm/6UApgCmAab/pQGm/6UApgJa/VkDWv5ZAVoAWv9ZAlr9WQNa/lkBWv9ZAlr9WQNa/lkAWgFaAFr/WQFaAFr/WQFaAFr/WQJa/lkBWgBaAFoAWgBa/1kCWv5ZAlr+WQFaAFoAWgBaAFr/WQJa/lkCWv5ZAVoAWgBaAFr/WQJa/lkCWv6lAqb+pQKm/qUBpgGm/6X/pQOm/KUDpv+lAKYBpv+lAKYApgCmAab/pQCmAKYApgGm/qUCpv6lAqb/pQCmAKYBpv6lAqb/pQCmAab+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAab/pQKm/KUFpvulBab8pQOm/qUBpgCm/6UCpv6lAqb+pQKm/aUDpv+lAKYApgCm/6UCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/6UApgCm/6UDpvylBab6pQWm/aUCpv6lAqb/pQCmAKYApv+lA6b8pQSm/KUDpv6lAqb+pQGmAKYApgCmAKb/pQOm/KUEpvylBKb9pQGmAab+pQKm/qUCpv+lAKYApgCmAKYBpv+lAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgGm/6UBpgCm/6UBpgCm/6UCpv6lAaYApv+lAab/pQGmAKb/pQCmAab/pQGm/6UBpv+lAaYApv6lBKb8pQOm/6X/pQGmAKb/pQKm/qUApgKm/aUDpv2lA6b9pQOm/qUBpgCm/6UBpv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UBWgBaAFoAWv9ZAVoAWgBaAFoAWv9ZAlr+WQJa/lkBWgBaAFoAWgBaAFoBWv5ZAlr+WQNa/VkCWv9ZAFoBWv9ZAFoBWv9ZAFoBWv5ZAloAWv5ZA1r8WQNa/1kAWgFa/lkCWv5ZAlr+WQJa/1kAWgFa/lkCpv+lAab+pQOm/aUCpgCm/qUCpgCm/qUDpv2lAqb/pQGm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgCmAab+pQOm/KUDpv+lAKYBpv6lAaYApgGm/qUCpv6lAaYApgCmAKYApgCm/6UBpgCmAKYApgCm/6UCpv6lAqb+pQKm/qUCpv6lA6b9pQKm/6UApgKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKb/pQGmAKb/pQKm/aUDpv6lAqb9pQOm/qUBpgCm/6UBpv+lAaYApgCmAKb/pQGmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAab9pQOm/aUEpvylBKb7pQSm/6X/pQOm+6UEpv6lAaYApv+lAaYApgCm/6UCpv2lBKb9pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpgCm/qUDpv2lAqYApv+lAKYCpv2lA6b+pQCmAab/pQGm/6UApgGm/6UApgCmAab+pQSm+qUGpvulBKb9pQKm/6UApgGm/6UApgGm/6UBpv+lAKYApgGm/6UBpv6lAqb/pQGm/6UApgGm/6UBpv+lAFoBWv5ZA1r8WQRa/VkBWgFa/lkCWv9ZAVr+WQJa/lkCWv9ZAFoAWv9ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVoBWv1ZBFr8WQRa/VkCWv5ZAlr+WQJa/1kAWgFa/1kAWgFa/lkDWv1ZAlr/WQBaAab+pQKm/6UApgGm/6UBpgCm/6UBpv+lAab/pQKm/qUApgGm/6UBpgCm/6UBpv+lAKYBpv+lAqb9pQKm/qUCpgCm/6UBpv6lAqb/pQGm/6UBpv6lA6b9pQOm/aUDpv2lBKb8pQOm/qUBpgCmAKYApgCm/6UBpgCmAKb/pQGm/6UBpgCm/6UApgKm/qUBpgCm/6UCpv6lAaYApv+lAqb+pQGmAKb/pQGmAKYApgCm/6UBpgCmAKYApv+lAqb+pQKm/6X/pQKm/qUBpgKm/KUDpv6lAaYBpv+l/6UBpgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv+lAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQCmAab/pQGm/6UBpv+lAqb9pQOm/aUDpv+l/6UCpv6lAaYApv+lAqb/pf+lAqb+pQGmAKYApgCmAab+pQKm/6UApgGm/qUDpv2lAaYApgCmAKYBpv2lA6b9pQSm/aUBpgCm/6UCpv2lBKb8pQSm/aUBpgCmAKYApgCmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv6lAaYApgCmAKb/pQKm/qUDpvylA6b/pQCmAab+pQKm/1kAWgFa/lkDWv1ZAlr/WQBaAVr/WQBaAVr+WQNa/VkCWv9ZAVr/WQFa/1kAWgJa/VkCWv9ZAFoBWv9ZAFoAWgBaAVr+WQNa/FkEWv1ZAlr+WQNa/VkCWv9ZAFoBWv9ZAVr/WQFa/1kAWgFaAFr/WQFa/qUCpgCm/6UBpv+lAKYBpv+lAKYBpv+lAKYBpv6lAaYBpv6lAqb+pQGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b9pQSm+6UFpvylA6b+pQGmAKb/pQKm/aUDpv6lAqb+pQKm/aUDpv+lAKYBpv6lAqb+pQGmAab+pQOm/KUDpv6lA6b8pQSm/aUBpgKm/KUDpv+lAKYBpv6lAqb+pQOm/aUDpvylBKb+pQGm/6UApgGm/6UBpv+lAaYApv+lAKYApgKm/qUCpvylBKb+pQGmAKb/pQGmAKYApv+lAqb+pQKm/6X/pQKm/6UApgGm/aUFpvulBKb+pf+lA6b8pQSm/qUApgGm/qUCpv+lAKYApgCmAKYApgCmAKb/pQKm/qUCpv2lBKb8pQOm/6UApgCmAab9pQSm/aUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUDpvylBKb9pQKm/6UApgCmAKYBpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgGm/6UBpv+lAKYBpv+lAab/pQCmAKYApgCmAab9pQOm/qUCpv+l/6UBpgCmAKYApgCm/6UCpv2lBKb8pQNa/lkBWgBaAFoAWgBaAFoBWv5ZAlr+WQNa/VkDWvxZBFr9WQNa/lkAWgBaAVr+WQNa/FkEWv1ZAlr+WQFaAVr+WQJa/lkBWgFa/lkCWv1ZA1r+WQFaAVr+WQFaAFr/WQJa/lkCWv5ZAlr/WQBaAFoAWgCmAab/pf+lAqb+pQOm/aUCpv6lA6b9pQOm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQGm/6UApgKm/aUEpvulBab8pQKmAab9pQSm+6UFpvulBab8pQKm/6UApgCmAab+pQKm/qUCpv6lAaYApv+lA6b7pQWm+6UEpv+l/6UBpgCm/qUDpv6lAaYApv+lAaYApv+lAab/pQGm/6UBpv6lA6b9pQKm/qUCpv6lAqb/pf+lA6b8pQSm/KUEpv2lAqb/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv6lA6b8pQSm/qUApgGm/qUCpv+lAab/pQCmAKYBpv6lA6b9pQKm/6UApgGm/6UBpv6lAqYApv+lAab/pQCmAab/pQGm/6UBpv+lAab+pQOm/KUFpvulBKb+pQCmAab/pQCmAqb9pQOm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAab+pQKmAKb/pQGm/6UApgGm/6UBpv+lAab/pQCmAKYBpv+lAKYBpv6lAqb/pf+lA6b8pQSm/aUBpgCmAKYApgCmAKb/pQOm/KUEpvylA6b+pQKm/qUCpv6lAaYApgCm/6UCWv1ZA1r+WQFaAFr/WQJa/VkDWv5ZAVoBWv1ZA1r+WQFaAFr/WQJa/VkEWvxZBFr8WQNa/lkCWv5ZAlr+WQJa/lkCWv5ZAlr+WQJa/lkCWv5ZAlr/WQBaAFoAWgBaAFoAWgFa/VkEWvtZBVr9WQFa/1kBpv+lAqb9pQOm/aUDpv6lAKYCpv2lA6b+pQGm/6UBpv+lAqb+pQGm/6UBpgCmAKYApgCm/6UBpgCmAKYBpv2lA6b+pQKm/6UApgCmAKYBpv+lAab/pQGmAKb/pQKm/qUBpgGm/aUFpvqlBab9pQGmAab+pQKm/6UApv+lAqb+pQKm/qUBpgCm/6UBpv+lAab/pQGm/6UApgGm/qUCpv+lAKYBpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UBpv+lAqb9pQSm+6UFpv2lAaYApv+lAqb+pQKm/qUBpgCmAKb/pQKm/aUDpv6lAaYApv+lAab/pQGmAKYApv6lA6b9pQKmAab8pQWm/KUDpv2lBKb7pQWm/KUCpgCm/6UBpv+lAaYApv+lAaYApv+lAqb9pQOm/qUBpv+lAab+pQOm/aUDpv2lA6b9pQOm/aUDpv2lA6b+pQCmAqb9pQOm/aUDpv6lAaYApv6lA6b9pQKm/6UBpv6lA6b8pQSm/qUBpv+lAqb9pQSm/KUDpv6lAqb+pQKm/qUCpv6lAqb+pQGmAab+pQKm/qUCpv6lAqb+pQGmAKb/pQGmAKb+pQOm/aUCpv+lAVr+WQNa/lkAWgFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kBWv5ZAlr/WQBaAVr+WQJa/1kAWgBaAVr+WQJa/lkCWv5ZA1r8WQRa/FkDWv9ZAFoAWgFa/VkEWvxZBFr9WQJa/lkCWv5ZAlr+WQFaAFr/WQJa/qUBpgCm/6UBpgCm/6UCpv2lBKb8pQOm/qUApgKm/qUBpgCmAKb/pQKm/aUDpv6lAqb9pQOm/qUBpgCmAKb+pQSm/KUDpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQGmAKb/pQGm/6UCpv2lA6b+pQGm/6UBpv6lA6b+pQCmAab+pQOm/aUDpv6lAab/pQGm/6UCpv2lA6b9pQKm/6UApgGm/qUCpv6lAqb/pQCmAab/pQCmAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAKYBpv+lAKYBpv6lAqYApv6lAqb/pQCmAab/pQCmAaYApv+lAab/pQGm/6UBpv+lAaYApv+lAab+pQOm/aUCpv+lAab/pQGm/qUCpgCm/6UBpv+lAKYBpv6lA6b9pQKm/6X/pQOm/aUCpv+lAKYApgCmAab+pQKm/qUBpgCmAKYApgCm/6UCpv2lBKb8pQOm/qUCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYApgCmAKYApgCmAKYApgCmAab+pQKm/6UApgGm/6UBpv6lA6b8pQSm/aUCpv+lAKYApgCmAKYApgGm/qUCpv6lAqb+pQFaAFr/WQJa/lkBWgBa/1kCWv5ZAlr+WQJa/lkCWv1ZA1r+WQFa/1kCWv1ZA1r9WQNa/lkBWgBa/1kCWv5ZAVoAWgBa/1kCWv5ZAlr+WQJa/VkEWvxZA1r/WQBaAFoAWgBaAFoBWv5ZAlr/WQBaAFoAWgCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+l/6UCpv+lAKYApv+lAqb9pQSm+6UFpvylAqb/pQKm/aUDpv2lA6b+pQGm/6UBpgCmAKb/pQGm/6UBpgGm/aUDpv2lA6b+pQGm/6UBpv+lAab+pQOm/aUDpv2lAqb+pQOm/qUApgGm/qUDpv2lAqb/pQGm/qUDpvylBKb+pQCmAab/pQCmAab/pQCmAKYBpv+lAab+pQGmAab/pQCmAKb/pQKm/qUCpv6lAaYApv+lA6b8pQSm/KUDpv6lAab/pQKm/aUCpv+lAKYCpv2lAqb+pQOm/qUBpv+lAKYBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCmAKYApv+lAaYApgCmAKb/pQGm/6UCpv6lAab/pQGmAKb/pQKm/aUDpv6lAKYBpgCm/6UCpv2lA6b+pQKm/qUCpv6lAqb/pQCmAKYApgCmAab/pQCmAKYApgCmAab/pQCmAab/pQCmAab/pQGm/6UApgCmAKYBpv6lAqb+pQKm/qUDpvylBKb8pQOm/6UApgCmAKb/pQKm/qUDpvylBKb8pQOm/6UApgGm/qUBpgCm/6UCpv9ZAFoAWv9ZAVoAWgBaAFr/WQFaAFoAWgBa/1kCWv5ZAlr/WQBaAFoAWv9ZAVoBWv1ZBFr8WQJaAFoAWv9ZAlr9WQRa/VkBWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAVoAWv9ZAlr+WQFaAFr/WQJa/lkCWv6lAqb/pQCmAKYApgCmAab+pQKm/qUBpgGm/qUCpv2lA6b+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/6UCpv6lAaYApv+lAab/pQKm/qUBpgCm/6UBpgCmAKb/pQKm/aUEpv2lAaYApv+lA6b8pQSm/aUBpgCmAKYApgCmAKb/pQKm/qUBpgCm/6UBpgCm/qUDpv6lAKYBpv6lAqb/pQCmAab+pQKm/qUCpv+lAab+pQKm/qUCpv+lAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApv+lAqb+pQKm/aUEpvylA6b+pQGm/6UCpv6lAab/pQGm/6UBpv+lAKYBpgCm/6UBpv6lA6b+pQKm/aUCpgCm/6UBpv+lAKYBpv6lA6b8pQSm/KUEpvylBKb8pQSm/aUBpgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQOm/aUCpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYBpv6lAaYBpv+lAab/pQCmAKYBpv+lAab+pQKm/qUCpv+lAKYBpv+lAKYBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UAWgFa/lkCWv9ZAFoBWv5ZAlr+WQFaAFoAWgBaAFr/WQFa/1kBWgBa/1kCWv1ZA1r+WQFaAFr/WQFaAFr/WQJa/VkCWgBa/1kBWv9ZAFoBWv9ZAVr/WQFa/lkDWv1ZA1r+WQFaAFoAWv9ZAVoAWgBaAVr+pQGmAKYApgGm/qUCpv6lAqb+pQKm/aUEpvylBKb8pQOm/aUEpvylBKb7pQWm/KUEpvylA6b+pQGmAKYApv+lAaYApv+lAqb+pQCmAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/aUEpvulBab8pQOm/6X/pQKm/qUCpv+lAKYApgCmAKYApgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKYApgCmAKYApgCmAab+pQKm/6UBpv6lA6b8pQSm/aUCpv+l/6UCpv6lAqb/pf+lAqb+pQKm/6X/pQOm/KUEpv2lAqb/pQCmAab+pQOm/aUDpv6lAKYBpv+lAqb+pQGmAKb+pQSm+6UEpv+l/qUDpv2lAqb/pQGmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lA6b+pQCmAqb9pQOm/aUCpv+lAKYBpv6lAqb+pQKm/6UBpv6lAqb+pQKm/6UApgGm/qUBpgCmAKYApgCmAKYApgCmAKb/pQOm/KUDpv+l/6UCpv6lAKYCpv6lAqb+pQCmAaYApv+lAqb9pQSm/KUDpv2lA6b+pQGmAKb/pQKm/aUDpv6lAaYApgCmAFoAWv9ZAVoAWgFa/VkEWvtZBlr7WQNa/lkCWv5ZAlr9WQRa/FkEWvxZA1r9WQRa/VkCWv5ZAFoCWv5ZAlr+WQFa/1kCWv5ZAVoAWv9ZAVoAWv9ZAVr/WQBaAlr9WQJaAFr/WQJa/VkDWv5ZAlr+WQFa/6UBpgCm/6UCpv2lA6b9pQOm/aUDpv6lAKYBpv+lAaYApv6lA6b9pQOm/qUBpv+lAqb9pQOm/qUBpgCmAKYApv+lAab/pQKm/qUCpv2lA6b+pQKm/6UApgGm/6UApgGm/6UBpgCm/6UApgGm/6UBpv+lAab+pQKm/qUCpv+lAKb/pQGmAKYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQGmAKb/pQOm+6UEpv6lAKYCpv2lAqb/pQGm/6UBpv6lA6b9pQOm/aUCpv+lAab/pQCmAab+pQKm/qUCpv+lAKYApgCmAKYApgCmAab/pQGm/qUCpv6lA6b9pQOm/KUDpv6lAqb/pQCmAKYApgCmAKYApgCmAab+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv2lA6b+pQCmAqb9pQOm/qUApgGmAKb/pQKm/qUBpv+lAqb+pQKm/qUApgGmAKb/pQGm/6UApgGm/6UApgKm/aUDpv6lAKYBpv+lAqb+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UCpv2lA6b+pQGmAKb/pQGm/6UBpv+lAab/pQCmAab/pQGm/qUCpv9ZAVr/WQFa/1kBWv9ZAVr/WQFa/1kCWv1ZAlr/WQFaAFr/WQBaAVr/WQFa/lkCWv5ZA1r8WQRa/FkDWv9Z/1kDWvxZBFr8WQRa/VkCWv9ZAFoAWgFa/1kAWgFa/lkCWv9ZAFoAWgBaAFoAWgBaAFoAWgCmAKYApgCmAKYApgCmAKYApgCm/6UCpv6lAqb/pf+lAaYBpv6lA6b8pQOm/6X/pQOm/KUEpvylA6b+pQGmAKb/pQGm/6UBpv+lAKYBpv+lAab/pQCmAaYApv+lAab/pQCmAab/pQGm/6UApgCmAab/pQGm/qUCpv+lAab/pQCmAKYApgCmAab/pQCmAab/pQCmAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGmAKb/pQGm/6UBpv+lAab/pQCmAab/pQGm/6UApgGm/6UBpgCm/qUEpvulBab8pQOm/qUBpgCmAKb/pQKm/qUBpgCm/6UCpv6lAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv6lBKb8pQOm/qUBpgCmAKYApv+lAqb+pQGmAKYApv+lAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/aUEpvulBqb6pQWm/KUDpv6lAqb9pQSm/KUDpv+l/6UCpv6lAaYApgCmAKYApgCmAKYApgCmAab+pQOm/aUBpgKm/aUDpv2lAqYApgCm/6UCpv2lBKb8pQOm/6X/WQJa/lkCWv9ZAFoAWgBaAVr+WQJa/1n/WQJa/lkCWv1ZBFr7WQVa/FkCWgBa/1kBWv9ZAVoAWv9ZAFoBWv9ZAVr/WQBaAVr+WQNa/FkEWv1ZAlr/WQBaAVr/WQBaAVr/WQFa/1kBWv9ZAVr+WQJa/1kBpv+l/6UCpv+lAKYBpv6lAqb/pQCmAKYBpv+lAKYBpv6lA6b9pQKm/qUDpv2lAqb+pQKm/6UApgGm/qUCpv+lAab/pQGm/qUCpv+lAaYApv6lAqb/pQCmAab+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYBpv6lA6b9pQOm/aUDpv2lAqb/pQGmAKb+pQOm/aUDpv6lAKYBpv+lAqb9pQOm/aUCpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQOm/qUApgKm/aUDpv6lAab/pQGmAKYApgCm/6UCpv6lAqb+pQGmAKb/pQKm/aUEpvulBab8pQOm/qUBpv+lAqb9pQOm/aUCpv+lAaYApv+lAKYApgGm/6UBpv+lAab/pQCmAab/pQKm/aUCpv+lAqb9pQOm/aUCpv+lAab/pQGm/qUCpv+lAaYApv6lAqYApv+lAab/pQGm/6UBpv+lAab/pQKm/aUEpvulBKb+pQGmAKYApv6lBKb8pQOm/qUBpgCm/6UCpv2lA6b9pQKm/6UBpv+lAKYBpv6lA6b9pQKmAKb9pQWm+6UEpv6lAKYBpv+lAKYCpv2lA6b9pQKm/1kBWv9ZAVr+WQNa/FkFWvtZBFr9WQJa/1kAWgBaAFoAWgBaAVr9WQVa+lkGWvtZA1r/WQBaAVr+WQJa/lkCWv9Z/1kCWv5ZAlr+WQFaAFr/WQJa/lkBWgBa/1kBWgBa/1kCWv1ZAlr/WQBaAVoAWv5ZAqb/pQCmAqb9pQOm/aUDpv2lAqYApv+lAab/pQCmAab/pQGmAKb/pQGm/6UApgGm/6UApgGm/qUDpv2lAqb/pQGm/6UCpv2lA6b+pQGm/6UBpgCm/qUDpv2lAqYApv6lA6b8pQSm/aUDpv6lAKYApgGm/qUDpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpgCm/qUCpv6lA6f9pgOn/aYBpwKn/aYDp/2mAqf/pgGn/6YBp/6mAqf/pgCnAaf+pgKn/qYCp/+m/6YDp/ymA6f/pv+mAqf+pgGnAKcAp/+mAqf9pgOn/qYBp/+mAaf/pgGn/6YApwCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/6mA6f8pgSn/KYDp/6mAacAp/+mAqf9pgKn/6YBpwCn/6YBp/6mA6f9pgSn+6YFp/umBaf8pgOn/aYDp/6mAacAp/+mAqf+pgGnAKf/pgKn/qYCp/6mAKcCp/2mBKf9pgGnAKf/pgGnAaf/pv+mAqf9pgSn/aYBpwGn/aYEp/2mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAaf+pgKn/6YApwGn/qYCp/6mAqf/pv+mAqf/pv+mA1n8WANZAFn9WARZ/VgCWf5YAln9WARZ/FgDWf9YAFkAWf9YAVkAWQBZAFn/WAJZ/lgAWQJZ/VgDWf5YAVkAWQBZ/1gBWQBZAFkAWQFZ/VgFWfpYBln8WAFZAVn+WANZ/lj/WAJZ/lgCWf9Y/1gCWf5YAqf+pgKn/qYCp/+mAKcBp/+mAaf/pgCnAaf/pgKn/aYCpwCn/qYEp/umBaf8pgOn/qYCp/+mAKf/pgOn/aYDp/ymBKf9pgOn/aYBpwGn/6YBp/+mAaf/pgGn/6YBpwCn/6YCp/2mA6f9pgOn/qYBpwCn/6YCp/2mA6f9pgSn+6YGp/mmBqf8pgKnAKf/pgCnAaf/pgGnAKf+pgOn/aYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf+pgOn/KYEp/ymA6f/pgCnAaf+pgGnAaf+pgOn/aYCp/+mAKcApwGn/qYDp/2mAqf+pgKn/qYDp/ymBKf8pgOn/6b/pgOn+6YGp/qmBaf9pgCnA6f8pgOn/qYBpwCnAKcApwCnAKcAp/+mAqf+pgGnAKf/pgKn/aYDp/6mAacBp/6mAqf+pgKn/6YApwGn/qYCp/6mAqf/pgCnAKcAp/+mA6f9pgKn/qYBp/+mAqf/pv+mAqf8pgan+aYGp/ymAqcBp/ymBKf9pgOn/aYCp/+mAaf/pgGn/qYDp/2mAqf+pgOn/aYDp/ymA6f/pgCnAKcApwCnAaf+pgKn/qYDp/2mAqf/pgGnAKcAp/9YAVkAWf9YAln9WARZ+1gGWfpYBFn/WP5YBFn8WANZ/lgBWQBZ/1gBWQBZ/1gBWQBZ/1gBWf9YAVkAWQBZ/1gBWf9YAln9WANZ/lgBWQBZ/1gBWQBZAFn/WAFZ/1gBWQBZ/1gCWfxYBVn8WANZ/1j/WAKn/qYCp/6mAacBp/6mAqf+pgKn/6YApwCnAKcBp/6mAqf+pgKn/qYCp/6mAqf+pgGn/6YDp/ymA6f+pgCnA6f9pgCnAqf+pgKn/6b/pgKn/qYBpwGn/qYCp/6mAacApwCn/6YBpwCnAKf/pgGn/6YApwOn+6YEp/6mAKcCp/2mAqf/pgGn/6YApwGn/qYCp/+mAKcBp/6mAacApwCnAKcAp/+mAacApwCnAKf/pgGnAKf/pgKn/aYEp/ymA6f9pgOn/6YApwCn/6YBpwCnAKcApwCn/6YBpwCnAKf/pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YApwKn/aYDp/2mAqcApwCn/6YBp/+mAaf/pgGn/6YBp/+mAKcBp/6mA6f9pgOn/qYApwGnAKf/pgKn/aYDp/6mAqf+pgGn/6YBpwCnAKcAp/+mAaf/pgKn/qYBpwCn/qYEp/ymA6f+pgGn/6YBpwCn/6YBpwCn/6YCp/6mAacApwGn/6YApwGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgCnAqf9pgOn/aYCpwCn/6YAWQFZ/lgDWf1YAln/WAFZ/lgDWf1YA1n+WABZAVkAWf9YAVn/WAFZAFkAWf9YAln9WARZ/FgDWf5YAln+WAJZ/lgBWQBZAVn9WARZ/FgDWf9Y/1gCWf5YAVkBWf5YAln+WAFZAFkAWQBZ/1gBWQBZ/1gCp/2mAqcAp/+mAqf9pgSn/KYDp/6mAacApwCnAKf/pgKn/aYEp/ymA6f+pgGnAKcApwCn/6YBp/+mAacAp/+mAacAp/+mAqf+pgGnAaf+pgKn/6YApwGn/qYCp/6mA6f8pgSn/aYBpwGn/aYEp/ymBKf8pgOn/qYCp/2mBKf8pgSn/aYCp/6mA6f9pgKn/6YApwGn/6YApwGn/qYDp/ymBKf9pgKn/6YApwGn/qYDp/2mAqcAp/6mA6f8pgSn/aYCp/+m/6YCp/6mAqf+pgKn/qYDp/2mAacApwCnAaf+pgOn+6YGp/qmBaf9pgKn/qYBp/+mAacApwCnAKf/pgGn/6YCp/6mAqf9pgSn/KYDp/6mAacApwGn/aYEp/ymBKf9pgGnAaf/pgGn/6YApwGn/6YBp/+mAaf/pgCnAKcBpwCn/6YApwGn/6YBp/+mAaf/pgGn/qYCp/+mAaf/pgGn/qYCp/6mAqcAp/6mA6f8pgOn/qYBpwCnAaf+pgGn/6YBp/+mAqf9pgOn/aYCp/+mAaf/pgGn/6YApwKn/aYDp/6mAKcBp/+mAacAp/+mAaf/pgKn/aYEp/ymA6f+pgGn/1gCWf1YA1n+WAFZAFn/WAFZAFn/WAFZAFn/WAJZ/VgDWf5YAVkBWf1YBFn8WANZ/lgCWf5YAln+WAFZAFkAWQFZ/lgCWf5YAVkBWf5YAVkAWQBZAFkAWQBZAFkBWf9YAFkAWQBZAFkBWf9Y/1gCWf5YAqf/pv+mA6f8pgSn/KYEp/2mAqf+pgKn/qYCp/6mAqf/pgCnAKcApwCnAaf/pgGn/6YApwGn/6YBpwCn/qYDp/2mA6f+pgCnAqf+pgGnAKf/pgKn/qYCp/6mAacApwCn/6YDp/umBqf6pgSn/6b/pgOn+6YEp/6mAacBp/6mAKcCp/2mBKf8pgOn/qYBpwCnAKcApwCnAKcApwGn/qYCp/+mAaf+pgOn/KYEp/2mAqf+pgOn/KYEp/2mAqf+pgKn/qYDp/2mAqf9pgSn/aYCp/+mAKcBp/6mA6f9pgOn/aYDp/2mA6f+pgGn/6YApwCnAKcBp/6mAqf9pgOn/qYBpwCn/6YBpwCnAKf/pgGnAKf/pgKn/qYApwKn/aYDp/2mAqf/pgCnAaf+pgKn/qYCp/+mAKcApwGn/qYDp/ymBKf9pgKn/6b/pgKn/qYCp/6mAacAp/+mAaf/pgGnAKcAp/+mAaf/pgKn/qYCp/6mAqf+pgKn/aYEp/ymBKf8pgSn/KYDp/6mAacBp/6mAaf/pgGnAKcAp/+mAqf9pgOn/qYCp/6mAqf+pgKn/6YApwCnAKcApwGn/qYCp/6mAqf+pgJZ/lgCWf5YAln+WAJZ/lgBWQBZAFkAWQBZAFkAWQBZ/1gDWfxYBFn9WAFZAVn+WAFZAFn/WANZ+1gFWfxYAlkAWf9YAVkAWf9YAVkAWf9YAln+WAFZAFkAWQBZ/1gBWf9YAln9WANZ/VgDWf5YAVn/WAGnAKcApwCnAKcApwCnAKcBp/6mA6f8pgSn/aYDp/2mAqf+pgOn/aYCp/+mAKcApwCnAKcApwCn/6YCp/6mAqf9pgOn/aYEp/ymAqcAp/+mAacAp/+mAqf+pgKn/qYCp/+mAKcBp/6mAqf/pgCnAKcBp/6mAqf+pgKn/6YApwGn/qYCp/+m/6YDp/ymBKf8pgOn/6YApwCnAKcApwGn/qYDp/2mA6f9pgOn/aYDp/2mAqcAp/+mAaf/pgCnAqf9pgOn/qYBpwCn/6YCp/2mBKf8pgOn/qYBpwCnAKcAp/+mAqf+pgKn/qYCp/6mAqf/pgCnAKcBp/6mA6f9pgKn/6YApwGn/qYCp/6mAqf+pgKn/qYCp/6mAqf/pgCnAaf+pgOn/aYDp/2mAqf/pgGn/6YBp/+mAKcApwGn/qYDp/ymA6f/pgCnAKf/pgKn/aYEp/ymA6f/pv+mAqf9pgSn/KYEp/2mAaf/pgKn/qYCp/6mAacApwGn/aYEp/ymBKf8pgOn/qYBpwCn/6YBpwCn/6YBpwCnAKf/pgKn/qYBpwGn/qYCp/+m/6YCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YApwBZAFkAWQBZAVn9WARZ/FgEWfxYBFn9WAFZAFkAWQBZAVn+WAFZAFkBWf1YBFn8WANZ/1j/WAJZ/lgCWf5YAln+WAJZ/lgCWf5YAln+WAFZAFkAWf9YAln9WANZ/lgAWQJZ/VgCWQBZ/lgDWf1YAlkAWf+mAKcBp/+mAacAp/6mA6f+pgGnAKf/pgGnAKcAp/+mAacAp/+mAqf9pgKnAKf+pgOn/aYDp/2mAqf+pgKn/6YApwGn/qYCp/6mAacBp/6mAqf/pgCnAaf+pgGnAKcApwGn/qYCp/2mA6f+pgKn/qYBpwCn/6YCp/6mAKcCp/2mBKf8pgOn/qYBpwGn/aYEp/ymA6f/pv+mAqf+pgGnAKcApwGn/6YApwGn/6YBp/+mAacAp/+mAaf+pgSn+6YFp/umBKf9pgKn/qYDp/ymBKf8pgOn/6YApwCnAKcApwGn/qYCp/+mAKcBp/6mAqf+pgOn/KYEp/ymA6f/pgCnAKcAp/+mAqf/pgCnAKf/pgGnAKcAp/+mAqf9pgOn/qYBp/+mAqf9pgSn/KYDp/6mAacApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKf/pgGn/6YCp/6mAaf/pgGn/6YCp/2mA6f+pgGnAKf/pgGnAKf/pgOn/KYDp/6mAacApwCn/6YBpwCn/6YBp/+mAacApwCnAKcApwCn/6YCp/6mAqf/pv+mAqf+pgGnAKcApwCnAKcAp/+mAacAp/+mAqf9WANZ/lgBWQBZ/1gBWf9YAln+WAFZ/1gBWf9YAln+WAFZ/1gBWQBZ/1gBWQBZ/1gCWf1YA1n+WAFZAFkAWQBZ/1gCWf5YAln+WAFZAFn/WAJZ/VgDWf5YAVkAWf9YAln9WAVZ+lgGWfpYBVn9WAJZ/lgBp/+mAqf/pgCnAKf/pgKn/qYCp/6mAqf+pgGn/6YCp/6mAacAp/+mAqf+pgGnAKf/pgKn/qYCp/6mAacApwCnAKcApwGn/aYEp/ymA6f+pgKn/aYEp/ymA6f/pv+mA6f7pgan+6YEp/2mAacApwCnAKcBp/6mAqf+pgKn/qYDp/2mAacBp/2mBKf9pgKn/qYCp/6mAacApwCnAKcApwCn/6YCp/6mAacAp/+mAaf/pgGn/6YBp/6mA6f+pgCnAqf9pgOn/qYBpwCn/6YCp/6mAacAp/+mAqf+pgGn/6YBpwCn/6YBp/+mAaf/pgCnAaf/pgGn/6b/pgKn/6YApwGn/qYBpwGn/qYCp/+mAKcApwCnAaf+pgOn/KYEp/2mAacBp/6mAqf+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYBp/+mAKcBpwCn/6YBp/+mAacAp/+mAacApwCnAKf/pgKn/qYCp/6mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwCn/6YBpwGn/aYEp/ymA6f+pgGn/6YCp/6mAaf/pgGn/6YBpwCn/6YBp/+mAaf/pgKn/qYBp/+mAaf/pgGn/1gBWf9YAVn9WAVZ/FgCWQBZ/lgDWf5YAVkAWf9YAln+WAJZ/lgBWQFZ/lgDWfxYA1n/WABZAFkAWQBZAFkAWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAFZAFkAWf9YAln9WANZ/lgBWQBZAFn/WAFZAKf/pgOn/KYDp/6mAacApwGn/6b/pgKn/aYEp/2mAacBp/6mAacAp/+mA6f9pgKn/qYCp/+mAaf/pgGnAKf/pgKn/aYDp/6mAqf+pgGn/6YBpwCnAKcAp/+mAqf+pgKn/qYCp/6mA6f9pgGnAaf/pgGnAKf/pgCnAqf9pgOn/aYDp/2mA6f9pgGnAaf+pgKn/6b/pgKn/qYCp/6mAacApwCnAKcApwCn/6YCp/6mAacAp/+mAqf+pgGn/6YCp/6mAqf+pgCnAqf+pgGnAKf/pgGnAKf/pgGnAKcApwCn/6YCp/2mBaf6pgWn/KYDp/6mAqf+pgGn/6YCp/2mA6f+pgGnAKf/pgGn/6YBp/+mAacAp/+mAaf+pgOn/qYBpwCn/6YBpwCn/6YBpwCnAKcApwCn/6YBpwCnAKcApwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgGn/6YCp/2mBKf7pgSn/6b+pgOn/aYDp/2mA6f9pgOn/qYApwGn/6YCp/2mAqf/pgGnAKf/pgCnAKcBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/+mAacAp/+mAaf/pgFZAFkAWQBZ/1gBWQBZAFkAWQBZAFkAWf9YAVkAWQBZAFn+WAJZ/1gAWQFZ/lgDWf1YAVkAWQBZAFkBWf9YAFkBWf5YA1n9WANZ/VgDWf5YAFkBWf9YAVkAWf9YAVkAWf9YAVkAWQBZAFkAWf9YAln+WAGnAKf/pgOn+6YFp/ymA6f+pgGn/6YBp/+mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgCnAKcApwGn/6YBp/6mAqf/pgCnAaf/pgGn/6YApwGn/6YCp/2mAqf/pgGn/6YCp/2mAqcAp/6mA6f9pgOn/aYDp/2mAqcAp/6mA6f+pgCnAqf9pgKnAKf/pgGn/6YApwGn/6YApwGn/qYCpwCn/qYCp/+mAKcCp/2mA6f8pgWn/KYDp/2mA6f9pgOn/qYApwKn/aYCp/+mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgCnAKcApwCnAKcAp/+mAqf/pgCnAKf/pgKn/6YApwGn/6YApwGn/6YBp/+mAacAp/+mAqf9pgSn+6YFp/umBaf8pgKnAKf/pgGn/6YBpwCnAKf/pgKn/qYBpwCn/6YCp/2mA6f9pgOn/qYBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAKcApwCnAKcBp/6mAqf+pgKn/qYCp/6mAqf+pgGnAKf/pgOn/KYDp/6mAacApwGn/aYFp/qmBaf9pgGnAaf/pgCnAKf/pgGnAaf+pgKn/qYApwKn/qYCp/+m/6YBpwCnAKcBWf5YAVkAWQBZAFkBWf5YAln/WP9YA1n8WARZ/VgBWQFZ/lgCWf5YAln+WAJZ/1gAWQFZ/lgDWfxYBFn9WANZ/lgAWQFZ/1gBWQBZ/lgEWftYBVn7WARZ/lgBWQBZ/lgDWf1YA1n+WABZAVn/WAFZ/1gApwKn/aYDp/6mAKcCp/6mAacAp/+mAacAp/+mAaf/pgGnAKf/pgGn/6YApwKn/aYCp/+mAaf+pgOn/aYBpwGn/qYCpwCn/qYCp/+mAKcApwGn/qYDp/2mAqf+pgKn/6YBp/+mAKcBp/+mAqf9pgKn/6YBp/+mAaf+pgOn/aYCp/+mAKcBpwCn/qYDp/2mAqcAp/6mA6f9pgOn/aYCp/+mAKcBp/+mAaf/pgCnAaf+pgOn/qYApwGn/6YApwKn/aYCp/+mAaf/pgCnAaf+pgOn/aYCp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAacAp/+mAacAp/+mAqf9pgSn/KYDp/2mAqcApwCnAKf/pgGn/6YBpwCnAKcAp/+mAaf/pgKn/qYBp/+mAKcCp/2mA6f9pgKn/6YBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAaf9pgSn/aYCp/6mAqf9pgSn/KYDp/6mAqf+pgKn/qYCp/+mAKcApwCnAaf+pgKn/qYBpwCn/6YCp/2mBKf7pgWn/KYDp/6mAqf9pgOn/6b/pgKn/qYApwOn/KYDp/+m/6YBpwCn/6YCWf5YAFkCWf1YA1n+WAFZAFkAWf9YAVkAWQBZAFn/WAFZAFkAWQBZAFkAWQBZAFkAWQBZAVn+WAFZAFn/WANZ/FgEWfxYBFn9WAJZ/lgCWf9YAFkBWf5YAln+WAJZ/lgCWf5YAln/WABZ/1gCWf5YA1n8pgOn/qYCp/+mAKcApwCnAKcBp/6mA6f9pgOn/aYCp/+mAacAp/6mA6f9pgOn/aYCp/+mAaf/pgCnAaf/pgGn/qYDp/ymBaf7pgSn/aYCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgGn/qYCp/+mAacAp/+mAaf/pgGn/6YBp/+mAaf/pgCnAKcBp/+mAKcBp/6mA6f9pgKn/6YApwKn/aYCp/+mAKcBpwCn/qYDp/2mAqf/pgGn/6YBp/+mAaf/pgGn/6YBp/+mAacAp/6mA6f9pgKnAKf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcAp/+mAqf+pgKn/qYBpwCnAKcBp/2mBKf9pgKn/qYBpwCnAaf/pgCnAKf/pgOn/KYEp/2mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwGn/qYCp/6mAacBp/6mAqf+pgKn/qYCp/2mBKf9pgGnAKcApwCnAaf9pgOn/qYCp/6mAqf+pgKn/qYCp/6mAqf/pgCnAaf+pgKn/6YApwGn/qYCp/+mAKcApwGn/qYCp/6mAqf/pgGn/6b/pgOn/VgCWQBZ/lgCWf9YAVn+WANZ/FgEWf5YAFkAWQFZ/lgDWf5Y/1gCWf9Y/1gDWfxYA1n/WP9YAln/WABZAVn+WAJZ/1gBWf9YAFkBWf5YAln+WAJZ/1gAWQBZAFkAWQBZAFkAWQBZAFkAWQBZAFn/WAJZ/qYCp/6mAacApwCn/6YBpwCn/6YCp/2mA6f+pgCnAqf9pgSn/KYCpwCn/6YCp/6mAacBp/6mAqf+pgKn/6YBp/+mAKcBp/+mAaf/pgCnAacAp/+mAaf/pgCnAqf9pgKn/6YApwGn/qYCp/+m/6YDp/ymA6cAp/6mA6f9pgKn/6YApwGn/qYEp/umBKf9pgGnAaf/pgCnAaf9pgWn+qYHp/mmBqf7pgSn/qYApwKn/KYFp/ymAqf/pgCnAaf/pgCnAaf+pgKn/qYCp/+mAKcAp/+mAqf/pgCnAKcAp/+mA6f8pgOn/6b+pgWn+qYFp/ymAqcApwCnAKf/pgGnAKf/pgKn/aYDp/6mAacApwCn/6YCp/6mAqf+pgGnAaf+pgKn/qYBpwGn/6b/pgKn/qYCp/+m/6YBpwCnAKcAp/+mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGnAKf/pgKn/aYEp/ymBKf8pgOn/qYCp/6mAqf9pgOn/qYBpwCn/6YBpwCn/6YCp/6mAacBp/2mA6f/pgCnAKcAp/+mAqf/pgCnAaf/pgCnAKcBp/6mA6f8pgOn/6b/pgOn/KYEp/2mAqf/pgFZ/lgDWf1YA1n9WANZ/VgCWQBZ/1gCWf1YA1n9WANZ/VgCWf9YAFkBWf5YAln/WABZAVn+WAJZ/lgCWf5YAln+WAFZAFkAWQBZAFn/WAJZ/lgCWf5YAVkAWQBZ/1gCWf5YAln/WABZAFkBWf9YAFkBWf+mAaf/pgGn/6YBp/+mAKcCp/2mAqf/pgCnAaf+pgKn/qYDp/2mAacApwCnAKcBp/6mAaf/pgKn/aYEp/ymAqf/pgGn/6YCp/2mA6f9pgOn/qYBp/+mAaf/pgKn/aYCpwCn/qYEp/umBKf+pgCnAqf9pgKn/6YBp/+mAKcApwCnAaf/pgCnAKcApwCnAaf/pgCnAaf+pgKn/6YApwGn/qYCp/6mAqf+pgKn/qYCp/6mAqf9pgSn/KYEp/ymA6f+pgGnAKcApwCnAKcApwCnAKcApwCnAaf+pgKn/qYCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YApwCnAKcBp/6mAqf+pgKn/6b/pgKn/aYDp/+m/6YCp/2mAqcBp/6mAqf9pgSn/aYCp/+mAKcApwGn/qYDp/2mAqf/pgCnAaf+pgOn/KYEp/2mAacBp/6mAqf/pgCnAKcBp/6mA6f8pgSn/aYDp/2mAqf/pgGn/6YBp/+mAaf/pgGn/6YApwGn/qYDp/6mAKcApwCnAKcCp/ymBKf8pgSn/aYBpwCnAaf+pgOn+6YGp/umA6f/pgCnAKcApwCnAKcAp/+mAqf+pgGnAKf/WAJZ/lgBWf9YAln+WAFZAFn/WAJZ/VgEWftYBVn8WAJZAFn/WAJZ/VgCWQBZ/1gBWf9YAVkAWf9YAVn/WAJZ/lgBWQBZ/1gCWf5YAln/WP9YAln+WAJZ/1j/WAJZ/lgCWf5YAln9WARZ/FgDWf5YAln+pgKn/qYBpwCnAKf/pgKn/aYDp/6mAacAp/+mAacApwCn/6YCp/6mAacAp/+mAacApwCnAKf/pgGnAKf/pgKn/aYDp/6mAKcCp/2mBKf8pgKnAKcApwCn/6YCp/6mAqf9pgOn/qYBpwGn/KYFp/umBaf9pgGn/6YBp/+mAqf/pv+mAqf9pgSn/KYEp/umBqf6pgWn/KYDp/6mAacApwCnAKf/pgGnAKcApwCnAKf/pgKn/qYCp/+mAKcApwGn/6YBp/+mAKcBp/6mA6f8pgSn/aYCp/6mAqf+pgKn/qYCp/6mAacAp/+mAqf+pgGn/6YBp/+mAqf+pgGn/6YBpwCnAKf/pgGnAKcAp/+mAaf/pgGnAKf/pgGn/6YBp/+mAqf+pgGnAKf/pgOn/KYDp/6mAacApwCn/6YBpwCn/6YCp/2mA6f+pgGnAaf+pgGnAKf/pgKn/6b/pgOn+6YGp/umA6f/pgCnAKcApwCnAKcApwCnAKcApwGn/qYBpwCnAKcBp/6mAqf+pgKn/6YApwCnAKcApwCnAKcApwCnAKcAp/+mAqf9pgOn/6b/pgKn/aYDp/+m/6YCp/2mA6f+pgGnAFn/WABZAVn/WAJZ/lgAWQJZ/VgEWfxYA1n+WAFZAFn/WAJZ/lgAWQJZ/VgDWf5YAVn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAln9WANZ/lgBWQBZ/1gBWQBZ/1gBWQBZ/1gCWf1YA1n+WAFZAFn/WAFZAKcAp/+mAaf/pgKn/qYCp/2mA6f+pgGnAKf/pgKn/aYDp/6mAacAp/+mAaf/pgGn/6YBp/6mA6f9pgKn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgCnAKcApwCnAaf+pgKn/qYBpwGn/aYEp/ymA6f+pgGnAKcAp/+mAacApwCnAKcAp/+mAqf+pgGnAKcApwCnAKf/pgGnAKcApwCn/6YCp/6mAacAp/+mAqf+pgKn/aYEp/ymA6f+pgGnAKcAp/+mAaf/pgGnAKf/pgGn/6YApwGn/6YApwCn/6YDp/2mAqf+pgGnAaf/pgGn/6YApwGn/6YApwGn/6YApwGn/qYCp/+mAaf+pgOn/aYBpwGn/qYCp/+m/6YCp/6mAacAp/+mAqf9pgOn/6b/pgKn/aYDp/+mAKcApwCnAKcBp/6mA6f9pgKn/6YApwCnAaf+pgKn/6YApwCnAaf+pgKn/6YApwCnAaf+pgKn/qYBpwCn/6YCp/2mA6f9pgKn/qYDp/2mAqf+pgKn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgFZ/1gBWf9YAln+WAFZ/1gBWQBZAFn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAVn/WAFZ/1gBWf5YA1n+WABZAVn9WAVZ/FgCWf9Y/1gDWfxYBFn8WARZ/FgEWftYBVn8WANZ/lgAWQFZAFn/WAJZ/FgFWfymA6f+pgGn/6YBpwCn/6YCp/2mAqcAp/+mAqf+pgGn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAqf9pgOn/6b/pgKn/KYFp/ymA6f+pgGnAKcAp/+mAacApwCnAKf/pgKn/qYCp/6mAacApwCnAKcBp/6mAqf+pgGnAaf+pgKn/qYBpwCnAKcApwCnAKf/pgKn/qYCp/6mAaf/pgKn/qYBp/+mAaf/pgKn/aYDp/2mA6f+pgGn/6YBp/+mAqf9pgOn/qYBp/+mAaf/pgKn/qYApwGn/6YBp/+mAaf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/6mA6f9pgOn/aYCp/+mAKcBp/6mAqf/pgCnAKcAp/+mAqf+pgKn/qYBpwCnAKcAp/+mAqf+pgOn/KYEp/umBqf6pgWn/aYApwKn/aYCp/+mAKcBpwCn/6YBp/6mAqcAp/+mAqf8pgSn/qYApwKn/KYEp/6mAacAp/+mAacAp/+mAqf+pgKn/aYDp/6mAacAp/+mAKcBp/+mAKcCp/ymBKf9pgOn/aYCp/+mAacAp/+mAKcBp/+mAaf+pgKn/qYDp/ymA6f+pgFZAFn/WAJZ/VgDWf5YAVkAWQBZ/1gCWf5YAln+WAJZ/lgCWf5YAln+WAJZ/lgCWf5YAVkAWQBZAFkAWQBZ/1gCWf5YAln+WAJZ/lgCWf5YA1n8WARZ/FgEWf5YAVn/WABZAFkBWf9YAVn/WABZAVn/WACnAaf+pgSn+6YFp/umBaf7pgan+qYGp/qmBaf8pgSn/aYCp/2mBKf9pgGnAKf/pgGnAKf/pgGnAKf/pgCnAaf+pgSn/KYDp/2mAqcApwCn/6YBp/+mAacAp/+mAaf/pgGn/6YBp/+mAacAp/+mAaf/pgGnAKf/pgCnAacAp/+mAaf+pgOn/aYCpwCn/qYDp/ymA6cAp/+mAKcBp/2mBqf6pgOnAKf+pgSn+6YFp/umBaf8pgKnAKf/pgGn/6YBp/+mAaf/pgGn/qYCp/+mAKcBp/+mAKcBp/6mAqf/pgCnAaf/pgCnAKcApwCnAaf/pgCnAKcBp/6mA6f9pgOn/aYCpwCn/6YCp/6mAacAp/+mAqf9pgSn+6YFp/umBKf9pgKn/6YApwCnAKcApwCnAKcApwCnAKcApwCnAKf/pgKn/qYBpwCn/6YBpwCn/6YBpwCn/6YBp/+mAaf/pgKn/aYDp/6mAacApwCnAKf/pgKn/qYCp/+m/6YCp/6mAqf/pgCnAKcApwCnAKcBp/2mBKf8pgOn/qYBp/+mAqf9pgOn/aYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf+pgKnAKf+WAJZ/1gAWQFZ/1gAWQFZ/1gAWQFZ/1gAWQJZ/FgEWf1YAln/WABZAFkBWf9YAVn+WAJZ/1gBWf9YAFkBWf5YA1n9WAJZ/1gAWQFZ/1gAWQBZAVn+WANZ/FgDWf9YAFkAWQFZ/lgDWf1YAVkBWf9YAVn/pgCnAaf/pgCnAKcBp/+mAaf+pgKn/qYCp/+mAKcBp/6mAacApwGn/qYCp/+mAKcApwCnAaf/pgGn/qYCp/+mAaf/pgCnAaf/pgGnAKf/pgGn/6YBpwCn/6YBp/+mAaf/pgCnAaf/pgCnAaf+pgOn/KYEp/ymBKf9pgKn/qYCp/6mAqf/pgCnAaf/pgCnAKcBp/+mAaf/pgCnAqf8pgan+aYGp/2mAKcDp/ymA6f+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYCp/6mAacAp/+mA6f8pgOn/6b/pgGnAKf/pgKn/aYDp/2mA6f+pgCnAaf/pgGn/6YBp/+mAqf+pgGn/6YBpwGn/qYBpwCnAKcBp/6mAacApwCnAaf+pgGnAKf/pgKn/qYBpwCn/6YBpwCn/6YCp/2mAqcAp/+mAaf/pgCnAaf+pgOn/KYEp/2mAqf/pgCnAaf+pgOn/qYBp/+mAaf/pgKn/aYDp/2mA6f9pgOn/aYDp/2mA6f+pgKn/aYDp/6mAqf/pgCn/6YCp/6mA6f8pgOn/qYCp/+mAKf/pgOn/KYFp/umA6f/pgGn/qYDp/2mAacCp/ymBKf9pgGnAVn+WANZ/VgCWf9YAFkBWf9YAVkAWf9YAFkAWQFZAFn/WAFZ/lgCWQBZ/1gBWf9YAFkBWf9YAVn/WAFZ/lgCWf9YAVn/WAFZ/lgDWf1YA1n9WANZ/VgDWf5YAFkBWf5YA1n9WANZ/FgEWf1YAln/WABZAaf+pgKn/6YApwCnAKf/pgOn/KYEp/ymA6f/pv+mA6f9pgGnAaf+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYBp/+mAaf/pgGnAKf/pgGn/6YBp/+mAaf/pgGn/6YCp/ymBaf8pgKnAKf+pgOn/aYDp/2mAqf/pgCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/+mAaf/pgCnAaf/pgGn/6YBp/+mAaf/pgCnAaf/pgGn/qYDp/2mAqf/pgCnAqf+pgCnAaf/pgKn/qYBpwCn/6YCp/6mAacBp/6mAqf+pgKn/6YBp/+m/6YDp/2mAqf/pv+mAqf/pgCnAKf/pgKn/qYCp/6mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGn/6YApwGn/6YApwGn/qYDp/2mAqcAp/+mAaf/pgGnAKcAp/+mAacAp/+mAacAp/+mAqf+pgCnAqf+pgGnAKf/pgGnAaf9pgSn+6YEp/6mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgGn/qYCp/6mA6f9pgKn/6YApwGn/6YApwGn/6YBp/+mAKcBp/6mA6f9pgGnAaf+pgKn/6YApwGn/6YBp/6mA6f9pgNZ/VgDWf1YA1n9WANZ/VgDWf1YA1n9WANZ/FgEWf1YA1n8WARZ/FgEWf1YAln+WAJZ/1gAWQBZAVn+WAJZ/1gAWQFZ/1gAWQFZ/1gBWf9YAVn/WAFZAFn/WAFZ/1gBWf9YAVkAWf9YAln8WAVZ/FgCWQGn/aYDp/2mAqcAp/+mAaf/pgGn/6YBpwCn/qYDp/6mAacAp/+mAacBp/6mAaf/pgGnAaf+pgGn/6YBpwCnAKcAp/+mAqf+pgKn/6b/pgKn/qYDp/2mAacApwCnAKcApwCn/6YCp/2mA6f9pgOn/aYCp/+mAKcApwGn/qYBpwCnAKcApwCnAKf/pgKn/qYBpwCn/6YCp/6mAacApwCnAKcApwCn/6YCp/6mAqf+pgGn/6YBp/+mAacAp/+mAaf/pgGn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAacAp/+mAqf9pgOn/qYCp/6mAKcCp/2mA6f/pv6mBKf7pgWn/KYDp/6mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGn/6YBp/+mAaf+pgOn/qYApwGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgCnAqf8pgSn/aYCpwCn/qYCp/+mAaf/pgGn/6YBp/+mAaf/pgKn/aYDp/2mA6f+pgGnAKf/pgGn/6YBpwCnAKcApwCn/6YBpwCnAKcApwCn/6YCp/6mAacApwCnAKcApwCn/6YDp/ymBKf9pgGnAaf+pgKn/6b/pgOn/KYFWfpYBln7WANZAFn+WANZ/VgCWf5YAln/WABZAFkAWf9YA1n8WARZ/FgEWfxYBFn9WAFZAFn/WAFZAFn/WAFZ/1gBWf9YAVn/WAFZ/1gBWQBZAFkAWf9YAln+WANZ/FgDWf9Y/1gCWf1YA1n+WAJZ/VgDp/2mA6f+pgGnAKcAp/+mAqf9pgSn/KYDp/6mAqf+pgGnAKcAp/+mAqf9pgOn/6b/pgGn/6YBpwCn/6YCp/2mBKf7pgWn/KYCpwCn/6YBp/+mAKcApwGn/6YApwCnAKcBp/+mAKcApwCnAaf/pgCnAKcApwCnAKcBp/6mAacBp/2mBaf7pgOnAKf+pgKnAKf+pgSn/KYBpwGn/6YBpwCn/qYCp/+mAKcBp/+mAacAp/6mA6f+pgGnAKf/pgGnAKf/pgGnAKf+pgOn/aYDp/2mAqf/pgGn/6YBp/+mAKcBp/+mAKcCp/umB6f5pgan+6YEp/2mA6f+pgCnAaf/pgGnAKf/pgGn/6YBpwCn/6YBp/+mAaf/pgKn/aYDp/2mAqcAp/+mAaf/pgCnAqf9pgOn/aYDp/6mAaf/pgGn/6YCp/2mA6f9pgKnAKf+pgSn+6YEp/6mAKcBpwCn/6YBp/+mAaf/pgKn/qYApwKn/aYDp/6mAaf/pgKn/aYCpwCn/6YCp/2mAqf/pgKn/qYBp/+mAKcBpwCnAKf/pgGn/6YBpwCn/6YCp/6mAacAp/+mAqf+pgGnAKcApwCn/6YCp/6mAqf+WAFZAFkAWQBZAFkAWf9YAVkAWQBZAFn+WANZ/VgCWQBZ/lgDWf1YAln/WAFZ/lgEWfpYBln8WAJZAFn+WAJZAFn/WAFZ/1gBWQBZ/1gBWf9YAln+WAFZAFkAWQBZAFkAWf9YA1n8WANZ/1j+WARZ/FgDp/2mA6f9pgOn/qYApwGn/qYDp/2mAqf/pgCnAaf/pgGn/qYDp/6mAaf/pgGn/6YCp/6mAacAp/+mAacBp/2mA6f+pgCnAaf/pgCnAaf/pgGn/qYDp/2mAqcAp/+mAqf9pgOn/aYDp/6mAacAp/+mAaf/pgGn/6YCp/2mA6f9pgKn/6YCp/2mBKf7pgWn/KYDp/6mAqf+pgKn/qYBpwCnAKf/pgGn/6YCp/2mBKf7pgSn/6b+pgSn/KYDp/+m/6YBpwGn/qYCp/6mAacApwCn/6YBp/+mAKcBp/6mAqf+pgKn/6YApwCnAaf+pgOn/aYCpwCn/qYDp/6mAaf/pgGn/6YCp/6mAKcCp/2mBKf8pgOn/aYDp/6mAaf/pgGn/6YBpwCn/qYDp/2mAqcAp/+mAacAp/+mAqf+pgGnAKcApwGn/6YAp/+mA6f8pgWn+qYFp/2mAqf/pv+mA6f7pgen+aYGp/umA6f+pgKn/6YBp/6mAqf+pgKn/6YApwGn/qYCp/6mAacApwCnAKcAp/+mAaf/pgKn/qYBpwCn/6YBp/+mAqf+pgKn/qYBpwCnAKcApwCnAKcApwCnAKcAp/+mA1n8WARZ/FgDWf9YAFkAWf9YAln+WAJZ/lgBWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAJZ/lgBWf9YAVkAWf9YAVn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAVn/WAFZ/1gBWf9YAVn/WABZAFkBWf9YAKcApwCnAaf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCn/6YCp/2mBKf7pgan+qYFp/ymA6f+pgGnAKcAp/+mAqf9pgSn/KYDp/2mA6f+pgKn/qYBp/+mAaf/pgGn/6YBp/6mAqf/pgCnAKcApwCnAaf/pgGn/qYDp/ymBaf7pgSn/aYCp/6mA6f8pgSn/KYEp/ymBKf9pgGnAKf/pgKn/6YApwCn/6YCp/6mAqf+pgKn/qYCp/6mAqf+pgOn/KYEp/ymA6f/pv+mAqf+pgGnAaf9pgSn/KYDp/+mAKcApwCnAKcApwCnAKcApwCnAKcAp/+mAqf+pgKn/qYCp/6mAqf/pgCnAKcApwCnAaf+pgKn/aYEp/ymA6f/pv6mBKf8pgOn/qYBp/+mAqf9pgOn/aYDp/6mAKcBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAKcBp/6mAqf+pgKn/6YApwCn/6YDp/2mA6f9pgKn/qYCp/+mAacAp/+mAKcBp/+mAacAp/+mAacAp/+mAacAp/+mAqf+pgGnAKcApwCnAKcApwCnAaf+pgOn/aYDp/1YA1n9WANZ/VgDWf1YA1n9WANZ/VgCWf9YAVn/WAJZ/FgEWf5YAFkCWf1YAlkAWf5YA1n9WANZ/lgAWQFZ/1gBWQBZ/1gBWQBZAFn/WAFZAFkAWQBZAFn/WAJZ/lgCWf9YAFkBWf5YAln/WABZAVn+WAGnAKcAp/+mAaf/pgGn/6YBp/6mA6f+pgGn/6YBp/+mAqf9pgOn/aYDp/6mAKcBp/6mAqcAp/2mBKf8pgOn/6b/pgGnAKcAp/+mAqf9pgSn/KYDp/2mA6f+pgKn/aYDp/2mBKf8pgOn/qYBpwCnAKcApwCnAKcAp/+mAqf/pgGn/qYBpwCnAKcApwGn/aYEp/ymAqcBp/6mAacAp/+mAqf+pgGnAKcAp/+mAqf9pgSn/KYDp/6mAacAp/+mAacApwCn/6YBp/+mAqf+pgGnAKf/pgGnAKcApwCnAKf/pgGnAKcApwCnAKf/pgGnAKcApwCn/6YCp/2mBKf8pgOn/qYCp/6mAacAp/+mA6f7pgan+6YDp/+m/6YDp/2mAacBp/6mA6f9pgKn/6YApwGn/6YBp/+mAKcBp/+mAaf+pgOn/KYEp/6mAKcBp/+mAKcBp/+mAaf/pgGn/6YBpwCn/6YApwKn/KYFp/umBKf+pgCnAaf+pgOn/qYApwKn/aYDp/2mA6f9pgOn/aYDp/6mAacAp/+mAacBp/6mA6f8pgKnAKcApwCnAKf+pgOn/aYDp/2mAqf/pgGnAKf+pgOn/KYFWftYA1n/WABZAVn+WAJZ/lgDWf1YAln/WABZAFkBWf5YAln+WAFZAVn+WAJZ/1j/WANZ/FgEWf1YAln+WANZ/VgBWQBZ/1gDWf1YAln9WARZ/FgEWfxYA1n/WP9YAln9WANZ/1j/WAJZ/VgDWf9YAFn/pgKn/qYCp/6mAacApwCnAKcAp/+mAqf9pgOn/qYBp/+mAaf+pgOn/aYDp/2mA6f9pgKnAKf+pgOn/aYBpwGn/qYCp/6mAqf+pgKn/6YApwGn/6YApwGn/6YBp/+mAaf+pgOn/KYEp/2mAqf/pgCnAKcApwCnAKcApwGn/qYCp/6mAacBp/6mAqf+pgGnAaf+pgKn/qYCp/+mAaf/pgCnAqf9pgOn/qYBp/+mAaf/pgGn/6YBp/+mAacAp/6mBKf8pgOn/qYBpwCnAKcAp/6mBKf7pgWn/KYCpwCn/6YBp/+mAqf9pgOn/qYBpwCn/6YApwKn/aYDp/2mAqf/pgGnAKf/pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgGnAKf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAKcApwGn/6YBp/6mA6f9pgOn/aYCpwCn/6YCp/6mAaf/pgGnAaf+pgGnAKf/pgKn/qYBpwCnAKcAp/+mAqf/pgCnAKf/pgKn/qYCp/+m/6YBp/+mAqf/pv+mAqf9pgSn/aYBpwCnAKcApwCnAKf/pgKn/lgBWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAJZ/lgBWf9YAFkBWQBZ/lgDWfxYBFn9WAJZ/1gAWQFZ/1gAWQFZ/lgDWf1YA1n9WAJZ/1gBWf9YAVn/WAFZ/1gBWf5YA1n9WAJZ/1gAWQBZAFkBWf5YA6f8pgSn/qYApwGn/6YApwKn/aYDp/ymBaf7pgSn/qYApwGn/6YBp/+mAaf/pgGnAKf/pgGn/6YBpwCn/6YBpwCn/qYDp/2mA6f+pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgKn/qYBpwCn/6YCp/6mAaf/pgGn/6YBp/+mAKcBp/+mAKcBp/+mAacAp/6mBKf7pgWn/aYBpwCnAKf/pgKn/qYCp/6mAacApwCnAKcAp/+mAqf+pgGnAKf/pgGnAaf9pgOn/qYBpwCnAKf/pgKn/qYCp/6mAqf+pgKn/6YApwCnAKcApwCnAKcApwCnAKcAp/+mAqf/pv+mAqf+pgGnAKf/pgGnAKcAp/+mAqf9pgOn/qYBpwCn/6YCp/2mA6f+pgGnAKf/pgGnAKcApwCn/6YBpwCnAaf+pgGn/6YBpwCnAKf/pgGn/6YBpwCn/6YBp/+mAacAp/6mA6f9pgKnAKf+pgOn/aYCpwCn/6YCp/2mA6f+pgGnAKcAp/+mAqf+pgGnAKf/pgKn/qYCp/2mBKf7pgWn/KYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgGn/6YBp/+mAVn/WAJZ/VgEWftYBFn+WAFZAFn/WAFZ/1gBWQBZ/1gBWf9YAln+WAJZ/VgDWf9Y/1gCWf5YAVkAWQBZ/1gBWQBZ/1gCWf1YAln/WABZAln9WANZ/VgCWQBZ/1gCWf1YA1n+WAFZAFn/WAFZ/1gBWQBZ/qYDp/6mAKcCp/ymBKf+pgCnAaf+pgKn/6YBp/6mAqf+pgGnAaf+pwKo/qcAqAKo/qcCqP6nAagAqACoAaj9pwSo/acCqP+nAKgAqACoAaj+pwKo/qcCqP6nAqj+pwKo/qcBqACo/6cCqP6nAqj9pwOo/acDqP+n/6cCqP6nAaj/pwKo/acEqPynAqgAqP+nAaj/pwGo/6cBqACo/6cCqP6nAagAqACoAaj+pwKo/qcBqAGo/qcCqP2nA6j+pwKo/qcAqAGo/6cCqP6nAaj/pwGo/6cCqP6nAaj/pwGo/6cBqACo/qcEqPunBKj+pwGo/6cBqP+nAaj/pwCoAaj/pwGo/6cAqACoAqj9pwKo/6cAqAGoAKj+pwKo/6cBqP+nAaj+pwOo/acDqP2nA6j9pwOo/acDqP6nAagAqP+nAagAqP+nAqj+pwGoAKj/pwKo/qcBqAGo/acEqP2nAagBqP2nBKj9pwGoAKj/pwKo/qcBqACo/6cBqACoAKgAqP+nAaj/pwKo/qcBqACo/6cBqACo/6cCqP6nAqj9pwSo/KcEqP2nAagAqP+nAqj+pwKo/qcBqACoAKgAqACoAKgAqABYAFgAWP9XAlj+VwFYAVj+VwFYAFgAWABYAVj+VwFYAVj+VwJY/1cAWABYAFgAWAFY/1f/VwJY/1cAWAFY/VcEWP1XAlj/VwBYAFgBWP5XA1j9VwJYAFj/VwFY/1cBWP9XAVgAWP5XA1j9VwJY/1cAWAGo/6cBqP+nAaj/pwGo/6cBqACo/6cBqP+nAagAqP+nAaj/pwGo/6cBqP+nAKgAqACoAaj/pwCoAKgAqACoAqj9pwOo/acDqP2nBKj8pwSo/KcDqP6nAqj+pwKo/qcBqACo/6cBqACo/6cBqP+nAaj/pwGo/6cAqAGo/6cBqP+nAKgBqP6nAqgAqP6nA6j9pwKo/6cBqP+nAaj/pwCoAaj/pwGo/6cBqACo/6cCqP6nAagBqP6nAqj/p/+nA6j8pwSo/KcEqP2nAagAqACoAKgAqACoAKgAqACoAKj/pwKo/qcBqACo/6cBqACo/6cBqACo/6cBqACo/6cBqACo/6cBqP+nAagAqP+nAaj+pwSo/KcDqP6nAKgCqP6nAagAqP+nAagAqP+nAagAqP6nA6j9pwKoAKj+pwOo/KcEqPynBKj9pwKo/6f/pwKo/qcBqACo/6cCqP6nAaj/pwGo/6cCqP6nAagBqP2nBKj8pwSo/KcEqP2nAagAqP+nAqj+pwKo/acEqPynA6j+pwKo/qcCqP2nBKj8pwOo/qcBqACo/6cBqP+nAaj/pwGo/6cBqP+nAaj/pwGo/6cAqAKo/KcFWPxXAlj/VwFY/1cBWP9XAVj/VwFY/1cAWAJY/VcDWP1XA1j+VwFY/1cAWAJY/lcBWABY/1cBWAFY/VcEWPxXA1j/V/9XAlj9VwNY/lcBWABYAFgAWABYAFgAWABYAVj+VwNY/FcEWP1XAlj/VwBYAFgBqP6nAqj/pwCoAaj/pwCoAaj+pwOo/acDqP2nAqj/pwCoAaj+pwOo/acCqP+n/6cDqP2nAqj/p/+nA6j9pwKo/6f/pwKo/6cAqACoAKgAqACoAKgAqACoAKgAqACoAKgAqP+nAqj+pwGoAKj+pwSo/KcCqACo/qcEqPunBaj8pwKo/6cBqACoAKj/pwCoAagAqACo/6cBqP6nA6j+pwGo/6cAqAGoAKgAqACo/qcDqP6nAqj+pwGo/6cBqP+nAaj/pwGoAKj+pwOo/acDqP6nAKgBqP+nAaj/pwGo/qcEqPunBKj+pwGoAKj/pwGo/6cCqP6nAKgCqP2nA6j+pwCoAqj9pwOo/acCqP+nAaj/pwCoAaj+pwOo/KcEqP2nA6j8pwSo/acDqP2nA6j9pwOo/qcBqACoAKgAqP+nAqj+pwKo/qcCqP+nAKgBqP6nAqj/pwGo/6cBqP+nAaj/pwGo/6cBqACo/6cBqP+nAaj/pwGo/6cAqAKo/acCqP+nAKgBqP+nAKgBqP6nA6j8pwSo/acCqP+nAKgAqACoAKgAqACoAKgAqP+nAqj+pwKo/qcBqP+nAqj+pwGo/6cBqACo/1cBWABY/1cCWP5XAVgAWABY/1cCWP5XAlj+VwJY/lcCWP9XAFgBWP9XAFgAWAFY/lcDWPxXA1j+VwJY/lcCWP5XAVgAWABYAFgBWP1XA1j+VwFYAVj9VwNY/VcEWPxXA1j9VwNY/lcBWABY/lcDWP5XAKgBqP+nAKgCqPynBKj9pwKoAKj/pwCoAaj+pwSo+6cFqPynAqgAqP+nAqj9pwSo+6cFqPynA6j+pwKo/acEqPynBKj8pwSo/acBqAGo/qcCqP+nAKgAqAGo/qcCqP+nAKgAqAGo/qcCqP+n/6cDqPynBKj9pwGoAaj9pwWo+6cDqP6nAagBqP6nAqj+pwGoAaj+pwGoAaj+pwOo/acCqP6nA6j9pwKo/6cAqAGo/qcDqP2nA6j9pwKo/6cCqP2nA6j8pwWo/KcCqP+nAKgBqP+nAKgAqAGo/6cBqP6nAqj+pwOo/qcAqAGo/6cAqAGo/6cAqAGo/qcCqP+nAKgBqP6nAqj+pwKo/6cAqACoAaj9pwWo+qcFqP6nAKgAqACoAKgAqAGo/qcCqP6nAagAqAGo/qcCqP6nAagBqP6nA6j9pwKo/qcCqP+nAKgBqP6nAqj+pwGoAKgBqP6nAqj+pwKo/6cAqACoAaj+pwKo/qcBqACoAKgAqACo/6cBqACo/6cCqP6nAKgDqPunBaj8pwKoAaj+pwGo/6cAqAOo+6cFqPynAqgAqP6nA6j+pwCoAaj+pwKo/6cAqAGo/6cAqABYAVgAWP5XA1j9VwNY/lcAWAFY/1cAWAFY/1cCWPxXBVj6VwdY+lcEWP5XAFgBWP9XAVgAWP9XAVj/VwFYAFj/VwJY/VcDWP5XAVgAWP9XAVj/VwJY/VcEWPtXBVj9VwFYAFgAWABYAFgAWABYAFgAWP+nAagAqACoAKj/pwGoAKgAqACo/6cCqP6nAqj+pwGoAKgAqACoAKgAqP+nAqj+pwGoAKj/pwGoAKj+pwOo/KcEqP6nAKgBqP6nAagBqP+nAKgAqACoAKgAqACoAKgAqAGo/acFqPunBKj+pwCoAaj/pwGo/6cCqP2nA6j+pwCoA6j7pwao+qcEqP+n/6cCqP6nAKgDqPunBqj7pwOo/6cAqACoAKgAqAGo/qcCqP6nAqj+pwKo/6f/pwOo/KcEqP2nAagBqP2nBKj8pwOo/6cAqP+nAagAqACoAaj+pwGo/6cDqPynBKj8pwOo/6cAqACoAKgAqACoAKgAqACoAaj+pwKo/6f/pwOo/acCqP+n/6cCqP6nAagAqP6nBKj8pwKoAKj+pwOo/acCqACo/6cBqP6nAqgAqP+nAaj/pwGoAKj/pwKo/acEqPynBKj9pwKo/6cAqAGo/6cBqP+nAKgBqP+nAaj/pwCoAaj/pwGo/qcDqP2nA6j8pwSo/acCqP+nAKgBqP");

module.exports = beeps;
},{}],8:[function(require,module,exports){
/*
 bindWithDelay jQuery plugin
 Author: Brian Grinstead
 MIT license: http://www.opensource.org/licenses/mit-license.php
 http://github.com/bgrins/bindWithDelay
 http://briangrinstead.com/files/bindWithDelay
 Usage:
 See http://api.jquery.com/bind/
 .bindWithDelay( eventType, [ eventData ], handler(eventObject), timeout, throttle )
 Examples:
 $("#foo").bindWithDelay("click", function(e) { }, 100);
 $(window).bindWithDelay("resize", { optional: "eventData" }, callback, 1000);
 $(window).bindWithDelay("resize", callback, 1000, true);
 */
$.fn.bindWithDelay = function (events, data, fn, timeout, throttle) {

    if ($.isFunction(data)) {
        throttle = timeout;
        timeout = fn;
        fn = data;
        data = undefined;
    }

    // Allow delayed function to be removed with fn in unbind function
    fn.guid = fn.guid || ($.guid && $.guid++);

    // Bind each separately so that each element has its own delay
    return this.each(function () {

        var wait = null;

        function cb() {
            var e = $.extend(true, {}, arguments[0]);
            var ctx = this;
            var throttler = function () {
                wait = null;
                fn.apply(ctx, [e]);
            };

            if (!throttle) {
                clearTimeout(wait);
                wait = null;
            }
            if (!wait) {
                wait = setTimeout(throttler, timeout);
            }
        }

        cb.guid = fn.guid;

        $(this).on(events, data, cb);
    });
};
},{}],9:[function(require,module,exports){
/**
 * Brings something like PHP's sprintf to js.
 * Use it like "{0} string {1}".format("Handy", "replacement");
 *
 * @author: fearphage
 * @link: http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format/4673436#4673436
 */
if (!String.prototype.format) {
    String.prototype.format = function () {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] != 'undefined' ? args[number] : match;
        });
    };
}
},{}],10:[function(require,module,exports){
(function (global){
var define, main, require, indexOf = [].indexOf || function (item) {
        for (var i = 0, l = this.length; i < l; i++) {
            if (i in this && this[i] === item)
                return i;
        }
        return -1;
    };
main = {};
require = function (deps, code) {
    return main = define.prototype.factory(main, code, deps);
};
define = function (name, deps, code) {
    define.modules.exports = {};
    return define.modules[name] = define.prototype.factory(define.modules[name], code, deps);
};
define.prototype.factory = function (scope, code, deps) {
    var generator, indexed;
    generator = indexOf.call(deps, 'exports') >= 0 ? define.prototype.fromExport : define.prototype.fromReturn;
    indexed = deps.indexOf('exports');
    return generator(scope, code, define.prototype.selectDeps(deps), indexed);
};
define.prototype.getModule = function (name) {
    return define.modules[name];
};
define.prototype.selectDeps = function (deps) {
    return deps.map(define.prototype.getModule);
};
define.prototype.fromExport = function (scope, code, deps, indexed) {
    code.apply(scope, deps);
    return deps[indexed];
};
define.prototype.fromReturn = function (scope, code, deps) {
    return code.apply(scope, deps);
};
define.modules = {};
define.modules.require = define.prototype.getModule;
global.$ = global.jQuery = require('jquery');
require('jquery-ui');
require('select2');
require('jQuery-QueryBuilder');
require('./assets/jQueryBindWithDelay');
require('./assets/sprintf');
var GeneralModule = require('./modules/general.js');
var General = new GeneralModule();
global.app = General;
$(document).ready(function () {
    General.init();
});
}).call(this,typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./assets/jQueryBindWithDelay":8,"./assets/sprintf":9,"./modules/general.js":11,"jQuery-QueryBuilder":2,"jquery":5,"jquery-ui":4,"select2":6}],11:[function(require,module,exports){
var VarietiesModule = require('./varieties.js');
var TreesModule = require('./trees.js');
var MarksModule = require('./marks.js');
var QueriesModule = require('./queries.js');

/**
 * handles all the general stuff
 */
function GeneralModule() {
    "use strict";

    /**
     * having our class always accessible can get handy
     */
    var self = this;

    /**
     * temp storages for filters etc. to set timeout before sending request
     */
    var last_search_term;
    var search_timer;

    /**
     * Load modules
     */
    this.Varieties = new VarietiesModule(self);
    this.Trees = new TreesModule(self);
    this.Marks = new MarksModule(self);
    this.Queries = new QueriesModule(self);

    /*
     * start up
     */
    this.init = function () {
        this.searching = '<div class="searching">' + trans.searching + '</div>';

        this.instantiateDatepicker();
        this.instantiateSelect2();
        this.selectConvar();
        this.selectTree();
        this.instantiateFilter();
        this.instantiatePrefillMarker();
        this.instantiatePrintButtons();
        this.Varieties.selectBatchId();
        this.Varieties.setCodeFromOfficialName();
        this.Trees.get();
        this.Marks.initValidationRulesCreator();
        this.Marks.addMarkFormFieldInit();
        this.Marks.loadFormFields();
        this.Marks.applyValidationRules();
        this.Marks.byScanner();
        this.Marks.unlockScannerField();
        this.Queries.init();
    };

    /*
     * load and configure the jquery ui datepicker
     */
    this.instantiateDatepicker = function () {
        $('.datepicker').datepicker({
            dateFormat: trans.dateformat
        });
    };

    /*
     * load and configure the select2 plugin
     */
    this.instantiateSelect2 = function () {
        // default select2
        $('select').select2({
            minimumResultsForSearch: 6
        });
    };

    /**
     * make a list filterable
     *
     * use the data-filter attribute to add a json containing
     *   [ 'controller' => '', 'action' => '', 'fields' => [''] ]
     */
    this.instantiateFilter = function () {
        var $filter = $('.filter').first();
        var $target = $('#index_table').first();
        var $sort = $target.find('th > a');
        var $paginate = $target.find('.pagination a');

        // filter the data when inputing to the filter field
        $filter.off('keyup paste change');
        $filter.on('keyup paste change', function () {
            // prevent searching twice the same
            if ($filter.val() === self.last_search_term) {
                return;
            } else {
                self.last_search_term = $filter.val();
            }

            // wait for typing
            var wait = 250; // milliseconds
            clearTimeout(self.search_timer);
            self.search_timer = setTimeout(function () {
                // search for the data
                self.getFilteredData($filter.val(), $filter.data('filter'), $target, null);
            }, wait);
        });

        // make ordering work with filters
        $sort.off('click');
        $sort.click(function (e) {
            var $link = $(this);
            var href = $link.attr('href');
            var order = null === href.match('direction=asc') ? 'asc' : 'desc';
            var anti_order = order === 'asc' ? 'desc' : 'asc';
            var new_href = href.replace(/direction=\w+/, 'direction=' + order);

            // prevent default
            e.preventDefault();

            // set new link
            $link.attr('href', new_href);

            // set order classes
            $sort.removeClass('asc desc');
            $link.addClass(anti_order);

            // get new data
            self.getFilteredData($filter.val(), $filter.data('filter'), $target, href);
        });

        // make pagination work with filters
        $paginate.off('click');
        $paginate.each(function () {
            var $link = $(this);

            $link.click(function (e) {
                var href = $link.attr('href');

                // prevent default
                e.preventDefault();

                // return if no href is set
                if ("" === href) {
                    return;
                }

                // get new data
                self.getFilteredData($filter.val(), $filter.data('filter'), $target, href);
            });
        });
    };

    /**
     * make an ajax call and fetch the filtered data
     *
     * @param term String with the filter criteria (search term)
     * @param params Object {controller: String, action: String, fields: Array}
     * @param $target jQuery object where the results will be displayed
     * @param url String
     */
    this.getFilteredData = function (term, params, $target, url) {
        url = null === url ? window.location : url;

        var sort = self.getUrlParameter('sort', url);
        var direction = self.getUrlParameter('direction', url);

        $.ajax({
            url: webroot + params.controller + '/' + params.action,
            data: {
                fields: params.fields,
                term: term,
                sort: sort,
                direction: direction,
                page: self.getUrlParameter('page', url)
            },
            success: function (resp) {
                var $tbody = $(resp).find('tbody');
                var $paginator = $(resp).siblings('div.paginator');

                if ($tbody.length && $paginator.length) {
                    $target.find('tbody').html($tbody.html());
                    $target.find('.paginator').html($paginator.html());
                    self.addPaginatorSortQueryString(sort, direction, $target.find('.paginator'));
                    self.instantiateFilter();
                } else {
                    $target.find('tbody').html(resp);
                }
            },
            dataType: 'html',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $target.find('tbody').html(trans.searching);
            }
        });
    };

    /**
     * Decorate the links in the $paginator jquery object with the sorting field and direction
     *
     * @param sort
     * @param direction
     * @param $paginator
     */
    this.addPaginatorSortQueryString = function (sort, direction, $paginator) {
        $paginator.find('a').each(function () {
            var link = $(this).attr('href');
            var new_link;

            // disable for empty links
            if (!link) {
                return;
            }

            new_link = link.replace('&sort=', '');
            new_link = new_link.replace('&direction=', '');
            new_link = new_link + '&sort=' + sort + '&direction=' + direction;
            $(this).attr('href', new_link);
        });
    };

    /*
     * load and configure the convar select field.
     */
    this.selectConvar = function () {
        var $select = $('.select2convar');
        var last_batch;

        // get convar
        $select.select2({
            ajax: {
                url: webroot + 'varieties/searchConvars',
                delay: 250,
                dataType: 'json',
                processResults: function (resp) {
                    var results;

                    // map the results
                    results = $.map(resp.data, function (value, index) {
                        return {
                            text: value,
                            id: index
                        };
                    });

                    // set first result as last_batch
                    if (results.length > 0) {
                        last_batch = results[0].text.match(/^[^.]+\.[^.]+/);
                    }

                    // if select2convar_add class is set
                    if ($select.hasClass('select2convar_add')) {
                        // if nothing was found, propose to create a new batch
                        if (results.length === 0 && last_batch) {
                            results = [{
                                text: trans.create_new_variety + ' ' + last_batch,
                                id: last_batch
                            }];
                        }
                    }

                    return {
                        results: results
                    };
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                },
                cache: true
            },
            minimumInputLength: 1,
            sorter: function (data) {
                return data.sort(function (a, b) {
                    a = a.text.toLowerCase();
                    b = b.text.toLowerCase();
                    if (a > b) {
                        return 1;
                    } else if (a < b) {
                        return -1;
                    }
                    return 0;
                });
            }
        });

        $select.on('select2:selecting', function (event) {
            var text = event.params.args.data.text;
            if (text.match(/[a-zA-Z0-9]{4,8}\.\d{2}[A-Z]$/)) {
                var crossing_batch = text.match(/[a-zA-Z0-9]{4,8}\.\d{2}[A-Z]$/)[0];
                event.params.args.data.text = trans.uc_new + ' ' + crossing_batch;
            }
        });
    };

    /*
     * load and configure the tree select field.
     */
    this.selectTree = function () {
        var $select = $('.select2tree');

        // get convar
        $select.select2({
            ajax: {
                url: webroot + 'trees/searchTrees',
                delay: 250,
                dataType: 'json',
                processResults: function (resp) {
                    var results;

                    // map the results
                    results = $.map(resp.data, function (value, index) {
                        return {
                            text: value,
                            id: index
                        };
                    });

                    return {
                        results: results
                    };
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                },
                cache: true
            },
            minimumInputLength: 1,
            sorter: function (data) {
                return data.sort(function (a, b) {
                    a = a.text.toLowerCase();
                    b = b.text.toLowerCase();
                    if (a > b) {
                        return 1;
                    } else if (a < b) {
                        return -1;
                    }
                    return 0;
                });
            }
        });
    };

    /**
     * get url get param value
     *
     * @param sParam String
     * @param url String
     * @returns String|Boolean
     */
    this.getUrlParameter = function (sParam, url) {
        var args = url.toString().split("?")[1],
            sPageURL = decodeURIComponent(args),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? false : sParameterName[1];
            }
        }
    };

    /**
     * Mark fields that are brain prefilled
     */
    this.instantiatePrefillMarker = function () {
        var $prefills = $('input.brain-prefilled, select.brain-prefilled');
        var msg = '<span class="brain_prefilled_msg">' + trans.brain_prefill + '</span>';

        $prefills.each(function () {
            $(this).parents('div.input').find('label').first()
                .append(msg);
        });
    };

    /**
     * Uses the speakers to beep
     */
    this.beep = function (type) {
        var beeps = require('./../assets/beeps.js');
        var audio;

        if ('success' === type) {
            audio = beeps.success;
        }
        if ('success2' === type) {
            audio = beeps.success2;
        }
        if ('error' === type) {
            audio = beeps.error;
        }

        audio.play();

        return audio;
    };

    this.instantiatePrintButtons = function () {
        $('.zpl_print').click(function (event) {
            var printWindow = window.open();
            printWindow.document.open('text/plain');
            printWindow.document.write($(this).attr('data-zpl'));
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
            if ($(this).hasClass('prevent_default')) {
                event.preventDefault();
            }
        });
    };
}

module.exports = GeneralModule;
},{"./../assets/beeps.js":7,"./marks.js":12,"./queries.js":13,"./trees.js":14,"./varieties.js":15}],12:[function(require,module,exports){
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
     * Apply validation rules
     */
    this.applyValidationRules = function () {
        $('.select_property').change(function () {
            $.ajax({
                url: webroot + 'mark-form-properties/get/' + $(this).val() + '/default',
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
                self.setMark($.parseJSON(resp));
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
                $el.val(data.mark_value);
            }
            self.General.beep('success');
        } else {
            self.General.beep('error').addEventListener("ended", function () {
                alert(String(trans.matching_elements).format($el.length));
            });
        }
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
},{}],13:[function(require,module,exports){
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
},{}],14:[function(require,module,exports){
/**
 * handles all the trees stuff
 */
function TreesModule(General) {
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
     * get tree
     *
     * use the data-filter attribute to add a json containing
     *   [ 'controller' => '', 'action' => '', 'fields' => [''] ]
     */
    this.get = function () {
        var $filter = $('.get_tree').first();
        var $container = $('#tree_container').first();
        var printable = $filter.hasClass('get_printable_tree_with_date') ? 'with_date' : false;

        $filter.on('keyup paste', function () {
            var params = $filter.data('filter');
            $.ajax({
                url: webroot + params.controller + '/' + params.action,
                data: {
                    fields: params.fields,
                    element: params.element,
                    term: $filter.val(),
                    printable: printable
                },
                success: function (resp, status) {
                    if ('success' === status) {
                        $container.html(resp);
                        self.General.beep('success');
                    } else {
                        $container.html('<div class="nothing_found">' + trans.no_tree_found + '</div>');
                        if (0 < $filter.val().length) {
                            self.General.beep('error');
                        }
                    }
                },
                dataType: 'html',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    $container.html(self.General.searching);
                }
            });
        });
    };
}

module.exports = TreesModule;
},{}],15:[function(require,module,exports){
/**
 * handles all the varieties stuff
 */
function VarietiesModule(General) {
    "use strict";

    /**
     * having our class always accessible can get handy
     */
    var self = this;

    /**
     * make the general module accessible
     */
    this.General = General;

    /*
     * load and configure the Crossing.Batch select field. Unlock Code
     */
    this.selectBatchId = function () {
        var $select = $('.select2batch_id');

        // get batch_id
        $select.select2({
            ajax: {
                url: webroot + 'varieties/searchCrossingBatchs',
                delay: 250,
                dataType: 'json',
                processResults: function (resp) {
                    var results;
                    results = $.map(resp.data, function (value, index) {
                        return {
                            text: value,
                            id: index
                        };
                    });

                    return {
                        results: results
                    };
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                },
                cache: true
            },
            minimumInputLength: 1
        });

        // get code
        $select.on('select2:select', function () {
            $.ajax({
                url: webroot + 'varieties/getNextFreeCode',
                data: {batch_id: $select.val()},
                success: function (resp) {
                    $('#code').val(resp.data)
                        .removeAttr('disabled');
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                }
            });
        });
    };

    /**
     * Set code as underscored official name
     */
    this.setCodeFromOfficialName = function () {
        var $official_name = $('.official_name').first();
        var $form = $official_name.parents('form');
        var $code = $form.find('#code').first();
        var $batch_id = $form.find('#batch-id');
        var name = '';

        $official_name.on('keyup paste change', function () {
            $code.val(function () {
                name = $official_name.val();
                name = name.trim();
                name = name.replace(/[^äöüa-zA-Z0-9-_]/g, '_');
                return name.toLowerCase();
            });
        });

        $form.on('submit', function () {
            $code.removeAttr('disabled');
            $batch_id.removeAttr('disabled');
        });
    };
}

module.exports = VarietiesModule;
},{}]},{},[10])