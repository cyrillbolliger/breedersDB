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
        $('select').not('.hide, .hidden').select2({
            minimumResultsForSearch: 12
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

    /**
     * Initiate ZPL printing
     */
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