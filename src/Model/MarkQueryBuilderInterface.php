<?php


namespace App\Model;

use Cake\ORM\Query;

/**
 * Construct db queries to obtain marks with their associated data.
 *
 * @package App\Model
 */
interface MarkQueryBuilderInterface
{
    /**
     * Build query to get the marks with the needed associated data.
     *
     * @param array $regularFieldsFilter the where conditions for the display fields
     * @param array $markProperties the ids of the mark properties to display or filter
     *
     * @return Query
     */
    public function buildQuery(array $regularFieldsFilter, array $markProperties): Query;
}
