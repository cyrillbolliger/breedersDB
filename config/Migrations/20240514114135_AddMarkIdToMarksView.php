<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMarkIdToMarksView extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->execute( "DROP VIEW IF EXISTS `marks_view`" );
        $this->execute( "CREATE VIEW `marks_view` AS select `mark_values`.`id` AS `id`,`marks`.`id` AS `mark_id`,`marks`.`date` AS `date`,`marks`.`author` AS `author`,`marks`.`tree_id` AS `tree_id`,`marks`.`variety_id` AS `variety_id`,`marks`.`batch_id` AS `batch_id`,`mark_values`.`value` AS `value`,`mark_values`.`exceptional_mark` AS `exceptional_mark`,`mark_form_properties`.`name` AS `name`,`mark_form_properties`.`id` AS `property_id`,`mark_form_properties`.`field_type` AS `field_type`,`mark_form_property_types`.`name` AS `property_type` from (((`marks` join `mark_values` on((`marks`.`id` = `mark_values`.`mark_id`))) join `mark_form_properties` on((`mark_values`.`mark_form_property_id` = `mark_form_properties`.`id`))) join `mark_form_property_types` on((`mark_form_properties`.`mark_form_property_type_id` = `mark_form_property_types`.`id`)))" );
    }
}
