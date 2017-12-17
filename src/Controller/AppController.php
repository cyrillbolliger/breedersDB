<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Database\Type;
use Cake\I18n\FrozenDate;
use Cake\I18n\Date;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\Core\Configure;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	
	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like loading components.
	 *
	 * e.g. `$this->loadComponent('Security');`
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();
		
		$this->loadComponent( 'RequestHandler' );
		$this->loadComponent( 'Flash' );
		
		/*
		 * Enable the following components for recommended CakePHP security settings.
		 * see http://book.cakephp.org/3.0/en/controllers/components/security.html
		 */
		$this->loadComponent( 'Security' );
		$this->loadComponent( 'Csrf' );
		
		// needed for Xety/Cake3CookieAuth
		$this->loadComponent( 'Cookie' );
		
		$this->loadComponent( 'Auth', [
			'authorize'            => 'Controller',
			'authenticate'         => [
				'Form' => [
					'fields' => [
						'username' => 'email',
						'password' => 'password'
					]
				],
				'Xety/Cake3CookieAuth.Cookie'
			],
			'loginAction'          => [
				'controller' => 'Users',
				'action'     => 'login'
			],
			'loginRedirect'        => [
				'controller' => 'ExperimentSites',
				'action'     => 'select'
			],
			'logoutRedirect'       => [
				'controller' => 'Users',
				'action'     => 'login'
			],
			'unauthorizedRedirect' => $this->referer() // If unauthorized, return them to page they were just on
		] );
		
		// Allow the display action so our pages controller
		// continues to work.
		$this->Auth->allow( [ 'display' ] );
		
		// Set format to accept localized date, time and datetime input
		Type::build( 'time' )->useLocaleParser()->setLocaleFormat( __x( 'time format', 'HH:mm' ) );
		Type::build( 'date' )->useLocaleParser()->setLocaleFormat( __x( 'date format', 'dd.MM.yyyy' ) );
		Type::build( 'datetime' )->useLocaleParser()->setLocaleFormat( __x( 'datetime format', 'dd.MM.yyyy HH:mm' ) );
		
		FrozenTime::setToStringFormat( __x( 'datetime format', 'dd.MM.yyyy HH:mm' ) );
		FrozenDate::setToStringFormat( __x( 'date format', 'dd.MM.yyyy' ) );
		Time::setToStringFormat( __x( 'datetime format', 'dd.MM.yyyy HH:mm' ) );
		Date::setToStringFormat( __x( 'date format', 'dd.MM.yyyy' ) );
	}
	
	/**
	 * Set default access roules
	 *
	 * @param type $user
	 *
	 * @return boolean
	 */
	public function isAuthorized( $user ) {
		// grant access to everything for users with level 0
		if ( isset( $user['level'] ) && 0 === $user['level'] ) {
			return true;
		}
		
		// all others must be given access in the specific 
		// controller by overwriting this method
		return false;
	}
	
	/**
	 * Before render callback.
	 *
	 * @param \Cake\Event\Event $event The beforeRender event.
	 *
	 * @return \Cake\Network\Response|null|void
	 */
	public function beforeRender( Event $event ) {
		if ( ! array_key_exists( '_serialize', $this->viewVars ) &&
		     in_array( $this->response->type(), [ 'application/json', 'application/xml' ] )
		) {
			$this->set( '_serialize', true );
		}
		
		// This is done here instead of the bootstrap to not affect the debug kit
		$this->setResourcesUrl();
	}
	
	private function setResourcesUrl() {
		$branch = 'dist/';
		
		if ( Configure::read( 'debug' ) ) {
			$branch = 'dev/';
		}
		
		Configure::write( 'App.cssBaseUrl', $branch . Configure::read( 'App.cssBaseUrl' ) );
		Configure::write( 'App.jsBaseUrl', $branch . Configure::read( 'App.jsBaseUrl' ) );
		Configure::write( 'App.imgBaseUrl', $branch . Configure::read( 'App.imgBaseUrl' ) );
	}
	
	public function beforeFilter( Event $event ) {
		parent::beforeFilter( $event );
		
		// Automaticaly login
		if ( ! $this->Auth->user() && $this->Cookie->read( 'CookieAuth' ) ) {
			
			$user = $this->Auth->identify();
			if ( $user ) {
				$this->Auth->setUser( $user );
			} else {
				$this->Cookie->delete( 'CookieAuth' );
			}
		}
		
		/**
		 *  redirect to experiment site selection if none is stored in session
		 */
		$controller = $this->request->params['controller'];
		$action     = $this->request->params['action'];
		$arg1       = isset( $this->request->params['pass'][0] ) ? $this->request->params['pass'][0] : null;
		$session    = $this->request->session();
		
		// exclude this controllers an actions form redirect
		$excluded = [
			'controllers' => [
				'ExperimentSites',
				'Users',
			],
			'actions'     => [
				'select',
				'login',
			],
		];
		
		if ( ! in_array( $controller, $excluded['controllers'] ) && ! in_array( $action, $excluded['actions'] ) ) {
			// if no experiment site is stored in session
			if ( empty( $session->read( 'experiment_site_id' ) ) ) {
				// keep intended desination route
				$session->write( 'redirect_after_action', [ 'controller' => $controller, 'action' => $action, $arg1 ] );
				
				// and redirect user to select an experiment site
				return $this->redirect( [ 'controller' => 'ExperimentSites', 'action' => 'select' ] );
			}
		}
		
		/**
		 * write users time zone to session
		 */
		if ( empty( $session->read( 'time_zone' ) ) ) {
			$session->write( 'time_zone', $this->Auth->user( 'time_zone' ) );
		}
	}
}
