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
            $conditions = $this->_convertRuleToCondition($ruleset);
        }
        
        return $conditions;
    }
    
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
                return [$rule->field . ' LIKE' => $rule->value.'%'];
            case 'not_begins_with':
                return ['NOT' => [$rule->field . ' LIKE' => $rule->value.'%']];
            case 'contains':
                return [$rule->field . ' LIKE' => '%'.$rule->value.'%'];
            case 'not_contains':
                return ['NOT' => [$rule->field . ' LIKE' => '%'.$rule->value.'%']];
            case 'ends_with':
                return [$rule->field . ' LIKE' => '%'.$rule->value];
            case 'not_ends_with':
                return ['NOT' => [$rule->field . ' LIKE' => '%'.$rule->value]];
            case 'is_empty':
                return ['OR' => [$rule->field => '', $rule->field . ' IS NULL']];
            case 'is_not_empty':
                return ['OR' => ['NOT' => [$rule->field => '']], ['NOT' => [$rule->field . ' IS NULL']]];
            case 'is_null':
                return [$rule->field . ' IS NULL'];
            case 'is_not_null':
                return ['NOT' => [$rule->field . ' IS NULL']];
            case 'between':
                if (count($rule->value) !== 2) {
                    throw new \InvalidArgumentException("Between statements require exactly two values");
                }
                
                return [$rule->field . ' BETWEEN ? AND ?' => $rule->value];
            case 'not_between':
                if (count($rule->value) !== 2) {
                    throw new \InvalidArgumentException("Between statements require exactly two values");
                }
                
                return [$rule->field . ' BETWEEN ? AND ?' => $rule->value];
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
        $complex = ['date', 'time', 'datetime', 'boolean'];
        
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
                    // ToDo
                    debug('TODO');
                }
                
                return;
            } else {
                // ToDo
                debug('TODO');
                
                return;
            }
        }
        
        throw new InvalidTypeException('The given type is not supported');
    }
}