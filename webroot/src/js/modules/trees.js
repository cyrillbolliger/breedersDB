/**
 * handles all the trees stuff
 */
function TreesModule() {

    /**
     * having our class always accessible can get handy
     */
    var self = this;

    /**
     * get tree
     *
     * use the data-filter attribute to add a json containing
     *   [ 'controller' => '', 'action' => '', 'fields' => [''] ]
     */
    this.get = function () {
        var $filter = $('.get_tree').first();
        var $container = $('#tree_container').first();

        $filter.on('keyup paste', function () {
            var params = $filter.data('filter');
            $.ajax({
                url: webroot + params.controller + '/' + params.action,
                data: {
                    fields: params.fields,
                    element: params.element,
                    term: $filter.val()
                },
                success: function (resp, status) {
                    if ('success' == status) {
                        $container.html(resp);
                        General.beep('success');
                    } else {
                        $container.html('<div class="nothing_found">' + trans.no_tree_found + '</div>');
                        if (0 < $filter.val().length) {
                            General.beep('error');
                        }
                    }
                },
                dataType: 'html',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    $container.html(General.searching);
                }
            });
        });
    };
}

module.exports = TreesModule;