<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
// @see https://github.com/cakephp/cakephp/issues/14085
session_id('cli');
