<?php
use Migrations\AbstractSeed;

/**
 * MarkForms seed.
 */
class MarkFormsSeed extends AbstractSeed
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
        $date = date('Y-m-d H:i:s');
        $data = [
            [
                'name' => 'Bonitur Mai',
                'description' => 'Die folgenden Werte werden jedes Jahr im Mai erfasst.',
                'created' => $date,
                'modified' => $date,
            ],
            [
                'name' => 'Bonitur August',
                'description' => 'Die folgenden Werte werden jedes Jahr im August erfasst.',
                'created' => $date,
                'modified' => $date,
            ],
            [
                'name' => 'Fruchtwerte',
                'description' => 'Erfassung nach der Ernte',
                'created' => $date,
                'modified' => $date,
            ],
        ];

        $table = $this->table('mark_forms');
        $table->insert($data)->save();
    }
}
