<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 15:58
 */

namespace Autoupdate;


class BackupHandler
{
    /**
     * @var string holding the path to the mysql binaries if not installed globally
     */
    private $pathToMysqlBinaries;
    
    /**
     * BackupHandler constructor.
     *
     * @param string $pathToMysqlBinaries
     */
    public function __construct($pathToMysqlBinaries)
    {
        $this->pathToMysqlBinaries = $pathToMysqlBinaries;
    }
    
    /**
     * Save a zip of the current project to the given path, excluding the files and folders of the given exclude list
     *
     * @param string $relBackupDestination relative destination path (folder only) to store the backup
     * @param array $excludeList with files and folders no to backup.
     * - 'path/to/file.txt' will exclude the file.txt in path/to
     * - 'path/to/folder' will exclude the folder in path/to and all its files
     *
     * @return bool
     */
    public function backupFiles(string $relBackupDestination, array $excludeList): bool
    {
        $backupPath = $this->getFileBackupPath($relBackupDestination);
        
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($backupPath,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->getRootPath()),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if ($file->isDir()) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($this->getRootPath()) + 1);
            
            // Skip the files of the exclude list
            $match = false;
            foreach ($excludeList as $path) {
                if (0 === strpos($relativePath, $path)) {
                    $match = true;
                }
            }
            if ($match) {
                continue;
            }
            
            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
            
        }
        
        // Zip archive will be created only after closing object
        return $zip->close();
    }
    
    /**
     * Return full path of file backup from given relative path
     *
     * @param string $relBackupDestination
     *
     * @return string
     */
    public function getFileBackupPath(string $relBackupDestination): string
    {
        return $this->getAbsPathFrom($relBackupDestination) . 'filebackup.zip';
    }
    
    /**
     * Get absolute path from given path which is relative to the project base
     *
     * @param string $relPath
     *
     * @return string
     */
    private function getAbsPathFrom(string $relPath): string
    {
        return realpath($this->getRootPath() . DIRECTORY_SEPARATOR . $relPath) . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Return absolute path to project root
     *
     * @return string
     */
    private function getRootPath(): string
    {
        return realpath(dirname(dirname(__FILE__)));
    }
    
    /**
     * Save a mysql dump to given destination using the commandline instruction 'mysqldump'
     *
     * @param string $relBackupDestination
     * @param array $dbconf
     *
     * @return array|bool
     */
    public function backupDatabase(string $relBackupDestination, array $dbconf)
    {
        $backupPath = $this->getDatabaseBackupPath($relBackupDestination);
        $mysqldump  = $this->getMysqlCommand('mysqldump');
        $command    = "$mysqldump --user={$dbconf['username']} --password={$dbconf['password']} --host={$dbconf['host']} {$dbconf['database']} > $backupPath";
        
        return $this->exec($command);
    }
    
    /**
     * Return full path of database backup from given relative path
     *
     * @param string $relBackupDestination
     *
     * @return string
     */
    public function getDatabaseBackupPath(string $relBackupDestination): string
    {
        return $this->getAbsPathFrom($relBackupDestination) . 'dbdump.sql';
    }
    
    /**
     * Returns path and mysql command from given command
     *
     * @param string $command mysql command you'd like to use
     *
     * @return string
     */
    private function getMysqlCommand(string $command): string
    {
        return empty($this->pathToMysqlBinaries) ? $command : preg_replace("/\\" . DIRECTORY_SEPARATOR . "$/", '',
                $this->pathToMysqlBinaries) . DIRECTORY_SEPARATOR . $command;
    }
    
    /**
     * Execute a commandline instruction and return true or array with the output
     *
     * @param $command
     *
     * @return array|bool
     */
    private function exec($command)
    {
        $output = [];
        
        exec($command, $output);
        
        // no output is good
        if (empty($output)) {
            return true;
        }
        
        return $output;
    }
    
    /**
     * Restore mysql backup
     *
     * @param string $relBackupDestination
     * @param array $dbconf
     *
     * @return array|bool
     */
    public function restoreDatabase(string $relBackupDestination, array $dbconf)
    {
        $backupPath = $this->getDatabaseBackupPath($relBackupDestination);
        $mysql      = $this->getMysqlCommand('mysql');
        $command    = "$mysql -h {$dbconf['host']} -u {$dbconf['username']} -p{$dbconf['password']} {$dbconf['database']} < $backupPath";
        
        return $this->exec($command);
    }
}