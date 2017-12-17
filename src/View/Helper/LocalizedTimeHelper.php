<?php

namespace App\View\Helper;

use Cake\View\Helper;

class LocalizedTimeHelper extends Helper {
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
	public function getUserTime( $time, $format = null ) {
		$time_zone = $this->_View->request->session()->read( 'time_zone' );
		
		return $this->Time->format(
			$time,
			$format,
			null,
			$time_zone
		);
	}
	
	/**
	 * Return a regex that will match all valid dates of the following formats
	 * - dd.mm.yyyy
	 * - dd-mm-yyyy
	 * - dd/mm/yyyy
	 *
	 * @see https://stackoverflow.com/a/15504877
	 *
	 * @return string
	 */
	public function getDateValidationRegex() {
		return '^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)\d{2})$';
	}
}