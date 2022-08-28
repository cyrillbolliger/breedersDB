<?php

declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Domain\Upload\UploadStrategy;
use App\Model\Entity\MarkFormProperty;
use App\Model\Table\MarkFormPropertiesTable;
use App\Model\Table\MarkValuesTable;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

/**
 * App\Model\Table\MarkValuesTable Test Case
 */
class MarkValuesTableTest extends TestCase
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [
        'MarkFormPropertyTypes',
        'MarkForms',
        'MarkFormProperties',
        'Marks',
        'MarkValues'
    ];

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkValuesTable
     */
    protected $MarkValuesTable;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MarkValues') ? [] : ['className' => MarkValuesTable::class];
        $this->MarkValuesTable = $this->getTableLocator()->get('MarkValues', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->MarkValuesTable);

        parent::tearDown();
    }

    /**
     * Test afterDelete method
     *
     * @return void
     * @uses \App\Model\Table\MarkValuesTable::afterDelete()
     */
    public function testAfterDelete(): void
    {
        $this->testRemoveImages();
    }

    private function testRemoveImages(): void
    {
        /**
         * add photos
         */
        $pathOrigImage = ROOT . DS . 'tests/4k.jpeg';
        $pathTmpFile = ROOT . DS . 'tests/tmp.jpeg';
        copy($pathOrigImage, $pathTmpFile);

        $ext = pathinfo($pathTmpFile, PATHINFO_EXTENSION);
        $finalFilename = substr(hash(Security::$hashType, Security::randomBytes(16), false), 0, 32);
        $finalFileDir = Configure::read('App.paths.photos') . DS . UploadStrategy::getSubdir($finalFilename);
        $pathFinalFile = $finalFileDir . DS . $finalFilename . '.' . $ext;
        $pathThumbnail = $finalFileDir . DS . $finalFilename . '-200x200.' . $ext;

        UploadStrategy::createDir($finalFileDir);
        rename($pathTmpFile, $pathFinalFile);
        copy($pathFinalFile, $pathThumbnail);

        /**
         * add photo mark property
         */
        /** @var MarkFormPropertiesTable $property */
        $property = $this->getTable('MarkFormProperties');
        $photoProperty = $property->find()->where(['field_type' => 'PHOTO'])->first();
        if (!$photoProperty) {
            /** @var MarkFormProperty $photoProperty */
            $photoProperty = $property->newEmptyEntity();
            $photoProperty->name = 'photo';
            $photoProperty->validation_rule = '';
            $photoProperty->field_type = 'PHOTO';
            $photoProperty->mark_form_property_type = $this->getTable('MarkFormPropertyTypes')
                ->find()
                ->firstOrFail();
            $photoProperty->tree_property = true;
            $photoProperty->variety_property = false;
            $photoProperty->batch_property = false;
            $property->save($photoProperty);
        }

        /**
         * add mark value
         */
        $template = $this->MarkValuesTable->find()->firstOrFail()->toArray();
        unset($template['id']);
        $entity = $this->MarkValuesTable->newEntity($template);
        $entity->mark_form_property_id = $photoProperty->id;
        $entity->value = $finalFilename . '.' . $ext;
        $this->MarkValuesTable->save($entity);

        /**
         * delete mark value - actual test starts here
         */
        $this->MarkValuesTable->delete($entity);

        self::assertFileDoesNotExist($pathFinalFile);
        self::assertFileDoesNotExist($pathThumbnail);
    }
}
