<?php

namespace App\Domain\PrintDriver;

use const PHP_INT_MIN;

class QRCode
{
    /**
     * Edge length per zoom level in pixels
     *
     * Measured with a code contents 51 - 64 chars at 203dpi
     */
    private const ZOOM = [
        1 => 37,
        2 => 75,
        3 => 111,
        4 => 147,
        5 => 183,
        6 => 221,
        7 => 258,
        8 => 295,
        9 => 332,
        10 => 370
    ];

    private const SIMPLE_CHARS_REGEX = '/^[0-9A-Z $%*+-.\/:)]*$/';

    /**
     * Edge length per number of chars (inclusive upper bound) encoded in the QR code
     *
     * Charset: digits 0 – 9, upper case letters A – Z, space, and  $%*+-./:)
     * No lower case chars.
     *
     * Measured with a zoom level of 7 at 203 dpi
     */
    private const WIDTHS_SIMPLE_CHARS = [
        0 => 0,
        9 => 147,
        19 => 175,
        34 => 202,
        49 => 230,
        63 => 258,
        83 => 287,
        92 => 314,
        121 => 342
    ];

    /**
     * Edge length per number of chars (inclusive upper bound) encoded in the QR code
     *
     * Charset: 8-bit Latin/Kana character set in accordance with JIS X 0201
     *
     * Measured with a zoom level of 7 at 203 dpi
     */
    private const WIDTHS_LATIN_CHARS = [
        0 => 0,
        7 => 147,
        17 => 175,
        31 => 202,
        46 => 230,
        60 => 258,
        81 => 287,
        90 => 314,
        119 => 342
    ];

    public function __construct(private string $data)
    {
    }

    public function getZPL(int $maxWidth): string
    {
        $zoom = $this->getZoom($maxWidth);

        return "^BQ,2,$zoom^FDH,$this->data";
    }

    public function getEffectiveWidth(int $maxWidth): int
    {
        $zoom = $this->getZoom($maxWidth);

        return $this->width($zoom);
    }

    private function getZoom(int $maxWidth): int
    {
        $zoomLevels = array_keys(self::ZOOM);
        rsort($zoomLevels, SORT_NUMERIC);

        foreach ($zoomLevels as $level) {
            if ($maxWidth >= $this->width($level)) {
                return $level;
            }
        }

        throw new PrintDriverException(
            "Impossible to zoom QR code low enough to reach a max width of $maxWidth. " .
            "Smallest possible width is " . $this->width(end($zoomLevels))
        );
    }

    private function width(int $zoom): int
    {
        $zoomFactor = self::getZoomFactor($zoom);
        $defaultWidth = $this->defaultWidth();

        return round($defaultWidth * $zoomFactor);
    }

    private static function getZoomFactor(int $zoom): float
    {
        if (!array_key_exists($zoom, self::ZOOM)) {
            throw new PrintDriverException(
                "QR zoom out of bounds. Supported zoom values: integers including 1 up to 10. Given: $zoom"
            );
        }

        return self::ZOOM[$zoom] / self::ZOOM[7];
    }

    private function defaultWidth(): int
    {
        $dataLen = strlen($this->data);
        $widths = $this->getWidths();
        $bounds = array_keys($widths);

        $lower = PHP_INT_MIN;
        foreach ($bounds as $upper) {
            if ($dataLen > $lower && $dataLen <= $upper) {
                return $widths[$upper];
            }
            $lower = $upper;
        }

        throw new PrintDriverException("Max content size of QR code exceeded. Only up to 120 chars supported.");
    }

    private function getWidths(): array
    {
        if (preg_match(self::SIMPLE_CHARS_REGEX, $this->data)) {
            return self::WIDTHS_SIMPLE_CHARS;
        }

        return self::WIDTHS_LATIN_CHARS;
    }
}
