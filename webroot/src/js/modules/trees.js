/**
 * handles all the trees stuff
 */
function TreesModule(General) {

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
                    if ('success' == status) {
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