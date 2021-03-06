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