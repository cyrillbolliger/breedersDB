<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddFieldToTreesView extends AbstractMigration
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
        $this->execute( "CREATE OR REPLACE VIEW `trees_view` AS select `trees`.`id` AS `id`,`trees`.`publicid` AS `publicid`,`varieties_view`.`convar` AS `convar`,`trees`.`date_grafted` AS `date_grafted`,`trees`.`date_planted` AS `date_planted`,`trees`.`date_eliminated` AS `date_eliminated`,`trees`.`date_labeled` AS `date_labeled`,`trees`.`genuine_seedling` AS `genuine_seedling`,`trees`.`offset` AS `offset`,`rows`.`code` AS `row`,`trees`.`dont_eliminate` AS `dont_eliminate`,`trees`.`note` AS `note`,`trees`.`variety_id` AS `variety_id`,`graftings`.`name` AS `grafting`,`rootstocks`.`name` AS `rootstock`,`experiment_sites`.`name` AS `experiment_site`,`experiment_site_id` from (((((`trees` left join `rows` on((`trees`.`row_id` = `rows`.`id`))) left join `graftings` on((`trees`.`grafting_id` = `graftings`.`id`))) left join `rootstocks` on((`trees`.`rootstock_id` = `rootstocks`.`id`))) join `experiment_sites` on((`trees`.`experiment_site_id` = `experiment_sites`.`id`))) join `varieties_view` on((`trees`.`variety_id` = `varieties_view`.`id`))) where isnull(`trees`.`deleted`)" );
    }
}
