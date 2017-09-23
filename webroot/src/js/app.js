/**
 * load dependencies
 */
global.$ = global.jQuery = require('jquery');
global.moment = require('moment');

require("jquery-ui");
require("jquery-ui/ui/data");
require("jquery-ui/ui/version");
require("jquery-ui/ui/ie");
require("jquery-ui/ui/scroll-parent");
require("jquery-ui/ui/widget");
require('jquery-ui/ui/widgets/mouse');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/sortable');
require('select2');
require('jQuery-QueryBuilder');
require('./assets/queryBuilder-validate')
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