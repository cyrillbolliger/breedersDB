<?php
use Migrations\AbstractSeed;

/**
 * MarkFormPropertyTypes seed.
 */
class MarkFormPropertyTypesSeed extends AbstractSeed
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
        $data = [
            [
                'name' => 'Bonitur',
            ],
            [
                'name' => 'Behandlung',
            ],
            [
                'name' => 'Hinweis',
            ],
            [
                'name' => 'Muster',
            ],
        ];

        $table = $this->table('mark_form_property_types');
        $table->insert($data)->save();
    }
}
