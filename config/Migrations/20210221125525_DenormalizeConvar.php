<?php
declare( strict_types=1 );

use Migrations\AbstractMigration;

class DenormalizeConvar extends AbstractMigration {
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change() {
        $table = $this->table( 'varieties' );
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
            $this->execute('CREATE TRIGGER `variety_convar_INSERT` BEFORE INSERT ON `varieties` FOR EACH ROW SET NEW.`convar` = CONCAT((SELECT `crossing_batch` FROM `batches` WHERE `batches`.`id` = NEW.`batch_id`), ".", NEW.`code`)');
            $this->execute('CREATE TRIGGER `variety_convar_UPDATE` BEFORE UPDATE ON `varieties` FOR EACH ROW SET NEW.`convar` = CONCAT((SELECT `crossing_batch` FROM `batches` WHERE `batches`.`id` = NEW.`batch_id`), ".", NEW.`code`)');
            $this->execute('CREATE TRIGGER `batch_convar_UPDATE` AFTER UPDATE ON `batches` FOR EACH ROW UPDATE `varieties` SET `varieties`.`convar` = CONCAT(NEW.`crossing_batch`, ".", `varieties`.`code`) WHERE `varieties`.`batch_id` = NEW.`id`');
        } else {
            $this->execute('DROP TRIGGER `variety_convar_INSERT`');
            $this->execute('DROP TRIGGER `variety_convar_UPDATE`');
            $this->execute('DROP TRIGGER `batch_convar_UPDATE`');
        }

        // trigger update trigger on all existing rows
        $builder = $this->getQueryBuilder();
        $builder
            ->update('varieties')
            ->set('convar', '')
            ->execute();

        // update view query for improved performance
        $this->execute( 'DROP VIEW IF EXISTS `varieties_view`' );
        if ($this->isMigratingUp()) {
            $this->execute( "CREATE VIEW `varieties_view` AS select `varieties`.`id` AS `id`,`varieties`.`convar` AS `convar`,`varieties`.`official_name` AS `official_name`,`varieties`.`acronym` AS `acronym`,`varieties`.`plant_breeder` AS `plant_breeder`,`varieties`.`registration` AS `registration`,`varieties`.`description` AS `description`,`varieties`.`batch_id` AS `batch_id` from `varieties` where isnull(`varieties`.`deleted`)" );
        }
    }
}
