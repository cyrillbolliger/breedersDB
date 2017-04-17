var General = new General();
var Varieties = new Varieties();
var Trees = new Trees();
var Marks = new Marks();

var searching;

/**
 * handles all the general stuff
 */
function General() {

     /**
      * having our class always accessible can get handy
      */
     var self = this;

     /*
      * start up
      */
     this.init = function() {
          searching = '<div class="searching">'+trans.searching+'</div>';

          this.instantiateDatepicker();
          this.instantiateSelect2();
          this.selectConvar();
          this.selectTree();
          this.instantiateFilter();
          this.instantiatePrefillMarker();
          this.instantiatePrintButtons();
          Varieties.selectBatchId();
          Varieties.setCodeFromOfficialName();
          Trees.get();
          Marks.initValidationRulesCreator();
          Marks.addMarkFormFieldInit();
          Marks.loadFormFields();
          Marks.applyValidationRules();
          Marks.byScanner();
          Marks.unlockScannerField();
     };

     /*
      * load and configure the jquery ui datepicker
      */
     this.instantiateDatepicker = function() {
          $('.datepicker').datepicker({
               dateFormat : trans.dateformat
          });
     };

     /*
      * load and configure the select2 plugin
      */
     this.instantiateSelect2 = function() {
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
     this.instantiateFilter = function() {
          var $filter = $('.filter').first();
          var $target = $('#index_table').first();

          $filter.on('keyup paste change', function() {
              // search for the data
              self.getFilteredData($filter.val(), $filter.data('filter'), $target);
          });
     };

    /**
     * make an ajax call and fetch the filtered data
     *
     * @param term String with the filter criteria (search term)
     * @param params Object {controller: String, action: String, fields: Array}
     * @param $target jQuery object where the results will be displayed
     */
    this.getFilteredData = function(term, params, $target) {
        $.ajax({
            url: webroot + params.controller + '/' + params.action,
            data: {
                fields : params.fields,
                term : term,
                sort : self.getUrlParameter('sort'),
                direction : self.getUrlParameter('direction')
            },
            success: function(resp) {
                var $tbody = $(resp).find('tbody');
                var $paginator = $(resp).siblings('div.paginator');

                if ( $tbody.length && $paginator.length ) {
                    $target.find('tbody').html($tbody.html());
                    $target.find('.paginator').html($paginator.html());
                } else {
                    $target.find('tbody').html(resp);
                }
            },
            dataType: 'html',
            beforeSend: function(xhr){
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $target.find('tbody').html(trans.searching);
            }
        });
    };

     /*
      * load and configure the convar select field.
      */
     this.selectConvar = function() {
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
                         results = $.map(resp.data, function( value, index ){
                              return {
                                   text : value,
                                   id   : index
                              };
                         });

                         // set first result as last_batch
                         if ( results.length > 0 ) {
                              last_batch = results[0].text.match(/^[^.]+\.[^.]+/);
                         }

                         // if select2convar_add class is set
                         if ( $select.hasClass('select2convar_add') ) {
                              // if nothing was found, propose to create a new batch
                              if ( results.length === 0 && last_batch ) {
                                   results = [{
                                        text : trans.create_new_variety + ' ' + last_batch,
                                        id   : last_batch
                                   }];
                              }
                         }

                         return {
                              results : results
                         };
                     },
                    beforeSend: function(xhr){
                         xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    },
                    cache: true
               },
               minimumInputLength: 1,
               sorter: function(data) {
                    return data.sort(function(a,b){
                         a = a.text.toLowerCase();
                         b = b.text.toLowerCase();
                         if(a > b) {
                             return 1;
                         } else if (a < b) {
                             return -1;
                         }
                         return 0;
                    });
               }
          });

          $select.on('select2:selecting', function(event) {
               var text = event.params.args.data.text;
               if ( text.match(/[a-zA-Z0-9]{4,8}\.\d{2}[A-Z]$/) ) {
                    var crossing_batch = text.match(/[a-zA-Z0-9]{4,8}\.\d{2}[A-Z]$/)[0];
                    event.params.args.data.text = trans.uc_new + ' ' + crossing_batch;
               }
          });
     };

     /*
      * load and configure the tree select field.
      */
     this.selectTree = function() {
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
                         results = $.map(resp.data, function( value, index ){
                              return {
                                   text : value,
                                   id   : index
                              };
                         });

                         return {
                              results : results
                         };
                     },
                    beforeSend: function(xhr){
                         xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    },
                    cache: true
               },
               minimumInputLength: 1,
               sorter: function(data) {
                    return data.sort(function(a,b){
                         a = a.text.toLowerCase();
                         b = b.text.toLowerCase();
                         if(a > b) {
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
      * @param {string} sParam
      * @returns {appL#4.General.getUrlParameter.sParameterName|Boolean}
      */
     this.getUrlParameter = function(sParam) {
          var sPageURL = decodeURIComponent(window.location.search.substring(1)),
              sURLVariables = sPageURL.split('&'),
              sParameterName,
              i;

          for (i = 0; i < sURLVariables.length; i++) {
              sParameterName = sURLVariables[i].split('=');

              if (sParameterName[0] === sParam) {
                  return sParameterName[1] === undefined ? true : sParameterName[1];
              }
          }
     };

    /**
     * Mark fields that are brain prefilled
     */
    this.instantiatePrefillMarker = function() {
          var $prefills = $('input.brain-prefilled, select.brain-prefilled');
          var msg = '<span class="brain_prefilled_msg">'+trans.brain_prefill+'</span>';

          $prefills.each(function(){
               $(this).parents('div.input').find('label').first()
                       .append( msg );
          });
     };

    /**
     * Uses the speakers to beep
     */
    this.beep = function(type) {
        var success = new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");
        var success2 = new Audio("data:audio/wav;base64,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");
        var error = new Audio("data:audio/wav;base64,UklGRpD2AABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YWz2AAABpv+lAKYBpv6lA6b9pQKm/6UApgCmAKYBpv6lA6b8pQOm/6X/pQOm/KUEpvylBKb9pQKm/6X/pQOm/aUCpv+lAKYApgGm/6UBpv+lAab/pQGmAKb/pQKm/qUBpgCm/6UBpgCm/6UCpv2lAqYApv+lAqb9pQOm/aUDpv6lAKYCpvylBKb+pQCmAab/pf+lA6b9pQKm/6UApgCmAKYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUDpv+l/6UCpv+l/6UCpv6lAaYApv+lAqb+pQKm/lkAWgNa/FkEWvxZBFr8WQRa/VkBWgFa/VkEWv1ZAVoBWv1ZBFr8WQNa/lkBWv9ZAVr/WQJa/lkBWgBa/1kDWvxZBFr9WQJa/1kAWgBaAVr/WQBaAFoBWv5ZA1r9WQFaAVr+WQFaAFoAWgBaAFoAWgBaAKYApv+lA6b8pQSm/aUBpgGm/aUFpvqlBqb6pQam/KUBpgGm/aUEpv2lAaYBpv6lAqb+pQKm/6UBpv+lAKYBpv+lAab+pQKm/6UBpv+lAKYApgCmAab/pQCmAab+pQOm/aUCpv+lAab/pQGm/6UApgGm/6UBpgCm/qUCpv6lAqb/pQCmAKYApv+lAqb9pQOm/qUBpgCm/6UBpgCm/6UCpv2lA6b+pQGmAKb/pQGmAKb/pQGm/6UBpv+lAqb9pQOm/qUBpv+lAqb9pQSm/KUDpv6lAqb+pQKm/qUBpgCmAKYApgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQGm/6UBpv+lAaYApv+lAab/pQGmAKb/pQKm/aUDpv6lAqb+pQKm/qUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQGm/6UBpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b+pQCmAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lA6b/pf+lAqb9pQOm/qUBpgCm/6UCpv6lAaYApgCmAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgFa/lkBWgBaAFoAWgFa/VkEWv1ZAVoBWv5ZAVoBWv5ZAVoAWv9ZAlr+WQFa/1kBWgBaAFr/WQFa/1kCWv5ZAVoAWv9ZAlr+WQFaAFr/WQJa/lkBWv9ZAVoAWv9ZAVr/WQFaAFr/WQFa/1kBWgBa/1kBWv+lAab/pQGm/6UBpv+lAab+pQOm/qUBpgCm/6UBpgCmAKYApgCm/6UCpv6lAqb+pQKm/qUBpgCmAKYApgGm/qUBpgGm/6UBpv+lAKYBpv+lAKYBpv+lAKYBpv6lAqb/pf+lA6b9pQGmAab9pQSm/aUCpv6lAqb+pQKm/6UApgCmAab+pQKm/qUCpv+lAKYApv+lAqb/pf+lAqb+pQKm/qUBpgCmAKYBpv2lBKb8pQOm/6X/pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpv6lAqb/pQCmAKb/pQKm/aUFpvqlBab8pQOm/qUDpvylBKb8pQSm/aUCpv+lAKYApgCmAKYApgCmAKYApgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQGmAKYApgCmAKYApgGm/qUCpv6lAqb/pQGm/qUCpv6lAqb/pQCmAab+pQKm/qUDpv2lAqb/pQCmAKYApgCmAab/pf+lAqb+pQOm/aUCpv6lAqb/pQGm/6X/pQKm/6UBpv+lAKYApgCmAab+pQOm/aUCpv+lAKYBpv+lAKYApgGm/6UBpv6lAaYBpv+lAab+pQKm/6UApgCmAKb/pQKm/aUDWv1ZA1r9WQJa/1kAWgJa/VkDWv1ZAloBWv1ZBFr8WQNa/1kAWgBaAFoAWgBaAFr/WQJa/lkBWv9ZAVoAWgBaAFr/WQJa/VkEWv1ZAVoAWv9ZAVoBWv5ZAVoAWv5ZBFr8WQJaAFr+WQNa/lkAWgFa/1kBpv+lAab/pQKm/aUDpv6lAaYApv+lAqb+pQKm/aUDpv6lAab/pQGm/6UBpv+lAKYBpv+lAqb+pQCmAaYApv+lAqb9pQOm/qUCpv2lA6b+pQGmAKb/pQGmAKb/pQGm/6UBpgCm/6UBpv+lAab/pQKm/aUCpgCm/qUDpv2lA6b+pQGm/qUDpv2lA6b+pQCmAqb9pQOm/aUEpvylA6b+pQCmAqb+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/6UBpgCm/6UBpv+lAKYCpv2lA6b9pQKm/6UBpgCm/6UBpv+lAaYApv+lAKYBpgCm/6UApgCmAKYBpv6lAaYApv+lAqb+pQKm/aUEpvylBKb8pQOm/qUCpv+lAKb/pQGmAKYBpv6lAqb+pQKm/6UApgGm/qUDpv2lAaYBpv+lAKYBpv2lBKb+pQCmAqb8pQSm/qUBpv+lAab+pQOm/aUCpv+lAKYBpv6lAqb+pQKm/6UApgCm/6UCpv6lAaYApv+lAqb+pQGmAKYApgCm/6UCpv6lA6b8pQSm/KUEpv2lAaYBpv6lA6b8pQSm/KUFpvulBKb9pQKmAKb/pQCmAab/pQGm/6UApgGm/6UBWv5ZAloAWv5ZAlr/WQBaAVr+WQFaAVr+WQNa/FkEWvxZA1r+WQJa/lkCWv1ZA1r+WQJa/VkDWv5ZAVoAWgBaAFoAWv9ZAlr+WQJa/1n/WQNa/FkDWv9ZAFoAWgFa/VkEWvxZA1r/WQBa/1kCWv5ZAlr/pQCmAKYApgCmAKYApgCmAab9pQOm/aUDpv+l/6UBpv+lAqb9pQOm/aUDpv+lAKb/pQGm/6UCpv+lAKb/pQGmAKYApgGm/aUDpv6lAqb+pQKm/qUBpgCm/6UBpgCm/6UCpv2lAqYApv+lAqb+pQGmAKYApgCmAKYApv+lAqb+pQKm/qUBpgCmAKYApgCm/6UCpv+lAKYBpv2lBKb+pQCmAab/pQCmAaYApv6lA6b9pQGmAqb9pQOm/aUCpv+lAaYApv+lAab/pQGm/6UCpvylBab8pQKm/6UBpv+lAab/pQCmAab/pQGm/6UBpv6lA6b8pQSm/aUCpv+l/6UBpgCmAKYApgCm/6UCpv6lAaYBpv6lAaYBpv6lAqb/pQCmAKYBpv6lA6b9pQKm/qUDpv2lAqb+pQKm/6UBpv6lAqb+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAab+pQOm/aUBpgGm/qUCpv+lAKYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/aUEpvylBKb+pf+lA6b8pQSm/aUCpgCm/6UBpv6lAqb/pQGm/6UApgCmAFoAWgFa/lkCWv9Z/1kDWvxZBFr9WQJa/1kBWv9ZAFoCWv1ZA1r+WQFaAFoAWv9ZAlr+WQFaAFoAWgBaAFr/WQFaAFoAWv9ZAVr/WQFaAFr/WQFa/1kAWgJa/VkDWv1ZA1r9WQNa/VkDWv5ZAFoCWv1ZA6b+pQCmAqb+pQGmAKb/pQGmAKYApgCmAKYApv+lAqb+pQKm/6X/pQGmAKb/pQGmAKb/pQKm/qUApgKm/qUDpv2lAqb+pQKm/qUDpvylBKb8pQSm/KUDpv6lAqb/pQCm/6UCpv6lA6b8pQSm/KUEpvylBKb9pQGmAKYApgCmAKb/pQKm/aUEpvulBab8pQKm/6UApgKm/aUCpv+lAKYBpv+lAKYApgGm/qUCpv6lAqb+pQKm/aUDpv+lAKYApv+lAaYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYBpv+lAKYApgCmAab/pQCmAKYBpv6lA6b8pQSm/KUDpv+lAKYBpv6lAqb/pf+lA6b9pQKm/6X/pQOm/KUFpvqlBab9pQKm/6UBpv6lAqb/pQCmAab+pQKm/qUBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv2lA6b9pQOm/qUApgGm/6UApgGm/qUDpv6lAKYBpv6lA6b9pQOm/qUApgKm/aUEpv2lAaYApv+lA6b8pQSm/KUDpv+l/6UCpv6lAaYApgCm/6UCpv6lAqb/pQCm/6UCpv6lA6b9pQGmAKYApv9ZAlr9WQRa/FkDWv1ZA1r9WQRa/FkDWv5ZAFoDWvxZA1r+WQBaAlr+WQFaAFr+WQNa/VkDWv5ZAVr/WQFa/1kBWgBa/1kBWv9ZAVr/WQJa/FkEWv1ZAlr/WQFa/lkCWv9ZAFoBWv5ZA1r9WQNa/VkBWgGm/6UBpv+lAKYBpv+lAKYBpv+lAab/pQCmAaYApv+lAKYBpv6lBKb8pQKm/6UBpv6lA6b+pQCmAab/pQGm/6UCpv2lA6b+pQCmAqb9pQOm/aUCpv+lAKYApgCmAKYBpv+lAKYApgGm/6UApgGm/qUDpv6l/6UCpv+lAKYApgCmAKYApgCm/6UBpgCmAKb/pQGm/6UCpv6lAab/pQKm/qUDpvylA6b/pQCmAKYApgCmAab+pQKm/qUBpgCmAKYApgCmAKb/pQKm/qUBpgGm/qUCpv+l/6UDpvylBab7pQSm/aUCpv6lAqb+pQOm/KUDpv6lAaYApgCm/6UCpv2lA6b9pQOm/qUBpv+lAKYBpgCm/6UBpv+lAKYBpgCm/6UCpvylBab7pQWm/KUCpgCm/qUDpv2lAqb/pQCmAab+pQKm/qUDpvylBKb8pQSm/aUBpgCmAKYApgCmAKYApgCmAKb/pQOm/KUDpv6lAaYApgCm/6UBpv+lAaYApv+lAqb+pQGmAKb/pQKm/6UApgCm/6UCpv6lAaYApgCm/6UCpv2lBKb9pQGmAKYBpv6lA6b8pQSm/aUBpgGm/qUCpv6lAaYAWgFa/lkDWvtZBVr+WQBaAVr+WQJa/1kBWv5ZAlr/WQBaAVr/Wf9ZA1r8WQRa/ln/WQNa/VkCWv9ZAFoBWgBa/lkCWv9ZAFoCWv1ZAlr/WQFa/1kBWv9ZAVoAWgBa/1kBWv9ZAlr+WQJa/VkDWv5ZAlr+pQGm/6UCpv2lBKb8pQOm/qUBpgCmAKYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKb/pQOm+6UGpvqlBKb/pQCmAKYApv+lAqb/pf+lA6b8pQSm/aUCpv6lA6b9pQKmAKb9pQSm/aUCpv6lAab/pQGmAKb/pQGmAKb/pQGmAKb/pQOm+6UFpvylA6b+pQCmAaYApgCm/6UBpv+lA6b8pQOm/qUBpgGm/aUDpv6lAab/pQCmAKYBpv+lAKYBpv6lAqb/pQCmAqb9pQKm/6UApgGm/6UApgGm/qUDpv2lAqb/pQCmAKYBpv6lA6b8pQOm/qUBpgCm/6UBpgCm/6UBpv+lAab/pQKm/aUEpvylA6b+pQGmAKYBpv6lAqb9pQOm/qUCpv6lAaYApv+lAaYApv+lAqb+pQGmAKYApgCm/6UCpv2lBab6pQWm/KUDpv6lAqb+pQGm/6UBpgCmAKYApv+lAqb+pQGmAKYApgGm/qUCpv6lA6b9pQKm/6UApgGm/6UApgGm/qUCpgCm/qUDpvylBKb9pQOm/aUCpv+lAKYBpv6lA6b9pQKm/qUCpv6lA6b9pQKm/qUCpv6lAqb/pQCmAFr/WQJa/1kAWv9ZAVoAWgBaAFr/WQFaAFr/WQJa/VkEWvtZBlr5WQda+lkFWv1ZAVr/WQFaAFoAWgBaAFr/WQFaAFr/WQJa/lkAWgJa/VkDWv5ZAFoBWgBa/1kBWv9ZAFoBWv9ZAFoBWv9ZAFoBWv5ZA6b9pQOm/aUCpgCm/qUDpv2lAqYApv6lA6b+pQCmAab+pQOm/qUApgGm/6UApgGm/6UBpgCm/qUDpv6lAqb+pQGm/6UBpgCmAKb/pQKm/KUFpvylA6b+pQCmAab/pQKm/aUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAKYCpvylBab6pQam/KUCpv+lAKYApgCmAab+pQOm/KUEpvylBKb9pQKm/6UApgCmAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lAqYApv+lAab/pQCmAab/pQCmAKYBpv+lAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAab+pQOm/aUCpv+lAKYBpv+lAab+pQOm/qUBpv+lAab/pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApv+lAaYApgCmAab9pQSm/KUEpv2lAaYApv+lAqb9pQSm+6UFpvulBKb+pQGm/6UBpv+lAaYApv6lA6b9pQOm/qUApgGm/6UBpv+lAKYBpv+lAab/pQCmAKYBpv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVoAWgBa/1kBWv9ZAlr/Wf9ZAlr+WQFaAVr9WQVa+lkGWvtZA1r/WQBaAFoBWv5ZAlr+WQFaAFoAWv9ZAVr/WQJa/VkDWv1ZA1r+WQFa/1kBWv9ZAVr/WQBaAVr/WQCmAab+pQKm/6UApgGm/6UApgGm/qUDpv6lAKYBpv+lAaYApv+lAqb9pQOm/qUApgGm/6UBpgCm/qUBpgCmAab/pQGm/qUCpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGmAKb/pQGmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYBpv6lAqb+pQKm/6UApgGm/qUBpgGm/qUDpvylA6b/pQCmAKb/pQKm/qUCpv6lAab/pQKm/qUBpgGm/aUEpv2lAaYApgGm/qUCpv6lAaYBpv6lAaYApv+lAqb+pQGmAKYApgCm/6UBpv+lAqb+pQCmAqb9pQOm/aUCpgCmAKYApv+lAab/pQKm/qUBpgCm/6UBpgCm/qUDpv2lA6b+pQGm/6UApgGm/6UBpgCm/6UBpv+lAKYCpv2lAqYApv6lBKb7pQSm/aUCpgCm/qUDpvylBKb9pQKm/6UBpv+lAKYApgGm/6UBpv6lAqb/pQCmAKYApgCmAKYApgCmAab/pQBaAVr+WQNa/VkDWv5ZAFoBWv5ZAlr/WQBaAVr/Wf9ZAlr+WQJa/1kAWgBaAFoAWgBaAVr9WQRa/FkEWv1ZAlr+WQJa/lkCWv9ZAFoBWv5ZAlr/WQBaAVr+WQJa/1kAWgFa/lkCWv9ZAFoBWv9ZAVr/WQGm/6UCpv2lBKb7pQWm/KUDpv6lAqb9pQOm/qUCpv+lAKb/pQKm/qUDpvylBKb9pQGmAab9pQSm/aUCpv6lAqb+pQKm/6X/pQKm/qUCpv6lAaYApgCmAKYApv+lAaYApgCmAKYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpgCm/6UBpv+lAKYCpv6lAab/pQCmAab/pQGm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQKm/6UApgGm/6UApgGm/6UBpv+lAab/pQGm/6UApgGm/6UApgCmAKYApgGm/qUCpv+l/6UDpvylBab7pQSm/aUDpv2lA6b9pQOm/aUDpv6lAab/pQGm/6UCpv6lAKYCpv2lBKb8pQKmAKYApgCm/6UCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgGm/qUCpv6lA6b8pQWm+6UEpv2lAqb/pQCmAqb8pQSm/aUCpgCm/6UBpv+lAab/pQGmAKb/pQGm/6UApgGmAKb+pQSm+6UEpv6lAKYCpv2lA6b9pQOm/qUBpgCm/6UCpv6lAab/pQKm/qUCpv6lAKYCWv5ZAlr/Wf9ZAVoAWv9ZA1r7WQVa+1kEWv5ZAVr/WQFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFaAFr+WQNa/lkBWv9ZAVr/WQFa/1kBWv9ZAVr+WQNa/VkDWvxZBFr9WQJa/1n/WQJa/1kAWgBaAFoApgCmAab9pQWm+qUGpvqlBqb6pQWm/aUBpgGm/qUBpgCmAKYApgGm/qUCpv6lAqb+pQKm/6X/pQKm/qUBpgCmAKb/pQGm/6UBpgCm/6UApgCmAab/pQGm/qUCpv+lAKYBpv+lAKYBpv6lAqb/pQCmAKYApgCmAKYApgCm/6UDpvylBab6pQWm/KUEpv2lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAab/pQGm/6UCpv2lA6b+pQCmAaYApv+lAqb9pQOm/qUBpgCm/6UCpv6lAaYApv+lAqb+pQGm/6UBpv+lAqb9pQOm/aUCpgCm/qUDpvylBKb9pQGmAKb/pQKm/qUBpgCm/6UBpgCmAKYApgCm/6UBpgCmAKYApgCm/6UBpv+lAqb+pQGmAKb/pQKm/aUDpv6lAaYApv6lBKb8pQOm/aUCpgCm/6UBpv6lAqb/pQCmAKYApgCmAKYApv+lAqb+pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQKm/aUDpv+lAKYApgCm/6UDpv2lAaYApgCmAab+pQGmAKYApgCmAKb/pQKm/1kAWgBa/1kCWv5ZAlr/Wf9ZAVoAWv9ZAlr+WQBaAVr/WQFa/1kAWgBaAFoAWgFa/VkEWvxZBFr9WQJa/lkCWv9ZAVr+WQJa/lkDWv1ZAlr/WQBaAVr/WQFa/1kBWv9ZAVr/WQBaAFoBWv5ZA1r8WQNa/6X/pQOm/KUDpv6lAqb+pQKm/qUBpgCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/qUBpgCm/6UDpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UCpvylBqb6pQSm/qUBpv+lAqb9pQKmAKYApv+lAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/qUDpvylA6b/pQCmAab+pQGmAab+pQKm/qUBpgCmAKYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b9pQKm/6UApgGm/6UApgGm/qUDpv2lAqYApv+lAab+pQOm/aUDpv2lAqYApv+lAKYBpv+lAqb+pQCmAaYApgCmAKb/pQCmAqb+pQGmAKb+pQOm/aUDpv2lA6b9pQKm/6UBpgCm/6UBpv6lBKb7pQWm+6UFpvylAqYApv6lBKb8pQKmAKb/pQKm/aUDpv2lA6b+pQGm/6UApgGm/6UBpv+lAab/pQGm/qUDpv6lAab/pQGmAKb/pQGmAKb/pQKm/qUApgGm/6UBpgCmAKb+pQKm/6UBpgCm/6UApgCmAab+pQKm/6UApgGm/qUCpv6lA6b9pQJa/1kAWgFa/lkCWv5ZAlr/WQFa/VkFWvpZB1r5WQZa+1kEWv5ZAFoBWv9ZAFoBWv9ZAVr/WQBaAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQJa/lkBWgBaAFr/WQJa/lkCWv9Z/1kCWv5ZAlr+WQFaAFoAWgCm/qUDpv2lAqYApv6lAqb/pQCmAab/pQCmAKYBpgCm/6UBpv+lAab/pQKm/aUDpv6lAaYApv+lAab/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQGm/6UBpv+lAaYApv6lA6b9pQKm/6UApgGm/6UApgGm/aUFpvqlBqb7pQOm/6X/pQOm/KUEpvylBab7pQSm/aUCpv+lAab+pQKm/6UApgGm/qUBpgGm/qUCpv+l/6UCpv+lAKYBpv6lAqb/pQCmAKYApv+lAqb+pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQKmAKb+pQSm/KUDpv6lAaYApv+lAqb+pQGmAKb/pQGmAKb/pQKm/qUBpv+lAaYApgCmAKb/pQGmAKb/pQKm/qUBpv+lAqb9pQOm/aUCpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UCpv2lA6b9pQOm/qUApgGm/6UBpv+lAKYApgGm/6UBpv6lAaYApgGm/qUCpv2lA6b+pQGmAKb/pQKm/aUEpvylA6b+WQFaAFr/WQJa/VkEWvtZBFr+WQFaAVr+WQBaAVr/WQJa/lkBWv9ZAFoBWv9ZAlr9WQJa/1kBWv9ZAVr+WQNa/VkCWv9Z/1kEWvtZBFr9WQFaAlr9WQJa/1kBWv9ZAVr+WQNa/lkBWv9ZAVr/WQFa/1kBpv+lAab/pQGm/6UBpv+lAab/pQGmAKYApv+lAKYBpgCm/6UCpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpv+lAKYBpv+lAab/pQGm/6UBpv+lAaYApgCm/6UBpv+lAab/pQGmAKb/pQGm/6UApgKm/aUDpv6lAKYBpv6lA6b9pQOm/aUBpgKm/KUFpvulBKb+pQGm/6UApgGm/6UBpv+lAKYApgCmAKYBpv6lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQGmAKYApgCmAKb/pQGmAKYApgCmAKYApgCm/6UCpv6lAqb+pQGmAKYApv+lAqb+pQKm/qUCpv6lAqb9pQSm+6UGpvqlBKb9pQOm/aUDpv6lAab/pQGm/6UCpv2lA6b9pQKmAab9pQKm/6UApgGm/6UBpv+lAab/pQCmAab/pQKm/aUDpv2lAqYApv+lAab/pQCmAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAqb9pQOm/aUCpgCm/6UBpgCm/6UBpv+lAKYCpv6lAab/pQCmAaYApgCm/6UBpv6lBKb7pQSm/qUApgGm/6UApgCmAab+pQKm/6UAWgBaAVr+WQJa/lkCWv5ZAlr/WQBaAFr/WQJa/lkCWv5ZAVoAWgBa/1kBWgBa/1kDWvtZBVr8WQNa/lkBWv9ZAVoAWv9ZAVr/WQFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFaAFr/WQJa/lkCWv5ZAlr+pQGmAab+pQKm/6X+pQSm/KUDpv6lAaYApv+lAqb+pQGmAKb/pQOm/KUDpv+l/6UCpv6lAaYApgCm/6UCpv6lAab/pQKm/qUBpgCm/qUFpvmlB6b7pQOm/6X/pQKm/qUCpv6lAaYBpv2lBKb8pQKmAab+pQKm/6X/pQOm/KUEpv6lAKYBpv6lA6b9pQOm/aUCpv+lAab/pQCmAab/pQGmAKb+pQOm/aUDpv2lAqYApv6lA6b9pQGmAqb8pQSm/aUCpv+lAab/pQCmAab/pQGmAKb/pQGmAKb/pQGmAKb+pQOm/aUCpgCm/qUCpv+lAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b9pQGmAKYApgGm/qUCpv6lAaYApgCm/6UCpv6lAqb+pQGm/6UCpv6lAqb9pQOm/qUCpv6lAqb+pQGmAab+pQKm/6X/pQKm/qUBpgCmAKYApgCm/6UCpv6lA6b8pQOm/6UApgCmAKYApv+lA6b8pQOm/6X/pQGmAKYApgCmAKb/pQKm/aUDpv6lAaYApv+lAKYCpv6lAaYApv+lAaYApv+lAqb+pQGm/6UApgGm/1kBWv9ZAFoBWv5ZA1r9WQJa/1kAWgFaAFr+WQNa/VkCWgBa/lkDWv1ZA1r8WQVa+1kEWv5ZAFoBWv9ZAVoAWv9ZAVr/WQJa/lkBWgBa/1kCWv5ZAVoAWv9ZAlr+WQJa/lkAWgJa/lkCWv5ZAVr/WQJa/qUCpv2lBKb8pQOm/qUApgGmAKb/pQGm/6UApgGm/6UBpv+lAKYBpv6lA6b8pQWm+qUGpvqlBqb8pQKm/qUBpgCmAab/pQGm/aUEpv2lAqb/pQCmAab/pQGm/qUDpv6lAaYApv+lAqb+pQGmAab+pQKm/qUCpv6lAaYApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b8pQSm/aUBpgGm/qUBpgCm/6UCpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQGm/6UApgGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQKm/aUDpv6lAaYApgCmAKYApgGm/qUCpv+lAKYApgCmAKYBpv+l/6UBpgCmAab/pf+lAqb+pQKm/6X/pQKm/qUCpv6lAqb+pQGmAab9pQSm/KUDpv+lAKYApgCmAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAab+pQOm/KUEpv2lAaYBpv6lAqb+pQGmAab+pQKm/qUBpgGm/aUDpv6lAaYApv+lAab/pQKm/aUCpv+lAab/pQKm/KUEpv6lAKYCpv2lAqb/pQGm/6UBpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UBpv9ZAVr/WQJa/VkDWv5ZAFoBWgBa/lkDWv5ZAFoBWv9ZAVr/WQFa/1kAWgFa/1kAWgFa/lkCWv9ZAFoBWv5ZAlr/WQFa/lkCWv9ZAVr/WQBaAVr/WQJa/VkCWgBa/1kBWv9ZAFoCWvxZBVr6WQZa/FkCWv+lAKYApgGm/6UApgGm/qUDpv2lAqb+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAKYBpv6lA6b9pQKm/6X/pQOm/aUCpv6lA6b8pQWm+6UEpvylBab8pQOm/aUBpgCmAqb9pQOm/KUEpv2lAqYApv+lAab/pQGm/6UCpv2lA6b+pQGm/6UBpv+lAqb9pQKm/6UBpgCm/qUDpvylBab8pQKm/6UApgCmAab/pQGm/qUCpv6lAqYApv6lAqb+pQOm/KUFpvulA6YApv6lAqYApv6lA6b9pQKmAKb/pQGmAKb+pQOm/aUCpgCm/6UApgGm/6UBpv+lAab/pQKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQKm/qUCpv6lAaYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUCpv+l/6UDpvylBKb9pQGmAab+pQOm/KUEpv2lAqYApv6lAqb/pQCmAab/pQGm/6UBpv+lAKYCpv2lAqYApv+lAKYBpv6lA6b9pQOm/KUFpvulBab7pQSm/aUCpgCm/qUDpv2lA6b+pQCmAqb9pQOm/qUBpv+lAab/pQGm/6UApgGm/6UApgGm/qUCWv9ZAFoAWgBaAFoAWgFa/lkCWv5ZAlr/WQBaAFoAWgFa/1kAWgBaAFoBWv9ZAVr+WQJa/1kAWgFa/lkCWv5ZAlr+WQJa/lkBWgBaAFoAWgBa/1kCWv5ZAlr/Wf9ZA1r8WQRa/VkCWv5ZAlr/WQBaAVr+pQKm/6UApgCmAKYApgCm/6UCpv2lBKb8pQOm/aUDpv6lAaYBpv2lA6b+pQGmAKYBpv6lAqb+pQKm/qUDpv2lAqb/pf+lAqb/pQGm/qUDpvylBKb9pQKm/6UApgCmAKYApgCm/6UCpv6lAaYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/qUDpv6lAaYApv6lA6b9pQOm/aUCpv+lAab/pQCmAab+pQKm/6UBpgCm/qUCpv+lAqb9pQOm/aUDpv6lAKYBpgCm/qUCpv+lAKYCpv2lAaYBpv6lAqYApv6lA6b9pQKm/6UBpgCm/6UApgGm/qUEpvulBKb8pQWm+qUGpvulBKb9pQKm/qUDpv2lAqb/pQGm/6UBpv6lA6b9pQOm/qUApgGm/qUDpv2lA6b+pQCmAab/pQGm/6UBpv6lA6b9pQKm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQCmAab+pQOm/aUCpv+lAab/pQGm/6UBpgCmAKb/pQKm/aUEpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAqb+pQGmAKb/pQKm/6X/pQKm/aUDpv6lAqb+pQGmAKb/pQKm/lkCWv1ZBFr9WQFaAFoAWv9ZA1r7WQVa/FkDWv5ZAVr/WQFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kBWgBa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQFa/1kBWgBa/1kBWv9ZAlr9WQNa/aUDpv6lAKYApgGm/6UBpv+l/6UCpv+lAKYApgGm/aUFpvqlBab9pQKm/6UApv+lA6b8pQSm/KUDpv+l/6UBpv+lAaYApgCm/6UBpv+lAab/pQGm/6UCpv6lAaYApv+lA6b8pQSm/aUCpv+lAKYApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUDpv2lAqb/pQCmAqb9pQOm/aUCpgCm/6UBpv+lAab/pQKm/KUFpvulBab8pQKmAKb+pQOm/aUCpgCm/6UApgGm/6UBpv+lAKYApgGm/qUCpv6lAqb+pQKm/qUCpv+lAab/pQCmAab+pQSm+6UEpv2lAqb/pQGm/6UApgGm/qUDpv6lAKYBpv+lAKYCpv2lA6b9pQKm/6UBpgCm/6UApgCmAKYBpgCm/qUCpv6lAqb/pQGm/qUCpv+l/6UDpvylBKb9pQCmA6b8pQSm/aUBpgGm/qUDpvylBKb9pQKm/6UBpv6lA6b8pQSm/aUCpv6lAqb+pQKm/aUEpvylA6b/pQCm/6UCpv6lAqb/pf+lAqb9pQSm/KUDpv6lAab/pQKm/qUBpgCmAKb/pQOm/KUDpv+l/1kCWv5ZAVoAWgBaAFr/WQJa/lkCWv9ZAFoAWgFa/lkDWv1ZAlr/WQFa/lkDWv1ZA1r9WQNa/VkDWv5ZAFoCWv5ZAVoAWv5ZBFr8WQRa/FkDWv5ZAVoAWgBaAFr/WQFa/1kBWgBa/1kBWv9ZAFoCWv1ZBKb7pQWm/KUCpgCm/qUEpvylAqb/pQCmAab/pQGm/6UApgCmAKYBpv+lAKYApgCmAKYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQGmAKb/pQKm/qUCpv6lAaYApgCmAKYBpv2lBKb9pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQGm/qUDpvylBab8pQOm/qUBpv+lAaYApv+lAqb9pQOm/aUDpv6lAaYApv+lAab/pQKm/qUBpgCm/6UCpv+l/6UCpv6lAqb+pQKm/qUCpv6lAaYApgCmAKb/pQKm/aUDpv6lAaYBpv2lA6b9pQSm/aUBpgCm/6UBpgCm/6UCpv6lAKYBpv6lA6b9pQKm/6UApgCmAKYBpv+lAab+pQOm/aUDpv2lAqb/pQGm/6UApgCmAKYBpv+lAKYBpv6lA6b9pQKmAKb/pQGm/6UBpv+lAab/pQGmAKb/pQCmAaYApv+lAab+pQKmAKb/pQGm/qUCpv6lA6b+pQCmAab+pQKmAKb/pQGm/6UApgGmAKb/pQGm/6UBpv9ZAlr9WQNa/lkBWgBaAFr/WQJa/VkEWvxZA1r+WQBaAlr+WQJa/lkBWv9ZAlr/WQBaAFr/WQFaAFr/WQFaAFr/WQFa/1kAWgJa/lkAWgFa/1kBWgBa/1kBWv9ZAFoBWv9ZAlr9WQJa/1kAWgJa/VkDWv6lAKYBpv+lAaYApv+lAKYApgGm/6UBpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQGm/qUDpv2lAqYApv6lA6b9pQGmAab/pQCmAab+pQKm/6UApgCmAab+pQOm/KUEpvylBKb9pQGmAKYApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b8pQSm/KUEpv2lAqb+pQKm/qUCpv6lAaYBpv6lAqb/pf+lAqb/pf+lA6b9pQKm/qUBpgCmAab+pQKm/qUCpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UCpv2lA6b+pQKm/qUBpgCmAKYApgCm/6UCpv+l/6UDpvulBab8pQOm/6UApv+lAab/pQKm/qUCpv6lAaYApv+lAqb+pQGmAKb/pQKm/aUDpv2lA6b+pQCmAqb8pQSm/qUApgGm/6UApgGm/6UApgGm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgGm/qUCpv6lAqb/pQGm/qUCpv6lAqb/pQCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+lAKYCpv2lA6b8pQWm/KUCpgCm/6UBpgCm/6UBpgGm/aUEpvylA6b+pQKm/qUCpv6lAaYAWgBaAFoAWgBaAFoAWv9ZAlr9WQRa/VkBWv9ZAVoAWgBaAFoAWgBaAFoAWgBaAFoAWgBaAFoAWgBa/1kCWv5ZAVoAWv9ZAlr+WQFaAFoAWgBaAFr/WQFaAVr+WQFa/1kBWgBaAFoAWv9ZAlr+WQJa/lkCpv+lAab/pQCmAab/pQGm/6UCpv2lA6b8pQWm/KUCpgCm/qUDpv2lAqYApv+lAqb9pQOm/qUBpgCmAKYApgCm/6UCpv6lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAab9pQWm+6UEpv2lAqb/pQGm/6UApgCmAab+pQOm/KUEpv2lAaYApv+lAqb/pf+lAab/pQGmAKYApv+lAaYApv+lAqb/pf+lAqb+pQGmAKYApv+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKb/pQKm/aUEpv2lAKYCpv2lA6b+pQGm/6UBpgCm/6UBpv6lA6b+pQGm/6UApgGm/6UBpv+lAKYBpv+lAaYApv6lA6b+pQGmAKb/pQKm/qUBpv+lAqb+pQKm/aUDpv6lAqb+pQKm/qUCpv2lBKb9pQKm/6X/pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKYBpv6lAaYBpv6lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQGmAKYApv+lAqb+pQKm/qUCpv6lAqb/pf+lAqb/pQCmAab+pQKm/6UBpv6lA6b8pQWm+6UDpgCm/qUDpvylBFr9WQJa/1n/WQJa/1kAWgBaAFoAWgBaAFoAWgFa/lkDWvxZBFr9WQJa/1kBWv9ZAFoAWgBaAFoBWv5ZAlr/WQBaAFoAWgBaAFoBWv5ZAlr+WQFaAVr+WQJa/1kAWgFa/lkCWv9ZAFoCWvxZBFr9WQFaAab/pQCmAKYBpv6lA6b8pQOm/6UApgCmAKb/pQKm/aUDpv2lA6b+pQGm/6UBpv+lAaYApv+lAqb9pQOm/qUCpv6lAab/pQKm/aUEpvulBab8pQOm/aUCpv+lAab/pQGm/aUFpvqlBqb7pQSm/aUCpv6lA6b9pQKm/6UApgGm/qUCpv+lAKYApv+lAqb+pQOm/KUDpv6lAqb+pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQCmAKYBpv6lA6b9pQKm/6UApgKm/aUDpvylBab7pQWm/KUCpgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQGm/6UBpv+lAqb9pQOm/aUCpv+lAKYBpv6lAqb/pf+lA6b8pQOm/6X/pQOm/KUEpvylA6b+pQGmAKYApgCm/6UBpv+lAaYApgCm/6UCpv2lA6b+pQKm/qUBpgCmAKYApgCm/6UBpgCm/6UCpv2lA6b+pQCmAqb+pQKm/qUBpv+lAqb+pQKm/qUBpv+lAaYApgCm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/qUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgGm/qUDpvxZBFr9WQJa/1kBWv9ZAFoBWv5ZA1r+WQBaAlr8WQVa/FkCWgBa/lkDWv1ZAloAWv5ZA1r9WQJa/1kBWv9ZAlr9WQJa/1kBWv9ZAFoBWv5ZAlr+WQJa/lkBWgBaAFoBWv5ZAlr9WQRa/VkCWgBa/lkDWvylBKb+pQCmAab+pQGmAab+pQKm/qUBpgCmAKYApgCmAKYApgCm/6UBpv+lAqb+pQGm/6UApgGmAKb/pQGm/6UApgKm/qUApgGm/qUDpv2lAqb+pQKm/6UApgCmAKYApgCmAab+pQKm/6UApgGm/qUCpv+lAKYApgCmAKYApgCmAKb/pQGmAKb/pQGm/6UBpv+lAqb8pQWm+6UEpv2lA6b9pQOm/KUEpv2lA6b+pQCmAKYApgGm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgGm/6UApgCmAKYBpv+lAKYApgGm/qUCpv2lA6b/pQCmAKb/pQGmAKYApv+lAab/pQKm/aUDpv6lAab/pQGmAKb/pQKm/aUDpv2lBKb7pQWm+6UFpvylA6b+pQGmAKYApgCmAKYApgCmAKYApgGm/qUCpv6lAaYApgCm/6UCpv6lAaYApv+lAaYApgCmAKYApv+lAqb+pQKm/qUCpv6lAqb/pf+lAqb+pQKmAKb9pQSm/KUDpv6lAqb/pQCmAKb/pQKm/6UBpv6lA6b9pQKm/6UBpv+lAab+pQKm/6UApgGm/qUCpv6lAqb/pQCmAab9WQVa+1kDWv9Z/1kCWv5ZAVoAWgBa/1kCWv5ZAVoAWgBa/1kCWv5ZAVoAWv9ZAVoAWgBa/1kCWv5ZAVoAWv9ZAVoAWv9ZAlr9WQNa/VkDWv1ZA1r9WQJaAFr+WQNa/VkCWv9ZAVr+WQNa/VkCWv9ZAFoAWgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKb/pQGmAKYApv+lAab/pQKm/6UApgCmAKYApgGm/qUCpv+lAKYApgGm/qUCpv6lAaYBpv+lAKYApgCmAab/pQCmAab+pQSm/KUCpv+lAKYBpv+lAab/pQCmAKYApgGm/6UBpv+lAKYCpv2lBKb7pQSm/aUEpvylAqb/pQCmAqb+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/qUEpvylA6b+pQCmAaYApv6lA6b9pQKm/6UApgCmAab/pQCmAKYApgGm/6UBpv6lA6b9pQKmAKb+pQSm+6UFpvylAqb/pQGmAKb/pQCmAab+pQOm/aUCpgCm/qUDpvylBab7pQSm/aUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgGm/qUCpv+l/6UCpv6lAaYBpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv2lAqb/pQKm/aUDpv2lA6b+pQKm/aUDpv6lAaYBpv6lAaYApv+lAqb/pQCmAKb/pQKm/qUCpv6lAaYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQGm/6UBpgCmAKb/pQKm/aUEWvxZBFr9WQFaAFr/WQJa/VkEWvtZBlr6WQRa/lkBWgBaAFoAWgBa/1kCWv5ZA1r8WQRa/FkEWv1ZAlr/WQBaAVr9WQRa/VkBWgFa/VkEWv1ZAVoBWv5ZAlr+WQJa/1kAWgBa/1kCWv5ZAlr+WQFa/1kBpgCm/6UBpv+lAab/pQGm/qUCpgCm/6UBpv6lAqb/pQKm/qUBpv+lAaYApgCmAab9pQOm/qUBpgCm/6UBpgCm/6UBpv+lAaYApv+lAqb9pQKmAKb/pQGm/6UApgGm/6UBpv6lA6b+pQGm/qUCpv+lAqb+pQCmAab/pQKm/qUBpgCmAKYApgGm/qUCpv6lAqb/pQCmAKYApv+lA6b7pQWm/KUCpgGm/aUDpv2lA6b/pQCmAKb/pQGmAab+pQKm/qUCpv6lAqb9pQSm/aUCpv6lAqb/pQCmAab+pQOm/aUCpv+lAKYBpv6lAqb+pQOm/aUBpgCm/6UCpv+l/6UBpgCm/qUEpvulBab8pQKmAKb+pQOm/aUCpv+lAKYBpv6lA6b9pQOm/aUCpv+lAaYApv+lAKYApgCmAab/pQGm/6UApgGm/6UCpv6lAaYApv+lAqb+pQKm/aUEpvulBab8pQKm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgCmAab+pQOm/KUDpgCm/6UBpv+lAab/pQKm/aUDpv6lAKYCpv2lA6b9pQKm/6UBpv+lAab/pQGmAKb/pQGmAFoAWv9ZA1r7WQVa/VkBWgFa/VkDWv5ZAlr+WQJa/VkEWvxZA1r/Wf9ZAlr+WQFaAFoAWv9ZAlr+WQFaAVr+WQJa/lkBWgFa/1kAWgBaAFoAWgFa/lkCWv9ZAFoAWgBaAFoBWv5ZA1r8WQNa/lkBWgFa/6X/pQGmAKYApgCmAKYApgCm/6UBpgCmAab+pQGm/6UBpgGm/qUBpgCmAKYApgCm/6UCpv6lA6b8pQOm/qUCpv+lAab+pQKm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/6UApgCmAKYBpv6lA6b8pQSm/aUCpv6lAqb/pQCmAab9pQSm/aUCpv+lAKYBpv+lAab+pQOm/aUDpv6lAKYBpv+lAaYApv+lAqb+pQGmAab+pQKm/qUCpv+lAab+pQKm/qUDpv2lAqb/pQCmAab+pQKm/6UApgGm/aUDpv+l/6UCpv6lAaYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAKYCpv2lA6b9pQGmAab/pQCmAab+pQOm/KUEpvylBKb9pQGmAab9pQSm/KUDpv+l/6UCpv6lAqb/pQCm/6UCpv6lAqb+pQGmAKb/pQOm+6UFpvylA6b/pf+lAqb9pQOm/qUBpgCm/6UApgGm/6UCpv2lAqb/pQGm/6UBpv6lA6b+pQCmAqb8pQWm/KUCpgCmAKb/pQKm/KUFpvylA6b+pQCmAab/pQKm/qUBpv+lAaYApgCmAKb/pQGm/6UBpv9ZAlr+WQBaAlr8WQVa/VkAWgJa/VkDWv5ZAVr/WQFaAFr/WQJa/VkEWvtZBVr8WQNa/lkBWgBa/1kCWv1ZA1r+WQFaAFr/WQFaAFoAWv9ZAVr/WQJa/1kAWgBa/1kCWv9ZAVr+WQFaAVr+WQNa/FkDWgCm/aUEpvylBKb9pQKm/qUBpgCmAab+pQKm/qUBpgGm/6X/pQKm/qUCpv+l/6UCpv6lA6b9pQGmAKYBpv+lAab+pQKm/6UApgGm/aUFpvulBKb9pQGmAab+pQKm/qUCpv6lAqb9pQOm/qUCpv6lAaYApgCmAKYApgCmAab+pQKm/6UApgKm/aUCpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAab/pQGmAKb/pQGm/qUDpv2lA6b8pQOm/6X/pQOm/KUDpv+l/6UCpv+lAKYBpv6lAqb/pQCmAab/pQCmAqb8pQWm+6UEpv2lAqb+pQKm/qUBpgCm/6UCpv6lAaYApgCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+lAab/pQGm/qUDpv2lA6b9pQKm/qUCpv+lAKYBpv6lAqb/pQCmAKYBpv6lA6b9pQKm/6UApgGmAKb/pQGm/6UBpgCmAKb/pQGmAKb/pQKm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQCmAab/pQGm/6UApgCmAab/pQCmAab+pQOm/aUCpv+lAKYBpv+lAab+pQOm/aUCpgCm/qUCpv+lAab+WQJa/lkCWv9Z/1kBWgBaAFoAWgBa/1kBWgFa/VkEWvxZAloBWv5ZAlr9WQNa/lkCWv5ZAVoAWgBa/1kCWv5ZAVoAWv9ZAlr/Wf5ZBFr7WQZa+1kCWgBa/1kBWv9ZAVoAWgBa/lkDWv1ZA1r+WQFa/1kCpvylBKb+pQCmAab+pQOm/aUCpv6lAqb/pQGm/qUCpv+lAKYBpv6lA6b9pQKmAKb+pQSm/KUDpv6lAaYApgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQGmAKb/pQKm/qUBpgCmAKYApgGm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUEpvylAqYBpv6lAqb+pQKm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCmAKYBpv2lA6b+pQGmAab+pQGmAKb/pQKm/qUCpv6lAaYApgCmAab9pQSm/KUEpvylA6b/pQCmAab+pQGmAab+pQKm/6UApgCmAab+pQOm/KUFpvulBab7pQSm/aUDpv6lAab/pQGm/6UCpv6lAqb+pQGm/6UBpgCm/6UCpvylBab8pQKmAKb+pQOm/aUCpv+lAKYApgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgCmAKYBpv+l/6UDpv2lAqYApv6lA6b9pQOm/VkDWv5ZAFoCWv1ZA1r9WQNa/VkEWvtZBFr+WQBaAlr9WQNa/VkCWv9ZAVr/WQFa/1kAWgJa/FkFWvtZBFr+WQFa/1kBWv5ZA1r+WQFa/1kAWgFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVr+WQNa/qUCpv2lA6b9pQOm/qUBpgCm/6UBpv+lAaYApv+lAab/pQGm/6UBpv6lAqb/pQGm/qUCpv6lAqb+pQKm/qUBpgCm/6UCpv6lAKYCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYBpv6lA6b9pQKm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAab/pQKm/aUDpv2lA6b+pQCmAab+pQOm/aUCpv+lAKYBpv6lAqb/pQGm/6UApgCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv6lAaYApgCm/6UCpv2lA6b9pQKm/6UBpv+lAab/pQCmAab/pQGm/6UBpv+lAqb9pQKm/6UApgKm/aUDpv2lAqb+pQOm/qUBpgCm/qUDpv6lAaYApv+lAqb+pQKm/qUBpgCmAKYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv2lBKb8pQSm/KUDpv+lAKYBpv6lAqb/pQGm/qUCpv+lAKYApgCmAKYBpv+l/6UCpv6lA1r9WQFaAVr9WQVa+1kDWv9ZAFoAWgBaAFoBWv9ZAFoBWv5ZAloAWv5ZA1r9WQJaAFr+WQNa/VkDWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/1kAWgFaAFr/WQJa/VkDWv5ZAFoBWv9ZAFoBWv5ZAlr+WQFaAKYApgGm/qUBpv+lAqb/pQCmAKb/pQGmAKYBpv6lAqb+pQCmA6b9pQKm/qUBpv+lAqb+pQGmAKb/pQGmAKb/pQGmAKb/pQOm/KUDpv6lAaYBpv6lAqb+pQGmAKYApgGm/qUCpv2lBKb8pQSm/KUDpv6lAaYApgCmAKYApgCmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgGm/qUCpv6lAqb/pQCmAKYApgGm/qUCpv2lBKb9pQKm/qUCpv6lA6b8pQWm+6UEpv6lAKYBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb+pQSm+6UFpv2lAKYCpv2lA6b+pQGmAKb+pQOm/aUDpv2lA6b8pQWm/KUDpv6lAaYApgCmAKYBpv6lAqb/pQCmAab+pQKm/6UApgCmAKYApgCmAKYApv+lA6b7pQam+6UCpgGm/aUEpv2lAqb+pQGmAKYApgGm/6UApgCmAKYBpv6lAqb+pQKm/6UApgCmAKYApgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAKYApgGm/qUCpv2lBKb8pQOm/6X+pQSm/KUDpv9Z/lkEWvxZA1r+WQFaAFr/WQJa/VkDWv5ZAVoAWv5ZA1r9WQNa/lkBWv5ZA1r9WQNa/lkAWgBaAVr/WQBaAVr+WQNa/FkEWvxZBFr8WQRa/FkDWv9Z/1kDWvtZBVr8WQRa/VkBWv9ZAVoAWgBaAFoAWv+lA6b8pQSm/aUBpgGm/qUDpv2lAqb+pQKmAKb/pQGm/qUCpgCm/6UBpv6lA6b9pQOm/aUDpv2lAqb/pQGmAKb/pQCmAab/pQGm/6UApgGm/6UApgGm/6UApgGm/qUDpv2lAqb/pQGm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQKm/qUBpv+lAaYApgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpv+lAaYApgCmAKb/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UBpgCm/6UBpgCm/6UCpv6lAKYCpv6lAqb+pQGm/6UCpv6lAqb+pQGmAKYApgCmAKYApgCmAab+pQGmAKYApgCmAab9pQOm/aUDpv2lA6b9pQOm/aUCpv+lAab/pQCmAab/pQGm/6UBpv+lAKYBpv+lAaYApv+lAKYCpv2lA6b9pQKm/6UApgGm/qUCpv+l/6UCpv+l/6UDpvulBqb7pQOm/qUCpv6lAqb+pQKm/qUBpgGm/qUCpv6lAaYApgCmAKb/pQKm/qUBpgCm/6UCpv6lAqb+pQKm/qUCpv6lAqb/pQCmAKb/WQJa/lkBWgFa/FkFWvxZA1r+WQFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/1kBWv9ZAFoAWgBaAlr8WQVa+lkFWv5ZAFoBWv5ZAlr/WQFa/1kAWgFa/lkDWvxZBFr9WQJa/lkCWv5ZAlr/Wf9ZA1r8pQSm/aUCpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb/pQGm/6UApgGm/qUDpv2lA6b9pQKm/qUCpv+lAab+pQKm/qUCpv+lAKYApgCmAKYBpv6lAqb+pQKm/6UApgGm/qUDpv2lAqb/pQCmAab/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv+lAab/pQCmAKYBpv+lAab/pQCmAKYBpv+lAab/pQGm/6UBpv+lAab/pQCmAab/pQCmAab+pQKm/6UBpv+lAab/pQGmAKb/pQGm/6UCpv2lAqb+pQKm/6UBpv6lAqb+pQKm/6UApgGm/6UApgKm/aUDpv6lAab/pQKm/aUEpvulBab8pQKm/6UBpv+lAqb9pQKm/6UBpgCm/6UBpv+lAaYApv6lA6b+pQGmAKb+pQOm/qUCpv6lAKYCpv6lAqb+pQKm/aUEpvylBKb9pQKm/qUCpv6lAqb/pQCmAKYApgCmAKYApgCm/6UCpv2lBKb8pQOm/aUDpv6lAab/pQCmAqb9pQKm/6UApgGmAKb+pQKm/6UApgKm/aUCpv+lAab/pQGm/6UApgGm/6UBpv+lAFoAWgFa/1kBWv5ZAlr+WQJa/1kAWgBaAFoAWgFa/lkDWvxZBVr8WQJaAFr+WQNa/lkBWgBa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVr/WQJa/lkBWgBa/1kCWv5ZAlr+WQJa/lkCWv5ZAqb+pQKm/6X/pQKm/qUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQCmAab+pQKm/6UApgGm/6UApgCmAKYBpv+lAKYApgCmAab+pQOm+6UGpvulA6b/pf+lAqb/pQCmAKYApv+lAqb/pf+lA6b7pQam+6UDpv6lAqb+pQOm/KUDpv+l/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv6lAqb/pf+lAqb+pQOm/aUBpgCmAKYApgCmAKYApv+lAab/pQGmAKb/pQCmAab+pQSm/KUCpv+lAab/pQKm/qUApgGm/6UApgGm/6UApgGm/qUCpv6lA6b9pQGmAab+pQKmAKb+pQOm/aUCpgCm/6UCpv2lA6b+pQGmAKb/pQKm/qUBpgCmAKYApgCmAKYApgCmAab+pQKm/6X/pQOm/aUBpgGm/aUDpv6lAqb+pQKm/aUEpvylBKb8pQSm/aUBpgCmAKYApgGm/aUEpvylBab6pQWm/KUDpv+l/6UCpv2lA6b+pQGmAab9pQSm/KUDpgCm/aUEpvylA6b+pQKm/qUBpgCmAKb/pQJa/lkBWgBa/1kCWv5ZAlr9WQNa/lkBWgBa/1kBWgBa/lkEWvtZBVr8WQJa/1kCWv1ZBFr7WQRa/1n+WQRa+1kGWvpZBVr8WQNa/1n/WQFaAFr/WQJa/VkDWv5ZAVr/WQJa/VkEWvxZA1r+WQJa/VkEWv2lAaYBpv2lA6b+pQGmAKYApv+lAab/pQGmAKb/pQKm/qUBpv+lAab/pQKm/aUEpvqlB6b6pQWm/KUCpgCm/6UBpv+lAab/pQGm/6UApgGm/qUDpv2lA6b8pQSm/aUCpv+lAab+pQOm/aUCpv+lAKYBpv+lAab/pQCmAab+pQOm/aUCpv+lAKYApgCmAKYApgGm/qUBpgGm/qUCpv6lAaYBpv+lAKYApgCmAKYBpv+l/6UCpv6lAaYBpv6lAab/pQGmAKYBpv+lAKYApgGmAKb/pQKm/qUBpgGm/aUDpv6lAqb+pQGm/6UBpgCm/6UBpv6lA6b9pQOm/aUCpv6lA6b9pQKm/6UApgGm/6X/pQKm/qUDpv2lAaYApv+lAqb/pf+lAqb+pQGmAab+pQGmAKYApgGm/qUBpv+lA6b8pQSm/KUDpv6lAqb+pQKm/qUBpgCmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv2lA6b+pQGmAKb/pQCmAab/pQGmAKb+pQOm/aUDpv6lAKYBpv6lA6b+pQCmAab+pQKmAKb+pQOm/aUBpgGm/6UBpv6lAqb+pQNa/VkCWv5ZA1r9WQJa/1kAWgFaAFr+WQJa/lkCWv9ZAVr+WQJa/lkCWv5ZAlr+WQFaAFoAWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAVoBWv5ZAlr9WQNa/lkCWv5ZAlr+WQFa/1kCWv5ZAlr/Wf9ZAlr/WQCmAab/pQCmAKYBpv6lA6b+pf+lA6b8pQSm/qUApgCmAab+pQKm/qUCpv6lAqb+pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAKYApgCmAab+pQKm/qUCpv+lAKb/pQOm/KUFpvqlBab8pQSm/aUCpv6lAqb+pQKm/6X/pQKm/6X/pQKm/aUDpv6lAqb9pQOm/aUDpv6lAab/pQGm/6UBpv+lAKYApgGm/qUDpv2lAqYApv6lA6b9pQKmAKb/pQGmAKb+pQOm/aUDpv6lAKYCpv2lA6b9pQOm/qUCpv2lAqb/pQKm/qUBpgCm/qUEpvylAqYApv+lAqb9pQOm/aUCpgCm/qUDpv2lAqb/pQGm/6UBpv+lAKYBpv+lAaYApv6lA6b9pQOm/qUApgGm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQOm/aUDpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQCmAqb8pQSm/qUApgGm/qUCpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgGm/qUDpv2lAaYBpv6lA6b8pQSm/aUCpv6lAqb/WQFa/1kAWgFa/1kBWv9ZAVoAWv9ZAVr+WQRa/FkDWv5ZAFoCWv5ZAVoAWv9ZAlr+WQFaAFoAWv9ZAlr9WQNa/lkBWgBa/1kBWv9ZAlr9WQNa/lkBWgBa/1kBWgBaAFr/WQJa/lkCWv5ZAVoAWgBaAFoApgCm/6UBpv+lAqb+pQGm/6UApgKm/aUDpv2lA6b+pQCmAab/pQGmAKb+pQOm/aUCpv+lAKYBpv+lAKYApgCmAKYApgCmAKYApgCmAKYApv+lAqb9pQSm/aUBpgCm/6UBpgCm/6UCpv2lA6b+pQGm/6UBpgCm/6UCpv2lAqb/pQCmAKYCpvylBKb7pQWm/aUDpvylA6b9pQSm/KUEpvylAqYApv+lAqb+pQGmAKb/pQGmAKb/pQKm/aUDpv6lAKYBpv+lAab/pQGm/qUDpv2lAqYApv+lAab/pQGmAKYApgCm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQGmAKb/pQKm/qUCpv6lAqb+pQOm/aUCpv+lAKYBpv+lAab/pQGm/6UBpv+lAqb+pQKm/qUBpgCmAKYBpv6lAab/pQGmAab9pQOm/aUDpv6lAaYApv+lAaYApgCmAab+pQKm/qUCpv+lAab+pQKm/6UApgGm/qUCpv6lAqb/pf+lA6b8pQOm/6X/pQKm/qUCpv6lAaYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab/pQCmAKYBpv+lAab/pQCmAab+pQOm/aUDpv2lA1r9WQNa/lkBWgBaAFoAWgBaAFr/WQJa/lkCWv5ZAlr9WQRa/FkEWv1ZAlr+WQJa/lkDWvxZBFr8WQRa/VkBWgBaAFoBWv9ZAFoAWgBaAVr/WQFa/lkCWv5ZA1r8WQVa+lkGWvtZBFr9WQJaAFr/WQFa/6UApgGm/6UCpv2lA6b9pQKmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv6lAKYBpv+lAaYApv+lAaYApv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv+lAqb9pQKmAKb/pQGmAKb/pQGm/6UBpv+lAqb9pQOm/aUDpv6lAKYBpv+lAab/pQGm/6UBpv+lAKYBpgCm/6UBpv+lAaYApv+lAqb+pQKm/qUBpgGm/qUCpv6lAqb/pQCmAKYApgCmAab/pQCmAKYBpv6lA6b9pQGmAqb8pQSm/aUCpv6lAqb/pQCmAab9pQSm/aUCpv+l/6UDpvylBab6pQWm/aUCpgCm/6UApgCmAaYApv+lAqb9pQOm/qUBpv+lAqb9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAaYApv+lAqb+pQKm/aUDpv+l/6UCpv6lAaYApv+lAab/pQGm/6UBpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQGm/qUDpvylBKb+pQGm/qUDpvylBKb+pQCmAqb9pQKm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQNa/VkCWv9ZAlr9WQNa/VkCWgBa/1kBWv9ZAVoAWv9ZAFoBWgBaAFr/WQBaAVr/WQJa/FkFWvtZBFr9WQJa/lkCWv5ZAlr+WQFaAFr/WQJa/lkCWv5ZAlr+WQJa/1kBWv9ZAFoBWv5ZAlr/WQBaAVr+WQKm/qUBpgGm/aUEpv2lAaYApv+lAaYApgGm/aUDpv6lAaYApgCm/6UCpv6lAab/pQGmAKb/pQKm/KUFpvylA6b9pQOm/aUDpv6lAaYApv6lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAqb9pQOm/qUCpv+l/6UCpv2lBab6pQam+qUFpv2lAaYApv+lAqb+pQGmAKb/pQKm/qUBpv+lAqb+pQGmAKb/pQGmAKb/pQGmAKb/pQGm/6UBpv+lAqb8pQWm/KUDpv2lAqYApv+lAqb8pQSm/aUDpv2lAqb/pf+lA6b8pQSm/aUCpv+l/6UCpv6lAqb+pQGmAKb/pQGm/6UBpgCm/6UCpv2lBKb7pQam+6UDpv+lAKYApgCmAKYApgCmAKb/pQKm/qUCpv2lA6b+pQGmAKb/pQGmAKb+pQOm/aUDpv2lAqb/pQCmAab/pQCmAab/pQCmAab+pQKm/6UApgCmAab+pQKm/qUCpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgCmAab/pQGm/qUCpv+lAab/pQCmAKYApgCmAKYApgCm/6UCpv2lBKb8pQKmAab9pQSm/KUDpv2lA6b9WQNa/lkBWv9ZAVr/WQFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVoAWv9ZAVoAWv5ZBFr8WQJaAFr+WQRa/FkCWv9ZAVoAWgBa/1kBWv9ZAlr+WQFaAFr/WQJa/VkDWv1ZA1r+WQBaAVr/WQBaAFoBWv5ZA1r8pQOm/6UApgGm/qUBpgGm/6UApgCmAKYApgGm/qUBpgCmAKYApgCmAKYApv+lAqb+pQKm/qUBpv+lAqb+pQGmAKb/pQGm/6UCpv2lBKb7pQWm/KUCpgCm/6UBpgCm/6UBpgCm/6UBpgCm/6UBpv+lAab/pQGm/qUDpv2lAqb/pQCmAab/pQGm/qUDpv6lAKYCpv2lA6b+pQGmAKb/pQKm/qUBpgGm/aUEpv2lAaYApv+lAqb/pQCmAKb/pQKm/qUDpv2lAaYBpv6lAqb/pQCmAab/pf+lAqb/pQGm/6UApgCmAab+pQOm/KUEpv2lAqb/pQCmAKYApgGm/qUDpvylBKb9pQKm/qUCpv6lA6b8pQSm/KUEpv2lAqb+pQKm/6UApgGm/qUCpv6lAqb/pf+lAqb9pQOm/6X/pQKm/qUBpgCmAKYBpv+lAKYApgCmAab/pQCmAKYApgCmAab+pQKm/qUCpv6lAqb+pQOm/KUEpvylBKb9pQKm/6UApgGm/qUDpv6lAab/pQCmAaYApv+lAab+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAaYApv+lAab+pQKmAKb/pQGm/1kAWgFa/1kBWv9ZAFoBWv9ZAVr/WQBaAVr+WQNa/VkCWv5ZA1r8WQRa/FkEWv1ZAlr+WQJa/1kAWgBaAVr+WQNa/FkEWv1ZAlr/WQBaAVr/WQBaAVr/WQFa/1kAWgFa/1kAWgFa/lkCWv9ZAFoBWv9ZAFoBpv+lAaYApv+lAab/pQGm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UCpv6lAaYApv+lA6b7pQam+qUFpvylA6b+pQGmAKb+pQSm+6UFpvulBab8pQOm/qUBpv+lAaYApv+lA6b6pQem+qUFpvylA6b+pQGmAKYApgCmAKYApgCmAKYBpv6lA6b9pQKm/6UApgGm/6UBpv+lAab/pQGm/6UCpv6lAaYApgCmAKYBpv2lBab6pQem+aUGpvylAqb/pQGm/6UBpv+lAKYCpv6lAab/pQGmAKYApgCm/6UCpv2lA6b+pQGmAKb/pQGm/6UCpv2lA6b+pQCmAaYApv+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv+lAKYBpv6lAqb/pQCmAKYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv2lA6b/pf+lAqb9pQSm/aUCpv6lAaYApgCmAab+pQKm/aUDpv+l/6UCpv6lAaYApgCmAKYApgCmAKYApgGm/qUCpv+lAKYApgCmAKYApgGm/aUDpv6lAqb+pQGm/6UCpv6lAab/pQGmAKb/pQGmAKb/pQGmAKb+pQOm/lkBWgBa/lkDWv5ZAVoAWv9ZAVoAWgBa/1kCWv5ZAVoBWv5ZAVoBWv9ZAVr/WQFa/1kCWv1ZA1r+WQFa/1kBWv9ZAVoAWv9ZAVr/WQFa/1kCWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/lkDWv1ZA1r+WQBaAab+pQSm/KUCpv+lAab/pQKm/KUFpvulBab8pQOm/aUCpgCm/6UCpv6lAaYApv+lAqb+pQKm/qUBpv+lAqb+pQGm/6UBpv+lAqb+pQCmAqb9pQOm/qUBpv+lAqb9pQOm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYBpv6lAaYApgCm/6UCpv2lA6b/pf6lBKb7pQWm/KUDpv6lAqb9pQSm/KUCpgGm/qUCpv+l/6UCpv6lAqb/pQCmAab9pQSm/aUCpv6lAqb/pQCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb/pQGm/qUDpvylBKb9pQKm/6UApgCm/6UCpv6lA6b8pQOm/qUBpgGm/qUCpv6lAqb/pQCmAKYBpv+lAab+pQKm/6UCpvylBab6pQam/KUCpgCm/qUDpv2lA6b9pQKm/qUDpv2lAqb+pQGmAKYApv+lAqb+pQGmAKb/pQKm/6UApgCmAKYBpv+lAKYBpv6lA6b9pQGmAab+pQKm/6X/pQKm/qUCpv+lAKYApgCmAKYBpv+lAKYApv+lA6b9pQJa/lkCWv9ZAVr/WQBaAVr+WQNa/VkCWv9ZAFoBWv5ZA1r9WQJa/1kAWgFaAFr/WQBaAVr+WQRa+1kEWv1ZAloAWv9ZAVr+WQJaAFr/WQFa/1kAWgJa/VkDWv1ZAloAWv9ZAVoAWv9ZAVr/WQFaAFr/WQGm/6UBpgCm/6UBpv+lAKYBpv+lAab/pQCmAKYApgGm/6UBpv6lAaYBpv+lAab+pQGmAKYBpv6lAaYApgCmAab+pQKm/6UApgCmAab+pQOm/aUBpgCmAKYApgGm/qUBpgCmAab+pQKm/qUCpv+lAKYApgCmAab+pQKm/qUCpv+lAKYApgCmAab/pQCmAKYApgGmAKb+pQOm/KUFpvulBKb9pQKmAKb/pQCmAab/pQGmAKb/pQKm/aUDpv6lAaYBpv2lA6b+pQGmAab9pQOm/qUBpgCmAKb/pQGmAKb/pQKm/aUEpvylBKb8pQOm/qUCpv+lAab+pQKm/qUCpv+lAab/pQCmAKb/pQOm/aUCpv+l/6UCpv6lAqb/pQCmAKYApgCmAKYBpv+lAKYBpv2lBab7pQSm/aUBpgCmAab+pQKm/aUEpvylBKb9pQGmAKYApgCmAab+pQGmAKYBpv6lAqb+pQKm/6UApgCmAKYApgCmAab+pQKm/qUBpgGm/qUBpgGm/qUCpv6lAaYApgCmAab+pQGmAKYApgCmAKb/pQKm/qUBpv+lAaYApgCm/6UApgGmAKb/pQKm/aUDpv+l/6UCWv5ZAlr+WQNa/FkEWv1ZAVoAWgBaAFoAWgBaAFoAWgBa/1kCWv5ZA1r8WQNa/lkBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/lkDWv1ZA1r9WQNa/VkDWv1ZA1r+WQFaAFr/WQFaAFr/WQFa/1kBWgBa/1kBpv+lAaYApv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpgCmAKYApv+lAaYApgCmAKb/pQGmAKYApgCm/6UBpgCmAKb/pQKm/aUDpv6lAab/pQGm/6UApgKm/aUCpv+lAKYBpv+lAKYApgGm/6UApgCm/6UDpv2lAqb+pQGmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab+pQKm/6UApgCmAab/pQCmAab+pQKm/6X/pQOm/aUBpgGm/aUEpv6lAKYBpv+lAKYBpv+lAKYBpv6lAqb+pQOm+6UGpvulA6YApv6lA6b9pQKm/6UBpgCm/qUDpv2lA6b+pQCmAab/pQGm/6UBpv+lAab/pQGm/6UApgKm/aUDpv6lAKYBpv+lAaYApv+lAab/pQGm/6UCpv2lBKb8pQKmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv6lAKYCpv2lA6b+pQCmAqb9pQOm/aUDpv2lA6b+pQCmAaYApv6lBKb7pQWm/KUDpv6lAaYApgCmAKYApgCm/6UCpv6lAaYApv+lAqb+pQGmAKYApgGm/qUCpv6lAqb+pQKm/qUDpvylBKb8pQSm/aUDpv2lAlr/WQBaAVr/WQBaAFoAWgFa/lkCWv5ZAVoBWv5ZAlr/Wf9ZAlr/WQBaAlr8WQRa/VkDWvxZBVr7WQVa/FkCWv9ZAVoAWgBaAFr/WQFa/1kCWv1ZBFr7WQVa/FkDWv5ZAVoAWgBaAFoAWgBaAFoAWgBaAab+pQKm/qUCpv+lAKb/pQOm+6UGpvqlBKb/pQCm/6UBpv+lAaYBpv6lAab/pQGmAKb/pQKm/aUEpvulBab7pQSm/qUBpv+lAab+pQOm/aUDpv6lAKYBpv+lAqb+pQGm/qUDpv6lAqb9pQOm/qUBpgCm/6UCpv+l/6UCpv6lAqb+pQKm/aUEpv2lAaYBpv6lAqb+pQKm/qUDpv2lAaYBpv2lBKb9pQGmAab9pQOm/6X/pQOm/KUDpv6lAqb/pQCmAKb/pQKm/qUCpv6lAqb+pQGmAKYApgGm/qUCpv6lAqb/pQCmAab+pQKm/qUCpv6lAqb+pQKm/qUCpv6lAqb/pQGm/6UApgCmAab/pQKm/aUCpv+lAKYBpv+lAab+pQOm/aUCpv+lAab/pQGm/6UApgKm/aUCpv+lAab/pQGm/qUDpv6lAab/pQGm/6UBpgCm/6UCpvylBab7pQWm/KUBpgGm/qUDpvylBKb8pQSm/aUCpv+lAKYBpv6lBKb7pQSm/qUApgGmAKb/pQGmAKb/pQKm/qUCpv6lAqb+pQKm/6UApv+lAqb+pQKm/qUBpv+lAqb+pQGmAKYApgCmAKYApgBaAFoBWv5ZA1r9WQJa/1kBWv9ZAVr/WQFa/1kCWv1ZA1r+WQFaAFr/WQJa/lkCWv5ZAVoAWgFa/lkCWv5ZAVoBWv5ZAlr+WQJa/lkDWvxZBFr9WQJaAFr+WQNa/VkDWv5ZAVr/WQJa/lkBWgBa/1kCWv6lAqb+pQGmAKYApgCmAab+pQKm/qUCpv+lAab+pQKm/qUDpv2lAqb/pQCmAKYApgCmAKYBpv6lAqb+pQKm/qUDpv2lAqb/pQCmAKYBpv6lAqb+pQKm/6UApgCmAKYApgGm/qUCpv6lAqb/pf+lAqb+pQKm/6UApv+lAqb/pQCmAab+pQKm/qUCpv+l/6UDpvylBab7pQOm/qUDpv2lAqb/pQCmAab+pQOm/KUEpv2lAqb/pQCmAab+pQOm/aUCpgCm/qUDpv2lA6b+pQCmAqb8pQWm/KUCpgCm/qUDpv2lA6b+pQCmAab/pQGm/6UApgGm/6UBpv+lAKYCpv2lA6b9pQOm/qUBpv+lAab/pQGmAKb/pQGm/6UApgGmAKb/pQGm/6UApgGm/qUDpv2lA6b8pQSm/aUCpgCm/qUDpv2lAqb/pQGm/qUDpvylBab7pQOm/6UApgGm/6UBpv6lAqb/pQGm/6UBpv6lA6b9pQKm/6UApgGm/qUCpv6lAqb+pQKm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab+pQOm/aUDpv6lAab/pQGm/6UBpgCm/6UBpv+lAKYCpv2lA6b9pQJa/1kBWv9ZAVr/WQBaAVr/WQFa/1kAWgFa/1kBWv9ZAFoBWv9ZAVr+WQJa/lkDWv1ZAlr+WQNa/FkFWvpZBlr7WQNa/1kAWv9ZAlr9WQRa+1kFWvxZA1r+WQFa/1kBWgBa/1kBWv9ZAFoCWv1ZAlr/WQGmAKb+pQKm/6UCpv6lAab+pQOm/qUBpgCm/6UCpv6lAab/pQGmAab/pf+lAaYApgCmAKYApv+lA6b9pQKm/6UApgGmAKb+pQOm/qUApgKm/aUDpv6lAKYCpv2lA6b+pQGm/6UCpvylBab8pQOm/qUBpv+lAab/pQKm/qUApgKm/aUDpv+l/qUEpvylA6b/pQCmAKYApgCmAKYBpv6lA6b8pQSm/KUEpv2lAaYApgCm/6UCpv6lAKYCpv2lBKb7pQWm+6UFpv2lAKYBpgCm/6UCpv2lAqYBpv2lA6b9pQKm/6UBpv6lA6b9pQKm/6UBpv6lA6b+pQCmAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGm/6UBpv+lAaYApv+lAaYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYBpv2lBKb8pQSm/KUDpv+lAKYApv+lAaYBpv2lBKb7pQWm/KUDpv6lAaYBpv6lAqb+pQGmAab/pQGm/qUCpv+lAKYBpv+lAKYCpv2lA6b+pQGmAKYApv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UCpv6lAaYBWv5ZAVoAWv9ZAlr/WQBaAFoAWgBaAVr/WQBaAVr+WQNa/FkEWv1ZAlr+WQNa/VkDWv1ZAloAWv9ZAVr/WQBaAVr/WQFa/1kAWgFa/lkDWv1ZA1r+WQBaAFoBWv9ZAlr9WQJaAFr+WQRa+lkHWvlZB1r5pQam+6UEpv2lAqb/pQCmAKYApgGm/qUCpv6lAqb/pQGm/qUCpv+lAab/pQCmAKYApgGm/6UApgGm/qUCpv+lAKYBpv6lA6b8pQSm/KUEpv2lAqb+pQKm/qUCpv6lAqb+pQKm/aUDpv6lAaYApv+lAab/pQGm/6UCpv2lA6b+pQCmAqb+pQGmAKYApv+lAqb+pQGmAab+pQKm/qUCpv+lAKYApgCmAKYApgGm/aUEpvylAqYApv+lAaYApgCm/qUDpv2lA6b+pQGm/6UBpgCm/6UBpv+lAaYApgCm/6UBpgCm/6UDpvulBab8pQOm/6X/pQKm/aUEpv2lAaYBpv6lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv+lAKYBpv+lAab/pQGm/qUDpv2lA6b+pQCmAab/pQKm/aUDpv2lA6b+pQKm/aUEpvulBKb+pQGmAKb/pQGm/6UCpv2lBKb8pQOm/qUBpgCmAKYApgCmAKYApgCmAKYApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUCpv+lAKYCpvylBKb9pQKmAKb+pQKm/qUCpv+lAKYApgCmAKYApgCmAFoAWgBaAVr+WQJa/1kAWgFa/lkCWv9ZAVr/WQBaAFoBWv5ZA1r9WQJa/1kBWv9ZAVr/WQFaAFr/WQBaAlr9WQNa/lkAWgFa/1kBWgBa/1kBWgBa/1kCWv1ZA1r+WQFa/1kBWv5ZAlr/WQBaAFoAWgBaAKYApv+lAqb+pQKm/aUDpv+lAKYApv+lAqb/pQCmAab9pQSm/KUDpv+lAKYApgCm/6UCpv6lAqb/pf+lA6b7pQam+6UDpv+lAKb/pQOm/KUEpv2lAqb/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv6lA6b9pQKm/6UApgCmAab+pQOm/aUCpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCmAKb/pQKm/aUEpvylAqYApv+lAqb+pQGm/6UBpgCm/6UCpv6lAKYCpv2lA6b+pQCmAab/pQGmAKb+pQOm/aUDpv6lAKYBpv+lAab/pQGm/6UCpv6lAaYApv+lA6b8pQSm/KUDpv6lAqb+pQKm/qUBpgCm/6UCpv6lAaYApv+lAaYApv+lAqb+pQGmAKYApgGm/qUCpv6lA6b+pQGm/6UApgGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b9pQKm/6UBpgCm/6UBpv6lA6b+pQKm/aUDpv2lA6b+pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAqb9pQOm/aUCpgCmAKb/pQGm/6UBpgCm/qUDpv5ZAVoAWv5ZA1r+WQFa/1kBWv9ZAVr/WQFa/1kBWv9ZAVr/WQFaAFr/WQFaAFr+WQRa+1kFWvtZBFr+WQBaAVr/WQBaAlr9WQJaAFr/WQJa/VkDWv5ZAlr+WQJa/VkDWv5ZAVoAWgBa/1kBWv9ZAVoAWv+lAab/pQGm/6UBpv+lAqb+pQCmAab/pQKm/6X/pQGm/6UBpgCmAKb/pQGm/6UBpv+lAab+pQOm/aUCpv+lAKYBpv+lAKYApgCmAab+pQOm/KUEpv2lAaYApgGm/6UApgGm/aUEpv2lAaYBpv6lAqb+pQGmAab+pQKm/qUBpgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKb/pQKm/qUBpgCm/6UBpv+lAqb+pQGm/6UApgKm/aUDpv2lA6b9pQOm/aUCpv+lAab/pQKm/aUDpv2lA6b+pQKm/qUBpv+lAab/pQGm/6UBpgCm/qUDpv2lAqYApv6lA6b9pQKm/6X/pQOm/aUCpv+l/6UDpvylBKb9pQKm/6UApgCmAab/pQGm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQGm/6UBpgCm/6UBpgCm/6UBpgCm/6UCpv6lAaYApgCmAKb/pQKm/qUBpgCm/6UBpgCm/qUEpvulBKb9pQKm/6UApgGm/qUDpvylBKb9pQKm/6UApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAKYBpv6lAqb/Wf9ZA1r8WQRa/VkBWgFa/1kAWgFa/1kBWv9ZAVr+WQNa/VkCWv5ZAlr+WQJa/lkCWv5ZA1r8WQRa/VkCWv9ZAFoBWv9ZAFoAWgBaAVr/WQBaAFoAWgBaAFoAWgBaAVr+WQJa/1kBWv9ZAVr/WQJa/lkBpv+lAaYApgCm/6UBpv+lAaYApv+lAaYApv+lAqb+pQGmAab9pQSm/KUDpv+l/6UBpv+lAaYApv+lAab/pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UBpv+lAqb+pQGm/6UBpgCmAKb/pQGmAKb/pQGmAKb/pQOm+6UEpv6lAaYBpv2lA6b9pQOm/6UApgCm/6UCpv6lAqb/pQCmAab/pQCmAab/pQKm/qUCpv6lAaYBpv2lBKb8pQOm/qUApgGm/qUDpv2lAaYBpv6lAqb/pQCmAab+pQKm/6UApgKm/KUEpv6lAab/pQCmAab/pQKm/aUCpv+lAab/pQKm/KUFpvylA6b+pQGm/6UBpgCm/6UBpv+lAKYBpv6lAqb/pQCmAab+pQOm/aUDpv6lAKYCpv2lBKb8pQKmAKb/pQKm/qUBpgCm/6UCpv+lAKYApgCmAKYBpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKb/pQOm/KUDpv6lAaYApgCm/6UCpv2lBKb8pQOm/qUApgGmAKb/pQGm/6UApgGmAKb+pQSm+6UFpvylAqYApv+lAab/pQGm/6UBpv+lAKYCpv2lBKb7WQRa/lkBWgBa/1kBWv9ZAlr9WQNa/lkBWgBa/lkDWv5ZAVr/WQFa/lkDWvxZBVr7WQVa+1kDWv9ZAVr/WQFa/lkDWv1ZAloAWv9ZAlr9WQNa/lkBWgBa/1kCWv5ZAVr/WQFa/1kBWv9ZAVr/WQBaAFoApgGm/6UApgCmAKYApgGm/6UApgGm/qUDpv2lAqb/pQCmAab+pQOm/aUCpv6lA6b9pQSm+6UFpvylA6b+pQGmAab9pQSm/KUDpv6lAab/pQKm/aUCpgCm/qUDpv2lAqb/pQGm/qUDpv6lAKYBpv+lAKYCpvylBKb9pQKm/6UApgGm/qUCpv+lAKYApgGm/qUCpv6lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAKYApgGm/qUCpv+l/6UDpvylBKb9pQOm/KUEpv2lAqb/pQCmAKYBpv6lA6b8pQSm/aUCpv+lAKYApgGm/6UApv+lAqb+pQKm/qUBpv+lAqb9pQOm/qUApgKm/aUDpv6lAab/pQKm/qUBpv+lAab/pQKm/qUBpv+lAab/pQKm/qUBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lAqYApv+lAaYApv6lA6b9pQKmAKb/pQCmAab/pQCmAqb8pQWm/KUCpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb8pQam+aUFpv6lAKYBpv+lAKYBpv+lAaYApv6lA6b9pQOm/qUBpv+lAab/pQKm/VkDWv5ZAVoAWv9ZAVoAWgBaAFr/WQFaAFr/WQJa/VkEWvxZA1r+WQFaAFoBWv5ZAlr/Wf9ZA1r9WQJa/1kAWgBaAlr8WQVa+1kDWgBa/lkDWvxZBFr8WQRa/FkEWvxZA1r+WQFaAFoAWv9ZAlr+WQBaAqb+pQGmAKb/pQKm/aUDpv6lAaYBpv2lA6b+pQKm/6UApgCmAKYBpv+lAab/pQCmAKYBpv6lA6b8pQOm/6UApgCm/6UCpv6lAqb+pQGmAKb/pQKm/qUCpv6lAqb+pQKm/qUCpv+lAab+pQKm/6UApgGm/qUCpv6lA6b9pQKm/6UApgGm/6UBpv+lAKYApgGm/6UApgCmAKYApgGm/aUFpvqlBab+pf+lA6b8pQOm/6X/pQKm/6UApgCmAKb/pQOm/KUEpv2lAqb+pQKm/qUCpv6lAqb+pQKm/aUDpv2lA6b/pf+lAab/pQGmAKYApv+lAqb+pQGmAKYApgCmAKYApgCmAKYBpv6lAqb/pQCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAKYBpv6lA6b9pQKm/qUCpv+lAKYApgCmAab+pQOm/KUEpv2lAqb+pQKm/6UApgCmAKYApgGm/6UApgCmAKYBpv+lAab/pQCmAab/pQGmAKb+pQOm/aUDpv6lAab/pQGm/6UCpv2lBKb7pQam+qUFpvulBKb/pf+lAqb9pQOm/qUCpv6lAqb+pQKm/qUCpv+l/6UDpvxZBFr8WQNa/1kAWgFa/VkDWv5ZAlr+WQJa/lkCWv5ZAVoAWv9ZAVoAWv9ZAVr+WQJaAFr/WQFa/lkCWgBa/1kBWv9ZAFoBWgBa/1kBWv9ZAVr/WQFa/1kAWgFa/lkDWv1ZAlr/WQBaAVr/WQFa/1kBWv+lAKYApgGm/6UBpv+lAKYBpv6lA6b+pQGm/6UApgGm/6UCpv2lA6b9pQOm/qUCpv2lA6b+pQGmAKb/pQGmAKYApv+lAaYApgCmAKYApgCmAKYApgCmAab+pQGmAab9pQSm/KUDpv+lAKYApgCmAKYBpv+lAab+pQKm/6UApgCmAKYApgCmAKYApgCmAab+pQKm/qUDpvylBKb8pQSm/aUCpv+l/6UDpv2lA6b9pQKm/qUCpv+lAKYApv+lAab/pQKm/qUBpgCm/6UCpv2lBKb8pQSm/KUDpv6lAaYApgCmAKYApv+lAqb+pQKm/qUCpv2lBKb8pQOm/6X/pQKm/qUCpv6lAqb9pQSm/KUEpvylAqb/pQGmAKYApv6lA6b+pQGmAab8pQWm/KUEpvylA6b+pQGmAKb/pQGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b8pQSm/aUDpv6lAKYBpv6lA6b+pQGmAKb/pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpgCm/6UCpv6lAaYApgCmAKb/pQKm/aUEpvylA6b9pQOm/aUEpvylA6b9pQKm/6UCpvylBab7pQOmAKb+WQJa/1kAWgFa/1kAWgFa/1kBWv9ZAFoCWv1ZA1r9WQNa/lkAWgFa/lkDWv1ZAlr+WQNa/VkCWv5ZAlr/WQFa/1kAWgFa/1kBWv9ZAVoAWv9ZAVr/WQJa/VkDWv1ZAloAWv9ZAVr/WQFa/1kBWgBa/1kCpv2lBKb8pQOm/6X/pQKm/qUBpgGm/qUCpv6lAqb/pQCmAKYApgGm/6UApgCm/6UCpv+lAKYApgCm/6UCpv6lAaYApv+lAaYApv+lAab/pQKm/aUEpvulBqb6pQWm/KUDpv+l/6UBpgCmAKb/pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQGmAKb/pQKm/qUBpv+lAab/pQGm/6UBpgCm/qUDpv2lA6b9pQOm/aUCpv+lAKYApgGm/qUCpv+l/6UDpv2lAqb/pQCmAqb9pQKmAKb+pQWm+aUHpvmlB6b6pQWm/KUCpv+lAab/pQGm/6UApgGm/qUDpv2lAqb/pQCmAab+pQKm/6UApgGm/qUCpv6lAqb+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApv+lAaYApgCmAKYApv+lAaYApv+lA6b8pQOm/qUBpgCmAKYApgCmAKYApgCmAKYApgCm/6UDpvylBKb8pQOm/6X/pQKm/qUCpv6lAqb+pQGmAab9pQSm/aUBpgGm/aUEpvylA6b/pf+lAqb9pQOm/qUBpv+lAaYApgCm/6UBpv+lAqb+pQGmAKYApv+lAqb9pQOm/1n/WQFa/1kBWgBa/1kBWv5ZBFr8WQNa/lkBWgBaAFoAWgBaAFoAWgBaAFoAWv9ZA1r7WQZa+lkEWv5ZAVoAWgBa/1kCWv1ZBFr8WQRa/FkEWv1ZAlr/WQBaAFoBWv9ZAVr/WQBaAVr/WQFa/1kBWv5ZA6b8pQWm+6UEpv2lAaYBpv+lAKYCpvylBKb+pQCmAqb+pQCmAqb9pQOm/qUBpv+lAqb9pQKmAKb/pQGmAKb/pQGmAKb/pQKm/qUBpgCmAKYApgCmAKb/pQOm/KUEpvylA6b+pQKm/qUCpv2lBKb8pQOm/qUBpgCmAab9pQOm/qUBpgGm/qUCpv+lAKYApgCmAab+pQOm/aUCpv+lAKYApgGm/qUDpv2lAqb/pQCmAab/pQCmAab/pQGm/6UApgGm/6UBpv+lAKYBpv+lAab/pQGm/qUCpv+lAab/pQCmAKYApgGm/6UApgCmAab+pQOm/KUEpv2lAqb+pQKm/6UApgCmAKYApgGm/qUCpv+l/6UCpv6lAqb/pf+lAaYBpv6lA6b8pQOm/6UApgCmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab/pQCmAab+pQKm/6UBpv+lAab+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYBpv+lAKYBpv6lAqb/pQGm/6UBpv+lAKYBpgCm/6UBpgCm/qUDpv6lAKYCpv2lA6b9pQOm/aUDpv6lAKYCpv6lAab/pQGm/6UBpgCm/lkDWv1ZAlr/WQFa/1kAWgFa/lkDWv1ZAlr/WQFa/1kAWgFa/lkDWv1ZAlr/WQBaAVr+WQNa/VkCWv9ZAVr/WQBaAVr/WQFa/1kAWgFaAFr/WQFa/lkDWv5ZAlr+WQFa/1kBWgBaAFoAWv9ZAVr/WQJa/qUCpv6lAKYCpv2lBKb8pQOm/aUCpv+lAab/pQGm/6UBpv+lAab/pQKm/qUCpv2lBKb9pQGmAab+pQOm/aUBpgGm/qUDpv2lAqb/pQGm/qUDpv2lAqb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UApgCmAqb9pQSm+6UFpvylBKb8pQOm/6UApgCm/6UBpgCmAKb/pQGm/6UBpgCm/6UBpgCm/qUEpvulBab8pQKmAKYApv+lAqb9pQOm/6UApgCmAKb/pQKm/qUCpv6lAaYBpv2lBKb9pQKm/6UApv+lA6b8pQWm+qUFpv2lAaYBpv6lAaYBpv6lA6b8pQSm/KUFpvulBKb+pQCmAab/pQGm/6UBpv+lAaYApv6lA6b9pQOm/aUDpv2lA6b9pQOm/aUDpv2lAqb/pQCmAab+pQKm/qUBpgCmAKb/pQKm/aUDpv+lAKYApgCm/6UDpvylBKb9pQGmAab9pQSm/KUEpv2lAqb+pQKm/6UBpv6lAqb/pQGm/6X/pQKm/qUCpv+l/6UCpv2lA6b/pQCm/6UBpv+lAqb/pf+lAaYApgCm/6UCpv6lAqb/pQCmAKYApgFa/VkFWvtZA1r+WQJa/lkDWvxZA1r+WQNa/FkEWvxZA1r/Wf9ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVr/WQFaAFr/WQJa/VkDWv5ZAlr+WQJa/VkEWvxZBFr8WQNa/1n/WQJa/VkEWv1ZAlr+WQGmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAab+pQKm/6X/pQOm/aUCpgCm/qUDpv2lA6b9pQOm/aUDpv2lAqb/pQCmAab/pQCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAaYApv6lA6b9pQOm/qUBpv+lAab/pQKm/aUDpv2lAqYApv+lAab/pQCmAab/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UCpv2lBKb7pQSm/qUBpgCm/6UApgKm/aUEpvylA6b+pQKm/6UApgCmAKYApgCmAKYApgCm/6UBpv+lAqb+pQCmAab+pQOm/qUBpv+lAab/pQGmAKb/pQKm/qUCpv2lA6b+pQKm/qUBpgCm/6UDpvylA6b/pQCmAKYBpv2lBKb+pf+lA6b8pQOm/6UBpv6lA6b8pQOm/6UApgGm/qUCpv2lBKb8pQOm/6X/pQKm/qUBpgCmAKYApgGm/aUDpv6lAaYBpv6lAaYApv6lA6b+pQGmAKYApv6lA6b9pQOm/6X/pQGm/6UBpgCm/6UBpv+lAqb+pQGm/6UBpgCmAab+pQKm/qUCWv5ZA1r9WQNa/VkCWv9ZAVr/WQJa/VkEWvtZBFr+WQJa/lkCWv5ZAVoAWgBa/1kCWv5ZAVoBWv1ZBFr8WQNa/lkBWgBaAFr/WQFa/1kBWgBa/1kCWv1ZA1r+WQJa/lkCWv1ZBFr8WQRa/FkDWv5ZAlr+pQKm/qUBpgGm/aUEpvylA6b+pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpv6lA6b8pQWm+6UEpv6lAab/pQGmAKb/pQKm/qUBpgCm/6UCpv2lBKb7pQWm/KUDpv2lA6b9pQOm/aUCpv+lAab/pQGm/6UApgGm/6UCpv2lBKb8pQKmAab8pQem+KUHpvulA6b/pf+lAqb+pQKm/qUCpv6lAaYApv+lAqb+pQGmAKYApgCmAKb/pQKm/qUDpvylBKb8pQSm/KUEpv2lAqb+pQKm/qUCpv+lAKYApgCmAKYApgGm/qUBpgCm/6UCpv2lBKb8pQSm/KUDpv6lAaYBpv6lAaYApv+lAqb+pQGm/6UBpv+lAab/pQGm/6UApgGm/6UBpv+lAab/pQGm/6UApgKm/KUEpv2lAqYApv6lA6b9pQKmAKb+pQOm/aUBpgGm/qUCpv6lAqb+pQKm/6X/pQOm/aUCpv6lAqb+pQKm/qUBpgCmAKb/pQGmAKb/pQKm/aUDpv6lAaYApv+lAaYApgCm/6UBpv+lAaYApv6lA6b9pQOm/aUCpgCm/6UCpv6lAaYApv+lAqb/pQCmAKb/pQGmAVr+WQJa/lkBWgBaAFoAWgBaAFoAWgBaAFoBWv5ZA1r8WQRa/lkAWgJa/FkFWvtZBVr7WQVa+1kFWvxZAlr/WQBaAlr9WQNa/VkCWgBa/lkDWv1ZAlr/WQBaAVr/WQBaAFoAWgFa/lkDWv1ZAlr+WQJa/qUDpv2lAqb+pQGmAab/pQGm/qUCpv+lAab/pQGm/qUDpv6lAaYApv6lAqYApgCmAKYApv6lA6b+pQKm/qUCpv6lAaYBpv2lA6b+pQGmAKb/pQGmAKYApgCm/6UBpgGm/6UApgCmAKYBpv+lAKYBpv+lAab/pQGm/6UBpgCm/qUEpvqlB6b6pQSm/aUCpv6lAqb+pQKm/qUBpv+lAqb+pQGm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAKYBpv+lAab+pQKm/6UApgGm/qUCpv+lAKYBpv+lAKYCpvylBab8pQKmAKb/pQGmAKYApv+lAqb9pQOm/qUBpgCmAKb/pQGm/6UCpv6lAqb9pQSm/KUEpvylBKb8pQSm/aUCpv+lAab/pQCmAqb9pQKm/6UApgKm/aUBpgGm/aUFpvulA6b/pQCmAKYApgCmAab/pQCmAab+pQOm/qUBpgCm/6UBpgCmAKb/pQKm/aUDpv6lAaYApv+lAKYBpgCmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lA6b+pQKm/qUCpv6lAaYApgCmAKYApv+lAqb+pQGmAab9pQWm+qUFpvylBKb8pQRa/FkDWv5ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVoAWv9ZAlr+WQFa/1kBWgBaAFr/WQFaAFr/WQJa/lkBWgBa/1kBWgBa/1kBWv9ZAVr/WQFaAFr/WQFa/1kBWgBaAFr/WQFaAFr/WQJa/VkDWv6lAab/pQGm/6UBpv+lAKYBpv+lAab/pQCmAab/pQCmAab+pQOm/aUCpv+l/6UDpvylBab7pQOm/6UApgCmAKYApgCmAKYApv+lAqb/pf+lAab/pQGmAab+pQGm/6UBpv+lAaYApv+lAqb9pQOm/aUDpv6lAqb+pQGm/6UBpgCmAKb/pQGm/6UBpv+lAab/pQGmAKb/pQGm/6UBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCm/6UBpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAab+pQOm/KUEpv2lA6b9pQKm/qUDpv2lA6b9pQKm/6UApgCmAab/pQCmAKb/pQOm/KUEpvylA6b/pQCmAKYApv+lAqb/pQCmAKYApgCmAab+pQKm/6UBpv+lAKYBpv+lAab/pQCmAab/pQCmAab+pQOm/qUBpv+lAab/pQGmAKYApv+lAab/pQGmAKYApv+lAab/pQGm/6UCpvylBKb9pQOm/aUCpv6lAqYApv6lAqb+pQGmAab+pQGmAab9pQSm/aUBpgGm/qUCpv+lAKYBpv6lAqb/pQGm/6UBpv+lAqb+pQGm/6UCpv+lAKYAWv9ZAlr/WQBaAFoAWgBaAVr+WQJa/lkBWgBaAVr+WQJa/VkDWv9ZAFoAWgBaAFoBWv5ZA1r8WQVa+1kEWv5ZAFoBWv9ZAVoAWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAlr+WQJa/lkCWv9ZAFoAWgBaAFoApgGm/qUCpv+lAKYApgCmAKYBpv+lAKYApgCmAKYApgCmAKYApgCmAKb/pQGmAKYApgCm/6UBpv+lAqb9pQOm/qUBpv+lAab/pQKm/aUDpv6lAaYApv+lAaYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCm/6UCpv6lAaYApv+lAaYBpvylBab8pQOm/qUBpv+lAqb9pQSm/KUDpv6lAaYApgCm/6UCpv6lAqb/pf+lAqb+pQKm/qUCpv2lBKb8pQOm/qUBpgCmAKb/pQKm/qUCpv6lAaYApv+lA6b8pQSm/KUDpv6lAaYApv+lAqb+pQGm/6UBpgCmAKYApv+lAaYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKb/pQGmAKb/pQKm/qUApgKm/aUDpv6lAab/pQGm/6UBpv+lAKYCpv2lA6b+pQCmAqb+pQGmAKb/pQGmAKb/pQGmAKb+pQOm/KUFpvylAqb/pQCmAKYBpv+lAKYCpvylBKb9pQKm/6UBpv+lAKYApgCmAab/pQCmAKYApgCmAab+pQKm/qUCpv6lA6b8pQOm/6UApgGm/qUCpv6lAqb/pf+lA6b9WQJa/lkCWv5ZA1r9WQJa/1kAWgFa/1kBWv5ZAlr/WQBaAVr/WQBaAVr+WQJaAFr+WQNa/VkCWgBa/1kBWv9ZAVr+WQNa/lkBWv9ZAFoAWgJa/VkDWv1ZAloAWv5ZBFr7WQVa/FkCWgFa/VkEWvxZA1r/pQCmAKYApv+lA6b8pQSm/KUDpv+lAKYApgGm/qUDpvylBab7pQWm+6UEpv6lAKYBpv6lAqb/pQCmAab+pQKm/6UApgGm/qUDpvylBKb9pQKm/qUBpgCmAKYApgCm/6UDpvylBKb9pQKmAKb/pQCmAqb9pQSm+6UFpvulBqb5pQem+qUFpvylA6b+pQKm/qUBpgCmAab/pQCm/6UCpv6lBKb6pQam+qUFpv6l/6UDpv2lAqb+pQGmAKYBpv+l/6UCpv6lAqb/pf+lAqb/pQCmAab+pQKm/qUDpv2lAaYBpv6lA6b9pQKm/6UBpv+lAab/pQGm/6UCpvylBab7pQSm/qUApgGm/qUCpv+lAKYBpv6lAqb/pQCmAab/pQGm/6UBpv+lAaYApv+lAaYApv+lAqb9pQSm/KUEpv2lAqb/pQCmAKYApgGm/6UApgCm/6UDpvylBKb9pQGmAab+pQKm/6UApgCmAKYApgGm/qUCpv6lAaYBpv6lAqb+pQGmAKYBpv6lAqb+pQKm/6UBpv6lAqb/pQCmAab/pQCmAKYApgGm/qUCpv6lAqb+pQKm/qUCpv+l/6UCpv+lAKYApv+lAlr+WQJa/lkBWgBa/1kBWgBa/1kCWv1ZBFr8WQNa/lkBWgFa/1kAWgBa/1kCWv9ZAFr/WQFa/1kCWv1ZA1r9WQNa/VkCWv9ZAVr/WQFa/lkDWv1ZA1r+WQFaAFr/WQFaAFr/WQFaAFr/WQFa/1kBWv9ZAqb9pQOm/qUBpgCm/6UCpv6lAaYApgCmAKYApgCmAKYApv+lAaYApgCmAKb/pQKm/aUDpv6lAaYBpv6lAaYApv+lAqb/pQCmAKYApgGm/qUDpvylBKb9pQKm/6UApgCmAKYApgGm/qUCpv6lAqb/pQCmAab+pQKm/6UApgCmAab+pQOm/KUDpv+lAKYBpv6lAqb/pQCmAKYApgGm/6UApgCmAKYApgGm/qUCpv+lAKYBpv6lAqb+pQOm/aUDpvylBKb+pQCmAab+pQOm/aUDpv2lA6b9pQOm/aUDpv+l/6UCpv2lA6b/pQCmAKb/pQKm/qUCpv6lAKYCpv6lAaYApv+lAqb9pQOm/aUDpv+l/qUDpv2lA6b+pQGm/6UCpv2lBKb8pQSm+6UFpvylBKb8pQKmAKb/pQGm/6UBpgCm/qUCpv+lAab/pQGm/6UBpv+lAKYBpv+lAab/pQCmAKYApgGm/qUDpvylBKb+pQCmAKYBpv6lAqYApv6lA6b9pQGmAab/pQCmAab+pQKm/6UApgCmAKYApgCmAKYApgCmAKYApgCmAKYBpv6lAqb/pQGm/6UApgCmAab/pQGm/6UApgJa/VkDWv5ZAVoAWv9ZAlr9WQNa/lkBWv9ZAlr9WQNa/lkAWgFaAFr/WQFaAFr/WQFaAFr/WQJa/lkBWgBaAFoAWgBa/1kCWv5ZAlr+WQFaAFoAWgBaAFr/WQJa/lkCWv5ZAVoAWgBaAFr/WQJa/lkCWv6lAqb+pQKm/qUBpgGm/6X/pQOm/KUDpv+lAKYBpv+lAKYApgCmAab/pQCmAKYApgGm/qUCpv6lAqb/pQCmAKYBpv6lAqb/pQCmAab+pQOm/aUDpv2lAqb/pQGm/6UBpv+lAab/pQCmAab/pQKm/KUFpvulBab8pQOm/qUBpgCm/6UCpv6lAqb+pQKm/aUDpv+lAKYApgCm/6UCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/6UApgCm/6UDpvylBab6pQWm/aUCpv6lAqb/pQCmAKYApv+lA6b8pQSm/KUDpv6lAqb+pQGmAKYApgCmAKb/pQOm/KUEpvylBKb9pQGmAab+pQKm/qUCpv+lAKYApgCmAKYBpv+lAKYApgGm/qUCpv+lAKYBpv+lAKYBpv+lAKYBpv6lA6b9pQKm/6UApgGm/6UApgGm/6UBpgCm/6UBpgCm/6UCpv6lAaYApv+lAab/pQGmAKb/pQCmAab/pQGm/6UBpv+lAaYApv6lBKb8pQOm/6X/pQGmAKb/pQKm/qUApgKm/aUDpv2lA6b9pQOm/qUBpgCm/6UBpv+lAqb+pQGmAKb/pQKm/qUBpgCm/6UBWgBaAFoAWv9ZAVoAWgBaAFoAWv9ZAlr+WQJa/lkBWgBaAFoAWgBaAFoBWv5ZAlr+WQNa/VkCWv9ZAFoBWv9ZAFoBWv9ZAFoBWv5ZAloAWv5ZA1r8WQNa/1kAWgFa/lkCWv5ZAlr+WQJa/1kAWgFa/lkCpv+lAab+pQOm/aUCpgCm/qUCpgCm/qUDpv2lAqb/pQGm/qUCpv+lAKYBpv6lAqb+pQKm/6UApgCmAab+pQOm/KUDpv+lAKYBpv6lAaYApgGm/qUCpv6lAaYApgCmAKYApgCm/6UBpgCmAKYApgCm/6UCpv6lAqb+pQKm/qUCpv6lA6b9pQKm/6UApgKm/qUBpgCm/6UCpv6lAqb+pQGmAKYApgCmAKb/pQKm/qUCpv6lAqb+pQKm/qUBpgCmAKb/pQGmAKb/pQKm/aUDpv6lAqb9pQOm/qUBpgCm/6UBpv+lAaYApgCmAKb/pQGmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAab9pQOm/aUEpvylBKb7pQSm/6X/pQOm+6UEpv6lAaYApv+lAaYApgCm/6UCpv2lBKb9pQKm/qUCpv6lAqb+pQKm/6UApgGm/qUCpgCm/qUDpv2lAqYApv+lAKYCpv2lA6b+pQCmAab/pQGm/6UApgGm/6UApgCmAab+pQSm+qUGpvulBKb9pQKm/6UApgGm/6UApgGm/6UBpv+lAKYApgGm/6UBpv6lAqb/pQGm/6UApgGm/6UBpv+lAFoBWv5ZA1r8WQRa/VkBWgFa/lkCWv9ZAVr+WQJa/lkCWv9ZAFoAWv9ZAlr+WQFaAFr/WQJa/lkBWgBa/1kCWv5ZAVoBWv1ZBFr8WQRa/VkCWv5ZAlr+WQJa/1kAWgFa/1kAWgFa/lkDWv1ZAlr/WQBaAab+pQKm/6UApgGm/6UBpgCm/6UBpv+lAab/pQKm/qUApgGm/6UBpgCm/6UBpv+lAKYBpv+lAqb9pQKm/qUCpgCm/6UBpv6lAqb/pQGm/6UBpv6lA6b9pQOm/aUDpv2lBKb8pQOm/qUBpgCmAKYApgCm/6UBpgCmAKb/pQGm/6UBpgCm/6UApgKm/qUBpgCm/6UCpv6lAaYApv+lAqb+pQGmAKb/pQGmAKYApgCm/6UBpgCmAKYApv+lAqb+pQKm/6X/pQKm/qUBpgKm/KUDpv6lAaYBpv+l/6UBpgGm/qUCpv6lAaYBpv6lAqb+pQKm/qUCpv+lAKYApgCmAab/pQGm/6UApgGm/6UBpv+lAab/pQCmAab/pQGm/6UBpv+lAqb9pQOm/aUDpv+l/6UCpv6lAaYApv+lAqb/pf+lAqb+pQGmAKYApgCmAab+pQKm/6UApgGm/qUDpv2lAaYApgCmAKYBpv2lA6b9pQSm/aUBpgCm/6UCpv2lBKb8pQSm/aUBpgCmAKYApgCmAKYApgCmAKb/pQKm/qUBpgCm/6UCpv6lAaYApgCmAKb/pQKm/qUDpvylA6b/pQCmAab+pQKm/1kAWgFa/lkDWv1ZAlr/WQBaAVr/WQBaAVr+WQNa/VkCWv9ZAVr/WQFa/1kAWgJa/VkCWv9ZAFoBWv9ZAFoAWgBaAVr+WQNa/FkEWv1ZAlr+WQNa/VkCWv9ZAFoBWv9ZAVr/WQFa/1kAWgFaAFr/WQFa/qUCpgCm/6UBpv+lAKYBpv+lAKYBpv+lAKYBpv6lAaYBpv6lAqb+pQGmAKb/pQKm/aUDpv2lA6b9pQOm/aUDpv2lA6b9pQSm+6UFpvylA6b+pQGmAKb/pQKm/aUDpv6lAqb+pQKm/aUDpv+lAKYBpv6lAqb+pQGmAab+pQOm/KUDpv6lA6b8pQSm/aUBpgKm/KUDpv+lAKYBpv6lAqb+pQOm/aUDpvylBKb+pQGm/6UApgGm/6UBpv+lAaYApv+lAKYApgKm/qUCpvylBKb+pQGmAKb/pQGmAKYApv+lAqb+pQKm/6X/pQKm/6UApgGm/aUFpvulBKb+pf+lA6b8pQSm/qUApgGm/qUCpv+lAKYApgCmAKYApgCmAKb/pQKm/qUCpv2lBKb8pQOm/6UApgCmAab9pQSm/aUCpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUDpvylBKb9pQKm/6UApgCmAKYBpv6lAqb+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYApgGm/6UBpv+lAKYBpv+lAab/pQCmAKYApgCmAab9pQOm/qUCpv+l/6UBpgCmAKYApgCm/6UCpv2lBKb8pQNa/lkBWgBaAFoAWgBaAFoBWv5ZAlr+WQNa/VkDWvxZBFr9WQNa/lkAWgBaAVr+WQNa/FkEWv1ZAlr+WQFaAVr+WQJa/lkBWgFa/lkCWv1ZA1r+WQFaAVr+WQFaAFr/WQJa/lkCWv5ZAlr/WQBaAFoAWgCmAab/pf+lAqb+pQOm/aUCpv6lA6b9pQOm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQGm/6UApgKm/aUEpvulBab8pQKmAab9pQSm+6UFpvulBab8pQKm/6UApgCmAab+pQKm/qUCpv6lAaYApv+lA6b7pQWm+6UEpv+l/6UBpgCm/qUDpv6lAaYApv+lAaYApv+lAab/pQGm/6UBpv6lA6b9pQKm/qUCpv6lAqb/pf+lA6b8pQSm/KUEpv2lAqb/pQCmAKYBpv+lAab+pQKm/6UBpv+lAKYBpv6lA6b8pQSm/qUApgGm/qUCpv+lAab/pQCmAKYBpv6lA6b9pQKm/6UApgGm/6UBpv6lAqYApv+lAab/pQCmAab/pQGm/6UBpv+lAab+pQOm/KUFpvulBKb+pQCmAab/pQCmAqb9pQOm/aUCpgCm/6UBpv+lAKYBpv+lAab/pQGm/6UBpv+lAab+pQKmAKb/pQGm/6UApgGm/6UBpv+lAab/pQCmAKYBpv+lAKYBpv6lAqb/pf+lA6b8pQSm/aUBpgCmAKYApgCmAKb/pQOm/KUEpvylA6b+pQKm/qUCpv6lAaYApgCm/6UCWv1ZA1r+WQFaAFr/WQJa/VkDWv5ZAVoBWv1ZA1r+WQFaAFr/WQJa/VkEWvxZBFr8WQNa/lkCWv5ZAlr+WQJa/lkCWv5ZAlr+WQJa/lkCWv5ZAlr/WQBaAFoAWgBaAFoAWgFa/VkEWvtZBVr9WQFa/1kBpv+lAqb9pQOm/aUDpv6lAKYCpv2lA6b+pQGm/6UBpv+lAqb+pQGm/6UBpgCmAKYApgCm/6UBpgCmAKYBpv2lA6b+pQKm/6UApgCmAKYBpv+lAab/pQGmAKb/pQKm/qUBpgGm/aUFpvqlBab9pQGmAab+pQKm/6UApv+lAqb+pQKm/qUBpgCm/6UBpv+lAab/pQGm/6UApgGm/qUCpv+lAKYBpv+lAKYBpv+lAab/pQGm/6UBpgCm/6UBpv+lAqb9pQSm+6UFpv2lAaYApv+lAqb+pQKm/qUBpgCmAKb/pQKm/aUDpv6lAaYApv+lAab/pQGmAKYApv6lA6b9pQKmAab8pQWm/KUDpv2lBKb7pQWm/KUCpgCm/6UBpv+lAaYApv+lAaYApv+lAqb9pQOm/qUBpv+lAab+pQOm/aUDpv2lA6b9pQOm/aUDpv2lA6b+pQCmAqb9pQOm/aUDpv6lAaYApv6lA6b9pQKm/6UBpv6lA6b8pQSm/qUBpv+lAqb9pQSm/KUDpv6lAqb+pQKm/qUCpv6lAqb+pQGmAab+pQKm/qUCpv6lAqb+pQGmAKb/pQGmAKb+pQOm/aUCpv+lAVr+WQNa/lkAWgFa/1kBWgBa/1kBWv9ZAVr/WQFa/1kBWv5ZAlr/WQBaAVr+WQJa/1kAWgBaAVr+WQJa/lkCWv5ZA1r8WQRa/FkDWv9ZAFoAWgFa/VkEWvxZBFr9WQJa/lkCWv5ZAlr+WQFaAFr/WQJa/qUBpgCm/6UBpgCm/6UCpv2lBKb8pQOm/qUApgKm/qUBpgCmAKb/pQKm/aUDpv6lAqb9pQOm/qUBpgCmAKb+pQSm/KUDpv6lAab/pQGmAKb/pQKm/aUDpv2lA6b+pQGmAKb/pQGm/6UCpv2lA6b+pQGm/6UBpv6lA6b+pQCmAab+pQOm/aUDpv6lAab/pQGm/6UCpv2lA6b9pQKm/6UApgGm/qUCpv6lAqb/pQCmAab/pQCmAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAKYBpv+lAKYBpv6lAqYApv6lAqb/pQCmAab/pQCmAaYApv+lAab/pQGm/6UBpv+lAaYApv+lAab+pQOm/aUCpv+lAab/pQGm/qUCpgCm/6UBpv+lAKYBpv6lA6b9pQKm/6X/pQOm/aUCpv+lAKYApgCmAab+pQKm/qUBpgCmAKYApgCm/6UCpv2lBKb8pQOm/qUCpv6lAqb+pQGmAKYApgGm/qUBpgCmAKYApgCmAKYApgCmAKYApgCmAab+pQKm/6UApgGm/6UBpv6lA6b8pQSm/aUCpv+lAKYApgCmAKYApgGm/qUCpv6lAqb+pQFaAFr/WQJa/lkBWgBa/1kCWv5ZAlr+WQJa/lkCWv1ZA1r+WQFa/1kCWv1ZA1r9WQNa/lkBWgBa/1kCWv5ZAVoAWgBa/1kCWv5ZAlr+WQJa/VkEWvxZA1r/WQBaAFoAWgBaAFoBWv5ZAlr/WQBaAFoAWgCmAKYBpv6lAqb+pQKm/6UApgGm/qUCpv+l/6UCpv+lAKYApv+lAqb9pQSm+6UFpvylAqb/pQKm/aUDpv2lA6b+pQGm/6UBpgCmAKb/pQGm/6UBpgGm/aUDpv2lA6b+pQGm/6UBpv+lAab+pQOm/aUDpv2lAqb+pQOm/qUApgGm/qUDpv2lAqb/pQGm/qUDpvylBKb+pQCmAab/pQCmAab/pQCmAKYBpv+lAab+pQGmAab/pQCmAKb/pQKm/qUCpv6lAaYApv+lA6b8pQSm/KUDpv6lAab/pQKm/aUCpv+lAKYCpv2lAqb+pQOm/qUBpv+lAKYBpgCm/6UCpv2lA6b9pQOm/qUBpgCm/6UBpgCmAKYApv+lAaYApgCmAKb/pQGm/6UCpv6lAab/pQGmAKb/pQKm/aUDpv6lAKYBpgCm/6UCpv2lA6b+pQKm/qUCpv6lAqb/pQCmAKYApgCmAab/pQCmAKYApgCmAab/pQCmAab/pQCmAab/pQGm/6UApgCmAKYBpv6lAqb+pQKm/qUDpvylBKb8pQOm/6UApgCmAKb/pQKm/qUDpvylBKb8pQOm/6UApgGm/qUBpgCm/6UCpv9ZAFoAWv9ZAVoAWgBaAFr/WQFaAFoAWgBa/1kCWv5ZAlr/WQBaAFoAWv9ZAVoBWv1ZBFr8WQJaAFoAWv9ZAlr9WQRa/VkBWgBa/1kBWgBaAFoAWgBa/1kCWv5ZAVoAWv9ZAlr+WQFaAFr/WQJa/lkCWv6lAqb/pQCmAKYApgCmAab+pQKm/qUBpgGm/qUCpv2lA6b+pQGmAKb/pQGmAKb/pQKm/qUBpgCm/6UCpv6lAaYApv+lAab/pQKm/qUBpgCm/6UBpgCmAKb/pQKm/aUEpv2lAaYApv+lA6b8pQSm/aUBpgCmAKYApgCmAKb/pQKm/qUBpgCm/6UBpgCm/qUDpv6lAKYBpv6lAqb/pQCmAab+pQKm/qUCpv+lAab+pQKm/qUCpv+lAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApgCmAKYApv+lAqb+pQKm/aUEpvylA6b+pQGm/6UCpv6lAab/pQGm/6UBpv+lAKYBpgCm/6UBpv6lA6b+pQKm/aUCpgCm/6UBpv+lAKYBpv6lA6b8pQSm/KUEpvylBKb8pQSm/aUBpgGm/qUCpv+lAKYBpv+lAKYBpv6lA6b9pQOm/aUCpv6lAqb/pQCmAab+pQKm/qUCpv+lAKYBpv6lAaYBpv+lAab/pQCmAKYBpv+lAab+pQKm/qUCpv+lAKYBpv+lAKYBpv+lAaYApv+lAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UAWgFa/lkCWv9ZAFoBWv5ZAlr+WQFaAFoAWgBaAFr/WQFa/1kBWgBa/1kCWv1ZA1r+WQFaAFr/WQFaAFr/WQJa/VkCWgBa/1kBWv9ZAFoBWv9ZAVr/WQFa/lkDWv1ZA1r+WQFaAFoAWv9ZAVoAWgBaAVr+pQGmAKYApgGm/qUCpv6lAqb+pQKm/aUEpvylBKb8pQOm/aUEpvylBKb7pQWm/KUEpvylA6b+pQGmAKYApv+lAaYApv+lAqb+pQCmAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/aUEpvulBab8pQOm/6X/pQKm/qUCpv+lAKYApgCmAKYApgGm/qUCpv6lAqb+pQKm/qUCpv6lAqb+pQGmAKYApgCmAKYApgCmAab+pQKm/6UBpv6lA6b8pQSm/aUCpv+l/6UCpv6lAqb/pf+lAqb+pQKm/6X/pQOm/KUEpv2lAqb/pQCmAab+pQOm/aUDpv6lAKYBpv+lAqb+pQGmAKb+pQSm+6UEpv+l/qUDpv2lAqb/pQGmAKb/pQGm/6UBpgCm/6UBpgCm/6UCpv2lA6b+pQCmAqb9pQOm/aUCpv+lAKYBpv6lAqb+pQKm/6UBpv6lAqb+pQKm/6UApgGm/qUBpgCmAKYApgCmAKYApgCmAKb/pQOm/KUDpv+l/6UCpv6lAKYCpv6lAqb+pQCmAaYApv+lAqb9pQSm/KUDpv2lA6b+pQGmAKb/pQKm/aUDpv6lAaYApgCmAFoAWv9ZAVoAWgFa/VkEWvtZBlr7WQNa/lkCWv5ZAlr9WQRa/FkEWvxZA1r9WQRa/VkCWv5ZAFoCWv5ZAlr+WQFa/1kCWv5ZAVoAWv9ZAVoAWv9ZAVr/WQBaAlr9WQJaAFr/WQJa/VkDWv5ZAlr+WQFa/6UBpgCm/6UCpv2lA6b9pQOm/aUDpv6lAKYBpv+lAaYApv6lA6b9pQOm/qUBpv+lAqb9pQOm/qUBpgCmAKYApv+lAab/pQKm/qUCpv2lA6b+pQKm/6UApgGm/6UApgGm/6UBpgCm/6UApgGm/6UBpv+lAab+pQKm/qUCpv+lAKb/pQGmAKYApgCmAKb/pQKm/qUCpv+l/6UCpv6lAqb+pQGmAKb/pQOm+6UEpv6lAKYCpv2lAqb/pQGm/6UBpv6lA6b9pQOm/aUCpv+lAab/pQCmAab+pQKm/qUCpv+lAKYApgCmAKYApgCmAab/pQGm/qUCpv6lA6b9pQOm/KUDpv6lAqb/pQCmAKYApgCmAKYApgCmAab+pQKm/qUCpv+lAKYApgCmAKYApgCm/6UCpv2lA6b+pQCmAqb9pQOm/qUApgGmAKb/pQKm/qUBpv+lAqb+pQKm/qUApgGmAKb/pQGm/6UApgGm/6UApgKm/aUDpv6lAKYBpv+lAqb+pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGm/6UCpv2lA6b+pQGmAKb/pQGm/6UBpv+lAab/pQCmAab/pQGm/qUCpv9ZAVr/WQFa/1kBWv9ZAVr/WQFa/1kCWv1ZAlr/WQFaAFr/WQBaAVr/WQFa/lkCWv5ZA1r8WQRa/FkDWv9Z/1kDWvxZBFr8WQRa/VkCWv9ZAFoAWgFa/1kAWgFa/lkCWv9ZAFoAWgBaAFoAWgBaAFoAWgCmAKYApgCmAKYApgCmAKYApgCm/6UCpv6lAqb/pf+lAaYBpv6lA6b8pQOm/6X/pQOm/KUEpvylA6b+pQGmAKb/pQGm/6UBpv+lAKYBpv+lAab/pQCmAaYApv+lAab/pQCmAab/pQGm/6UApgCmAab/pQGm/qUCpv+lAab/pQCmAKYApgCmAab/pQCmAab/pQCmAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAab/pQGmAKb/pQGmAKb/pQGm/6UBpv+lAab/pQCmAab/pQGm/6UApgGm/6UBpgCm/qUEpvulBab8pQOm/qUBpgCmAKb/pQKm/qUBpgCm/6UCpv6lAab/pQGmAKb/pQGm/6UBpv+lAab/pQGm/6UBpv+lAaYApv6lBKb8pQOm/qUBpgCmAKYApv+lAqb+pQGmAKYApv+lAqb9pQOm/qUBpgCm/6UCpv2lA6b+pQGmAKb/pQKm/aUEpvulBqb6pQWm/KUDpv6lAqb9pQSm/KUDpv+l/6UCpv6lAaYApgCmAKYApgCmAKYApgCmAab+pQOm/aUBpgKm/aUDpv2lAqYApgCm/6UCpv2lBKb8pQOm/6X/WQJa/lkCWv9ZAFoAWgBaAVr+WQJa/1n/WQJa/lkCWv1ZBFr7WQVa/FkCWgBa/1kBWv9ZAVoAWv9ZAFoBWv9ZAVr/WQBaAVr+WQNa/FkEWv1ZAlr/WQBaAVr/WQBaAVr/WQFa/1kBWv9ZAVr+WQJa/1kBpv+l/6UCpv+lAKYBpv6lAqb/pQCmAKYBpv+lAKYBpv6lA6b9pQKm/qUDpv2lAqb+pQKm/6UApgGm/qUCpv+lAab/pQGm/qUCpv+lAaYApv6lAqb/pQCmAab+pQKm/qUCpv6lAqb+pQKm/qUCpv+lAKYBpv6lA6b9pQOm/aUDpv2lAqb/pQGmAKb+pQOm/aUDpv6lAKYBpv+lAqb9pQOm/aUCpgCm/6UBpv+lAab/pQGm/6UBpv+lAab/pQCmAqb9pQOm/qUApgKm/aUDpv6lAab/pQGmAKYApgCm/6UCpv6lAqb+pQGmAKb/pQKm/aUEpvulBab8pQOm/qUBpv+lAqb9pQOm/aUCpv+lAaYApv+lAKYApgGm/6UBpv+lAab/pQCmAab/pQKm/aUCpv+lAqb9pQOm/aUCpv+lAab/pQGm/qUCpv+lAaYApv6lAqYApv+lAab/pQGm/6UBpv+lAab/pQKm/aUEpvulBKb+pQGmAKYApv6lBKb8pQOm/qUBpgCm/6UCpv2lA6b9pQKm/6UBpv+lAKYBpv6lA6b9pQKmAKb9pQWm+6UEpv6lAKYBpv+lAKYCpv2lA6b9pQKm/1kBWv9ZAVr+WQNa/FkFWvtZBFr9WQJa/1kAWgBaAFoAWgBaAVr9WQVa+lkGWvtZA1r/WQBaAVr+WQJa/lkCWv9Z/1kCWv5ZAlr+WQFaAFr/WQJa/lkBWgBa/1kBWgBa/1kCWv1ZAlr/WQBaAVoAWv5ZAqb/pQCmAqb9pQOm/aUDpv2lAqYApv+lAab/pQCmAab/pQGmAKb/pQGm/6UApgGm/6UApgGm/qUDpv2lAqb/pQGm/6UCpv2lA6b+pQGm/6UBpgCm/qUDpv2lAqYApv6lA6b8pQSm/aUDpv6lAKYApgGm/qUDpv2lA6b9pQKm/6UBpv+lAab+pQOm/aUCpgCm/qUCpv6lA6f9pgOn/aYBpwKn/aYDp/2mAqf/pgGn/6YBp/6mAqf/pgCnAaf+pgKn/qYCp/+m/6YDp/ymA6f/pv+mAqf+pgGnAKcAp/+mAqf9pgOn/qYBp/+mAaf/pgGn/6YApwCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/6mA6f8pgSn/KYDp/6mAacAp/+mAqf9pgKn/6YBpwCn/6YBp/6mA6f9pgSn+6YFp/umBaf8pgOn/aYDp/6mAacAp/+mAqf+pgGnAKf/pgKn/qYCp/6mAKcCp/2mBKf9pgGnAKf/pgGnAaf/pv+mAqf9pgSn/aYBpwGn/aYEp/2mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAaf+pgKn/6YApwGn/qYCp/6mAqf/pv+mAqf/pv+mA1n8WANZAFn9WARZ/VgCWf5YAln9WARZ/FgDWf9YAFkAWf9YAVkAWQBZAFn/WAJZ/lgAWQJZ/VgDWf5YAVkAWQBZ/1gBWQBZAFkAWQFZ/VgFWfpYBln8WAFZAVn+WANZ/lj/WAJZ/lgCWf9Y/1gCWf5YAqf+pgKn/qYCp/+mAKcBp/+mAaf/pgCnAaf/pgKn/aYCpwCn/qYEp/umBaf8pgOn/qYCp/+mAKf/pgOn/aYDp/ymBKf9pgOn/aYBpwGn/6YBp/+mAaf/pgGn/6YBpwCn/6YCp/2mA6f9pgOn/qYBpwCn/6YCp/2mA6f9pgSn+6YGp/mmBqf8pgKnAKf/pgCnAaf/pgGnAKf+pgOn/aYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf+pgOn/KYEp/ymA6f/pgCnAaf+pgGnAaf+pgOn/aYCp/+mAKcApwGn/qYDp/2mAqf+pgKn/qYDp/ymBKf8pgOn/6b/pgOn+6YGp/qmBaf9pgCnA6f8pgOn/qYBpwCnAKcApwCnAKcAp/+mAqf+pgGnAKf/pgKn/aYDp/6mAacBp/6mAqf+pgKn/6YApwGn/qYCp/6mAqf/pgCnAKcAp/+mA6f9pgKn/qYBp/+mAqf/pv+mAqf8pgan+aYGp/ymAqcBp/ymBKf9pgOn/aYCp/+mAaf/pgGn/qYDp/2mAqf+pgOn/aYDp/ymA6f/pgCnAKcApwCnAaf+pgKn/qYDp/2mAqf/pgGnAKcAp/9YAVkAWf9YAln9WARZ+1gGWfpYBFn/WP5YBFn8WANZ/lgBWQBZ/1gBWQBZ/1gBWQBZ/1gBWf9YAVkAWQBZ/1gBWf9YAln9WANZ/lgBWQBZ/1gBWQBZAFn/WAFZ/1gBWQBZ/1gCWfxYBVn8WANZ/1j/WAKn/qYCp/6mAacBp/6mAqf+pgKn/6YApwCnAKcBp/6mAqf+pgKn/qYCp/6mAqf+pgGn/6YDp/ymA6f+pgCnA6f9pgCnAqf+pgKn/6b/pgKn/qYBpwGn/qYCp/6mAacApwCn/6YBpwCnAKf/pgGn/6YApwOn+6YEp/6mAKcCp/2mAqf/pgGn/6YApwGn/qYCp/+mAKcBp/6mAacApwCnAKcAp/+mAacApwCnAKf/pgGnAKf/pgKn/aYEp/ymA6f9pgOn/6YApwCn/6YBpwCnAKcApwCn/6YBpwCnAKf/pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YApwKn/aYDp/2mAqcApwCn/6YBp/+mAaf/pgGn/6YBp/+mAKcBp/6mA6f9pgOn/qYApwGnAKf/pgKn/aYDp/6mAqf+pgGn/6YBpwCnAKcAp/+mAaf/pgKn/qYBpwCn/qYEp/ymA6f+pgGn/6YBpwCn/6YBpwCn/6YCp/6mAacApwGn/6YApwGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgCnAqf9pgOn/aYCpwCn/6YAWQFZ/lgDWf1YAln/WAFZ/lgDWf1YA1n+WABZAVkAWf9YAVn/WAFZAFkAWf9YAln9WARZ/FgDWf5YAln+WAJZ/lgBWQBZAVn9WARZ/FgDWf9Y/1gCWf5YAVkBWf5YAln+WAFZAFkAWQBZ/1gBWQBZ/1gCp/2mAqcAp/+mAqf9pgSn/KYDp/6mAacApwCnAKf/pgKn/aYEp/ymA6f+pgGnAKcApwCn/6YBp/+mAacAp/+mAacAp/+mAqf+pgGnAaf+pgKn/6YApwGn/qYCp/6mA6f8pgSn/aYBpwGn/aYEp/ymBKf8pgOn/qYCp/2mBKf8pgSn/aYCp/6mA6f9pgKn/6YApwGn/6YApwGn/qYDp/ymBKf9pgKn/6YApwGn/qYDp/2mAqcAp/6mA6f8pgSn/aYCp/+m/6YCp/6mAqf+pgKn/qYDp/2mAacApwCnAaf+pgOn+6YGp/qmBaf9pgKn/qYBp/+mAacApwCnAKf/pgGn/6YCp/6mAqf9pgSn/KYDp/6mAacApwGn/aYEp/ymBKf9pgGnAaf/pgGn/6YApwGn/6YBp/+mAaf/pgCnAKcBpwCn/6YApwGn/6YBp/+mAaf/pgGn/qYCp/+mAaf/pgGn/qYCp/6mAqcAp/6mA6f8pgOn/qYBpwCnAaf+pgGn/6YBp/+mAqf9pgOn/aYCp/+mAaf/pgGn/6YApwKn/aYDp/6mAKcBp/+mAacAp/+mAaf/pgKn/aYEp/ymA6f+pgGn/1gCWf1YA1n+WAFZAFn/WAFZAFn/WAFZAFn/WAJZ/VgDWf5YAVkBWf1YBFn8WANZ/lgCWf5YAln+WAFZAFkAWQFZ/lgCWf5YAVkBWf5YAVkAWQBZAFkAWQBZAFkBWf9YAFkAWQBZAFkBWf9Y/1gCWf5YAqf/pv+mA6f8pgSn/KYEp/2mAqf+pgKn/qYCp/6mAqf/pgCnAKcApwCnAaf/pgGn/6YApwGn/6YBpwCn/qYDp/2mA6f+pgCnAqf+pgGnAKf/pgKn/qYCp/6mAacApwCn/6YDp/umBqf6pgSn/6b/pgOn+6YEp/6mAacBp/6mAKcCp/2mBKf8pgOn/qYBpwCnAKcApwCnAKcApwGn/qYCp/+mAaf+pgOn/KYEp/2mAqf+pgOn/KYEp/2mAqf+pgKn/qYDp/2mAqf9pgSn/aYCp/+mAKcBp/6mA6f9pgOn/aYDp/2mA6f+pgGn/6YApwCnAKcBp/6mAqf9pgOn/qYBpwCn/6YBpwCnAKf/pgGnAKf/pgKn/qYApwKn/aYDp/2mAqf/pgCnAaf+pgKn/qYCp/+mAKcApwGn/qYDp/ymBKf9pgKn/6b/pgKn/qYCp/6mAacAp/+mAaf/pgGnAKcAp/+mAaf/pgKn/qYCp/6mAqf+pgKn/aYEp/ymBKf8pgSn/KYDp/6mAacBp/6mAaf/pgGnAKcAp/+mAqf9pgOn/qYCp/6mAqf+pgKn/6YApwCnAKcApwGn/qYCp/6mAqf+pgJZ/lgCWf5YAln+WAJZ/lgBWQBZAFkAWQBZAFkAWQBZ/1gDWfxYBFn9WAFZAVn+WAFZAFn/WANZ+1gFWfxYAlkAWf9YAVkAWf9YAVkAWf9YAln+WAFZAFkAWQBZ/1gBWf9YAln9WANZ/VgDWf5YAVn/WAGnAKcApwCnAKcApwCnAKcBp/6mA6f8pgSn/aYDp/2mAqf+pgOn/aYCp/+mAKcApwCnAKcApwCn/6YCp/6mAqf9pgOn/aYEp/ymAqcAp/+mAacAp/+mAqf+pgKn/qYCp/+mAKcBp/6mAqf/pgCnAKcBp/6mAqf+pgKn/6YApwGn/qYCp/+m/6YDp/ymBKf8pgOn/6YApwCnAKcApwGn/qYDp/2mA6f9pgOn/aYDp/2mAqcAp/+mAaf/pgCnAqf9pgOn/qYBpwCn/6YCp/2mBKf8pgOn/qYBpwCnAKcAp/+mAqf+pgKn/qYCp/6mAqf/pgCnAKcBp/6mA6f9pgKn/6YApwGn/qYCp/6mAqf+pgKn/qYCp/6mAqf/pgCnAaf+pgOn/aYDp/2mAqf/pgGn/6YBp/+mAKcApwGn/qYDp/ymA6f/pgCnAKf/pgKn/aYEp/ymA6f/pv+mAqf9pgSn/KYEp/2mAaf/pgKn/qYCp/6mAacApwGn/aYEp/ymBKf8pgOn/qYBpwCn/6YBpwCn/6YBpwCnAKf/pgKn/qYBpwGn/qYCp/+m/6YCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YApwBZAFkAWQBZAVn9WARZ/FgEWfxYBFn9WAFZAFkAWQBZAVn+WAFZAFkBWf1YBFn8WANZ/1j/WAJZ/lgCWf5YAln+WAJZ/lgCWf5YAln+WAFZAFkAWf9YAln9WANZ/lgAWQJZ/VgCWQBZ/lgDWf1YAlkAWf+mAKcBp/+mAacAp/6mA6f+pgGnAKf/pgGnAKcAp/+mAacAp/+mAqf9pgKnAKf+pgOn/aYDp/2mAqf+pgKn/6YApwGn/qYCp/6mAacBp/6mAqf/pgCnAaf+pgGnAKcApwGn/qYCp/2mA6f+pgKn/qYBpwCn/6YCp/6mAKcCp/2mBKf8pgOn/qYBpwGn/aYEp/ymA6f/pv+mAqf+pgGnAKcApwGn/6YApwGn/6YBp/+mAacAp/+mAaf+pgSn+6YFp/umBKf9pgKn/qYDp/ymBKf8pgOn/6YApwCnAKcApwGn/qYCp/+mAKcBp/6mAqf+pgOn/KYEp/ymA6f/pgCnAKcAp/+mAqf/pgCnAKf/pgGnAKcAp/+mAqf9pgOn/qYBp/+mAqf9pgSn/KYDp/6mAacApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKf/pgGn/6YCp/6mAaf/pgGn/6YCp/2mA6f+pgGnAKf/pgGnAKf/pgOn/KYDp/6mAacApwCn/6YBpwCn/6YBp/+mAacApwCnAKcApwCn/6YCp/6mAqf/pv+mAqf+pgGnAKcApwCnAKcAp/+mAacAp/+mAqf9WANZ/lgBWQBZ/1gBWf9YAln+WAFZ/1gBWf9YAln+WAFZ/1gBWQBZ/1gBWQBZ/1gCWf1YA1n+WAFZAFkAWQBZ/1gCWf5YAln+WAFZAFn/WAJZ/VgDWf5YAVkAWf9YAln9WAVZ+lgGWfpYBVn9WAJZ/lgBp/+mAqf/pgCnAKf/pgKn/qYCp/6mAqf+pgGn/6YCp/6mAacAp/+mAqf+pgGnAKf/pgKn/qYCp/6mAacApwCnAKcApwGn/aYEp/ymA6f+pgKn/aYEp/ymA6f/pv+mA6f7pgan+6YEp/2mAacApwCnAKcBp/6mAqf+pgKn/qYDp/2mAacBp/2mBKf9pgKn/qYCp/6mAacApwCnAKcApwCn/6YCp/6mAacAp/+mAaf/pgGn/6YBp/6mA6f+pgCnAqf9pgOn/qYBpwCn/6YCp/6mAacAp/+mAqf+pgGn/6YBpwCn/6YBp/+mAaf/pgCnAaf/pgGn/6b/pgKn/6YApwGn/qYBpwGn/qYCp/+mAKcApwCnAaf+pgOn/KYEp/2mAacBp/6mAqf+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYBp/+mAKcBpwCn/6YBp/+mAacAp/+mAacApwCnAKf/pgKn/qYCp/6mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwCn/6YBpwGn/aYEp/ymA6f+pgGn/6YCp/6mAaf/pgGn/6YBpwCn/6YBp/+mAaf/pgKn/qYBp/+mAaf/pgGn/1gBWf9YAVn9WAVZ/FgCWQBZ/lgDWf5YAVkAWf9YAln+WAJZ/lgBWQFZ/lgDWfxYA1n/WABZAFkAWQBZAFkAWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAFZAFkAWf9YAln9WANZ/lgBWQBZAFn/WAFZAKf/pgOn/KYDp/6mAacApwGn/6b/pgKn/aYEp/2mAacBp/6mAacAp/+mA6f9pgKn/qYCp/+mAaf/pgGnAKf/pgKn/aYDp/6mAqf+pgGn/6YBpwCnAKcAp/+mAqf+pgKn/qYCp/6mA6f9pgGnAaf/pgGnAKf/pgCnAqf9pgOn/aYDp/2mA6f9pgGnAaf+pgKn/6b/pgKn/qYCp/6mAacApwCnAKcApwCn/6YCp/6mAacAp/+mAqf+pgGn/6YCp/6mAqf+pgCnAqf+pgGnAKf/pgGnAKf/pgGnAKcApwCn/6YCp/2mBaf6pgWn/KYDp/6mAqf+pgGn/6YCp/2mA6f+pgGnAKf/pgGn/6YBp/+mAacAp/+mAaf+pgOn/qYBpwCn/6YBpwCn/6YBpwCnAKcApwCn/6YBpwCnAKcApwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgGn/6YCp/2mBKf7pgSn/6b+pgOn/aYDp/2mA6f9pgOn/qYApwGn/6YCp/2mAqf/pgGnAKf/pgCnAKcBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/+mAacAp/+mAaf/pgFZAFkAWQBZ/1gBWQBZAFkAWQBZAFkAWf9YAVkAWQBZAFn+WAJZ/1gAWQFZ/lgDWf1YAVkAWQBZAFkBWf9YAFkBWf5YA1n9WANZ/VgDWf5YAFkBWf9YAVkAWf9YAVkAWf9YAVkAWQBZAFkAWf9YAln+WAGnAKf/pgOn+6YFp/ymA6f+pgGn/6YBp/+mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgCnAKcApwGn/6YBp/6mAqf/pgCnAaf/pgGn/6YApwGn/6YCp/2mAqf/pgGn/6YCp/2mAqcAp/6mA6f9pgOn/aYDp/2mAqcAp/6mA6f+pgCnAqf9pgKnAKf/pgGn/6YApwGn/6YApwGn/qYCpwCn/qYCp/+mAKcCp/2mA6f8pgWn/KYDp/2mA6f9pgOn/qYApwKn/aYCp/+mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgCnAKcApwCnAKcAp/+mAqf/pgCnAKf/pgKn/6YApwGn/6YApwGn/6YBp/+mAacAp/+mAqf9pgSn+6YFp/umBaf8pgKnAKf/pgGn/6YBpwCnAKf/pgKn/qYBpwCn/6YCp/2mA6f9pgOn/qYBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAKcApwCnAKcBp/6mAqf+pgKn/qYCp/6mAqf+pgGnAKf/pgOn/KYDp/6mAacApwGn/aYFp/qmBaf9pgGnAaf/pgCnAKf/pgGnAaf+pgKn/qYApwKn/qYCp/+m/6YBpwCnAKcBWf5YAVkAWQBZAFkBWf5YAln/WP9YA1n8WARZ/VgBWQFZ/lgCWf5YAln+WAJZ/1gAWQFZ/lgDWfxYBFn9WANZ/lgAWQFZ/1gBWQBZ/lgEWftYBVn7WARZ/lgBWQBZ/lgDWf1YA1n+WABZAVn/WAFZ/1gApwKn/aYDp/6mAKcCp/6mAacAp/+mAacAp/+mAaf/pgGnAKf/pgGn/6YApwKn/aYCp/+mAaf+pgOn/aYBpwGn/qYCpwCn/qYCp/+mAKcApwGn/qYDp/2mAqf+pgKn/6YBp/+mAKcBp/+mAqf9pgKn/6YBp/+mAaf+pgOn/aYCp/+mAKcBpwCn/qYDp/2mAqcAp/6mA6f9pgOn/aYCp/+mAKcBp/+mAaf/pgCnAaf+pgOn/qYApwGn/6YApwKn/aYCp/+mAaf/pgCnAaf+pgOn/aYCp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAacAp/+mAacAp/+mAqf9pgSn/KYDp/2mAqcApwCnAKf/pgGn/6YBpwCnAKcAp/+mAaf/pgKn/qYBp/+mAKcCp/2mA6f9pgKn/6YBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAaf9pgSn/aYCp/6mAqf9pgSn/KYDp/6mAqf+pgKn/qYCp/+mAKcApwCnAaf+pgKn/qYBpwCn/6YCp/2mBKf7pgWn/KYDp/6mAqf9pgOn/6b/pgKn/qYApwOn/KYDp/+m/6YBpwCn/6YCWf5YAFkCWf1YA1n+WAFZAFkAWf9YAVkAWQBZAFn/WAFZAFkAWQBZAFkAWQBZAFkAWQBZAVn+WAFZAFn/WANZ/FgEWfxYBFn9WAJZ/lgCWf9YAFkBWf5YAln+WAJZ/lgCWf5YAln/WABZ/1gCWf5YA1n8pgOn/qYCp/+mAKcApwCnAKcBp/6mA6f9pgOn/aYCp/+mAacAp/6mA6f9pgOn/aYCp/+mAaf/pgCnAaf/pgGn/qYDp/ymBaf7pgSn/aYCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgGn/qYCp/+mAacAp/+mAaf/pgGn/6YBp/+mAaf/pgCnAKcBp/+mAKcBp/6mA6f9pgKn/6YApwKn/aYCp/+mAKcBpwCn/qYDp/2mAqf/pgGn/6YBp/+mAaf/pgGn/6YBp/+mAacAp/6mA6f9pgKnAKf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcAp/+mAqf+pgKn/qYBpwCnAKcBp/2mBKf9pgKn/qYBpwCnAaf/pgCnAKf/pgOn/KYEp/2mAqf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwGn/qYCp/6mAacBp/6mAqf+pgKn/qYCp/2mBKf9pgGnAKcApwCnAaf9pgOn/qYCp/6mAqf+pgKn/qYCp/6mAqf/pgCnAaf+pgKn/6YApwGn/qYCp/+mAKcApwGn/qYCp/6mAqf/pgGn/6b/pgOn/VgCWQBZ/lgCWf9YAVn+WANZ/FgEWf5YAFkAWQFZ/lgDWf5Y/1gCWf9Y/1gDWfxYA1n/WP9YAln/WABZAVn+WAJZ/1gBWf9YAFkBWf5YAln+WAJZ/1gAWQBZAFkAWQBZAFkAWQBZAFkAWQBZAFn/WAJZ/qYCp/6mAacApwCn/6YBpwCn/6YCp/2mA6f+pgCnAqf9pgSn/KYCpwCn/6YCp/6mAacBp/6mAqf+pgKn/6YBp/+mAKcBp/+mAaf/pgCnAacAp/+mAaf/pgCnAqf9pgKn/6YApwGn/qYCp/+m/6YDp/ymA6cAp/6mA6f9pgKn/6YApwGn/qYEp/umBKf9pgGnAaf/pgCnAaf9pgWn+qYHp/mmBqf7pgSn/qYApwKn/KYFp/ymAqf/pgCnAaf/pgCnAaf+pgKn/qYCp/+mAKcAp/+mAqf/pgCnAKcAp/+mA6f8pgOn/6b+pgWn+qYFp/ymAqcApwCnAKf/pgGnAKf/pgKn/aYDp/6mAacApwCn/6YCp/6mAqf+pgGnAaf+pgKn/qYBpwGn/6b/pgKn/qYCp/+m/6YBpwCnAKcAp/+mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGnAKf/pgKn/aYEp/ymBKf8pgOn/qYCp/6mAqf9pgOn/qYBpwCn/6YBpwCn/6YCp/6mAacBp/2mA6f/pgCnAKcAp/+mAqf/pgCnAaf/pgCnAKcBp/6mA6f8pgOn/6b/pgOn/KYEp/2mAqf/pgFZ/lgDWf1YA1n9WANZ/VgCWQBZ/1gCWf1YA1n9WANZ/VgCWf9YAFkBWf5YAln/WABZAVn+WAJZ/lgCWf5YAln+WAFZAFkAWQBZAFn/WAJZ/lgCWf5YAVkAWQBZ/1gCWf5YAln/WABZAFkBWf9YAFkBWf+mAaf/pgGn/6YBp/+mAKcCp/2mAqf/pgCnAaf+pgKn/qYDp/2mAacApwCnAKcBp/6mAaf/pgKn/aYEp/ymAqf/pgGn/6YCp/2mA6f9pgOn/qYBp/+mAaf/pgKn/aYCpwCn/qYEp/umBKf+pgCnAqf9pgKn/6YBp/+mAKcApwCnAaf/pgCnAKcApwCnAaf/pgCnAaf+pgKn/6YApwGn/qYCp/6mAqf+pgKn/qYCp/6mAqf9pgSn/KYEp/ymA6f+pgGnAKcApwCnAKcApwCnAKcApwCnAaf+pgKn/qYCp/+mAKcBp/6mAqf/pgCnAaf+pgKn/6YApwCnAKcBp/6mAqf+pgKn/6b/pgKn/aYDp/+m/6YCp/2mAqcBp/6mAqf9pgSn/aYCp/+mAKcApwGn/qYDp/2mAqf/pgCnAaf+pgOn/KYEp/2mAacBp/6mAqf/pgCnAKcBp/6mA6f8pgSn/aYDp/2mAqf/pgGn/6YBp/+mAaf/pgGn/6YApwGn/qYDp/6mAKcApwCnAKcCp/ymBKf8pgSn/aYBpwCnAaf+pgOn+6YGp/umA6f/pgCnAKcApwCnAKcAp/+mAqf+pgGnAKf/WAJZ/lgBWf9YAln+WAFZAFn/WAJZ/VgEWftYBVn8WAJZAFn/WAJZ/VgCWQBZ/1gBWf9YAVkAWf9YAVn/WAJZ/lgBWQBZ/1gCWf5YAln/WP9YAln+WAJZ/1j/WAJZ/lgCWf5YAln9WARZ/FgDWf5YAln+pgKn/qYBpwCnAKf/pgKn/aYDp/6mAacAp/+mAacApwCn/6YCp/6mAacAp/+mAacApwCnAKf/pgGnAKf/pgKn/aYDp/6mAKcCp/2mBKf8pgKnAKcApwCn/6YCp/6mAqf9pgOn/qYBpwGn/KYFp/umBaf9pgGn/6YBp/+mAqf/pv+mAqf9pgSn/KYEp/umBqf6pgWn/KYDp/6mAacApwCnAKf/pgGnAKcApwCnAKf/pgKn/qYCp/+mAKcApwGn/6YBp/+mAKcBp/6mA6f8pgSn/aYCp/6mAqf+pgKn/qYCp/6mAacAp/+mAqf+pgGn/6YBp/+mAqf+pgGn/6YBpwCnAKf/pgGnAKcAp/+mAaf/pgGnAKf/pgGn/6YBp/+mAqf+pgGnAKf/pgOn/KYDp/6mAacApwCn/6YBpwCn/6YCp/2mA6f+pgGnAaf+pgGnAKf/pgKn/6b/pgOn+6YGp/umA6f/pgCnAKcApwCnAKcApwCnAKcApwGn/qYBpwCnAKcBp/6mAqf+pgKn/6YApwCnAKcApwCnAKcApwCnAKcAp/+mAqf9pgOn/6b/pgKn/aYDp/+m/6YCp/2mA6f+pgGnAFn/WABZAVn/WAJZ/lgAWQJZ/VgEWfxYA1n+WAFZAFn/WAJZ/lgAWQJZ/VgDWf5YAVn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAln9WANZ/lgBWQBZ/1gBWQBZ/1gBWQBZ/1gCWf1YA1n+WAFZAFn/WAFZAKcAp/+mAaf/pgKn/qYCp/2mA6f+pgGnAKf/pgKn/aYDp/6mAacAp/+mAaf/pgGn/6YBp/6mA6f9pgKn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgCnAKcApwCnAaf+pgKn/qYBpwGn/aYEp/ymA6f+pgGnAKcAp/+mAacApwCnAKcAp/+mAqf+pgGnAKcApwCnAKf/pgGnAKcApwCn/6YCp/6mAacAp/+mAqf+pgKn/aYEp/ymA6f+pgGnAKcAp/+mAaf/pgGnAKf/pgGn/6YApwGn/6YApwCn/6YDp/2mAqf+pgGnAaf/pgGn/6YApwGn/6YApwGn/6YApwGn/qYCp/+mAaf+pgOn/aYBpwGn/qYCp/+m/6YCp/6mAacAp/+mAqf9pgOn/6b/pgKn/aYDp/+mAKcApwCnAKcBp/6mA6f9pgKn/6YApwCnAaf+pgKn/6YApwCnAaf+pgKn/6YApwCnAaf+pgKn/qYBpwCn/6YCp/2mA6f9pgKn/qYDp/2mAqf+pgKn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgFZ/1gBWf9YAln+WAFZ/1gBWQBZAFn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAVn/WAFZ/1gBWf5YA1n+WABZAVn9WAVZ/FgCWf9Y/1gDWfxYBFn8WARZ/FgEWftYBVn8WANZ/lgAWQFZAFn/WAJZ/FgFWfymA6f+pgGn/6YBpwCn/6YCp/2mAqcAp/+mAqf+pgGn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAqf9pgOn/6b/pgKn/KYFp/ymA6f+pgGnAKcAp/+mAacApwCnAKf/pgKn/qYCp/6mAacApwCnAKcBp/6mAqf+pgGnAaf+pgKn/qYBpwCnAKcApwCnAKf/pgKn/qYCp/6mAaf/pgKn/qYBp/+mAaf/pgKn/aYDp/2mA6f+pgGn/6YBp/+mAqf9pgOn/qYBp/+mAaf/pgKn/qYApwGn/6YBp/+mAaf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgGn/6YBp/6mA6f9pgOn/aYCp/+mAKcBp/6mAqf/pgCnAKcAp/+mAqf+pgKn/qYBpwCnAKcAp/+mAqf+pgOn/KYEp/umBqf6pgWn/aYApwKn/aYCp/+mAKcBpwCn/6YBp/6mAqcAp/+mAqf8pgSn/qYApwKn/KYEp/6mAacAp/+mAacAp/+mAqf+pgKn/aYDp/6mAacAp/+mAKcBp/+mAKcCp/ymBKf9pgOn/aYCp/+mAacAp/+mAKcBp/+mAaf+pgKn/qYDp/ymA6f+pgFZAFn/WAJZ/VgDWf5YAVkAWQBZ/1gCWf5YAln+WAJZ/lgCWf5YAln+WAJZ/lgCWf5YAVkAWQBZAFkAWQBZ/1gCWf5YAln+WAJZ/lgCWf5YA1n8WARZ/FgEWf5YAVn/WABZAFkBWf9YAVn/WABZAVn/WACnAaf+pgSn+6YFp/umBaf7pgan+qYGp/qmBaf8pgSn/aYCp/2mBKf9pgGnAKf/pgGnAKf/pgGnAKf/pgCnAaf+pgSn/KYDp/2mAqcApwCn/6YBp/+mAacAp/+mAaf/pgGn/6YBp/+mAacAp/+mAaf/pgGnAKf/pgCnAacAp/+mAaf+pgOn/aYCpwCn/qYDp/ymA6cAp/+mAKcBp/2mBqf6pgOnAKf+pgSn+6YFp/umBaf8pgKnAKf/pgGn/6YBp/+mAaf/pgGn/qYCp/+mAKcBp/+mAKcBp/6mAqf/pgCnAaf/pgCnAKcApwCnAaf/pgCnAKcBp/6mA6f9pgOn/aYCpwCn/6YCp/6mAacAp/+mAqf9pgSn+6YFp/umBKf9pgKn/6YApwCnAKcApwCnAKcApwCnAKcApwCnAKf/pgKn/qYBpwCn/6YBpwCn/6YBpwCn/6YBp/+mAaf/pgKn/aYDp/6mAacApwCnAKf/pgKn/qYCp/+m/6YCp/6mAqf/pgCnAKcApwCnAKcBp/2mBKf8pgOn/qYBp/+mAqf9pgOn/aYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf+pgKnAKf+WAJZ/1gAWQFZ/1gAWQFZ/1gAWQFZ/1gAWQJZ/FgEWf1YAln/WABZAFkBWf9YAVn+WAJZ/1gBWf9YAFkBWf5YA1n9WAJZ/1gAWQFZ/1gAWQBZAVn+WANZ/FgDWf9YAFkAWQFZ/lgDWf1YAVkBWf9YAVn/pgCnAaf/pgCnAKcBp/+mAaf+pgKn/qYCp/+mAKcBp/6mAacApwGn/qYCp/+mAKcApwCnAaf/pgGn/qYCp/+mAaf/pgCnAaf/pgGnAKf/pgGn/6YBpwCn/6YBp/+mAaf/pgCnAaf/pgCnAaf+pgOn/KYEp/ymBKf9pgKn/qYCp/6mAqf/pgCnAaf/pgCnAKcBp/+mAaf/pgCnAqf8pgan+aYGp/2mAKcDp/ymA6f+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYCp/6mAacAp/+mA6f8pgOn/6b/pgGnAKf/pgKn/aYDp/2mA6f+pgCnAaf/pgGn/6YBp/+mAqf+pgGn/6YBpwGn/qYBpwCnAKcBp/6mAacApwCnAaf+pgGnAKf/pgKn/qYBpwCn/6YBpwCn/6YCp/2mAqcAp/+mAaf/pgCnAaf+pgOn/KYEp/2mAqf/pgCnAaf+pgOn/qYBp/+mAaf/pgKn/aYDp/2mA6f9pgOn/aYDp/2mA6f+pgKn/aYDp/6mAqf/pgCn/6YCp/6mA6f8pgOn/qYCp/+mAKf/pgOn/KYFp/umA6f/pgGn/qYDp/2mAacCp/ymBKf9pgGnAVn+WANZ/VgCWf9YAFkBWf9YAVkAWf9YAFkAWQFZAFn/WAFZ/lgCWQBZ/1gBWf9YAFkBWf9YAVn/WAFZ/lgCWf9YAVn/WAFZ/lgDWf1YA1n9WANZ/VgDWf5YAFkBWf5YA1n9WANZ/FgEWf1YAln/WABZAaf+pgKn/6YApwCnAKf/pgOn/KYEp/ymA6f/pv+mA6f9pgGnAaf+pgKn/qYCp/6mAqf+pgGnAKf/pgKn/qYBp/+mAaf/pgGnAKf/pgGn/6YBp/+mAaf/pgGn/6YCp/ymBaf8pgKnAKf+pgOn/aYDp/2mAqf/pgCnAaf/pgCnAaf+pgOn/aYCp/+mAKcBp/+mAaf/pgCnAaf/pgGn/6YBp/+mAaf/pgCnAaf/pgGn/qYDp/2mAqf/pgCnAqf+pgCnAaf/pgKn/qYBpwCn/6YCp/6mAacBp/6mAqf+pgKn/6YBp/+m/6YDp/2mAqf/pv+mAqf/pgCnAKf/pgKn/qYCp/6mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGn/6YApwGn/6YApwGn/qYDp/2mAqcAp/+mAaf/pgGnAKcAp/+mAacAp/+mAacAp/+mAqf+pgCnAqf+pgGnAKf/pgGnAaf9pgSn+6YEp/6mAaf/pgGn/qYCp/+mAKcBp/6mAqf/pgGn/qYCp/6mA6f9pgKn/6YApwGn/6YApwGn/6YBp/+mAKcBp/6mA6f9pgGnAaf+pgKn/6YApwGn/6YBp/6mA6f9pgNZ/VgDWf1YA1n9WANZ/VgDWf1YA1n9WANZ/FgEWf1YA1n8WARZ/FgEWf1YAln+WAJZ/1gAWQBZAVn+WAJZ/1gAWQFZ/1gAWQFZ/1gBWf9YAVn/WAFZAFn/WAFZ/1gBWf9YAVkAWf9YAln8WAVZ/FgCWQGn/aYDp/2mAqcAp/+mAaf/pgGn/6YBpwCn/qYDp/6mAacAp/+mAacBp/6mAaf/pgGnAaf+pgGn/6YBpwCnAKcAp/+mAqf+pgKn/6b/pgKn/qYDp/2mAacApwCnAKcApwCn/6YCp/2mA6f9pgOn/aYCp/+mAKcApwGn/qYBpwCnAKcApwCnAKf/pgKn/qYBpwCn/6YCp/6mAacApwCnAKcApwCn/6YCp/6mAqf+pgGn/6YBp/+mAacAp/+mAaf/pgGn/6YBpwCn/6YCp/2mA6f+pgGnAKcAp/+mAacAp/+mAqf9pgOn/qYCp/6mAKcCp/2mA6f/pv6mBKf7pgWn/KYDp/6mAacAp/+mAqf+pgGnAKf/pgGnAKf/pgGn/6YBp/+mAaf+pgOn/qYApwGn/6YBp/+mAaf/pgGn/6YBp/+mAaf/pgCnAqf8pgSn/aYCpwCn/qYCp/+mAaf/pgGn/6YBp/+mAaf/pgKn/aYDp/2mA6f+pgGnAKf/pgGn/6YBpwCnAKcApwCn/6YBpwCnAKcApwCn/6YCp/6mAacApwCnAKcApwCn/6YDp/ymBKf9pgGnAaf+pgKn/6b/pgOn/KYFWfpYBln7WANZAFn+WANZ/VgCWf5YAln/WABZAFkAWf9YA1n8WARZ/FgEWfxYBFn9WAFZAFn/WAFZAFn/WAFZ/1gBWf9YAVn/WAFZ/1gBWQBZAFkAWf9YAln+WANZ/FgDWf9Y/1gCWf1YA1n+WAJZ/VgDp/2mA6f+pgGnAKcAp/+mAqf9pgSn/KYDp/6mAqf+pgGnAKcAp/+mAqf9pgOn/6b/pgGn/6YBpwCn/6YCp/2mBKf7pgWn/KYCpwCn/6YBp/+mAKcApwGn/6YApwCnAKcBp/+mAKcApwCnAaf/pgCnAKcApwCnAKcBp/6mAacBp/2mBaf7pgOnAKf+pgKnAKf+pgSn/KYBpwGn/6YBpwCn/qYCp/+mAKcBp/+mAacAp/6mA6f+pgGnAKf/pgGnAKf/pgGnAKf+pgOn/aYDp/2mAqf/pgGn/6YBp/+mAKcBp/+mAKcCp/umB6f5pgan+6YEp/2mA6f+pgCnAaf/pgGnAKf/pgGn/6YBpwCn/6YBp/+mAaf/pgKn/aYDp/2mAqcAp/+mAaf/pgCnAqf9pgOn/aYDp/6mAaf/pgGn/6YCp/2mA6f9pgKnAKf+pgSn+6YEp/6mAKcBpwCn/6YBp/+mAaf/pgKn/qYApwKn/aYDp/6mAaf/pgKn/aYCpwCn/6YCp/2mAqf/pgKn/qYBp/+mAKcBpwCnAKf/pgGn/6YBpwCn/6YCp/6mAacAp/+mAqf+pgGnAKcApwCn/6YCp/6mAqf+WAFZAFkAWQBZAFkAWf9YAVkAWQBZAFn+WANZ/VgCWQBZ/lgDWf1YAln/WAFZ/lgEWfpYBln8WAJZAFn+WAJZAFn/WAFZ/1gBWQBZ/1gBWf9YAln+WAFZAFkAWQBZAFkAWf9YA1n8WANZ/1j+WARZ/FgDp/2mA6f9pgOn/qYApwGn/qYDp/2mAqf/pgCnAaf/pgGn/qYDp/6mAaf/pgGn/6YCp/6mAacAp/+mAacBp/2mA6f+pgCnAaf/pgCnAaf/pgGn/qYDp/2mAqcAp/+mAqf9pgOn/aYDp/6mAacAp/+mAaf/pgGn/6YCp/2mA6f9pgKn/6YCp/2mBKf7pgWn/KYDp/6mAqf+pgKn/qYBpwCnAKf/pgGn/6YCp/2mBKf7pgSn/6b+pgSn/KYDp/+m/6YBpwGn/qYCp/6mAacApwCn/6YBp/+mAKcBp/6mAqf+pgKn/6YApwCnAaf+pgOn/aYCpwCn/qYDp/6mAaf/pgGn/6YCp/6mAKcCp/2mBKf8pgOn/aYDp/6mAaf/pgGn/6YBpwCn/qYDp/2mAqcAp/+mAacAp/+mAqf+pgGnAKcApwGn/6YAp/+mA6f8pgWn+qYFp/2mAqf/pv+mA6f7pgen+aYGp/umA6f+pgKn/6YBp/6mAqf+pgKn/6YApwGn/qYCp/6mAacApwCnAKcAp/+mAaf/pgKn/qYBpwCn/6YBp/+mAqf+pgKn/qYBpwCnAKcApwCnAKcApwCnAKcAp/+mA1n8WARZ/FgDWf9YAFkAWf9YAln+WAJZ/lgBWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAJZ/lgBWf9YAVkAWf9YAVn/WAFZ/1gBWQBZ/1gBWf9YAVkAWf9YAVn/WAFZ/1gBWf9YAVn/WABZAFkBWf9YAKcApwCnAaf+pgKn/qYCp/+mAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCnAKcApwCn/6YCp/2mBKf7pgan+qYFp/ymA6f+pgGnAKcAp/+mAqf9pgSn/KYDp/2mA6f+pgKn/qYBp/+mAaf/pgGn/6YBp/6mAqf/pgCnAKcApwCnAaf/pgGn/qYDp/ymBaf7pgSn/aYCp/6mA6f8pgSn/KYEp/ymBKf9pgGnAKf/pgKn/6YApwCn/6YCp/6mAqf+pgKn/qYCp/6mAqf+pgOn/KYEp/ymA6f/pv+mAqf+pgGnAaf9pgSn/KYDp/+mAKcApwCnAKcApwCnAKcApwCnAKcAp/+mAqf+pgKn/qYCp/6mAqf/pgCnAKcApwCnAaf+pgKn/aYEp/ymA6f/pv6mBKf8pgOn/qYBp/+mAqf9pgOn/aYDp/6mAKcBp/+mAaf/pgGn/6YBp/+mAKcBp/+mAKcBp/6mAqf+pgKn/6YApwCn/6YDp/2mA6f9pgKn/qYCp/+mAacAp/+mAKcBp/+mAacAp/+mAacAp/+mAacAp/+mAqf+pgGnAKcApwCnAKcApwCnAaf+pgOn/aYDp/1YA1n9WANZ/VgDWf1YA1n9WANZ/VgCWf9YAVn/WAJZ/FgEWf5YAFkCWf1YAlkAWf5YA1n9WANZ/lgAWQFZ/1gBWQBZ/1gBWQBZAFn/WAFZAFkAWQBZAFn/WAJZ/lgCWf9YAFkBWf5YAln/WABZAVn+WAGnAKcAp/+mAaf/pgGn/6YBp/6mA6f+pgGn/6YBp/+mAqf9pgOn/aYDp/6mAKcBp/6mAqcAp/2mBKf8pgOn/6b/pgGnAKcAp/+mAqf9pgSn/KYDp/2mA6f+pgKn/aYDp/2mBKf8pgOn/qYBpwCnAKcApwCnAKcAp/+mAqf/pgGn/qYBpwCnAKcApwGn/aYEp/ymAqcBp/6mAacAp/+mAqf+pgGnAKcAp/+mAqf9pgSn/KYDp/6mAacAp/+mAacApwCn/6YBp/+mAqf+pgGnAKf/pgGnAKcApwCnAKf/pgGnAKcApwCnAKf/pgGnAKcApwCn/6YCp/2mBKf8pgOn/qYCp/6mAacAp/+mA6f7pgan+6YDp/+m/6YDp/2mAacBp/6mA6f9pgKn/6YApwGn/6YBp/+mAKcBp/+mAaf+pgOn/KYEp/6mAKcBp/+mAKcBp/+mAaf/pgGn/6YBpwCn/6YApwKn/KYFp/umBKf+pgCnAaf+pgOn/qYApwKn/aYDp/2mA6f9pgOn/aYDp/6mAacAp/+mAacBp/6mA6f8pgKnAKcApwCnAKf+pgOn/aYDp/2mAqf/pgGnAKf+pgOn/KYFWftYA1n/WABZAVn+WAJZ/lgDWf1YAln/WABZAFkBWf5YAln+WAFZAVn+WAJZ/1j/WANZ/FgEWf1YAln+WANZ/VgBWQBZ/1gDWf1YAln9WARZ/FgEWfxYA1n/WP9YAln9WANZ/1j/WAJZ/VgDWf9YAFn/pgKn/qYCp/6mAacApwCnAKcAp/+mAqf9pgOn/qYBp/+mAaf+pgOn/aYDp/2mA6f9pgKnAKf+pgOn/aYBpwGn/qYCp/6mAqf+pgKn/6YApwGn/6YApwGn/6YBp/+mAaf+pgOn/KYEp/2mAqf/pgCnAKcApwCnAKcApwGn/qYCp/6mAacBp/6mAqf+pgGnAaf+pgKn/qYCp/+mAaf/pgCnAqf9pgOn/qYBp/+mAaf/pgGn/6YBp/+mAacAp/6mBKf8pgOn/qYBpwCnAKcAp/6mBKf7pgWn/KYCpwCn/6YBp/+mAqf9pgOn/qYBpwCn/6YApwKn/aYDp/2mAqf/pgGnAKf/pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgGnAKf/pgGn/6YApwGn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgCnAaf/pgCnAKcApwGn/6YBp/6mA6f9pgOn/aYCpwCn/6YCp/6mAaf/pgGnAaf+pgGnAKf/pgKn/qYBpwCnAKcAp/+mAqf/pgCnAKf/pgKn/qYCp/+m/6YBp/+mAqf/pv+mAqf9pgSn/aYBpwCnAKcApwCnAKf/pgKn/lgBWQBZ/1gCWf5YAVkAWf9YAln+WAFZAFn/WAJZ/lgBWf9YAFkBWQBZ/lgDWfxYBFn9WAJZ/1gAWQFZ/1gAWQFZ/lgDWf1YA1n9WAJZ/1gBWf9YAVn/WAFZ/1gBWf5YA1n9WAJZ/1gAWQBZAFkBWf5YA6f8pgSn/qYApwGn/6YApwKn/aYDp/ymBaf7pgSn/qYApwGn/6YBp/+mAaf/pgGnAKf/pgGn/6YBpwCn/6YBpwCn/qYDp/2mA6f+pgGn/6YBpwCn/6YBpwCn/6YCp/2mA6f+pgGnAKf/pgKn/qYBpwCn/6YCp/6mAaf/pgGn/6YBp/+mAKcBp/+mAKcBp/+mAacAp/6mBKf7pgWn/aYBpwCnAKf/pgKn/qYCp/6mAacApwCnAKcAp/+mAqf+pgGnAKf/pgGnAaf9pgOn/qYBpwCnAKf/pgKn/qYCp/6mAqf+pgKn/6YApwCnAKcApwCnAKcApwCnAKcAp/+mAqf/pv+mAqf+pgGnAKf/pgGnAKcAp/+mAqf9pgOn/qYBpwCn/6YCp/2mA6f+pgGnAKf/pgGnAKcApwCn/6YBpwCnAaf+pgGn/6YBpwCnAKf/pgGn/6YBpwCn/6YBp/+mAacAp/6mA6f9pgKnAKf+pgOn/aYCpwCn/6YCp/2mA6f+pgGnAKcAp/+mAqf+pgGnAKf/pgKn/qYCp/2mBKf7pgWn/KYCpwCn/6YBp/+mAKcBp/+mAaf/pgCnAaf/pgGn/6YBp/+mAVn/WAJZ/VgEWftYBFn+WAFZAFn/WAFZ/1gBWQBZ/1gBWf9YAln+WAJZ/VgDWf9Y/1gCWf5YAVkAWQBZ/1gBWQBZ/1gCWf1YAln/WABZAln9WANZ/VgCWQBZ/1gCWf1YA1n+WAFZAFn/WAFZ/1gBWQBZ/qYDp/6mAKcCp/ymBKf+pgCnAaf+pgKn/6YBp/6mAqf+pgGnAaf+pwKo/qcAqAKo/qcCqP6nAagAqACoAaj9pwSo/acCqP+nAKgAqACoAaj+pwKo/qcCqP6nAqj+pwKo/qcBqACo/6cCqP6nAqj9pwOo/acDqP+n/6cCqP6nAaj/pwKo/acEqPynAqgAqP+nAaj/pwGo/6cBqACo/6cCqP6nAagAqACoAaj+pwKo/qcBqAGo/qcCqP2nA6j+pwKo/qcAqAGo/6cCqP6nAaj/pwGo/6cCqP6nAaj/pwGo/6cBqACo/qcEqPunBKj+pwGo/6cBqP+nAaj/pwCoAaj/pwGo/6cAqACoAqj9pwKo/6cAqAGoAKj+pwKo/6cBqP+nAaj+pwOo/acDqP2nA6j9pwOo/acDqP6nAagAqP+nAagAqP+nAqj+pwGoAKj/pwKo/qcBqAGo/acEqP2nAagBqP2nBKj9pwGoAKj/pwKo/qcBqACo/6cBqACoAKgAqP+nAaj/pwKo/qcBqACo/6cBqACo/6cCqP6nAqj9pwSo/KcEqP2nAagAqP+nAqj+pwKo/qcBqACoAKgAqACoAKgAqABYAFgAWP9XAlj+VwFYAVj+VwFYAFgAWABYAVj+VwFYAVj+VwJY/1cAWABYAFgAWAFY/1f/VwJY/1cAWAFY/VcEWP1XAlj/VwBYAFgBWP5XA1j9VwJYAFj/VwFY/1cBWP9XAVgAWP5XA1j9VwJY/1cAWAGo/6cBqP+nAaj/pwGo/6cBqACo/6cBqP+nAagAqP+nAaj/pwGo/6cBqP+nAKgAqACoAaj/pwCoAKgAqACoAqj9pwOo/acDqP2nBKj8pwSo/KcDqP6nAqj+pwKo/qcBqACo/6cBqACo/6cBqP+nAaj/pwGo/6cAqAGo/6cBqP+nAKgBqP6nAqgAqP6nA6j9pwKo/6cBqP+nAaj/pwCoAaj/pwGo/6cBqACo/6cCqP6nAagBqP6nAqj/p/+nA6j8pwSo/KcEqP2nAagAqACoAKgAqACoAKgAqACoAKj/pwKo/qcBqACo/6cBqACo/6cBqACo/6cBqACo/6cBqACo/6cBqP+nAagAqP+nAaj+pwSo/KcDqP6nAKgCqP6nAagAqP+nAagAqP+nAagAqP6nA6j9pwKoAKj+pwOo/KcEqPynBKj9pwKo/6f/pwKo/qcBqACo/6cCqP6nAaj/pwGo/6cCqP6nAagBqP2nBKj8pwSo/KcEqP2nAagAqP+nAqj+pwKo/acEqPynA6j+pwKo/qcCqP2nBKj8pwOo/qcBqACo/6cBqP+nAaj/pwGo/6cBqP+nAaj/pwGo/6cAqAKo/KcFWPxXAlj/VwFY/1cBWP9XAVj/VwFY/1cAWAJY/VcDWP1XA1j+VwFY/1cAWAJY/lcBWABY/1cBWAFY/VcEWPxXA1j/V/9XAlj9VwNY/lcBWABYAFgAWABYAFgAWABYAVj+VwNY/FcEWP1XAlj/VwBYAFgBqP6nAqj/pwCoAaj/pwCoAaj+pwOo/acDqP2nAqj/pwCoAaj+pwOo/acCqP+n/6cDqP2nAqj/p/+nA6j9pwKo/6f/pwKo/6cAqACoAKgAqACoAKgAqACoAKgAqACoAKgAqP+nAqj+pwGoAKj+pwSo/KcCqACo/qcEqPunBaj8pwKo/6cBqACoAKj/pwCoAagAqACo/6cBqP6nA6j+pwGo/6cAqAGoAKgAqACo/qcDqP6nAqj+pwGo/6cBqP+nAaj/pwGoAKj+pwOo/acDqP6nAKgBqP+nAaj/pwGo/qcEqPunBKj+pwGoAKj/pwGo/6cCqP6nAKgCqP2nA6j+pwCoAqj9pwOo/acCqP+nAaj/pwCoAaj+pwOo/KcEqP2nA6j8pwSo/acDqP2nA6j9pwOo/qcBqACoAKgAqP+nAqj+pwKo/qcCqP+nAKgBqP6nAqj/pwGo/6cBqP+nAaj/pwGo/6cBqACo/6cBqP+nAaj/pwGo/6cAqAKo/acCqP+nAKgBqP+nAKgBqP6nA6j8pwSo/acCqP+nAKgAqACoAKgAqACoAKgAqP+nAqj+pwKo/qcBqP+nAqj+pwGo/6cBqACo/1cBWABY/1cCWP5XAVgAWABY/1cCWP5XAlj+VwJY/lcCWP9XAFgBWP9XAFgAWAFY/lcDWPxXA1j+VwJY/lcCWP5XAVgAWABYAFgBWP1XA1j+VwFYAVj9VwNY/VcEWPxXA1j9VwNY/lcBWABY/lcDWP5XAKgBqP+nAKgCqPynBKj9pwKoAKj/pwCoAaj+pwSo+6cFqPynAqgAqP+nAqj9pwSo+6cFqPynA6j+pwKo/acEqPynBKj8pwSo/acBqAGo/qcCqP+nAKgAqAGo/qcCqP+nAKgAqAGo/qcCqP+n/6cDqPynBKj9pwGoAaj9pwWo+6cDqP6nAagBqP6nAqj+pwGoAaj+pwGoAaj+pwOo/acCqP6nA6j9pwKo/6cAqAGo/qcDqP2nA6j9pwKo/6cCqP2nA6j8pwWo/KcCqP+nAKgBqP+nAKgAqAGo/6cBqP6nAqj+pwOo/qcAqAGo/6cAqAGo/6cAqAGo/qcCqP+nAKgBqP6nAqj+pwKo/6cAqACoAaj9pwWo+qcFqP6nAKgAqACoAKgAqAGo/qcCqP6nAagAqAGo/qcCqP6nAagBqP6nA6j9pwKo/qcCqP+nAKgBqP6nAqj+pwGoAKgBqP6nAqj+pwKo/6cAqACoAaj+pwKo/qcBqACoAKgAqACo/6cBqACo/6cCqP6nAKgDqPunBaj8pwKoAaj+pwGo/6cAqAOo+6cFqPynAqgAqP6nA6j+pwCoAaj+pwKo/6cAqAGo/6cAqABYAVgAWP5XA1j9VwNY/lcAWAFY/1cAWAFY/1cCWPxXBVj6VwdY+lcEWP5XAFgBWP9XAVgAWP9XAVj/VwFYAFj/VwJY/VcDWP5XAVgAWP9XAVj/VwJY/VcEWPtXBVj9VwFYAFgAWABYAFgAWABYAFgAWP+nAagAqACoAKj/pwGoAKgAqACo/6cCqP6nAqj+pwGoAKgAqACoAKgAqP+nAqj+pwGoAKj/pwGoAKj+pwOo/KcEqP6nAKgBqP6nAagBqP+nAKgAqACoAKgAqACoAKgAqAGo/acFqPunBKj+pwCoAaj/pwGo/6cCqP2nA6j+pwCoA6j7pwao+qcEqP+n/6cCqP6nAKgDqPunBqj7pwOo/6cAqACoAKgAqAGo/qcCqP6nAqj+pwKo/6f/pwOo/KcEqP2nAagBqP2nBKj8pwOo/6cAqP+nAagAqACoAaj+pwGo/6cDqPynBKj8pwOo/6cAqACoAKgAqACoAKgAqACoAaj+pwKo/6f/pwOo/acCqP+n/6cCqP6nAagAqP6nBKj8pwKoAKj+pwOo/acCqACo/6cBqP6nAqgAqP+nAaj/pwGoAKj/pwKo/acEqPynBKj9pwKo/6cAqAGo/6cBqP+nAKgBqP+nAaj/pwCoAaj/pwGo/qcDqP2nA6j8pwSo/acCqP+nAKgBqP");
        var audio;

        if ('success' === type) {
            audio = success;
        }
        if ('success2' === type) {
            audio = success2;
        }
        if ('error' === type) {
            audio = error;
        }

        audio.play();

        return audio;
    };

    this.instantiatePrintButtons = function() {
        $('.button.zpl_print').click(function() {
            var printWindow = window.open();
            printWindow.document.open('text/plain')
            printWindow.document.write($(this).attr('data-zpl'));
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    };
}

/**
 * handles all the trees stuff
 */
function Trees() {

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
     this.get = function() {
          var $filter = $('.get_tree').first();
          var $container = $('#tree_container').first();

          $filter.on('keyup paste', function() {
               var params = $filter.data('filter');
               $.ajax({
                    url: webroot + params.controller + '/' + params.action,
                    data: {
                         fields : params.fields,
                         element: params.element,
                         term : $filter.val()
                    },
                    success: function(resp, status) {
                        if ('success' == status) {
                            $container.html(resp);
                            General.beep('success');
                        } else {
                            $container.html('<div class="nothing_found">'+trans.no_tree_found+'</div>');
                            if ( 0 < $filter.val().length){
                                General.beep('error');
                            }
                        }
                    },
                    dataType: 'html',
                    beforeSend: function(xhr){
                         xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                         $container.html(searching);
                    }
               });
          });
     };
}

/**
 * handles all the varieties stuff
 */
function Varieties() {

     /**
      * having our class always accessible can get handy
      */
     var self = this;

     /*
      * load and configure the Crossing.Batch select field. Unlock Code
      */
     this.selectBatchId = function() {
          var $select = $('.select2batch_id');

          // get batch_id
          $select.select2({
               ajax: {
                    url: webroot + 'varieties/searchCrossingBatchs',
                    delay: 250,
                    dataType: 'json',
                    processResults: function (resp) {
                         var results;
                         results = $.map(resp.data, function( value, index ){
                              return {
                                   text : value,
                                   id   : index
                              };
                         });

                         return {
                              results : results
                         };
                     },
                    beforeSend: function(xhr){
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
                    data: { batch_id : $select.val() },
                    success: function(resp) {
                         $('#code').val(resp.data)
                                 .removeAttr('disabled');
                    },
                    dataType: 'json',
                    beforeSend: function(xhr){
                         xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    }
               });
          });
     };

     /**
      * Set code as underscored official name
      */
     this.setCodeFromOfficialName = function() {
          var $official_name = $('.official_name').first();
          var $form = $official_name.parents('form');
          var $code = $form.find('#code').first();
          var $batch_id = $form.find('#batch-id');

          $official_name.on('keyup paste change', function() {
               $code.val(function() {
                    return $official_name.val()
                            .trim()
                            .replace(/[^äöüa-zA-Z0-9-_]/ug,'_')
                            .toLowerCase();
               });
          });

          $form.on('submit', function(event) {
               $code.removeAttr('disabled');
               $batch_id.removeAttr('disabled');
          });
     };
}

/**
 * handles all the marks stuff
 */
function Marks() {

     /**
      * having our class always accessible can get handy
      */
     var self = this;

     /**
      * initialize
      */
     this.initValidationRulesCreator = function() {
          var $mark_field_type = $('.mark_field_type');

          $mark_field_type.change(function() {
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
     this.showValidationRulesCreatorFields = function(val) {
          var $all = $('.mark_validation_rule');

          switch(val) {
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
     this.removeControl = function(obj) {
          $(obj).attr('disabled', 'disabled');
          $(obj).parent().hide();
     };

     /**
      * re-add and re-enable a removed control
      *
      * @param {jQuery} obj control we want to add again
      */
     this.addControl = function(obj) {
          $(obj).removeAttr('disabled');
          $(obj).parent().show();
     };

     /**
      * load mark form field when selected in the form editor
      */
     this.addMarkFormFieldInit = function() {
          $('.add_mark_form_field')
            .off('change')
            .change(function() {
              $.ajax({
                   url: webroot + 'mark-form-properties/get/'+$(this).val()+'/'+$(this).attr('data-mode'),
                   success: function(resp) {
                        $('.mark_form_fields').append(resp);
                        self.initNewField();
                   },
                   method: 'GET',
                   dataType: 'html',
                   beforeSend: function(xhr){
                        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                   }
              });
          });
     };
     
     /**
      * Instantiate actions for newly added fields (applied to all fields)
      */
     this.initNewField = function() {
          self.makeFormFieldsSortable();
          self.makeFormFieldsDeletable();
          General.instantiateDatepicker();
     };
     
     /**
      * Enable sortable functionality for form fields. Grab them by the handle.
      */
     this.makeFormFieldsSortable = function() {
         $('.mark_form_fields.sortable').sortable({
             handle : '.sortable_handle'
         });
     };

     /**
      * Enable deletable functionality for form fields.
      */
     this.makeFormFieldsDeletable = function() {
         $('.mark_form_fields .delete_button')
            .off('click')
            .click(function(){
                var del = confirm(trans.delete_element +' '+$(this).prev().find('label').first().text()+'?');
                if (del == true) {
                    $(this).parents('.deletable_element').remove();
                }
            });
     };
     
     /**
      * Load fields of selected mark form
      */
     this.loadFormFields = function () {
          $('.form-field-selector').change(function() {
               $.ajax({
                   url: webroot + 'marks/get-form-fields/'+$(this).val(),
                   success: function(resp) {
                        $('.mark_form_fields_wrapper').html(resp);
                        self.initNewField();
                   },
                   method: 'GET',
                   dataType: 'html',
                   beforeSend: function(xhr){
                        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                   }
              });
          });
     };
     
     /**
      * Apply validation rules
      */
     this.applyValidationRules = function () {
          $('.select_property').change(function() {
               $.ajax({
                   url: webroot + 'mark-form-properties/get/'+$(this).val()+'/default',
                   success: function(resp) {
                        var $container = $('#mark_value_wrapper'),
                            $el = $('.replace_me'),
                            name = $el.attr('name'),
                            id = $el.attr('id'),
                            cls = $el.attr('class');
                        
                        $container.html($(resp));
                        $container.find('input')
                                .attr('name',name)
                                .addClass(cls)
                                .attr('id',id);
                        
                        General.instantiateDatepicker();
                   },
                   method: 'GET',
                   dataType: 'html',
                   beforeSend: function(xhr){
                        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                   }
               });
          });
     };

    /**
     * Unlock scanner field as soon as a mark form was chosen
     */
    this.unlockScannerField = function() {
        if ($('#mark-form-id').val()) {
            $('.scanner_mark_field')
                .removeAttr('disabled')
                .focus();
        } else {
            $('#mark-form-id').change(function(){
                $('.scanner_mark_field')
                    .removeAttr('disabled')
                    .focus();
            });
        }
    };

    this.byScanner = function() {
        var $scanner = $('.scanner_mark_field').first();

        var params = $scanner.data('filter');

        $scanner.bindWithDelay('keyup paste', function() {
            if (0 < $scanner.val().length) {
                self.processScannerCode($scanner);
            }

        }, 200);
    };

    this.getTree = function(val) {
        var $container = $('#tree_container').first();
        var $searching = $('#searching').first();

        $.ajax({
            url: webroot + '/trees/getTree',
            data: {
                fields : ['publicid'],
                element: 'get_tree',
                term : val
            },
            success: function(resp, status) {
                if ('success' == status) {
                    $container.html(resp);
                    General.beep('success');
                } else {
                    $container.html('<div class="nothing_found">'+trans.no_tree_found+'</div>');
                    General.beep('error');
                }
                $searching.hide();
            },
            dataType: 'html',
            beforeSend: function(xhr){
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $searching.show();
            }
        });
    };

    this.getScannerMark = function(val) {
        var $searching = $('#searching').first();

        $.ajax({
            url: webroot + 'mark-scanner-codes/get-mark',
            data: {
                term : val
            },
            success: function(resp) {
                self.setMark($.parseJSON(resp));
                $searching.hide();
            },
            error: function() {
                General.beep('error');
                $searching.hide();
            },
            dataType: 'html',
            beforeSend: function(xhr){
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                $searching.show();
            }
        });
    };

    this.setMark = function(data) {
        var input_id = '#mark-form-fields-mark-form-properties-'+data.mark_form_property_id+'-mark-values-value';
        var radio_id = '#mark-form-fields-mark-form-properties-'+data.mark_form_property_id+'-mark-values-value-'+data.mark_value;
        var $el = 1 === $(input_id).length ? $(input_id) : $(radio_id);

        if ( 1 === $el.length ) {
            if ( 'radio' === $el.attr('type') ) {
                $el.attr('checked', 'checked');
            } else {
                $el.val(data.mark_value);
            }
            General.beep('success');
        } else {
            General.beep('error').addEventListener("ended", function() {
                alert(String(trans.matching_elements).format($el.length));
            });
        }
    };

    this.submitForm = function() {
        var $form = $('form');
        var $inputs = $form.find('input, select, textarea');
        var valid = true;

        $inputs.each(function() {
            if (! $(this)[0].checkValidity()) {
                valid = false;
            }
        });

        if ( 0 == $('#tree_id').length || '' == $('#tree_id').val() ) {
            valid = false;
        }

        if ( valid ) {
            General.beep('success2').addEventListener("ended", function() {
                $('button[type=submit]').trigger('click');
            });
        } else {
            General.beep('error');
        }
    }

    this.processScannerCode = function($scanner) {
        var val = $scanner.val();

        if ( null !== val.match(/^M\d{5}$/) ) {
            self.getScannerMark(val);
        } else if (null !== val.match(/^SUBMIT$/)) {
            self.submitForm();
        } else {
            self.getTree(val);
        }

        $scanner.val('');
    };
}


/**
 * fires after DOM is loaded
 */
$( document ).ready(function() {
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

(function($) {

    $.fn.bindWithDelay = function( events, data, fn, timeout, throttle ) {

        if ( $.isFunction( data ) ) {
            throttle = timeout;
            timeout = fn;
            fn = data;
            data = undefined;
        }

        // Allow delayed function to be removed with fn in unbind function
        fn.guid = fn.guid || ($.guid && $.guid++);

        // Bind each separately so that each element has its own delay
        return this.each(function() {

            var wait = null;

            function cb() {
                var e = $.extend(true, { }, arguments[0]);
                var ctx = this;
                var throttler = function() {
                    wait = null;
                    fn.apply(ctx, [e]);
                };

                if (!throttle) { clearTimeout(wait); wait = null; }
                if (!wait) { wait = setTimeout(throttler, timeout); }
            }

            cb.guid = fn.guid;

            $(this).on(events, data, cb);
        });
    };

})(jQuery);

/**
 * Brings something like PHP's sprintf to js. Use it like "{0} string {1}".format("Handy", "replacement");
 *
 * @author: fearphage
 * @link: http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format/4673436#4673436
 */
if (!String.prototype.format) {
    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}