<?php

namespace App\Domain\PrintDriver;

/**
 * Class ZPLDriver
 * @package App\Domain\PrintDriver
 *
 * @link http://labelary.com/docs.html
 */
class ZPLDriver implements PrintDriverInterface
{

    /**
     * Margin proportional to the paper sqrt of the paper size
     */
    private const MARGIN_FACTOR = 0.1;

    /**
     * Line height relative to the height of a default line
     */
    private const LARGE_LINE_FACTOR = 1.3;

    /**
     * Code height relative to the height of a default line
     */
    private const CODE_HEIGHT_FACTOR = 2.5;

    /**
     * Height of the code byline relative to the height of a default line
     */
    private const CODE_BYLINE_FACTOR = 1;

    /**
     * Size of the QR code relative to the paper width
     */
    private const QR_CODE_SIZE = 0.35;

    /**
     * Margin between two objects like lines or a line and code
     */
    private const OBJECT_MARGIN = 0.3;

    /**
     * Factor to determine the max font size in relation to the paper width
     */
    private const FONT_TO_X_FACTOR = 11;

    /**
     * Dots per square millimeter
     */
    private const PRINT_DENSITY = 8;

    private const ZPL_FIELD_SEPARATOR = "^FS";
    private const ZPL_WRAPPER_START = '${^XA';
    private const ZPL_WRAPPER_END = '^XZ}$';
    private const ZPL_LABEL_HOME = '^LH0,0';

    /**
     * Paper height in dots
     *
     * @var int
     */
    private int $paper_y;

    /**
     * Paper width in dots
     *
     * @var int
     */
    private int $paper_x;

    private bool $code128;
    private bool $qr;

    private string $code = '';
    private array $lines = [];

    private float $default_font_size;
    private int $top = 0;
    private int $left = 0;

    private array $zpl_fields;

    /**
     * ZPLDriver constructor.
     *
     * @param int $paper_width in millimeters
     * @param int $paper_height in millimeters
     * @param bool $qr
     * @param bool $code128
     */
    public function __construct(int $paper_width, int $paper_height, bool $qr = true, bool $code128 = false)
    {
        $this->paper_x = (int)$paper_width * self::PRINT_DENSITY;
        $this->paper_y = (int)$paper_height * self::PRINT_DENSITY;
        $this->code128 = $code128;
        $this->qr = $qr;
    }

    public function setCode(string $code, bool $addByline = true ): void
    {
        $this->code = $code;

        if ($addByline) {
            array_unshift($this->lines, new Line($code));
        }
    }

    public function addLine(Line $line): void
    {
        $this->lines[] = $line;
    }

    public function getPrintData(): string
    {
        $this->determineDefaultFontSize();

        $margin = $this->getBorderMargin();
        $this->top = $margin;
        $this->left = $margin;

        if (!empty($this->code)) {
            $this->placeCode();
        }

        if (!empty($this->lines)) {
            $this->placeLines();
        }

        return self::ZPL_WRAPPER_START
            . self::ZPL_LABEL_HOME
            . implode(self::ZPL_FIELD_SEPARATOR, $this->zpl_fields)
            . self::ZPL_WRAPPER_END;
    }

    private function determineDefaultFontSize(): void
    {
        $size_by_height = round($this->getPrintableHeight() / $this->getNormalizedTotalHeight());
        $size_by_width = round($this->paper_x / self::FONT_TO_X_FACTOR);

        $this->default_font_size = min($size_by_height, $size_by_width);
    }

    private function placeCode()
    {
        $top = $this->top;
        $left = $this->left;

        $code128_height = (int)round($this->default_font_size * self::CODE_HEIGHT_FACTOR);

        if ($this->code128) {
            $this->zpl_fields[] = "^BY2^FO$left,$top^BCN,$code128_height,N^FD$this->code";

            $this->top += $code128_height + (int)round($this->default_font_size * self::OBJECT_MARGIN);
        }

        if ($this->qr) {
            $maxWidth = round($this->paper_x * self::QR_CODE_SIZE);

            $code = new QRCode($this->code);
            $qrCodeZpl = $code->getZPL($maxWidth);
            $qrWidth = $code->getEffectiveWidth($maxWidth);

            $this->zpl_fields[] = "^BY0,0,0^FO$left,{$this->top}$qrCodeZpl";

            $this->left += $qrWidth + (int)round($this->default_font_size * self::OBJECT_MARGIN);
        }
    }

    private function placeLines()
    {
        $left = $this->left;
        $object_margin = (int)round($this->default_font_size * self::OBJECT_MARGIN);

        foreach ($this->lines as $line) {
            $factor = $line->is_large() ? self::LARGE_LINE_FACTOR : 1;
            $size = (int)round($this->default_font_size * $factor, 0);

            $this->zpl_fields[] = "^A0,$size^FO$left,$this->top^FD{$line->get_text()}";
            $this->top += $size + $object_margin;
        }
    }

    private function getPrintableHeight(): int
    {
        return $this->paper_y - (2 * $this->getBorderMargin());
    }

    private function getNormalizedTotalHeight()
    {
        $small_lines = $this->getLinesCount(false);
        $large_lines = $this->getLinesCount(true);
        $code = empty($this->code) ? 0 : 1;

        $text = $small_lines
            + $small_lines * self::OBJECT_MARGIN
            + $large_lines * self::LARGE_LINE_FACTOR
            + $large_lines * self::OBJECT_MARGIN;

        $code128 = 0;
        if ($this->code128) {
            $code128 = $code * self::CODE_HEIGHT_FACTOR
                + $code * self::CODE_BYLINE_FACTOR;
        }

        return $code128 + $text;
    }

    private function getBorderMargin(): int
    {
        return (int)round(sqrt($this->paper_x * $this->paper_y) * self::MARGIN_FACTOR);
    }

    private function getLinesCount($large): int
    {
        return count(
            array_filter(
                $this->lines,
                static fn($line) => $line->is_large() === $large
            )
        );
    }
}
