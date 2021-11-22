<?php


namespace App\Domain\PrintDriver;


interface PrintDriverInterface {
    public function setCode( string $code, bool $addByline = true );
    public function addLine( Line $line );
    public function getPrintData();
}
