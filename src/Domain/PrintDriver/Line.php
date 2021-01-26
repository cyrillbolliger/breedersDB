<?php


namespace App\Domain\PrintDriver;


class Line {
    private string $text;
    private string $large;

    /**
     * Line constructor.
     *
     * @param string $text
     * @param bool $large
     */
    public function __construct( string $text, bool $large = false ) {
        $this->text = $text;
        $this->large = $large;
    }

    /**
     * @return string
     */
    public function get_text(): string {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function is_large() {
        return $this->large;
    }


}
