<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 10.09.17
 * Time: 17:49
 */

namespace App\Model\Behavior;


use Cake\ORM\Behavior;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Class RulesToConditionsConvertibleBehavior is used to convert rules
 * from the where query builder to cakephp conforming where conditions.
 * @see http://querybuilder.js.org/
 *
 * @package App\Model\Behavior
 */
class RulesToConditionsConvertibleBehavior extends Behavior
{
    /**
     * Convert rules from the where query builder to cake conforming where conditions
     *
     * @param \stdClass|null $ruleset like they are coming from the where query builder
     *
     * @return array
     */
    public function convertRulesetToConditions($ruleset): array
    {
        if (empty($ruleset)) {
            return [];
        }
        
        $conditions = [];
        
        if (is_object($ruleset) &&
            property_exists($ruleset, 'rules') &&
            property_exists($ruleset, 'condition')
        ) { // it's a ruleset
            $tmp = [];
            foreach ($ruleset->rules as $r) {
                $tmp[$ruleset->condition][] = $this->convertRulesetToConditions($r);
            }
            $conditions[] = $tmp;
        } else { // it's a rule
            
            if ( ! $this->_isMarkProperty($ruleset->id)) {
                // if it's not a Mark Property
                return $this->_convertRuleToCondition($ruleset);
            } else {
                // if it is a Mark Property
                return $this->_getMarkPropertyConditionFromRule($ruleset);
            }
        }
        
        return $conditions;
    }
    
    /**
     * Return true if its a mark property else false
     *
     * @param string $id
     *
     * @return bool
     */
    private function _isMarkProperty(string $id): bool
    {
        return 0 === strpos($id, 'MarkProperty');
    }
    
    /**
     * Return cake where condition array from given rule
     *
     * @param \stdClass $rule
     *
     * @return array
     */
    private function _convertRuleToCondition(\stdClass $rule): array
    {
        $this->_typecastValue($rule);
        
        switch ($rule->operator) {
            case 'equal':
                return [$rule->field => $rule->value];
            case 'not_equal':
                return ['NOT' => [$rule->field => $rule->value]];
            case 'in':
                return [$rule->field . ' IN' => (array)$rule->value];
            case 'not_in':
                return ['NOT' => [$rule->field . ' IN' => (array)$rule->value]];
            case 'less':
                return [$rule->field . ' <' => $rule->value];
            case 'less_or_equal':
                return [$rule->field . ' <=' => $rule->value];
            case 'greater':
                return [$rule->field . ' >' => $rule->value];
            case 'greater_or_equal':
                return [$rule->field . ' >=' => $rule->value];
            case 'begins_with':
                return [$rule->field . ' LIKE' => $rule->value . '%'];
            case 'not_begins_with':
                return ['NOT' => [$rule->field . ' LIKE' => $rule->value . '%']];
            case 'contains':
                return [$rule->field . ' LIKE' => '%' . $rule->value . '%'];
            case 'not_contains':
                return ['NOT' => [$rule->field . ' LIKE' => '%' . $rule->value . '%']];
            case 'ends_with':
                return [$rule->field . ' LIKE' => '%' . $rule->value];
            case 'not_ends_with':
                return ['NOT' => [$rule->field . ' LIKE' => '%' . $rule->value]];
            case 'is_empty':
                return ['OR' => [$rule->field => '', $rule->field . ' IS NULL']];
            case 'is_not_empty':
                return ['OR' => ['NOT' => [$rule->field => '']], ['NOT' => [$rule->field . ' IS NULL']]];
            case 'is_null':
                return [$rule->field . ' IS NULL'];
            case 'is_not_null':
                return ['NOT' => [$rule->field . ' IS NULL']];
            default:
                throw new \InvalidArgumentException("Given operator is not supported");
        }
    }
    
    /**
     * Typecast the where query typed value(s) to cakephps orm understandable values
     *
     * @param \stdClass $rule
     */
    private function _typecastValue(\stdClass &$rule): void
    {
        $simple  = ['integer', 'double', 'boolean', 'string'];
        $complex = ['date', 'time', 'datetime'];
        
        if (in_array($rule->type, $simple)) {
            if (is_array($rule->value)) {
                foreach ($rule->value as &$value) {
                    settype($value, $rule->type);
                }
                
                return;
            } else {
                settype($value, $rule->type);
                
                return;
            }
        }
        
        if (in_array($rule->type, $complex)) {
            if (is_array($rule->value)) {
                foreach ($rule->value as &$value) {
                    $rule->value = $this->_parseTime($rule->type, (string) $rule->value);
                }
                
                return;
            } else {
                $rule->value = $this->_parseTime($rule->type, (string) $rule->value);
                
                return;
            }
        }
        
        throw new InvalidTypeException('The given type is not supported');
    }
    
    /**
     * Return given date, time or datetime in mysql format
     *
     * @param string $type
     * @param string $value
     *
     * @return string
     */
    private function _parseTime(string $type, string $value): string
    {
        switch ($type) {
            case 'date':
                $date = Date::parse($value);
                
                return $date->format('Y-m-d');
            
            case 'time':
                $date = Time::parse($value);
                
                return $date->format('H:i:s');
            
            case 'datetime':
                $date = Time::parse($value);
                
                return $date->format('Y-m-d H:i:s');
            
            default:
                throw new InvalidTypeException('The given type is not supported');
        }
        
    }
    
    /**
     * Return cake where condition array from given mark property rule
     *
     * @param \stdClass $rule
     *
     * @return array
     */
    private function _getMarkPropertyConditionFromRule(\stdClass $rule): array
    {
        $property    = substr($rule->id, strpos($rule->id, '.') + 1);
        $rule->field = 'MarksView.value';
        
        // if it is a Mark Property
        return [
            'AND' => [
                'MarksView.name' => $property,
                $this->_convertRuleToCondition($rule),
            ]
        ];
    }
}