<?php
declare(strict_types=1);

namespace App\Test\Util;

trait AuthenticateTrait {
    protected function authenticate() : void{
        $this->ensureUsersFixture();

        $this->session([
            'Auth' => [
                'User' => $this->getUser(),
            ]
        ]);

        parent::setUp();
    }

    private function ensureUsersFixture(): void {
        if (! isset($this->dependsOnFixture) ) {
            $this->dependsOnFixture = [];
        }

        if (! in_array('Users', $this->dependsOnFixture) ){
            $this->dependsOnFixture[] = 'Users';
        }

        $this->ensureDependingFixtures();
    }

    private function getUser(): array {
        $table = \Cake\ORM\TableRegistry::getTableLocator()->get( 'users' );
        return $table->find()->first()->toArray();
    }
}
