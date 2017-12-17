/**
 * handles all displaying of results
 */
function ResultsViewer(General) {
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
     * Holds the loaded mark parents data
     *
     * @type {Array}
     */
    this.markParents = [];

    /**
     * start up, initialize tooltips
     */
    this.init = function () {
        var $mark_values = $('.mark_value');

        if ($mark_values.length < 1) {
            return;
        }

        $mark_values.tooltip({
            items: 'span.mark_value',
            //track: true,
            tooltipClass: 'mark_value_tooltip',
            position: {
                my: "left+30 center",
                collision: 'flipfit flip'
            },
            content: trans.loading,
            // overloading ensures to have the loading message replaced as soon
            // as the real content is fetched.
            content: function (callback) {
                return self.getMarkValueTooltipContent(this, callback);
            }
        });
    };

    /**
     * Return content for tooltip. Fetch from server, if not yet loaded into self.markParents
     *
     * @param caller
     * @param callback
     * @returns string with the content for the tooltip (HTML)
     */
    this.getMarkValueTooltipContent = function (caller, callback) {
        var $caller = $(caller);
        var id = parseInt($caller.attr('class').toString().replace(/[^\d]*/, ''));
        if (undefined === self.markParents[id]) {
            self.markParents[id] = trans.loading;
            self.loadMark(id, $caller, callback);
        }

        return self.markParents[id];
    };

    /**
     * Fetch content for tooltip on server, then trigger callback with the fetched data
     *
     * @param id
     * @param $caller
     * @param callback
     */
    this.loadMark = function (id, $caller, callback) {
        $.ajax({
            url: webroot + 'marks-view/get/' + id,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
            }
        }).done(function (data, textStatus, jqXHR) {
            self.markParents[id] = data;
            callback(data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            callback(String(trans.loading_error).format(trans.mark + ' ' + id));
            console.log(errorThrown);
        });
    };
}

module.exports = ResultsViewer;