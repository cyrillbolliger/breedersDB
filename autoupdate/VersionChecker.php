<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 14:32
 */

namespace Autoupdate;


class VersionChecker
{
    private $pathToVersionsFile;
    private $bitbucket;
    private $remote_version;
    private $local_version;
    
    /**
     * VersionChecker constructor.
     *
     * @param $pathToVersionsFile
     * @param $bitbucket
     */
    public function __construct($pathToVersionsFile, $bitbucket)
    {
        $this->pathToVersionsFile = $pathToVersionsFile;
        $this->bitbucket          = $bitbucket;
    }
    
    /**
     * Check if a new version is available. Return false on network error.
     *
     * @return bool
     */
    public function isUpdateAvailable()
    {
        $this->detectLocalVersion();
        
        try {
            $this->detectRemoteVersion();
        } catch (\Exception $e) {
            return false;
        }
        
        return 1 === version_compare($this->remote_version, $this->local_version);
    }
    
    /**
     * Read the newest version from bitbucket
     */
    private function detectRemoteVersion()
    {
        $url = 'https://api.bitbucket.org/2.0/repositories/'.$this->bitbucket['repo_owner'].'/'.$this->bitbucket['repo'].'/src/latest/version.txt';
        
        // Create a cURL handle.
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERPWD, $this->bitbucket['user'].':'.$this->bitbucket['pass']);
        $resp = curl_exec($ch);
    
        // If there was an error, throw an Exception
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
    
        // Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        // Close the cURL handler.
        curl_close($ch);
        
        if ($statusCode == 200) {
            $version = $this->parseVersion($resp);
            $this->remote_version = $version;
            return $version;
        }
        
        return false;
    }
    
    /**
     * Extract version number out of given string
     *
     * @param string $txt
     *
     * @return string
     */
    public function parseVersion(string $txt): string
    {
        preg_match("/^\d+\.\d+(\.\d+)*/", $txt, $matches);
        
        return $matches[0];
    }
    
    /**
     * Read version number from the versions file
     *
     * @throws \Exception if versions file could not be read
     * @return string current local version
     */
    public function detectLocalVersion(): string
    {
        if ( !file_exists($this->pathToVersionsFile) ) {
            throw new \Exception("Versions file could not be found under: {$this->pathToVersionsFile}");
        }
        
        $file = fopen($this->pathToVersionsFile, "r");
        $txt = fread($file, filesize($this->pathToVersionsFile));
        fclose($file);
        
        $this->local_version = $this->parseVersion($txt);
    
        return $this->local_version;
    }
}
