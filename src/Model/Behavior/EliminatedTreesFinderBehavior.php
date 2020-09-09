<?php


namespace App\Model\Behavior;


use Cake\ORM\Behavior;
use Cake\ORM\Query;

class EliminatedTreesFinderBehavior extends Behavior
{
    public function findWithEliminated(Query $query, array $showEliminated)
    {
        if ( !empty($showEliminated['show_eliminated']) ) {
            return $query;
        } else {
            return $query->where( ['Trees.date_eliminated IS NULL'] );
        }
    }
}
