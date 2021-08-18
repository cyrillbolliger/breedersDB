/**
 * Brings something like PHP's sprintf to js.
 * Use it like "{0} string {1}".format("Handy", "replacement");
 *
 * @author: fearphage
 * @link: http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format/4673436#4673436
 */
if (!String.prototype.format) {
    String.prototype.format = function () {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] != 'undefined' ? args[number] : match;
        });
    };
}