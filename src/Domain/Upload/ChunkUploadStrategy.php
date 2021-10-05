<?php


namespace App\Domain\Upload;

use Psr\Http\Message\UploadedFileInterface;

class ChunkUploadStrategy extends UploadStrategy
{
    /**
     * The uploaded file (chunk)
     *
     * @var UploadedFileInterface
     */
    private UploadedFileInterface $file;

    /**
     * The chunk number
     *
     * @var int
     */
    private int $offset;

    /**
     * Save the uploaded data as temporary file
     *
     * @param UploadedFileInterface $file the uploaded file
     * @param int $offset the byte offset
     *
     * @return void
     */
    public function storeTmp(UploadedFileInterface $file, int $offset): void
    {
        $this->file = $file;
        $this->offset = $offset;

        $this->validateData();
        $this->saveChunk();
    }


    private function validateData(): void
    {
        if ($this->offset < 0) {
            throw new UploadException('Invalid upload file offset.', 400);
        }

        if ($this->offset > 0 && !file_exists($this->getTmpPath())) {
            throw new UploadException('Invalid upload file offset.', 400);
        }

        if ($this->offset > 0 && $this->offset !== filesize($this->getTmpPath())) {
            throw new UploadException('Invalid upload file offset.', 400);
        }

        if (! $this->file->getSize()) {
            throw new UploadException('Missing file upload data.', 400);
        }

        $fileMax = $this->allowedFileSize;
        $current = $this->getCurrentTmpFileSize();
        $chunk = $this->file->getSize();
        if ($current + $chunk > $fileMax) {
            throw new UploadException('Max upload file size exceeded.', 400);
        }
    }

    /**
     * Return the size of the already uploaded chunks of this file.
     *
     * Returns zero if there were no chunks uploaded.
     *
     * @return int
     */
    private function getCurrentTmpFileSize(): int
    {
        $path = $this->getTmpPath();

        if (!file_exists($path)) {
            return 0;
        }

        return filesize($path);
    }

    /**
     * Save the file chunk
     *
     * @throws UploadException
     */
    private function saveChunk(): void
    {
        $filePath = $this->getTmpPath();

        if ($this->offset === 0) {
            $flags = LOCK_EX;
        } else {
            $flags = FILE_APPEND | LOCK_EX;
        }

        $written = file_put_contents($filePath, $this->file->getStream(), $flags);

        if (false === $written) {
            throw new UploadException('Unable to store file.', 500);
        }

        chmod($filePath, 0600);
    }
}
