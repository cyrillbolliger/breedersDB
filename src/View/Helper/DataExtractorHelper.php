<?php

namespace App\View\Helper;

use Cake\View\Helper;

class DataExtractorHelper extends Helper {
	/**
	 * Return content of given cell ($key is the dot notated path) from $data.
	 * $data will be recursively scanned and every matching $key will me returned.
	 * Multiple matches will be returned as unordered list.
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return string
	 */
	public function getCell( $key, $data ) {
		$extractor = new \App\Utility\DataExtractorUtility();
		$cell      = $extractor->getCell( $key, $data );
		
		if ( ! is_array( $cell ) ) {
			return h( $cell );
		}
		
		return $this->_makeList( $cell );
	}
	
	/**
	 * Return unordered HTML-list from given array
	 *
	 * @param $array
	 *
	 * @return string
	 */
	private function _makeList( $array ) {
		$html = '<ul>';
		foreach ( $array as $item ) {
			$html .= '<li>' . h( $item ) . '</li>';
		}
		$html .= '</ul>';
		
		return $html;
	}
}