<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;
use Cake\Http\Response;

/**
 * Trees Controller
 *
 * @property \App\Model\Table\TreesTable $Trees
 * @method \App\Model\Entity\Tree[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TreesController extends REST1Controller
{
    /**
     * Return tree
     *
     * @return Response|void
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
