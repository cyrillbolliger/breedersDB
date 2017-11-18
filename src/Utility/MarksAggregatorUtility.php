<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 29.10.17
 * Time: 08:54
 */

namespace App\Utility;


use App\Model\Entity\MarksView;
use Cake\Collection\Collection;
use Cake\Collection\Iterator\ReplaceIterator;
use Cake\Core\Exception\Exception;

class MarksAggregatorUtility
{
    /**
     * @var string which holds the way we want to retrieve the breeding object (and which breeding objects).
     * Possible values:
     * - 'trees': get marks of trees only, group by tree
     * - 'varieties': get marks of varieties only, group by variety
     * - 'convar': get marks of trees AND varieties, group by convar
     * - 'batches': get marks of batches only, group by batches
     */
    private $mode;
    
    /**
     * @var array with properties that must be excluded from aggregated results
     */
    private $nonAggregatableMarkProperties = [
        'id',
        'date',
        'author',
        'exceptional_mark',
        'value',
    ];
    
    /**
     * MarksAggregatorUtility constructor.
     *
     * @param string $mode
     *
     * @throws Exception if a undefined mode was given.
     */
    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }
    
    /**
     * Return the aggregated marks of the given mark collection.
     * Strings and dates get concatenated (separated by '; '),
     * true is set if one or more booleans are true, multiple
     * statistical values (count, average, min, max, median and
     * standard deviation are calculated for numerical values.
     *
     * The aggregated values are accessible in the 'value' field.
     *
     * The 'values' field holds an object with the original values
     * (value), the id of the breeders object the mark belongs to
     * (reference_id), the identifier code of the breeders object
     * (reference_code) and the type of the breeders object (obj_type).
     *
     * @param Collection $marks
     *
     * @return MarksView
     */
    public function aggregate(Collection $marks): MarksView
    {
        $aggregated = clone $marks->first();
        $this->_removeNonAggregatableProperties($aggregated);
        
        // preserve single values including reference
        $aggregated->values = $this->_extractValuesWithReference($marks);
        
        // get array of values
        $values = $marks->extract('value')->toArray();
        
        // aggregate according to the field type
        switch ($aggregated->field_type) {
            case 'INTEGER':
                $aggregated->value = (object)$this->_calculateStats($values);
                break;
            
            case 'FLOAT':
                $aggregated->value = (object)$this->_calculateStats($values);
                break;
            
            case 'VARCHAR':
                $aggregated->value = implode('; ', $values);
                break;
            
            case 'BOOLEAN':
                $aggregated->value = (bool)array_sum($values);
                break;
            
            case 'DATE':
                $aggregated->value = implode('; ', $values);
                break;
            
            default:
                throw new Exception("'{$aggregated->field_type}' is not an defined field type.'");
        }
        
        return $aggregated;
    }
    
    /**
     * Remove all $this->nonAggregatableMarkProperties from given $obj by reference.
     *
     * @param MarksView $obj
     */
    private function _removeNonAggregatableProperties(MarksView &$obj): void
    {
        foreach ($this->nonAggregatableMarkProperties as $property) {
            if (isset($property, $obj)) {
                unset($obj->$property);
            }
        }
    }
    
    /**
     * Return the value, its mark_type (breeders object)
     * with the containing reference id (ex. tree_id)
     * and reference code (ex. publicid).
     *
     * @param Collection $marks
     *
     * @return ReplaceIterator
     */
    private function _extractValuesWithReference(Collection $marks): ReplaceIterator
    {
        return $marks->map(function ($mark) {
            $mark_type = $this->_getMarkType($mark);
            
            $return['value']          = $mark->value;
            $return['obj_type']       = $mark_type;
            $return['reference_id']   = $mark->{$mark_type . '_id'};
            $return['reference_code'] = $this->_getReferenceCode($mark, $mark_type);
            $return['date']           = $mark->date;
            
            return (object)$return;
        });
    }
    
    /**
     * Return the breeders object type the mark belongs to.
     * Possible return values:
     *  - tree
     *  - variety
     *  - batch
     *
     * @param MarksView $mark
     *
     * @return string
     *
     * @throws Exception if $this->mode is not defined
     */
    private function _getMarkType(MarksView $mark): string
    {
        switch ($this->mode) {
            case 'trees':
                return 'tree';
            
            case 'varieties':
                return 'variety';
            
            case 'batches':
                return 'batch';
            
            case 'convar':
                if ( ! empty($mark->tree_id)) {
                    return 'tree';
                }
    
                return 'variety';
                
            default:
                throw new Exception("'{$this->mode}' mode is not defined.'");
        }
    }
    
    /**
     * Return publicid resp. convar resp. crossing_batch of the breeders object of the given mark.
     *
     * @param MarksView $mark
     * @param string $mark_type accepted values: tree, variety, batch
     *
     * @return string
     */
    private function _getReferenceCode(MarksView $mark, string $mark_type): string
    {
        switch ($mark_type) {
            case 'tree':
                return $mark->trees_view->publicid;
            case 'variety':
                return $mark->varieties_view->convar;
            case 'batch':
                return $mark->batches_view->crossing_batch;
        }
        
        return __('unknown');
    }
    
    /**
     * Return array with count, avg, min, max, median and std (standard deviation) from given values
     *
     * @param array $values
     *
     * @return array stats
     */
    private function _calculateStats(array $values): array
    {
        // calculate reused stats
        $count = count($values);
        $avg   = (float)array_sum($values) / $count;
        
        // sort values now and only once (for min, max and median)
        sort($values);
        
        // calculate stats
        $stats['count']  = $count;
        $stats['avg']    = $avg;
        $stats['min']    = $values[0];
        $stats['max']    = $values[$count - 1];
        $stats['median'] = $this->_median($values, $count);
        $stats['std']    = $this->_stdDev($values, $avg, $count);
        
        return $stats;
    }
    
    /**
     * Calculate median
     *
     * @param array $sortedArray values must be in ascending order
     * @param int $count number of items in $sortedArray
     *
     * @return float
     */
    private function _median(array $sortedArray, int $count): float
    {
        if ($count <= 1) {
            return $sortedArray[0];
        }
        
        $middle = (int)($count / 2);
        
        if ($count % 2) {
            return $sortedArray[$middle];
        }
        
        return (float)(($sortedArray[$middle - 1] + $sortedArray[$middle]) / 2);
    }
    
    /**
     * Calculate standard deviation
     *
     * @param array $values
     * @param float $avg
     * @param int $count
     *
     * @return float
     */
    private function _stdDev(array $values, float $avg, int $count): float
    {
        if ($count <= 1) {
            return 0;
        }
        
        $tmp = array_map(function ($v) use ($avg) {
            return pow(($v - $avg), 2);
        }, $values);
        $var = array_sum($tmp) / $count;
        
        return sqrt($var);
    }
}