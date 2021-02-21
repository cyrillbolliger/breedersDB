<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DenormalizeTreeConvar extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table( 'trees' );
        $table->addColumn( 'convar', 'string', [
            'default' => null,
            'limit'   => 58, // crossing code is limited to 8, batch code to 3, variety code to 45, plus 2 for the separator dots
            'null'    => false,
        ] )
              ->addIndex(
                  [
                      'convar',
                  ]
              );
        $table->update();

        // manually migrate triggers as Phinx can't handle them
        if ($this->isMigratingUp()) {
            $this->execute('CREATE TRIGGER `tree_convar_INSERT` BEFORE INSERT ON `trees` FOR EACH ROW SET NEW.`convar` = (SELECT `convar` FROM `varieties` WHERE `varieties`.`id` = NEW.`variety_id`)');
            $this->execute('CREATE TRIGGER `tree_convar_UPDATE` BEFORE UPDATE ON `trees` FOR EACH ROW SET NEW.`convar` = (SELECT `convar` FROM `varieties` WHERE `varieties`.`id` = NEW.`variety_id`)');
            $this->execute('CREATE TRIGGER `variety_tree_UPDATE` AFTER UPDATE ON `varieties` FOR EACH ROW UPDATE `trees` SET `trees`.`convar` = NEW.`convar` WHERE `trees`.`variety_id` = NEW.`id`');
        } else {
            $this->execute('DROP TRIGGER `tree_convar_INSERT`');
            $this->execute('DROP TRIGGER `tree_convar_UPDATE`');
            $this->execute('DROP TRIGGER `variety_tree_UPDATE`');
        }

        // trigger update trigger on all existing rows
        $builder = $this->getQueryBuilder();
        $builder
            ->update('trees')
            ->set('convar', '')
            ->execute();

        $this->execute( 'DROP VIEW IF EXISTS `trees_view`' );

        if ($this->isMigratingUp()) {
            $this->execute( "CREATE VIEW `trees_view` AS select `trees`.`id` AS `id`,`trees`.`publicid` AS `publicid`,`trees`.`convar` AS `convar`,`trees`.`date_grafted` AS `date_grafted`,`trees`.`date_planted` AS `date_planted`,`trees`.`date_eliminated` AS `date_eliminated`,`trees`.`date_labeled` AS `date_labeled`,`trees`.`genuine_seedling` AS `genuine_seedling`,`trees`.`offset` AS `offset`,`rows`.`code` AS `row`,`trees`.`dont_eliminate` AS `dont_eliminate`,`trees`.`note` AS `note`,`trees`.`variety_id` AS `variety_id`,`graftings`.`name` AS `grafting`,`rootstocks`.`name` AS `rootstock`,`experiment_sites`.`name` AS `experiment_site` from (((((`trees` left join `rows` on((`trees`.`row_id` = `rows`.`id`))) left join `graftings` on((`trees`.`grafting_id` = `graftings`.`id`))) left join `rootstocks` on((`trees`.`rootstock_id` = `rootstocks`.`id`))) join `experiment_sites` on((`trees`.`experiment_site_id` = `experiment_sites`.`id`)))) where isnull(`trees`.`deleted`)" );
        }
    }
}
