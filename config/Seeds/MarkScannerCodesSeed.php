<?php
use Migrations\AbstractSeed;

/**
 * MarkScannerCodes seed.
 */
class MarkScannerCodesSeed extends AbstractSeed
{
    private int $codeCounter = 0;

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
        $propertiesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('MarkFormProperties');
        $data = [];

        foreach( $propertiesTable->find() as $property ) {
            switch ($property->field_type) {
                case 'INTEGER':
                    $data += $this->integer($property);
                    break;
                case 'DATE':
                case 'VARCHAR':
                case 'FLOAT':
                    break;
                case 'BOOLEAN':
                    $data += $this->boolean($property);
                    break;
            }
        }

        $table = $this->table('mark_scanner_codes');
        $table->insert($data)->save();
    }

    private function integer( \App\Model\Entity\MarkFormProperty $property ) {
        $validation = $property->validation_rule;
        $min        = (int) $validation['min'];
        $max        = (int) $validation['max'];
        $data = [];

        if ($max > 9) {
            return $data;
        }

        for( $i = $min; $i <= $max; $i++ ){
            $data[] = $this->generateData($i, $property->id);
        }

        return $data;
    }

    private function boolean( \App\Model\Entity\MarkFormProperty $property ) {
        return [
            $this->generateData(1, $property->id ),
            $this->generateData(0, $property->id ),
        ];
    }

    private function generateData($value, $propertyId){
        return [
            'code' => $this->generateCode(),
            'mark_value' => $value,
            'mark_form_property_id' => $propertyId,
        ];
    }

    private function generateCode() {
        $this->codeCounter++;

        return sprintf('M%05d', $this->codeCounter);
    }
}
