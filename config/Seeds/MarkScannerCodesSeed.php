<?php

use App\Generator\MarkScannerCodesGenerator;
use Migrations\AbstractSeed;

/**
 * MarkScannerCodes seed.
 */
class MarkScannerCodesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $generator = new MarkScannerCodesGenerator();
        $data = $generator->generate();

        $table = $this->table('mark_scanner_codes');
        $table->insert($data)->save();
    }
}
