<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 18:30
 */

namespace Autoupdate;


class FileUpdateHandler
{
    private $downloadDest;
    private $extractionDest;
    private $rootPath;
    private $excludeList;
    
    /**
     * FileUpdateHandler constructor.
     *
     * @param string $downloadDest
     * @param string $extractionDest
     * @param array $excludeList
     */
    public function __construct(string $downloadDest, string $extractionDest, array $excludeList)
    {
        $this->rootPath = realpath(dirname(dirname(__FILE__)));
        
        $saveTo             = $this->rootPath . DIRECTORY_SEPARATOR . $downloadDest . DIRECTORY_SEPARATOR . 'latest.zip';
        $this->downloadDest = $saveTo;
        
        $extractTo            = $this->rootPath . DIRECTORY_SEPARATOR . $extractionDest;
        $this->extractionDest = $extractTo;
        
        $this->excludeList = $excludeList;
    }
    
    /**
     * Remove given value from exclude list
     *
     * @param $value
     */
    public function removeFromExcludeList($value) {
        if(($key = array_search($value, $this->excludeList)) !== false) {
            unset($this->excludeList[$key]);
        }
    }
    
    /**
     * Download zipball from bitbucket
     *
     * @param array $bitbucketconf @see Class::Updater
     * @param string $tag the tag used on bitbucket
     *
     * @return bool|string
     */
    public function getFromBitbucket($bitbucketconf, string $tag)
    {
        // The resource that we want to download.
        $fileUrl = 'https://bitbucket.org/' . $bitbucketconf['repo_owner'] . '/' . $bitbucketconf['repo'] . '/get/' . $tag . '.zip';
        
        // Open file handler.
        $fp = fopen($this->downloadDest, 'w+');
        
        // If $fp is FALSE, something went wrong.
        if ($fp === false) {
            throw new Exception('Could not open: ' . $this->downloadDest);
        }
        
        // Create a cURL handle.
        $ch = curl_init($fileUrl);
        
        // Pass our file handle to cURL.
        curl_setopt($ch, CURLOPT_FILE, $fp);
        
        // Timeout if the file doesn't download after 30 seconds.
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Set login credentials
        curl_setopt($ch, CURLOPT_USERPWD, $bitbucketconf['user'] . ':' . $bitbucketconf['pass']);
        
        // Execute the request.
        curl_exec($ch);
        
        // If there was an error, throw an Exception
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        // Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Close the cURL handler.
        curl_close($ch);
        
        // on success
        if ($statusCode == 200) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Unzip files or throw error
     *
     * @return bool
     * @throws \Exception
     */
    public function extractFiles()
    {
        $zip = new \ZipArchive;
        
        if ($zip->open($this->downloadDest) === true) {
            $zip->extractTo($this->extractionDest);
            $zip->close();
            
            return true;
        }
        
        throw new \Exception("Error unzipping {$this->downloadDest} to {$this->extractionDest}");
    }
    
    /**
     * Move the extracted files to their final destination
     *
     * @return bool
     * @throws \Exception
     */
    public function moveFiles(): bool
    {
        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->extractionDest),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        $dirname = $this->detectExtractionDirname();
        
        foreach ($files as $name => $file) {
            // Skip directories (they will be added automatically)
            if ($file->isDir()) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($this->extractionDest) + strlen($dirname) + 2);
            
            // skip files that are in the exclude list
            if ($this->isInExcludeList($relativePath)) {
                continue;
            }
            
            // destination path
            $copyTo     = $this->rootPath . DIRECTORY_SEPARATOR . $relativePath;
            $destFolder = substr($copyTo, 0, strrpos($copyTo, DIRECTORY_SEPARATOR));
            
            // If the destination directory does not exist create it
            if ( ! is_dir($destFolder)) {
                if ( ! mkdir($destFolder)) {
                    // If the destination directory could not be created stop processing
                    throw new \Exception('Error creating folder:' . $destFolder);
                }
            }
            
            // copy the file
            if ( ! copy($filePath, $copyTo)) {
                throw new \Exception('Error copying ' . $filePath . ' to ' . $copyTo);
            }
        }
        
        return true;
    }
    
    /**
     * Return the first folder name in the extraction folder
     *
     * @return string
     * @throws \Exception
     */
    private function detectExtractionDirname(): string
    {
        $content = scandir($this->extractionDest);
        
        foreach ($content as $el) {
            if (is_dir($this->extractionDest . DIRECTORY_SEPARATOR . $el) && ! in_array($el, ['.', '..'])) {
                return $el;
            }
        }
        
        throw new \Exception('Directory with files to move could not be found.');
    }
    
    /**
     * Return true if given relative path is in exclude list.
     *
     * @param string $relativePath
     *
     * @return bool
     */
    private function isInExcludeList(string $relativePath): bool
    {
        foreach ($this->excludeList as $path) {
            if (0 === strpos($relativePath, $path)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Delete all files that are not in the exclude list
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteFiles($rootPath): bool
    {
        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            
            // Skip directories we need to delete them later, when they are empty
            if ($file->isDir()) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($this->rootPath) + 1);
            
            // skip files that are in the exclude list
            if ($this->isInExcludeList($relativePath)) {
                continue;
            }
            
            // delete links
            if ($file->isLink()) {
                if ( ! unlink($file)) {
                    throw new \Exception('Error deleting ' . $filePath);
                }
                continue;
            }
            
            // delete file
            if ( ! unlink($filePath)) {
                throw new \Exception('Error deleting ' . $filePath);
            }
        }
        
        return $this->recursivelydeleteEmptyFolders($rootPath);
    }
    
    /**
     * Recursively delete all empty folders in given path
     *
     * @return bool true if folder was empty and deleted, false if folder was not emtpy
     * @throws \Exception on error
     */
    private function recursivelyDeleteEmptyFolders($path): bool
    {
        // skip files
        if ( ! is_dir($path)) {
            return false;
        }
        
        $empty = true;
        foreach (scandir($path) as $file) {
            // skip '.' and '..'
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath     = realpath($path . DIRECTORY_SEPARATOR . $file);
            $relativePath = substr($filePath, strlen($this->rootPath) + 1);
            
            // skip files that are in the exclude list
            if ($this->isInExcludeList($relativePath)) {
                $empty = false;
                continue;
            }
            
            // recursively delete empty folders
            $tmp = $this->recursivelyDeleteEmptyFolders($filePath);
            if ($empty) {
                $empty = $tmp;
            }
        }
        
        // if folder is empty
        if ($empty) {
            // delete it
            if ( ! rmdir($path)) {
                throw new \Exception('Error deleting ' . $path);
            }
            
            return true;
        }
        
        // folder was not empty
        return false;
    }
}