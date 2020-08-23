<?php


namespace App\Model;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class ConvarMarkQueryBuilder implements MarkQueryBuilderInterface
{
    public function buildQuery(array $regularFieldsFilter, array $markProperties): Query
    {
        $marks = TableRegistry::getTableLocator()->get('MarksView');

        $treeMarks = $marks->find()
            ->select($this->_getInterallyNeededFields('TreesView'))
            ->contain([
                'TreesView' => [
                    'joinType' => 'INNER',
                    'VarietiesView' => [
                        // using a join strategy is a must, else the nested
                        // association isn't loaded
                        'strategy' => 'join'
                    ]
                ]
            ])
            ->where( $regularFieldsFilter )
            ->andWhere(['MarksView.property_id IN' => $markProperties]);

        $varietyMarks = $marks->find()
            ->select($this->_getInterallyNeededFields('VarietiesView'))
            ->contain(['VarietiesView' => ['joinType' => 'INNER']])
            ->where( $regularFieldsFilter )
            ->andWhere(['MarksView.property_id IN' => $markProperties]);

        return $treeMarks->union($varietyMarks);
    }


    /**
     * Return array with all fields that are used internally
     *
     * @param string $baseTable
     *
     * @return array
     */
    private function _getInterallyNeededFields(string $baseTable): array
    {
        return [
            'MarksView.id',
            'MarksView.value',
            'MarksView.property_id',
            'MarksView.field_type',
            'TreesView' == $baseTable ? 'TreesView.variety_id' : 'MarksView.variety_id',
            'VarietiesView.convar'
        ];
    }
}
