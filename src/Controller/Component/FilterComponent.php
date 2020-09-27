<?php


namespace App\Controller\Component;


use Cake\ORM\Query;

class FilterComponent extends \Cake\Controller\Component
{

    private $_request;

    public function setSortingParams()
    {
        if ( ! empty( $this->_getRequest()->getQuery('sort') ) ) {
            $sort                    = $this->_getRequest()->getQuery('sort');
            $direction               = empty( $this->_getRequest()->getQuery('direction') ) ? 'asc' : $this->_getRequest()->getQuery('direction');
            $this->getController()->paginate['order'] = [ $sort => $direction ];
        }
    }

    public function setPaginationParams(Query $query)
    {
        // pagination
        $page = (int) $this->_getRequest()->getQuery('page');
        if ( ! empty( $page ) ) {
            $limit = $this->getController()->paginate['limit']
                  ?? $this->getController()->loadComponent('Paginator')->getConfig('limit');
            $totalPages = (int) ceil($query->count() / $limit );
            if ( $totalPages < $page ) {
                $page = $totalPages;

                // fake the page query param in the request, because
                // the paginator depends on it.
                $queryParams = $this->_getRequest()->getQueryParams();
                $queryParams['page'] = $page;
                $this->getController()->setRequest($this->_getRequest()->withQueryParams($queryParams));
            }

            $this->getController()->paginate['page'] = $page;
        }
    }

    private function _getRequest()
    {
        if (! $this->_request) {
            $this->_request = $this->getController()->getRequest();
        }

        return $this->_request;
    }
}
