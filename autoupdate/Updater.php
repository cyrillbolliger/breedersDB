<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 10:21
 */

namespace Autoupdate;

// recovery only works if reporting is off
error_reporting(0);

require_once dirname(dirname(__FILE__)) . '/autoupdate/VersionChecker.php';
require_once dirname(dirname(__FILE__)) . '/autoupdate/BackupHandler.php';
require_once dirname(dirname(__FILE__)) . '/autoupdate/FileUpdateHandler.php';

class Updater
{
    private $pathToVersionsFile = '../version.txt';
    private $bitbucket = [
        'repo'       => 'poc',
        'repo_owner' => 'cyrillbolliger',
        'user'       => 'breedersdatabase',
        'pass'       => 'cwQ6LyBUU5qdjZHxA62tDYLAWZJaQem8',
        'branch'     => 'master',
    ];
    private $dbconf = [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'poc',
        'port'     => 8889,
    ];
    private $backupFolder = 'backup';
    private $exclude = [
        'autoupdate',
        'config' . DIRECTORY_SEPARATOR . 'app.php',
        'tests',
        'webroot' . DIRECTORY_SEPARATOR . 'autoupdate.php',
        'webroot' . DIRECTORY_SEPARATOR . '.htaccess',
        'logs',
        'node_modules',
        'tests',
        '.htaccess',
        '.git',
        'tmp' . DIRECTORY_SEPARATOR . 'debug_kit.sqlite',
        // $backupFolder <-- gets pushed dynamically
        // $tempDir <-- gets pushed dynamically
    ];
    private $tempDir = 'tmp';
    private $pathToMysqlBinaries = ''; // set path to mysql and mysqldump command if not installed globally
    private $tempPath;
    private $rootPath;
    private $currentVersion;
    private $versionChecker;
    private $backupHandler;
    private $fileUpdateHandler;
    
    function __construct()
    {
        array_push($this->exclude, $this->backupFolder);
        array_push($this->exclude, $this->tempDir);
        
        $this->rootPath = realpath(dirname(dirname(__FILE__)));
        $this->tempPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->tempDir;
        
        $this->versionChecker = new VersionChecker($this->pathToVersionsFile, $this->bitbucket);
        $this->backupHandler  = new BackupHandler($this->pathToMysqlBinaries);
        
        $extractionFolder        = $this->tempDir . DIRECTORY_SEPARATOR . 'deploy';
        $this->fileUpdateHandler = new FileUpdateHandler($this->tempDir, $extractionFolder, $this->exclude);
    }
    
    public function isUpdateAvailable()
    {
        return $this->versionChecker->isUpdateAvailable();
    }
    
    public function backupFiles()
    {
        return $this->backupHandler->backupFiles($this->backupFolder, $this->exclude);
    }
    
    public function backupDatabase()
    {
        return $this->backupHandler->backupDatabase($this->backupFolder, $this->dbconf);
    }
    
    public function detectCurrentVersion()
    {
        $this->currentVersion = $this->versionChecker->detectLocalVersion();
    }
    
    public function downloadNewestVersion()
    {
        return $this->fileUpdateHandler->getFromBitbucket($this->bitbucket, 'latest');
    }
    
    public function deleteOldFiles()
    {
        return $this->fileUpdateHandler->deleteFiles($this->rootPath);
    }
    
    public function extractFiles()
    {
        return $this->fileUpdateHandler->extractFiles($this->fileUpdateHandler->getDownloadDest(),
            $this->fileUpdateHandler->getExtractionDest());
    }
    
    public function moveFiles()
    {
        return $this->fileUpdateHandler->moveFiles();
    }
    
    public function updateDatabase()
    {
        require_once $this->rootPath . DIRECTORY_SEPARATOR . 'dbupdate' . DIRECTORY_SEPARATOR . 'Updater.php';
        $dbUpdater = new \DBUpdate\Updater($this->dbconf);
        
        return $dbUpdater->update($this->currentVersion);
    }
    
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }
    
    public function setCurrentVersion(string $currentVersion)
    {
        $this->currentVersion = $this->versionChecker->parseVersion($currentVersion);
    }
    
    public function restoreFilesAndDB()
    {
        $success = $this->restoreFiles();
        if ($success) {
            $success = $this->backupHandler->restoreDatabase($this->backupFolder, $this->dbconf);
        }
        
        return $success;
    }
    
    public function restoreFiles()
    {
        $backupPath = $this->backupHandler->getFileBackupPath($this->backupFolder);
        
        $success = true;
        if (is_dir($this->tempPath)) {
            $success = $this->deleteTempFiles();
        }
        if (true === $success) {
            $success = $this->fileUpdateHandler->extractFiles($backupPath,
                $this->fileUpdateHandler->getExtractionDest() . DIRECTORY_SEPARATOR . 'backup');
        }
        if (true === $success) {
            $success = $this->fileUpdateHandler->moveFiles();
        }
        if (true === $success) {
            $success = $this->deleteTempFiles();
        }
        
        return $success;
    }
    
    public function deleteTempFiles()
    {
        // remove the temp dir from the exclude list, else nothing will get deleted
        $this->fileUpdateHandler->removeFromExcludeList($this->tempDir);
        return $this->fileUpdateHandler->deleteFiles($this->tempPath);
    }
    
    public function cleanUpFailedUpdate()
    {
        if (is_dir($this->tempPath)) {
            return $this->deleteTempFiles();
        }
        
        return true;
    }
}