<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;

/**
 * Stores request data to session and lets you prefill new forms with this data.
 *
 * You can define which values schould be stored in $memorize. To prefill a form
 * pass the entity thou the remember method (in the controller). The memorize
 * method will be called automatically.
 */
class BrainComponent extends Component {
	/**
	 * Put the fields you want to memorize in this array.
	 *
	 * @var array
	 */
	protected $memorize = [
		'date_grafted',
		'rootstock_id',
		'grafting_id',
		'date_planted',
		'date_eliminated',
        'date_labeled',
		'genuine_seedling',
		'row_id',
		'date',
		'author',
		'mark_form_id',
		'date_pollen_harvested',
		'date_impregnated',
		'date_fruit_harvested',
	];

	/**
	 * holds the session
	 */
	protected $session;

    /**
     * The request object available in the controller
     */
	protected $controllerRequest;

	/**
	 * Is called after the controller’s beforeFilter method but before the
	 * controller executes the current action handler.
	 *
	 * @param \Cake\Event\Event $event
	 */
	public function startup( \Cake\Event\EventInterface $event ) {
	    /** @var Controller $controller */
		$controller              = $event->getSubject();
		$this->controllerRequest = $controller->getRequest();

		$this->memorize();
	}

	/**
	 * Store the fields defined in $this->memorize to the session if they exist
	 * in the request data. Also store empty values.
	 */
	public function memorize() {
		$session    = $this->controllerRequest->getSession();
		$data       = $this->controllerRequest->getData();
		$controller = $this->controllerRequest->getParam('controller');
		$fields     = $this->memorize;

		$keys = array_intersect( $fields, array_keys( $data ) );

		foreach ( $keys as $key ) {
			$session->write( "Brain.$controller.$key", $data[ $key ] );
		}
	}

	/**
	 * Prepopulate $entity with memorized values. If $entity is a string, use it
	 * as key to get the memorized value and return it. If $fields are defined
	 * do only prepopulate the defined fields.
	 *
	 * @param \Cake\ORM\Entity|String $entity
	 * @param array|null $fields
	 *
	 * @return \Cake\ORM\Entity
	 */
	public function remember( $entity, $fields = null ) {
		$session    = $this->controllerRequest->getSession();
		$controller = $this->controllerRequest->getParam('controller');

		if ( ! isset( $session->read( 'Brain' )[ $controller ] ) ) {
			return $entity;
		}

		$memory = $session->read( 'Brain' )[ $controller ];

		if ( is_string( $entity ) ) {
			return $memory[ $entity ];
		}

		if ( null === $fields ) {
			$fields = $this->memorize;
		}

		$keys = array_intersect( $fields, array_keys( $memory ) );

		foreach ( $keys as $key ) {
			if ( empty( $entity[ $key ] ) ) {
				$entity[ $key ] = $memory[ $key ];
			}
		}

		return $entity;
	}


}
