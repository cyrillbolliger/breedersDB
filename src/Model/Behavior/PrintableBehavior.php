<?php
namespace App\Model\Behavior;

use Cake\ORM\Behavior;

class PrintableBehavior extends Behavior
{
    public function getZPL(string $code, string $description){
        return "^XA^BY3,2,100^FO240,30^BC^FD".$code."^FS^CFA,30^FO255,190^FD".$description."^FS^XZ";
    }
}