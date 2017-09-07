global.$ = global.jQuery = require('jquery');

require("jquery-ui");
require('jquery-ui/ui/widgets/mouse');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/sortable');
require('select2');
require('jQuery-QueryBuilder');
require('./assets/jQueryBindWithDelay');
require('./assets/sprintf');

var GeneralModule = require('./modules/general.js');
var General = new GeneralModule();
global.app = General;

/**
 * fires after DOM is loaded
 */
$(document).ready(function () {
    General.init();
});