<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 10:18
 */

require_once dirname(dirname(__FILE__)) . '/autoupdate/Updater.php';
require_once dirname(dirname(__FILE__)) . '/autoupdate/VersionChecker.php';
require_once dirname(dirname(__FILE__)) . '/autoupdate/BackupHandler.php';
require_once dirname(dirname(__FILE__)) . '/autoupdate/FileUpdateHandler.php';
require_once dirname(dirname(__FILE__)) . '/dbupdate/Updater.php';

$updater = new \Autoupdate\Updater();

// redirect to home if no update is aviable
if ( ! $updater->isUpdateAvailable()) {
    header("Location: //" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']));
    die();
}

$updater->setCurrentVersion();

//echo "Starting update process...\n";
//echo "Backing up files... " . $updater->backupFiles() . "\n";
//echo "Backing up database... " . $updater->backupDatabase() . "\n";
//echo "Downloading newest version... " . $updater->downloadNewestVersion() . "\n";
//echo "Extracting files... " . $updater->extractFiles() . "\n";
//echo "Deleting old files... " . $updater->deleteOldFiles() . "\n";
//echo "Moving new files... " . $updater->moveFiles() . "\n";
//echo "Deleting temp files... " . $updater->deleteTempFiles() . "\n";
//echo "Updating database... " . $updater->updateDatabase() . "\n";
// run db migration routine