<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 09.12.17
 * Time: 21:16
 */

namespace App\Controller\Component;


use Cake\Collection\CollectionInterface;
use Cake\Controller\Component;
use Cake\Network\Exception\NotFoundException;

/**
 * Makes collections paginatable
 *
 * Class CollectionPaginatorComponent
 * @package App\Controller\Component
 */
class CollectionPaginatorComponent extends Component {
	// load the pagination component
	public $components = [ 'Paginator' ];
	
	/**
	 * Add the pagination parameters to the request object and return paginated results.
	 *
	 * This function is needed, because the paginator doesn't
	 * work with collections out of the box. The paginator behavior
	 * is required.
	 *
	 * @param CollectionInterface $collection
	 * @param callable $sortFunction must return either
	 *  the name of the field to sort by in dot notation
	 *  or a callable that returns the sort value itself.
	 *
	 * @return CollectionInterface the paginated collection
	 */
	public function paginate( CollectionInterface $collection, callable $sortFunction ): CollectionInterface {
		// buffer the results to perform calculations without having to reiterate the collection
		$collection->buffered();
		
		$alias = $this->_registry->getController()->loadModel()->alias();
		
		$options = $this->Paginator->mergeOptions( $alias, $this->request->query ?? [] );
		$options = $this->Paginator->checkLimit( $options );
		
		$options         += [ 'page' => 1, 'scope' => $alias ];
		$options['page'] = (int) $options['page'] < 1 ? 1 : (int) $options['page'];
		$finder          = 'all';
		
		$sortDefault = $directionDefault = false;
		if ( ! empty( $defaults['order'] ) && count( $defaults['order'] ) == 1 ) {
			$sortDefault      = key( $defaults['order'] );
			$directionDefault = current( $defaults['order'] );
		}
		
		$sort = array_key_exists( 'sort', $options ) ? $options['sort'] : $sortDefault;
		$direction = array_key_exists( 'direction', $options ) ? $options['direction'] : $directionDefault;
		$order = [ $sort => $direction ];
		
		$sorted     = $this->_sort( $collection, key( $order ), current( $order ), $sortFunction );
		
		$offset     = $options['limit'] * ( $options['page'] - 1 );
		$results    = $sorted->take( $options['limit'], $offset );
		
		$numResults = count( $results );
		$count      = $numResults ? count( $collection->toArray() ) : 0;
		
		$defaults = $this->Paginator->getDefaults( $alias, [] );
		unset( $defaults[0] );
		
		$page          = $options['page'];
		$limit         = $options['limit'];
		$pageCount     = (int) ceil( $count / $limit );
		$requestedPage = $page;
		$page          = max( min( $page, $pageCount ), 1 );
		
		$paging = [
			'finder'           => $finder,
			'page'             => $page,
			'current'          => $numResults,
			'count'            => $count,
			'perPage'          => $limit,
			'prevPage'         => ( $page > 1 ),
			'nextPage'         => ( $count > ( $page * $limit ) ),
			'pageCount'        => $pageCount,
			'sort'             => key( $order ),
			'direction'        => current( $order ),
			'limit'            => $defaults['limit'] != $limit ? $limit : null,
			'sortDefault'      => $sortDefault,
			'directionDefault' => $directionDefault,
			'scope'            => $options['scope'],
		];
		
		$paging_params = [ $alias => $paging ];
		$this->request->addParams( [ 'paging' => $paging_params ] );
		
		if ( $requestedPage > $page ) {
			throw new NotFoundException();
		}
		
		return $results;
	}
	
	/**
	 * Sort the collection
	 *
	 * @param CollectionInterface $collection
	 * @param string|null $sort the field name to sort by
	 * @param string|null $order the order. Accepted are 'asc' and 'desc'
	 * @param callable $callback must return either
	 *  the name of the field to sort by in dot notation
	 *  or a callable that returns the sort value itself.
	 *
	 * @return CollectionInterface
	 */
	private function _sort( CollectionInterface $collection, $sort, $order, callable $callback ) {
		if ( empty( $sort ) ) {
			return $collection;
		}
		
		$orders = [ 'asc' => SORT_ASC, 'desc' => SORT_DESC ];
		$type   = SORT_NATURAL;
		
		$order = empty( $order ) ? 'asc' : $order;
		$order = $orders[ $order ];
		
		$sort = $callback( $sort );
		
		return $collection->sortBy( $sort, $order, $type );
	}
}