<?php


namespace App\Model;


use Cake\ORM\Query;

/**
 * Constructs the query for marks aggregated by trees, varieties and batches.
 *
 * @package App\Model
 */
class RegularMarkQueryBuilder implements MarkQueryBuilderInterface
{
    private static $allowedModes = ['trees', 'varieties', 'batches'];
    private string $mode;

    /**
     * RegularMarkQueryBuilder constructor.
     *
     * @param string $mode allowed values 'trees', 'varieties', 'batches'
     *
     * @throws \Exception if initialized with an unknown mode.
     */
    public function __construct(string $mode)
    {
        if (! in_array($mode, self::$allowedModes)){
            throw new \Exception( "The mode '{$mode}' is not defined." );
        }

        $this->mode = $mode;
    }

    /**
     * Build query to get the marks with the needed associated data.
     *
     * @param array $regularFieldsFilter the where conditions for the display fields
     * @param array $markProperties the ids of the mark properties to display or filter
     *
     * @return Query
     */
    public function buildQuery( array $regularFieldsFilter, array $markProperties ): Query
    {
        $marks = \Cake\Datasource\FactoryLocator::get('Table')->get( 'MarksView' );

        switch ( $this->mode ) {
            case 'trees':
                $associations             = 'TreesView';
                $breedingObjectConditions = [ 'NOT' => [ 'MarksView.tree_id IS NULL' ] ];
                break;
            case 'varieties':
                $associations             = 'VarietiesView';
                $breedingObjectConditions = [ 'NOT' => [ 'MarksView.variety_id IS NULL' ] ];
                break;
            case 'batches':
                $associations             = 'BatchesView';
                $breedingObjectConditions = [ 'NOT' => [ 'MarksView.batch_id IS NULL' ] ];
                break;
        }

        return $marks->find()
            ->select( $this->_getInterallyNeededFields() )
            ->contain( $associations )
            ->where( $regularFieldsFilter )
            ->andWhere( [ 'MarksView.property_id IN' => $markProperties ] )
            ->andWhere( $breedingObjectConditions );
    }

    /**
     * Return array with all fields that are used internally
     *
     * @return array
     */
    private function _getInterallyNeededFields(): array {
        $markFields = [
            'MarksView.id',
            'MarksView.value',
            'MarksView.property_id',
            'MarksView.field_type',
        ];

        switch ( $this->mode ) {
            case 'trees':
                $obj_fields = [
                    'MarksView.tree_id',
                ];
                break;

            case 'varieties':
                $obj_fields = [
                    'MarksView.variety_id',
                ];
                break;

            case 'batches':
                $obj_fields = [
                    'MarksView.batch_id',
                ];
                break;
        }

        return array_merge( $markFields, $obj_fields );
    }
}
