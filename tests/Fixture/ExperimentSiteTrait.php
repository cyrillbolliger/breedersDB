<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Model\Entity\ExperimentSite;
use Cake\ORM\Entity;

trait ExperimentSiteTrait {
    protected function setSite() : void{
        $this->ensureExperimentSiteFixture();

        $site = $this->getExerimentSite();

        $this->session([
            'experiment_site_id' => $site->id,
            'experiment_site_name' => $site->name
        ]);

        parent::setUp();
    }

    private function ensureExperimentSiteFixture(): void {
        if (! isset($this->dependsOnFixture) ) {
            $this->dependsOnFixture = [];
        }

        if (! in_array('ExperimentSites', $this->dependsOnFixture) ){
            $this->dependsOnFixture[] = 'ExperimentSites';
        }

        $this->ensureDependingFixtures();
    }

    private function getExerimentSite(): ExperimentSite {
        $table = \Cake\ORM\TableRegistry::getTableLocator()->get( 'ExperimentSites' );
        return $table->find()->first();
    }
}
