<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveScannerCodes extends AbstractMigration
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
        $this->table( 'mark_scanner_codes' )->drop();
    }
}
