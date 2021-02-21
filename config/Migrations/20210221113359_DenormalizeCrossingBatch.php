<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DenormalizeCrossingBatch extends AbstractMigration
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
        $table = $this->table( 'batches' );
        $table->addColumn( 'crossing_batch', 'string', [
            'default' => null,
            'limit'   => 12, // crossing code is limited to 8, batch code to 3, plus one for the separator dot
            'null'    => false,
        ] )
              ->addIndex(
                  [
                      'crossing_batch',
                  ]
              );
        $table->update();

        // manually migrate triggers as Phinx can't handle them
        if ($this->isMigratingUp()) {
            $this->execute('CREATE TRIGGER `batch_crossing_batch_INSERT` BEFORE INSERT ON `batches` FOR EACH ROW SET NEW.`crossing_batch` = CONCAT((SELECT `code` FROM `crossings` WHERE `crossings`.`id` = NEW.`crossing_id`), ".", NEW.`code`)');
            $this->execute('CREATE TRIGGER `batch_crossing_batch_UPDATE` BEFORE UPDATE ON `batches` FOR EACH ROW SET NEW.`crossing_batch` = CONCAT((SELECT `code` FROM `crossings` WHERE `crossings`.`id` = NEW.`crossing_id`), ".", NEW.`code`)');
            $this->execute('CREATE TRIGGER `crossing_crossing_batch_UPDATE` AFTER UPDATE ON `crossings` FOR EACH ROW UPDATE `batches` SET `batches`.`crossing_batch` = CONCAT(NEW.`code`, ".", `batches`.`code`) WHERE `batches`.`crossing_id` = NEW.`id`');
        } else {
            $this->execute('DROP TRIGGER `batch_crossing_batch_INSERT`');
            $this->execute('DROP TRIGGER `batch_crossing_batch_UPDATE`');
            $this->execute('DROP TRIGGER `crossing_crossing_batch_UPDATE`');
        }

        // trigger update trigger on all existing rows
        $builder = $this->getQueryBuilder();
        $builder
            ->update('batches')
            ->set('crossing_batch', '')
            ->execute();

        // update view query for improved performance
        $this->execute( 'DROP VIEW IF EXISTS `batches_view`' );
        if ($this->isMigratingUp()) {
            $this->execute( "CREATE VIEW `batches_view` AS select `batches`.`id` AS `id`,`batches`.`crossing_batch` AS `crossing_batch`,`batches`.`date_sowed` AS `date_sowed`,`batches`.`numb_seeds_sowed` AS `numb_seeds_sowed`,`batches`.`numb_sprouts_grown` AS `numb_sprouts_grown`,`batches`.`seed_tray` AS `seed_tray`,`batches`.`date_planted` AS `date_planted`,`batches`.`numb_sprouts_planted` AS `numb_sprouts_planted`,`batches`.`patch` AS `patch`,`batches`.`note` AS `note`,`batches`.`crossing_id` AS `crossing_id` from `batches` where isnull(`batches`.`deleted`)" );
        }
    }
}
