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
        var callback,
            $filter = $('.get_tree').first();

        if ($filter.hasClass('scanner')) {
            callback = this.processScannerCode;
        } else {
            callback = this.ajaxSearchTree;
        }

        $filter.bindWithDelay('keyup paste', callback, 200, false);
    };

    /**
     * detect what was scanned and call correct action (get tree, submit form)
     */
    this.processScannerCode = function () {
        var $filter = $('.get_tree').first(),
            val = $filter.val();

        if (null !== val.match(/^SUBMIT$/)) {
            self.submitForm();
        } else {
            self.ajaxSearchTree(true);
        }
    };

    /**
     * Submit form, if there is no ambiguity
     */
    this.submitForm = function () {
        var $form = $('form');

        if (1 === $form.length && $form.valid()) {
            self.General.beep('success');
            $form.submit();
            return;
        }

        self.General.beep('error');
    };

    /**
     * Search tree using the params provided in the filter field
     *
     * @param {boolean} byScanner
     */
    this.ajaxSearchTree = function (byScanner) {
        var $filter = $('.get_tree').first();
        var $container = $('#tree_container').first();
        var printable = $filter.hasClass('get_printable_tree_with_date') ? 'with_date' : false;
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
                    if (byScanner) {
                        $filter.val('');
                    }
                } else {
                    $container.html('<div class="nothing_found">' + trans.no_tree_found + '</div>');
                    if (0 < $filter.val().length || byScanner) {
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
    };
}

module.exports = TreesModule;
