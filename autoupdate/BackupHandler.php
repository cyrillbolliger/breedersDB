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
        // Get real path for project src
        $rootPath   = realpath(dirname(dirname(__FILE__)));
        $backupPath = realpath($rootPath . DIRECTORY_SEPARATOR . $relBackupDestination) . DIRECTORY_SEPARATOR . 'filebackup.zip';
        
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($backupPath,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if ($file->isDir()) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            
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
    
    public function backupDatabase($relBackupDestination, $dbconf)
    {
        $rootPath   = realpath(dirname(dirname(__FILE__)));
        $backupPath = realpath($rootPath . DIRECTORY_SEPARATOR . $relBackupDestination) . DIRECTORY_SEPARATOR . 'dbdump.sql.gz';
        
        $dbhost     = $dbconf['host'];
        $dbuser     = $dbconf['username'];
        $dbpassword = $dbconf['password'];
        $dbname     = $dbconf['database'];
        
        $output = [];
        exec(
            "mysqldump --user=$dbuser --password=$dbpassword --host=$dbhost $dbname | gzip -c  > $backupPath",
            $output
        );
        
        // no output is good
        if (empty($output)){
            return true;
        }
        
        return $output;
    }
}