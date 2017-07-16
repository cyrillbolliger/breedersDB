<?php

namespace App\View\Helper;

use Cake\View\Helper;

class LocalizedTimeHelper extends Helper
{
    public $helpers = [
        'Time'
    ];
    
    /**
     * Return time, date or datetime, adapted to the users timezone
     *
     * @param int|string|\DateTime $time UNIX timestamp, strtotime() valid string or DateTime object (or a date format string) to adapt
     * @param int|string|null $format date format string (or a UNIX timestamp, strtotime() valid string or DateTime object)
     *
     * @return mixed
     */
    public function getUserTime($time, $format = null)
    {
        $time_zone = $this->_View->request->session()->read('time_zone');
        
        return $this->Time->format(
            $time,
            $format,
            null,
            $time_zone
        );
    }
}