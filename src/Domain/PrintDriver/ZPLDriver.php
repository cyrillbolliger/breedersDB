<?php

namespace App\Domain\PrintDriver;

/**
 * Class ZPLDriver
 * @package App\Domain\PrintDriver
 *
 * @link http://labelary.com/docs.html
 */
class ZPLDriver implements PrintDriverInterface {

    /**
     * Margin proportional to the paper sqrt of the paper size
     */
    const MARGIN_FACTOR = 0.075;

    /**
     * Line height relative to the height of a default line
     */
    const LARGE_LINE_FACTOR = 1.3;

    /**
     * Code height relative to the height of a default line
     */
    const CODE_HEIGHT_FACTOR = 2.5;

    /**
     * Height of the code byline relative to the height of a default line
     */
    const CODE_BYLINE_FACTOR = 1;

    /**
     * Size of the QR code relative to the height of a default line
     */
    const QR_CODE_FACTOR = 0.13;

    /**
     * Factor to map the QR code size to dots
     */
    const QR_CODE_REAL_HEIGHT_FACTOR = 3.4;

    /**
     * Margin between two objects like lines or a line and code
     */
    const OBJECT_MARGIN = 0.3;

    /**
     * Factor to determine the max font size in relation to the paper width
     */
    const FONT_TO_X_FACTOR = 20;

    /**
     * Dots per square millimeter
     */
    const PRINT_DENSITY = 8;

    const ZPL_FIELD_SEPARATOR = "^FS";
    const ZPL_WRAPPER_START = '${^XA';
    const ZPL_WRAPPER_END = '^XZ}$';

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
    private float $top = 0;
    private float $left = 0;

    private array $zpl_fields;

    /**
     * ZPLDriver constructor.
     *
     * @param int $paper_width in millimeters
     * @param int $paper_height in millimeters
     * @param bool $qr
     * @param bool $code128
     */
    public function __construct( int $paper_width, int $paper_height, bool $qr = true, bool $code128 = false ) {
        $this->paper_x = (int) $paper_width * self::PRINT_DENSITY;
        $this->paper_y = (int) $paper_height * self::PRINT_DENSITY;
        $this->code128 = $code128;
        $this->qr      = $qr;
    }

    public function setCode( string $code ) {
        $this->code = $code;
        array_unshift( $this->lines, new Line( $code ) );
    }

    public function addLine( Line $line ) {
        $this->lines[] = $line;
    }

    public function getPrintData() {
        $this->determineDefaultFontSize();

        $margin     = $this->getBorderMargin();
        $this->top  = $margin;
        $this->left = $margin;

        if ( ! empty( $this->code ) ) {
            $this->placeCode();
        }

        if ( ! empty( $this->lines ) ) {
            $this->placeLines();
        }

        return self::ZPL_WRAPPER_START
               . implode( self::ZPL_FIELD_SEPARATOR, $this->zpl_fields )
               . self::ZPL_WRAPPER_END;
    }

    private function determineDefaultFontSize() {
        $size_by_height = round( $this->getPrintableHeight() / $this->getNormalizedTotalHeight() );
        $size_by_width = round( $this->paper_x / self::FONT_TO_X_FACTOR );

        $this->default_font_size = min($size_by_height, $size_by_width);
    }

    private function placeCode() {
        $top  = $this->top;
        $left = $this->left;

        $qr_size   = round( $this->default_font_size * self::QR_CODE_FACTOR );
        $qr_height = round( $this->default_font_size * self::QR_CODE_REAL_HEIGHT_FACTOR );

        $code128_height = round( $this->default_font_size * self::CODE_HEIGHT_FACTOR );

        if ( $this->code128 ) {
            $this->zpl_fields[] = "^BY2^FO$left,$top^BCN,$code128_height,N^FD$this->code";

            $this->top += $code128_height + round( $this->default_font_size * self::OBJECT_MARGIN );
        }

        if ( $this->qr ) {
            $this->zpl_fields[] = "^FO$left,{$this->top}^BQ,2,$qr_size^FDH,$this->code";

            $this->left += $qr_height + round( $this->default_font_size * self::OBJECT_MARGIN );
        }
    }

    private function placeLines() {
        $left          = $this->left;
        $object_margin = round( $this->default_font_size * self::OBJECT_MARGIN );

        foreach ( $this->lines as $line ) {
            $factor = $line->is_large() ? self::LARGE_LINE_FACTOR : 1;
            $size   = round( $this->default_font_size * $factor, 0 );

            $this->zpl_fields[] = "^A0,$size^FO$left,$this->top^FD{$line->get_text()}";
            $this->top          += $size + $object_margin;
        }
    }

    private function getPrintableHeight() {
        return $this->paper_y - ( 2 * $this->getBorderMargin() );
    }

    private function getNormalizedTotalHeight() {
        $small_lines = $this->getLinesCount( false );
        $large_lines = $this->getLinesCount( true );
        $code        = empty( $this->code ) ? 0 : 1;

        $text = $small_lines
                + $small_lines * self::OBJECT_MARGIN
                + $large_lines * self::LARGE_LINE_FACTOR
                + $large_lines * self::OBJECT_MARGIN;

        $code128 = 0;
        if ( $this->code128 ) {
            $code128 = $code * self::CODE_HEIGHT_FACTOR
                       + $code * self::CODE_BYLINE_FACTOR;
        }

        $qr = 0;
        if ( $this->qr ) {
            $qr = $code * self::QR_CODE_REAL_HEIGHT_FACTOR
                  + $code * self::OBJECT_MARGIN;
        }

        return $code128 + max( $text, $qr );
    }

    private function getBorderMargin() {
        return round( sqrt( $this->paper_x * $this->paper_y ) * self::MARGIN_FACTOR );
    }

    private function getLinesCount( $large ): int {
        $l = 0;
        $s = 0;

        foreach ( $this->lines as $line ) {
            if ( $large === $line->is_large() ) {
                $l ++;
            } else {
                $s ++;
            }
        }

        return $large ? $l : $s;
    }
}
