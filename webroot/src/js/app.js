/**
 * load dependencies
 */
global.$ = global.jQuery = require('jquery');

require("jquery-ui");
require("jquery-ui/ui/data");
require("jquery-ui/ui/version");
require("jquery-ui/ui/ie");
require("jquery-ui/ui/scroll-parent");
require("jquery-ui/ui/plugin");
require("jquery-ui/ui/disable-selection");
require("jquery-ui/ui/unique-id");
require("jquery-ui/ui/safe-blur");
require("jquery-ui/ui/safe-active-element");
require("jquery-ui/ui/position");
require("jquery-ui/ui/keycode");
require("jquery-ui/ui/focusable");
require("jquery-ui/ui/tabbable");
require("jquery-ui/ui/widget");
require('jquery-ui/ui/widgets/mouse');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/sortable');
require('jquery-ui/ui/widgets/button');
require('jquery-ui/ui/widgets/draggable');
require('jquery-ui/ui/widgets/resizable');
require('jquery-ui/ui/widgets/dialog');
require('jquery-ui/ui/widgets/tooltip');
require('select2');
require('jquery.cookie');
require('./assets/jQueryBindWithDelay');
require('./assets/sprintf');
require('./assets/jquery.fileDownload');

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