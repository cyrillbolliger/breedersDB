<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 10:22
 */

namespace App\Controller\Component;


use Cake\Collection\CollectionInterface;
use Cake\Controller\Component;
use Cake\ORM\Query;
use Cake\Utility\Text;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class ExcelComponent extends Component {
	/**
	 * Path to the folder with the export files
	 *
	 * @var string
	 */
	private $exportPath = TMP . 'export' . DS;

	/**
	 * Generate excel file from data of given query and return path to file.
	 *
	 * @param Query $query
	 * @param array $columns used to extract data and for column titles.
	 *                       Use the dot notation as key and the column title as value.
	 * @param string $title
	 *
	 * @return string with the path to the file
	 *
	 * @throws \PHPExcel_Exception
	 * @throws \PHPExcel_Writer_Exception
	 */
	public function exportFromQuery( Query $query, array $columns, string $title ): string {
		// load data extractor
		$extractor = new \App\Utility\DataExtractorUtility();

		// get data to export
		$in = $query->toArray();

        // prepare data
		$out = array();

        foreach ( $in as $row_in ) {
			$row = array();
			foreach ( $columns as $column_key => $column_name ) {
				$row[] = $this->_implode( $extractor->getCell( $column_key, $row_in ) );
			}
			$out[] = $row;
		}

        return $this->_export( $out, $columns, $title );
	}

    /**
	 * Make sure to return a string from given data.
	 * If data was an array, join the values with the glue.
	 *
	 * @param array|string $data
	 * @param string $glue
	 *
	 * @return string
	 */
	protected function _implode( $data, string $glue = '; ' ): string {
		if ( ! is_array( $data ) ) {
			return (string) $data;
		}

        return implode( $glue, $data );
	}

    /**
	 * Generate excel file from given data using columns as headers and filename
	 * as filename prefix. Return path to file.
	 *
	 * @param array $data two dimensional array with the data to export.
	 *      the first dimension holds the rows, the second the cells.
	 * @param array $columns the files headers. the order must match
	 *  the cells order of $data.
	 * @param string $title
	 *
	 * @return string path to file
	 *
	 * @throws \PHPExcel_Exception
	 * @throws \PHPExcel_Writer_Exception
	 */
	private function _export( array $data, array $columns, string $title ): string {
		// create excel obj
		$excel = new PHPExcel();
		$excel->getProperties()
		      ->setCreator( __( 'Breeders Database' ) )
		      ->setLastModifiedBy( __( 'Breeders Database' ) )
		      ->setTitle( $title );

        // select sheet
		$excel->setActiveSheetIndex( 0 );

        // write header
		$excel->getActiveSheet()->fromArray( $columns, null, 'A1' );

        // write data
		$i = 2;
		foreach ( $data as $row ) {
			$excel->getActiveSheet()->fromArray( $row, null, 'A' . $i );
			$i ++;
		}

        // save file
		$file        = $this->_prepareExportFile( $title );
		$excelWriter = new PHPExcel_Writer_Excel2007( $excel );
		$excelWriter->save( $file );

        // delete old files to prevent a huge tmp
		$this->_cleanUpCache( '1 Day' );

        return $file;
	}

    /**
	 * Return path to export file in the tmp/export folder.
	 * Make sure the folder exists.
	 *
	 * @return string
	 */
	protected function _prepareExportFile( string $name ): string {
		$slug     = strtolower( Text::slug( $name ) );
		$date     = date( 'Ymd' );
		$random   = substr( sha1( uniqid( 'asdfzui', true ) ), 0, 15 );
		$tmp_file = $slug . '_' . $date . '_' . $random . '.xlsx';

        if ( ! is_dir( $this->exportPath ) ) {
			mkdir( $this->exportPath, 0700, true );
		}

        return $this->exportPath . $tmp_file;
	}

    /**
	 * Delete files in the export directory if they are older than the given date
	 *
	 * @param string $maxAge as used by `strtotime`
	 */
	protected function _cleanUpCache( string $maxAge ) {
		$max = - strtotime( $maxAge ) + 2 * time();

        // loop through all files in the directory
		foreach ( glob( $this->exportPath . '*' ) as $file ) {

            // if file is older than $maxAge delete it
			if ( filectime( $file ) < $max ) {
				unlink( $file );
			}
		}

    }

    /**
	 * Return path to excel file generated from given mark collection.
	 *
	 * This method requires a collection returned by MarksView::customFindMarks().
	 *
	 * @param CollectionInterface $data @see MarksView::customFindMarks()
	 * @param array $regular_columns @see QueriesTable::getRegularColumns()
	 * @param array $mark_columns @see QueriesTable::getMarkColumns()
	 * @param string $title
	 *
	 * @return string with the path to the file
	 *
	 * @throws \PHPExcel_Exception
	 * @throws \PHPExcel_Writer_Exception
	 */
	public function exportFromMarkCollection(
		CollectionInterface $data,
		array $regular_columns,
		array $mark_columns,
		string $title
	): string {
		// get columns
		$columns = array_values( $regular_columns );
		foreach ( $mark_columns as $col ) {
			if ( $col->is_numerical ) {
                $columns[] = $col->name . ' -> ' . __( 'median' );
                $columns[] = $col->name . ' -> ' . __( 'average' );
                $columns[] = $col->name . ' -> ' . __( 'count' );
                $columns[] = $col->name . ' -> ' . __x( 'short: minimum', 'min' );
                $columns[] = $col->name . ' -> ' . __x( 'short: maximum', 'max' );
                $columns[] = $col->name . ' -> ' . __( 'standard deviation' );
            } else {
				$columns[] = $col->name . ' -> ' . __( 'value' );
			}
			$columns[] = $col->name . ' -> ' . __( 'values' );
		}

        // prepare data
		$out = array();

        // get data
		foreach ( $data as $row_in ) {
			$row = array();
			foreach ( $regular_columns as $column_key => $column_name ) {
				$row[] = $row_in->$column_key;
			}
			foreach ( $mark_columns as $col ) {
				if ( ! isset( $row_in->marks->toArray()[ $col->id ] ) ) {
					if ( $col->is_numerical ) {
						$row[] = "";
						$row[] = "";
						$row[] = "";
						$row[] = "";
						$row[] = "";
						$row[] = "";
					} else {
						$row[] = "";
					}
					$row[] = "";
				} else {
					$marks = $row_in->marks->toArray()[ $col->id ];
					if ( $col->is_numerical ) {
						$row[] = $marks->value->median;
						$row[] = $marks->value->avg;
						$row[] = $marks->value->count;
						$row[] = $marks->value->min;
						$row[] = $marks->value->max;
						$row[] = $marks->value->std;
					} else {
						$row[] = $marks->value;
					}
					$row[] = $this->_implode( $marks->values->unfold()->toArray() );
				}
			}
			$out[] = $row;
		}

        return $this->_export( $out, $columns, $title );
	}


}




