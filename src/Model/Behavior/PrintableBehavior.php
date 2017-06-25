<?php

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Core\Exception\Exception;

class PrintableBehavior extends Behavior
{
    public function getZPL(string $description, string $code = null)
    {
        if ($code && $description) {
            return $this->_wrap('^XA^BY3,2,100^FO240,30^BC^FD' . $code . '^FS^CFA,30^FO255,190^FD' . $description . '^FS^XZ');
        }
        if ($description) {
            return $this->_wrap('^XA^CFA,30^FO255,115^FD' . $description . '^FS^XZ');
        }
        throw new Exception(__('No printable content given.'));
    }
    
    private function _wrap(string $zpl)
    {
        return '${' . $zpl . '}$';
    }
}