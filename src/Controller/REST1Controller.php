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
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class REST1Controller extends Controller {

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void {
        parent::initialize();

        $this->loadComponent( 'RequestHandler' );

        $this->loadComponent( 'Auth', [
            'authorize'            => 'Controller',
            'authenticate'         => [
                'Form' => [
                    'fields' => [
                        'username' => 'email',
                        'password' => 'password'
                    ]
                ],
            ],
            'loginAction'          => [
                'controller' => 'Users',
                'action'     => 'login'
            ],
            'logoutRedirect'       => [
                'controller' => 'Users',
                'action'     => 'login'
            ],
            'unauthorizedRedirect' => false
        ] );
    }

    /**
     * Set default access rules
     *
     * @param  array  $user
     *
     * @return boolean
     */
    public function isAuthorized( array $user ): bool
    {
        // grant access to everything to any connected user
        if ( !empty( $user ) ) {
            return true;
        }

        // all others must be given access in the specific
        // controller by overwriting this method
        return false;
    }

    public function beforeRender(EventInterface $event) {
        $this->viewBuilder()
             ->setClassName('Json')
             ->setOption('serialize', ['data']);
    }

    public function beforeFilter( EventInterface $event ) {
        parent::beforeFilter( $event );

        $this->maybeCreateSessionDir();

        // write users time zone to session
        if ( empty( $this->request->getSession()->read( 'time_zone' ) ) ) {
            $this->request->getSession()->write( 'time_zone', $this->Auth->user( 'time_zone' ) );
        }

        // disable authentication error flash message
        if ( ! $this->Auth->user() ) {
            $this->Auth->setConfig( 'authError', false );
        }
    }

    /**
     * Create the tmp/sessions directory if needed
     */
    private function maybeCreateSessionDir(): void
    {
        if ( 'cake' !== Configure::read( 'Session.defaults' ) ) {
            // no session directory needed
            return;
        }

        $sessionsDir = TMP . 'sessions';

        if ( ! is_dir($sessionsDir)
             && ! mkdir($sessionsDir, 0700, true)
             && ! is_dir($sessionsDir)
        ) {
            throw new \RuntimeException(sprintf('Failed to create sessions directory: %s', $sessionsDir));
        }
    }
}
