<?php
declare(strict_types=1);

namespace App\Test\Util;


use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\TestFixture;

trait DependsOnFixtureTrait {
    public function __construct() {

        if ( ! isset( $this->dependsOnFixture ) ) {
            throw new \RuntimeException( 'Trait DependsOnFixtureTrait used, but $dependsOnFixture field not defined. Add a protected array $dependsOnFixture to your class and define the needed fixtures.' );
        }

        if ( ! empty( $this->dependsOnFixture ) ) {
            $this->ensureDependingFixtures();
        }

        parent::__construct();
    }

    public function ensureDependingFixtures(): void {
        foreach ( $this->dependsOnFixture as $tableName ) {
            if ( ! $this->hasEnoughData( $tableName ) ) {
                $this->runFixture( $tableName );
            }
        }
    }

    private function hasEnoughData( string $tableName ): bool {
        return $this->getTable( $tableName )
                    ->find()
                    ->count() > 1; // the first record is often special so we need more.
    }

    private function runFixture( string $tableName ): void {
        $className = 'App\\Test\\Fixture\\' . $tableName . 'Fixture';
        /** @var TestFixture $fixture */
        $fixture = new $className();
        $fixture->init();

        $table    = $this->getTable( $tableName );
        $entities = $table->newEntities( $fixture->records );
        $table->saveManyOrFail( $entities );
    }

    private function getTable( string $tableName ) {
        if ( \Cake\ORM\TableRegistry::getTableLocator()->exists($tableName) ) {
            return \Cake\ORM\TableRegistry::getTableLocator()->get( $tableName );
        }

        return \Cake\ORM\TableRegistry::getTableLocator()
                                      ->get( $tableName, [
                                          'connection' => ConnectionManager::get( 'test' ),
                                      ] );
    }
}
