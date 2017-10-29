<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 23:03
 */

namespace App\Model\Behavior;

use App\Model\Entity\MarksView;
use App\Utility\MarksAggregatorUtility;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\Core\Exception\Exception;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class MarkQueryBehavior extends Behavior
{
    /**
     * @var bool load data from cache if possible
     */
    private $fromCache;
    
    /**
     * @var array with the field as key and the direction as value
     */
    private $orderBy;
    
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
     * @var array of mark properties we want to use in the query
     */
    private $properties;
    
    /**
     * @var array with fields to display
     */
    private $display;
    
    /**
     * @var array with the filter data
     */
    private $filter;
    
    /**
     * @var Query used to retrieve the unfiltered data
     */
    private $query;
    
    /**
     * @var Collection containing intermediate data
     */
    private $data;
    
    /**
     * ToDo: proper description
     *
     * @param bool $fromCache
     * @param array $orderBy
     *
     * @return CollectionInterface
     */
    public function customFindMarks(bool $fromCache, array $orderBy): CollectionInterface
    {
        $this->fromCache = $fromCache;
        
        $this->orderBy = $orderBy;
        
        $this->mode = 'trees';
        
        $this->properties = [
            'H_Gesamteindruck Frucht',
            'K1_Schorf Blatt',
        ];
        
        $display = [
            'TreesView.id',
            'TreesView.publicid',
            'TreesView.convar',
        ];
        
        $this->display = array_merge($display, [
            'MarksView.tree_id',
            'MarksView.variety_id',
            'MarksView.batch_id',
            'MarksView.value',
            'MarksView.name',
            'MarksView.field_type',
        ]);
        
        $this->filter = [];
        
        $data   = $this->_getData();
        $sorted = $this->_sort($data);
        
        return $sorted;
    }
    
    private function _sort(CollectionInterface $data) {
        // todo
        return $data;
    }
    
    /**
     * Return breeding objects according to $this->mode ('convar' will return varieties)
     * containing the marks specified in $this->properties and the fields specified
     * in $this->display, all filtered by $this->filter. If a valid cache exists and
     * $this->fromCache is set to true, the results will be served from cache. The
     * cache is mainly used to speed up sorting and browsing using the paginator.
     *
     * @return CollectionInterface
     */
    private function _getData(): CollectionInterface
    {
        if ($this->fromCache && $this->_cacheExists()) {
            return $this->_fromCache();
        }
        
        $query                = $this->_getQuery();
        $filtered             = $this->_filterAllButMarkValues($query);
        $groupedByMark        = $this->_groupByMark($filtered);
        $aggregated           = $this->_aggregate($groupedByMark);
        $groupedByObj         = $this->_groupByBreedingObject($aggregated);
        $markedObj            = $this->_moveMarksIntoBreedingObjects($groupedByObj);
        $filteredByMarkValues = $this->_filterByMarkValues($markedObj);
        
        if (! $this->fromCache) {
            // todo: return $this->_cacheResults($filteredByMarkValues);
        }
        
        return $filteredByMarkValues;
    }
    
    /**
     * Test if a valid cache file with the same filter criteria and the same display fields exists.
     *
     * @return bool
     */
    private function _cacheExists(): bool
    {
        // todo
        return false;
    }
    
    /**
     * Get results from cache
     *
     * @return CollectionInterface
     */
    private function _fromCache(): CollectionInterface
    {
        // todo
    }
    
    /**
     * Set query according to $this->mode. Only extract fields defined in $this->display
     *
     * @return Query
     * @throws Exception if $this->mode is not defined
     */
    private function _getQuery(): Query
    {
        $marks = TableRegistry::get('MarksView');
        
        $associations = null;
        switch ($this->mode) {
            case 'trees':
                $associations = 'TreesView';
                break;
            case 'varieties':
                $associations = 'VarietiesView';
                break;
            case 'batches':
                $associations = 'BatchesView';
                break;
            case 'convar':
                $associations = ['TreesView', 'VarietiesView'];
                break;
            default:
                throw new Exception("'{$this->mode}' is not an defined mode.'");
        }
        
        return $marks->find()->select($this->display)->contain($associations);
    }
    
    /**
     * Filter the query data by property and mode as well as all the user given filter criteria excepting mark values.
     *
     * @param Query $query
     *
     * @return CollectionInterface with the filtered data
     */
    private function _filterAllButMarkValues(Query $query): CollectionInterface
    {
        return $query->filter(function ($item) {
            // filter by property
            if ( ! in_array($item->name, $this->properties)) {
                return false;
            }
            
            // filter mode
            if ( ! $this->_hasItemDataForCurrentMode($item)) {
                return false;
            }
            
            // todo: filter by other stuff except mark values
            return true;
        });
    }
    
    /**
     * Check if the given item has data for the current mode ($this->>mode).
     *
     * @param MarksView $item
     *
     * @return bool
     */
    private function _hasItemDataForCurrentMode(MarksView $item): bool
    {
        switch ($this->mode) {
            case 'trees':
                $fields = 'tree_id';
                break;
            case 'varieties':
                $fields = 'variety_id';
                break;
            case 'batches':
                $fields = 'batch_id';
                break;
            case 'convar':
                $fields = ['tree_id', 'variety_id'];
                break;
            default:
                throw new Exception("'{$this->mode}' is not an defined mode.'");
        }
        
        $empty = true;
        foreach ((array)$fields as $field) {
            if ($empty) {
                $empty = empty($item->$field);
            }
        }
        
        return ! $empty;
    }
    
    /**
     * Return collection grouped by mark and breeders object
     *
     * @param CollectionInterface $marks
     *
     * @return CollectionInterface
     */
    private function _groupByMark(CollectionInterface $marks): CollectionInterface
    {
        return $marks->groupBy(function ($mark) {
            // group by mark AND breeders object otherwise the stats will aggregate all objects
            return $mark->tree_id . $mark->variety_id . $mark->batch_id . $mark->name;
        });
    }
    
    /**
     * Reduce marks into one mark element containing the aggregated values in the field value
     * and the single values with their reference in the field values.
     * @see MarksAggregatorUtility::aggregate() for more details.
     *
     * @param CollectionInterface $groupedMarks
     *
     * @return CollectionInterface
     */
    private function _aggregate(CollectionInterface $groupedMarks): CollectionInterface
    {
        return $groupedMarks->map(function ($marks) {
            $collection = new Collection($marks);
            $aggregator = new MarksAggregatorUtility($this->mode);
            
            return $aggregator->aggregate($collection);
        });
    }
    
    /**
     * Return aggregated marks grouped by breeders object
     *
     * @param CollectionInterface $marks
     *
     * @return CollectionInterface
     */
    private function _groupByBreedingObject(CollectionInterface $marks): CollectionInterface
    {
        switch ($this->mode) {
            case 'trees':
                return $marks->groupBy('tree_id');
            
            case 'varieties':
                return $marks->groupBy('variety_id');
            
            case 'batches':
                return $marks->groupBy('batch_id');
            
            case 'convar':
                return $marks->groupBy(function ($mark) {
                    return ! empty($mark->tree_id) ? 'trees_view.convar' : 'varieties_view.convar';
                });
            
            default:
                throw new Exception("'{$this->mode}' is not an defined mode.'");
        }
    }
    
    /**
     * Return the breeders object (ex. tree) with its marks added to the field marks (array).
     *
     * @param CollectionInterface $breedersObjectsMarks
     *
     * @return CollectionInterface
     */
    private function _moveMarksIntoBreedingObjects(CollectionInterface $breedersObjectsMarks): CollectionInterface
    {
        return $breedersObjectsMarks->map(function ($marks) {
            $obj = $this->_getBreedersObjectFromMarks($marks);
            
            $obj->marks = array();
            
            foreach ($marks as $mark) {
                $obj->marks[$mark->name] = (object)[
                    'name'                    => $mark->name,
                    'value'                   => $mark->value,
                    'values'                  => $mark->values,
                    'field_type'              => $mark->field_type,
                    'mark_form_property_type' => $mark->mark_form_property_type,
                ];
            }
            
            return $obj;
        });
    }
    
    /**
     * Return breeders object (ex. tree) from given mark respecting $this->mode.
     * If the mode 'convar' is selected the breeders object will always be the variety.
     *
     * @param array $marks
     *
     * @return Entity
     */
    private function _getBreedersObjectFromMarks(array $marks): Entity
    {
        switch ($this->mode) {
            case 'trees':
                return $marks[0]->trees_view;
            
            case 'varieties':
                return $marks[0]->varieties_view;
            
            case 'batches':
                return $marks[0]->batches_view;
            
            case 'convar': // always return varieties_view as breeders obj
                if ( ! empty($marks[0]->varieties_view)) {
                    // if its already loaded return it directly
                    return $marks[0]->varieties_view;
                }
                
                // if the varieties_view is missing query it
                $varieties = TableRegistry::get('VarietiesView');
                
                return $varieties->get($marks[0]->trees_view->variety_id);
            
            default:
                throw new Exception("'{$this->mode}' is not an defined mode.'");
        }
    }
    
    private function _filterByMarkValues(CollectionInterface $markedObj): CollectionInterface
    {
        // todo
        return $markedObj;
    }
}