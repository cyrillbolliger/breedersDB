<?php

namespace App\View\Helper;

use App\Utility\DataExtractorUtility;
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
        $extractor = new DataExtractorUtility();
        $cell = $extractor->getCell( $key, $data );
		
		if ( ! is_array( $cell ) ) {
			return h( $cell );
		}
		
		return $this->_makeList( $cell );
	}
    
    /**
     * Return content of given cell ($key is the dot notated path) from $data.
     * $data will be recursively scanned and every matching $key will me returned.
     * Multiple matches will be returned as unordered list, with limited char
     * length and a hover box.
     *
     * @param $key
     * @param $data
     *
     * @return string
     */
	public function getMarkValueCell($key, $data) {
        $extractor = new DataExtractorUtility();
        $cell = $extractor->getMarkValueCell( $key, $data );
        
        $html = '<ul>';
        foreach ( $cell as $id => $value ) {
            $html .= '<li><span class="mark_value mark_value-'.$id.'">' . h( $value ) . '</span></li>';
        }
        $html .= '</ul>';
        
        return $html;
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
