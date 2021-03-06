<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 11:06
 */

namespace App\Utility;

use Cake\Utility\Inflector;

class DataExtractorUtility {
    /**
     * Works like self::getCell but returns the id in the array key
     *
     * @param $key
     * @param $data
     *
     * @return array
     */
    public function getMarkValueCell( $key, $data ) {
        $id_key = preg_replace( '/MarksView\.value$/', 'MarksView.id', $key );
        
        $ids    = $this->getCell( $id_key, $data );
        $values = $this->getCell( $key, $data );
        
        if ( ! is_array( $ids ) ) {
            return [ $ids => $values ];
        }
        
        $return = [];
        foreach ( $ids as $i => $id ) {
            $return[ $id ] = $values[ $i ];
        }
        
        return $return;
    }
    
    /**
     * Return content of given cell ($key is the dot notated path) from $data.
     * $data will be recursively scanned and every matching $key will me returned.
     * Multiple matches will be returned as array.
     *
     * @param $key
     * @param $data
     *
     * @return string|array
     */
    public function getCell( $key, $data ) {
        $path = explode( '.', $key );
        
        if ( $this->_isMain( $path, $data ) ) {
            unset( $path[0] );
        }
        
        $tmp = $this->_getCellRecursive( $path, $data );
        
        if ( ! is_array( $tmp ) ) {
            return $tmp;
        }
        
        // flatten array
        $flat = array();
        array_walk_recursive( $tmp, function ( $v, $k ) use ( &$flat ) {
            $flat[] = $v;
        } );
        
        // clean out null values
        $clean = array_filter( $flat, function ( $val ) {
            return null !== $val;
        } );
        
        return $clean;
    }
    
    /**
     * Return true if the first part of the path is the entity itself
     *
     * @param $path
     * @param $data
     *
     * @return bool
     */
    private function _isMain( $path, $data ) {
        $class_path = '\App\Model\Entity\\' . $path[0];
        $class      = new $class_path();
        
        return ( $data instanceof $class );
    }
    
    /**
     * Recursively scan the given object and return all elements matching the given key
     *
     * @param $path
     * @param $data
     *
     * @return array|null
     */
    private function _getCellRecursive( $path, $data ) {
        if ( null === $data ) {
            return null;
        }
        
        if ( 0 === count( $path ) ) {
            return $data;
        }
        
        $part     = array_shift( $path );
        $property = Inflector::underscore( $part );
        
        if ( is_array( $data ) ) {
            
            if ( empty( $data ) ) {
                return null;
            }
            
            $array = [];
            foreach ( $data as $key => $d ) {
                $array[] = $this->_getCellRecursive( $path, $d->$property );
            }
            
            return $array;
        }
        
        return $this->_getCellRecursive( $path, $data->$property );
    }
}
