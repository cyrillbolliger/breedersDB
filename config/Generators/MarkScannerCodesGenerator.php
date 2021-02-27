<?php
namespace App\Generator;

/**
 * MarkScannerCodes generator.
 */
class MarkScannerCodesGenerator
{
    private int $codeCounter = 0;


    public function generate()
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

        return $data;
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
