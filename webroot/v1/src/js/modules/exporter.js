/**
 * handles all the exporting
 */
function ExporterModule(General) {
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
     * start up
     */
    this.init = function () {
        var $export_button = $('.export-button');

        if ($export_button.length < 1) {
            return;
        }

        $export_button.click(function (e) {
            e.preventDefault();
            $.removeCookie('fileDownload', {path: urlbase});
            self.fileDownload(this);
        });
    };

    /**
     * setup file download
     */
    this.fileDownload = function (el) {
        var url = $(el).attr('href');

        $.fileDownload(url, {
            preparingMessageHtml: trans.preparing_report,
            failMessageHtml: trans.preparing_report_failed
        });
    };
}

module.exports = ExporterModule;