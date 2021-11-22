<?php

namespace App\Model\Behavior;

use App\Domain\PrintDriver\Line;
use App\Domain\PrintDriver\ZPLDriver;
use Cake\ORM\Behavior;

class PrintableBehavior extends Behavior {
    /**
     * Generate zebra code (zpl) with code (optional), description (mandatory) and date (optional)
     *
     * @param string[] $description
     * @param string|null $code
     * @param string|null $date
     *
     * @return string
     */
    public function getZPL(
        array $description,
        string $code = null,
        string $date = null,
        bool $codeByline = true,
    ) {
        $driver = new ZPLDriver(50, 31);

        if ($code) {
            $driver->setCode($code, $codeByline);
        }

        foreach($description as $line) {
            $driver->addLine(new Line($line, true));
        }

        if ($date) {
            $driver->addLine(new Line($date));
        }

        return $driver->getPrintData();
    }
}
