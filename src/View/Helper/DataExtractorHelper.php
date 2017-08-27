<?php

namespace App\View\Helper;

use Cake\View\Helper;
use Cake\Utility\Inflector;

class DataExtractorHelper extends Helper
{
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
    public function getCell($key, $data)
    {
        $path = explode('.', $key);
        
        if ($this->_isMain($path, $data)) {
            unset($path[0]);
        }
        
        $tmp = $this->_getCellRecursive($path, $data);
        
        if (! is_array($tmp)) {
            return h($tmp);
        }
        
        // flatten array
        $flat = array();
        array_walk_recursive($tmp,function($v, $k) use (&$flat){ $flat[] = $v; });
        
        // clean out null values
        $clean = array_filter($flat);
        
        return $this->_makeList($clean);
    }
    
    /**
     * Return true if the first part of the path is the entity itself
     *
     * @param $path
     * @param $data
     *
     * @return bool
     */
    private function _isMain($path, $data)
    {
        $class_path = '\App\Model\Entity\\' . $path[0];
        $class      = new $class_path();
        
        return ($data instanceof $class);
    }
    
    /**
     * Recursively scan the given object and return all elements matching the given key
     *
     * @param $path
     * @param $data
     *
     * @return array|null
     */
    private function _getCellRecursive($path, $data)
    {
        if (null === $data) {
            return null;
        }
        
        if (0 === count($path)) {
            return $data;
        }
        
        $part     = array_shift($path);
        $property = Inflector::underscore($part);
        
        if (is_array($data)) {
            
            if (empty($data)) {
                return null;
            }
            
            $array = [];
            foreach ($data as $key => $d) {
                $array[] = $this->_getCellRecursive($path, $d->$property);
            }
            
            return $array;
        }
        
        return $this->_getCellRecursive($path, $data->$property);
    }
    
    /**
     * Return unordered HTML-list from given array
     *
     * @param $array
     *
     * @return string
     */
    private function _makeList($array)
    {
        $html = '<ul>';
        foreach($array as $item) {
            $html .= '<li>'.h($item).'</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
}