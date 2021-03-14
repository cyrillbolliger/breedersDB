<?php
declare(strict_types=1);

namespace App\Test\Util;

trait AjaxTrait {
    protected function setAjaxHeader(): void {
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest']
        ]);
    }
}
