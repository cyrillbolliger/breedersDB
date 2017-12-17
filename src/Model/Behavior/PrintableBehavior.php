<?php

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Core\Exception\Exception;

class PrintableBehavior extends Behavior {
	/**
	 * Generate zebra code (zpl) with code (optional), description (mandatory) and date (optional)
	 *
	 * @param string $description
	 * @param string|null $code
	 * @param string|null $date
	 *
	 * @return string
	 */
	public function getZPL( string $description, string $code = null, string $date = null ) {
		$paper_height = 244;
		
		$code_height        = 0;
		$code_byline_height = 0;
		$description_height = 0;
		$date_height        = 0;
		$content_count      = 0;
		$content_height     = 0;
		if ( $code ) {
			$code_height        = 100;
			$code_byline_height = 30;
			$content_height     = $code_height + $code_byline_height;
			$content_count ++;
		}
		if ( $description ) {
			$description_height = 30;
			$content_height     += $description_height;
			$content_count ++;
		}
		if ( $date ) {
			$date_height    = 30;
			$content_height += $date_height;
			$content_count ++;
		}
		
		$margin = ( $paper_height - $content_height ) / ( $content_count + 1 );
		
		$zpl = '';
		
		$next_top_pos = 0;
		if ( $code ) {
			$this_top_pos = $margin;
			$zpl          .= '^BY3,2,' . $code_height . '^FO240,' . $this_top_pos . '^BC^FD' . $code . '^FS';
			$next_top_pos = $this_top_pos + $code_height + $code_byline_height;
		}
		if ( $description ) {
			$this_top_pos = $next_top_pos + $margin;
			$zpl          .= '^CFA,' . $description_height . '^FO255,' . $this_top_pos . '^FD' . $description . '^FS';
			$next_top_pos = $this_top_pos + $description_height;
		}
		if ( $date ) {
			$this_top_pos = $next_top_pos + $margin;
			$zpl          .= '^CFA,' . $date_height . '^FO255,' . $this_top_pos . '^FD' . $date . '^FS';
		}
		
		return '${^XA' . $zpl . '^XZ}$';
	}
}