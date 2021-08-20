<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\AppController;

/**
 * Trees Controller
 *
 * @property \App\Model\Table\TreesTable $Trees
 * @method \App\Model\Entity\Tree[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TreesController extends AppController
{
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        $this->viewBuilder()
             ->setClassName('Json')
             ->setOption('serialize', ['data']);

        parent::beforeRender($event);
    }

    /**
     * Return tree
     */
    public function getTree() {
        $allowed_fields = [ 'publicid' ];

        if ( empty( $this->request->getQuery('fields') )
             || ! array_intersect( $allowed_fields, $this->request->getQuery('fields') )
        ) {
            return $this->response->withStatus(422, 'Invalid query parameter.');
        }

        $publicid = $this->request->getQuery('term');
        $tree = $this->Trees->getByPublicId( $publicid );

        if (! $tree) {
            return $this->response->withStatus(404, "No tree found with publicid: $publicid");
        }

        $this->set( 'data', $tree );
    }
}
