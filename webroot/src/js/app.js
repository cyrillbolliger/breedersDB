global.$ = global.jQuery = require('jquery');

require("jquery-ui");
require('select2');

var GeneralModule = require('./modules/general.js');
var General = new GeneralModule();

/**
 * fires after DOM is loaded
 */
$(document).ready(function () {
    General.init();
});


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