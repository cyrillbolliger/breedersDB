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

class GetFilterDataBehavior extends Behavior
{
    /**
     * Return data for the query where builder
     *
     * @return array
     */
    public function getFilterData()
    {
        $queries = TableRegistry::get('Queries');
        
        $tables        = array_keys($queries->getViewNames());
        $tables_fields = $queries->getFieldTypeMapOf($tables);
        
        $fields = [];
        
        // get mark properties first
        $table      = 'MarksView';
        $properties = $this->_getDistinctValuesOf($table, 'name');
        foreach ($properties as $property) {
            $fields[$table][] = $this->_getMarkPropertyFieldFilterData($property);
        }
        
        // add normal filter data
        foreach ($tables_fields as $table => $table_fields) {
            foreach ($table_fields as $field => $type) {
                if ('MarksView' === $table && 'name' === $field) {
                    continue; // we've already added the fields. see above
                }
                $fields[$table][] = $this->_getFieldFilterData($table, $field, $type);
            }
        }
        
        return $fields;
    }
    
    /**
     * Return array with the possible select values of the given table.field
     *
     * @param string $tablename
     * @param string $field
     *
     * @return array
     */
    private function _getDistinctValuesOf(string $tablename, string $field): array
    {
        $table  = TableRegistry::get($tablename);
        $tmp    = $table->find()->select([$field])->distinct()->orderAsc($field)->toArray();
        $values = [];
        foreach ($tmp as $item) {
            if (empty($item->$field)) {
                continue;
            }
            $values[$item->$field] = $item->$field;
        }
        
        return $values;
    }
    
    /**
     * Return array with mark property filter data according to the needs of http://querybuilder.js.org/#filters
     *
     * @param string $table
     *
     * @return array
     */
    private function _getMarkPropertyFieldFilterData(string $field): array
    {
        $marks = TableRegistry::get('MarksView');
        $type  = $marks->find()->select('field_type')->where(['name' => $field])->firstOrFail()->field_type;
        
        $typeInputTypeMap = [
            'integer' => 'number',
            'double'  => 'number',
            'string'  => 'text',
            'boolean' => 'radio',
            'date'    => 'text'
        ];
        
        $data['id']    = 'MarkProperty.' . $field;
        $data['label'] = __('Mark Property') . ' -> ' . $field;
        $data['type']  = $this->_getFieldTypeForFilter($type);
        $data['input'] = $typeInputTypeMap[$data['type']];
        
        $this->_addRadioButtonProperties($data);
        
        return $data;
    }
    
    /**
     * Return the type our query where builder understands from given cakeish db type or Mark Property Data Type
     *
     * @param string $type
     *
     * @return string
     */
    private function _getFieldTypeForFilter(string $type): string
    {
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
        
        return $cast[$type];
    }
    
    /**
     * Add the radio button properties to given filter data, if needed.
     *
     * @param $data
     */
    private function _addRadioButtonProperties(&$data)
    {
        if ('radio' === $data['input']) {
            $data['values']        = [1 => __('Yes'), 0 => __('No')];
            $data['operators']     = ['equal'];
            $data['default_value'] = 1;
        }
    }
    
    /**
     * Return array with filter data according to the needs of http://querybuilder.js.org/#filters
     *
     * @param string $table
     * @param string $field
     * @param string $type
     *
     * @return array
     */
    private function _getFieldFilterData(string $table, string $field, string $type): array
    {
        $queries = TableRegistry::get('Queries');
        
        $data['id']    = $table . '.' . $field;
        $data['label'] = $queries->translateFields($data['id']);
        $data['type']  = $this->_getFieldTypeForFilter($type);
        $data['input'] = $this->_getFilterFieldInputType($table, $field, $type);
        
        $this->_addRadioButtonProperties($data);
        
        if ('select' === $data['input']) {
            $data['values']    = $this->_getDistinctValuesOf($table, $field);
            $data['operators'] = ['equal', 'not_equal', 'is_empty', 'is_not_empty'];
        }
        
        return $data;
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
    private function _getFilterFieldInputType(string $tablename, string $field, string $type): string
    {
        if (in_array($type,
            ['integer', 'smallinteger', 'tinyinteger', 'biginteger', 'float', 'decimal', 'timestamp'])) {
            return 'number';
        }
        
        $table = TableRegistry::get($tablename);
        if (in_array($field, $table->getBooleanFields())) {
            return 'radio';
        }
        if (in_array($field, $table->getSelectFields())) {
            return 'select';
        }
        
        return 'text';
    }
}