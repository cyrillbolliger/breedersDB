<?php


namespace App\Domain\Upload;


use Cake\Core\Configure;
use Cake\Utility\Security;
use DirectoryIterator;
use Mimey\MimeTypes;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

abstract class UploadStrategy
{
    /**
     * The filename of the uploaded file (differs from tmp and final filename)
     *
     * @var string
     */
    protected string $filename;

    /**
     * The allowed file extensions (mime types are derived and checked as well)
     *
     * @var string[]
     */
    protected array $allowedFileExt;

    /**
     * The allowed maximum file size in Byte
     *
     * @var int
     */
    protected int $allowedFileSize;

    /**
     * Path to the directory with the temporary upload files
     *
     * @var string
     */
    private string $tmpDir;

    /**
     * UploadStrategy constructor.
     *
     * @param array $allowedFileExt
     * @param string|null $filename filename of the uploaded file. Make sure it is unique, unguessable and deterministic.
     * @param int|float|null $allowedFileSizeMB in MB
     */
    public function __construct(
        array $allowedFileExt,
        string $filename = null,
        int|float|null $allowedFileSizeMB = null
    ) {
        if (null === $allowedFileSizeMB) {
            $allowedFileSizeMB = (float)Configure::read('App.maxUploadFileSize');
        }

        $this->allowedFileExt = $allowedFileExt;
        $this->filename = $filename;
        $this->allowedFileSize = (int)ceil($allowedFileSizeMB * 1024 * 1024);
        $this->tmpDir = Configure::read('App.paths.uploadTmp');

        self::createDir($this->tmpDir);
        $this->removeOldTmpFiles();
    }

    /**
     * Remove temporary files without change for more than uploads_ttl seconds
     */
    private function removeOldTmpFiles(): void
    {
        $maxAge = time() - (int)Configure::read('App.uploadTmpFileTTL');

        foreach (new DirectoryIterator($this->tmpDir) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->getMTime() < $maxAge) {
                unlink($file->getRealPath());
            }
        }
    }

    /**
     * Save the uploaded data as temporary file
     *
     * @param UploadedFileInterface $file the uploaded file
     * @param int $offset the byte offset
     *
     * @return void
     */
    abstract public function storeTmp(UploadedFileInterface $file, int $offset): void;

    /**
     * Save the uploaded temporary file in the given folder.
     *
     * @param string $destDir
     *
     * @return string  the file name
     */
    public function storeFinal(string $destDir): string
    {
        $this->validateFile();

        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        $finalFilename = $this->computeFinalFilename();
        $subDir = self::getSubdir($finalFilename);
        $destDir = rtrim($destDir, '/') . DS . $subDir . DS;

        self::createDir($destDir);

        $destPath = $destDir . $finalFilename . '.' . $extension;
        $tmpPath = $this->getTmpPath();

        // Since the final file name is based on the file hash
        // we do only have to move the file, if a file with the same
        // hash does not yet exist. This prevents duplication.
        // Remaining files will be deleted by the temporary file garbage
        // collector.
        if (!file_exists($destPath)) {
            if (!rename($tmpPath, $destPath)) {
                throw new UploadException('Failed to store final file.', 500);
            }
            chmod($destPath, 0600);
        }

        return $finalFilename . '.' . $extension;
    }

    /**
     * Abort if the file doesn't exist or if the mime type is not allowed
     */
    private function validateFile(): void
    {
        $tmpPath = $this->getTmpPath();

        if (!file_exists($tmpPath)) {
            throw new UploadException('Uploaded file not found.', 400);
        }

        $mimeType = mime_content_type($tmpPath);
        $converter = new MimeTypes;

        $mimeExt = $converter->getExtension($mimeType);
        if (!in_array($mimeExt, $this->allowedFileExt, true)) {
            throw new UploadException('The uploaded file has an invalid mime type.', 400);
        }

        if (filesize($tmpPath) > $this->allowedFileSize) {
            throw new UploadException('Uploaded file exceeds allowed file size.', 400);
        }
    }

    /**
     * Generate filename that depends on the file content but is hard to guess.
     *
     * Use the file hash as file name base, so we can prevent storing
     * duplicates.
     *
     * @return string
     */
    private function computeFinalFilename(): string
    {
        $path = $this->getTmpPath();

        $fileHash = hash_file(Security::$hashType, $path, true);
        $salted = hash(Security::$hashType, $fileHash . Security::getSalt());

        return substr($salted, 0, 32);
    }

    public function getTmpPath(): string
    {
        return $this->tmpDir . $this->filename;
    }

    public static function getSubdir(string $filename): string
    {
        return substr($filename, 0, 2);
    }

    public static function getTmpFileName(string $origFileName, string $sessionId): string
    {
        $extension = pathinfo($origFileName, PATHINFO_EXTENSION);
        $tmpFileBase = substr(
            Security::hash(
                $origFileName
                . $sessionId
                . Security::getSalt()
            ),
            0,
            32
        );

        return "$tmpFileBase.$extension";
    }

    public static function createDir(string $path, bool $recursive = true): void
    {
        if (is_dir($path)) {
            return;
        }

        if (!$recursive) {
            if (mkdir($path, 0700) && is_dir($path)) {
                return;
            }

            throw new RuntimeException(sprintf('Directory "%s" was not created.', $path));
        }

        $parts = array_filter(explode(DS, $path), static fn($val) => !empty($val));

        $subPath = '';
        foreach ($parts as $part) {
            $subPath .= DS . $part;
            self::createDir($subPath, false);
        }
    }
}
