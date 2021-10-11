<?php


namespace App\Domain\PrintDriver;


interface PrintDriverInterface {
    public function setCode( string $code );
    public function addLine( Line $line );
    public function getPrintData();
}
