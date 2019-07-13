<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 14.09.17
 * Time: 20:08
 */

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;

class GetFilterDataBehavior extends Behavior {
    /**
     * Return data for the query where builder
     *
     * @return array
     *
     * @throws \Exception if any fields filter data validator types is unknown
     */
    public function getFilterData() {
        $queries = TableRegistry::get( 'Queries' );

        $tables        = array_keys( $queries->getViewNames() );
        $tables_fields = $queries->getFieldTypeMapOf( $tables );

        // add normal filter data
        foreach ( $tables_fields as $table => $table_fields ) {
            foreach ( $table_fields as $field => $type ) {
                $fields[ $table ][] = $this->_getFieldFilterData( $table, $field, $type );
            }
        }

        return $fields;
    }

    /**
     * Return array with filter data according to the needs of http://querybuilder.js.org/#filters
     *
     * @param string $table
     * @param string $field
     * @param string $type
     *
     * @return array
     *
     * @throws \Exception if the fields type is unknown
     */
    private function _getFieldFilterData( string $table, string $field, string $type ): array {
        $queries = TableRegistry::get( 'Queries' );

        $data['id']    = $table . '.' . $field;
        $data['label'] = $queries->translateFields( $data['id'] );
        $data['type']  = $this->_getFieldTypeForFilter( $type );
        $data['input'] = $this->_getFilterFieldInputType( $table, $field, $type );

        $this->_addValidator( $data );
        $this->_addRadioButtonProperties( $data );

        if ( 'select' === $data['input'] ) {
            $data['values']    = $this->_getDistinctValuesOf( $table, $field );
            $data['operators'] = [ 'equal', 'not_equal', 'is_empty', 'is_not_empty' ];
        }

        return $data;
    }

    /**
     * Return the type our query where builder understands from given cakeish db type or Mark Property Data Type
     *
     * @param string $type
     *
     * @return string
     */
    private function _getFieldTypeForFilter( string $type ): string {
        $cast = [
            /* cakeisch db types below */
            'string'       => 'string',
            'text'         => 'string',
            'integer'      => 'integer',
            'smallinteger' => 'integer',
            'tinyinteger'  => 'integer',
            'biginteger'   => 'integer',
            'float'        => 'double',
            'decimal'      => 'double',
            'boolean'      => 'boolean',
            'date'         => 'date',
            'datetime'     => 'datetime',
            'timestamp'    => 'integer',
            'time'         => 'time',
            /* Mark Property data types below */
            'INTEGER'      => 'integer',
            'VARCHAR'      => 'string',
            'BOOLEAN'      => 'boolean',
            'DATE'         => 'date',
            'FLOAT'        => 'double',
        ];

        return $cast[ $type ];
    }

    /**
     * Return array with filter data according to the needs of http://querybuilder.js.org/#filters
     *
     * @param string $tablename
     * @param string $field
     * @param string $type
     *
     * @return string
     */
    private function _getFilterFieldInputType( string $tablename, string $field, string $type ): string {
        if ( in_array( $type,
            [ 'integer', 'smallinteger', 'tinyinteger', 'biginteger', 'float', 'decimal', 'timestamp' ] ) ) {
            return 'number';
        }

        $table = TableRegistry::get( $tablename );
        if ( in_array( $field, $table->getBooleanFields() ) ) {
            return 'radio';
        }
        if ( in_array( $field, $table->getSelectFields() ) ) {
            return 'select';
        }

        return 'text';
    }

    /**
     * Add a where query builder validator object
     *
     * @param array $field
     *
     * @throws \Exception if the fields type is unknown
     */
    private function _addValidator( array &$field ): void {
        switch ( $field['type'] ) {
            case 'string':
                $field['validation'] = [];
                break;

            case 'integer':
                $field['validation'] = [
                    'step'              => 1,
                    'min'               => PHP_INT_MIN,
                    'max'               => PHP_INT_MAX,
                    'allow_empty_value' => false,
                    'messages'          => [
                        'number_nan'         => __( 'Please enter a number' ),
                        'number_not_integer' => __( 'Only integers allowed' ),
                        'step'               => __( 'Only integers allowed' ),
                        'allow_empty_value'  => __( 'Please enter a number' ),
                        'min'                => sprintf( __( 'The given number must not be smaller then %s' ),
                            PHP_INT_MIN ),
                        'man'                => sprintf( __( 'The given number must not be greater then %s' ),
                            PHP_INT_MAX ),
                    ]
                ];
                break;

            case 'double':
                $field['validation'] = [
                    'step'              => 'any',
                    'allow_empty_value' => false,
                    'messages'          => [
                        'number_nan'        => __( 'Please enter a number' ),
                        'number_not_double' => __( 'Only numbers allowed' ),
                        'allow_empty_value' => __( 'Please enter a number' ),
                    ]
                ];
                break;

            case 'boolean':
                $field['validation'] = [];
                break;

            case 'date':
                $field['validation'] = [
                    'format'            => __x( 'moment.js date format', 'DD.MM.YYYY' ),
                    'allow_empty_value' => false,
                    'messages'          => [
                        'format'            => __x( 'moment.js date format',
                            'The given date must match the format DD.MM.YYYY' ),
                        'allow_empty_value' => __( 'Please enter a date' ),
                    ]
                ];
                break;

            case 'datetime':
                $field['validation'] = [
                    'format'            => __x( 'moment.js datetime format', 'DD.MM.YYYY HH:mm:ss' ),
                    'allow_empty_value' => false,
                    'messages'          => [
                        'format'            => __x( 'moment.js datetime format',
                            'The given date and time must match the format DD.MM.YYYY HH:mm:ss' ),
                        'allow_empty_value' => __( 'Please enter date and time' ),
                    ]
                ];
                break;

            case 'time':
                $field['validation'] = [
                    'format'            => __x( 'moment.js time format', 'HH:mm:ss' ),
                    'allow_empty_value' => false,
                    'messages'          => [
                        'format'            => __x( 'moment.js time format',
                            'The given time must match the format HH:mm:ss' ),
                        'allow_empty_value' => __( 'Please enter a time' ),
                    ]
                ];
                break;

            default:
                throw new \Exception( "The type '{$field['type']} is not defined." );
        }
    }

    /**
     * Add the radio button properties to given filter data, if needed.
     *
     * @param $data
     */
    private function _addRadioButtonProperties( &$data ) {
        if ( 'radio' === $data['input'] ) {
            $data['values']        = [ 1 => __( 'Yes' ), 0 => __( 'No' ) ];
            $data['operators']     = [ 'equal' ];
            $data['default_value'] = 1;
        }
    }

    /**
     * Return array with the possible select values of the given table.field
     *
     * @param string $tablename
     * @param string $field
     *
     * @return array
     */
    private function _getDistinctValuesOf( string $tablename, string $field ): array {
        $table = TableRegistry::get( $tablename );

        $tmp = $table->find()
                     ->hydrate( false )
                     ->select( [ $field ] )
                     ->distinct()
                     ->orderAsc( $field );

        $values = [];
        foreach ( $tmp as $item ) {
            $val = $item[ $field ];
            if ( empty( $val ) ) {
                continue;
            }
            $values[ $val ] = $val;
        }

        return $values;
    }
}
