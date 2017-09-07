/**
 * expose jQuerys $ to the global scope
 * @type {jQuery}
 */
global.$ = global.jQuery = require('jquery');

/**
 * load dependencies
 */
require("jquery-ui");
require('jquery-ui/ui/widgets/mouse');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/sortable');
require('select2');
require('jQuery-QueryBuilder');
require('./assets/jQueryBindWithDelay');
require('./assets/sprintf');

/**
 * get instance of the general module and expose it as app to the global scope
 *
 * @type {GeneralModule}
 */
var GeneralModule = require('./modules/general.js');
var General = new GeneralModule();
global.app = General;

/**
 * start up
 */
$(document).ready(function () {
    General.init();
});