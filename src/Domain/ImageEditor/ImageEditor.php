<?php

namespace App\Domain\ImageEditor;

use Cake\Log\Log;
use Imagick;
use ImagickException;

class ImageEditor
{
    public function __construct(
        private string $pathOriginalImage
    ) {
    }

    public function getThumbnail(int $maxWidth = 0, int $maxHeight = 0): string
    {
        $dims = getimagesize($this->pathOriginalImage);

        if (false === $dims) {
            throw new ImageEditorException('Image not found or invalid.');
        }

        [$imgWidth, $imgHeight] = $dims;

        // original image size wanted
        if (0 === $maxWidth && 0 === $maxHeight) {
            return $this->pathOriginalImage;
        }

        if ($maxWidth === 0) {
            $ratio = $maxHeight / $imgHeight;
        } elseif ($maxHeight === 0) {
            $ratio = $maxWidth / $imgWidth;
        } else {
            $ratio = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
        }

        // wanted size is larger than original image
        if ($ratio > 1) {
            return $this->pathOriginalImage;
        }

        $thumbWidth = (int)round($imgWidth * $ratio);
        $thumbHeight = (int)round($imgHeight * $ratio);

        $ext = pathinfo($this->pathOriginalImage, PATHINFO_EXTENSION);

        $thumbPath = preg_replace(
            "/\.$ext$/",
            "-${thumbWidth}x$thumbHeight.$ext",
            $this->pathOriginalImage
        );

        if (!file_exists($thumbPath)) {
            $this->generateThumbnail($thumbPath, $thumbWidth, $thumbHeight);
        }

        return $thumbPath;
    }

    private function generateThumbnail(string $thumbPath, int $width, int $height): void
    {
        try {
            $imagick = new Imagick($this->pathOriginalImage);
            $imagick->setbackgroundcolor('transparent');
            $imagick->thumbnailImage($width, $height, true, true);
            $imagick->writeImage($thumbPath);
            $imagick->destroy();
        } catch (ImagickException $e) {
            Log::error($e->__toString());
            throw new ImageEditorException('Failed to generate thumbnail. See log for details.');
        }
    }

    public static function isImage(string $path): bool
    {
        return false !== getimagesize($path);
    }

    public function normalizeRotation(): void
    {
        try {
            $im = new Imagick($this->pathOriginalImage);

            $success = match ($im->getImageOrientation()) {
                Imagick::ORIENTATION_TOPLEFT => true,
                Imagick::ORIENTATION_BOTTOMRIGHT => $im->rotateimage("transparent", 180),
                Imagick::ORIENTATION_RIGHTTOP => $im->rotateImage("transparent", 90),
                Imagick::ORIENTATION_LEFTBOTTOM => $im->rotateImage("transparent", -90),
                Imagick::ORIENTATION_TOPRIGHT => $im->flopImage(),
                Imagick::ORIENTATION_RIGHTBOTTOM => $im->transverseImage(),
                Imagick::ORIENTATION_LEFTTOP => $im->transposeImage(),
                Imagick::ORIENTATION_BOTTOMLEFT => $im->flipImage(),
                default => null
            };

            if ($success) {
                $im->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
                $im->writeImage($this->pathOriginalImage);
            } elseif (false === $success) {
                throw new ImageEditorException('Failed to normalize image rotation.');
            }

            $im->destroy();
            
        } catch (ImagickException $e) {
            Log::error($e->__toString());
            throw new ImageEditorException('Failed to normalize image rotation. See log for details.');
        }
    }
}
