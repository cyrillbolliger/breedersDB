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
use Cake\Database\TypeFactory;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
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

    protected const LAYOUT_V1 = 'default-v1';
    protected const LAYOUT_V2 = 'default-v2';

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(): void {
        parent::initialize();

        $this->loadComponent( 'RequestHandler' );
        $this->loadComponent( 'Flash' );
        $this->loadComponent( 'FormProtection' );

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

        FrozenTime::setToStringFormat( __x( 'datetime format', 'dd.MM.yyyy HH:mm' ) );
        FrozenDate::setToStringFormat( __x( 'date format', 'dd.MM.yyyy' ) );

        Configure::write('localizedDate', true);
    }

    /**
     * Set default access roules
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

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     *
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(\Cake\Event\EventInterface $event) {
        // if no layout is specified, use the v1 layout
        if ( ! $this->viewBuilder()->getLayout() && ! $this->viewBuilder()->getTemplate()) {
            $this->viewBuilder()->setLayout(self::LAYOUT_V1);
        }

        // This is done here instead of the bootstrap to not affect the debug kit
        $this->setResourcesUrl();
    }

    private function setResourcesUrl(): void
    {
        switch ($this->viewBuilder()->getLayout()) {
            case self::LAYOUT_V1:
                $this->setResourcesUrlV1();
                break;
            case self::LAYOUT_V2:
                $this->setResourcesUrlV2();
                break;
            default:
                // do not load any resources
        }
    }

    public function beforeFilter( \Cake\Event\EventInterface $event ) {
        parent::beforeFilter( $event );

        $this->maybeCreateSessionDir();

        /**
         *  redirect to experiment site selection if none is stored in session
         */
        $controller = $this->request->getParam( 'controller' );
        $action     = $this->request->getParam( 'action' );
        $arg1       = $this->request->getParam( 'pass.0' ) ? $this->request->getParam( 'pass.0' ) : null;
        $session    = $this->request->getSession();

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

        /**
         * disable authentication error flash message
         */
        if ( ! $this->Auth->user() ) {
            $this->Auth->setConfig( 'authError', false );
        }
    }

    /**
     * Create the tmp/sessions directory if needed
     */
    private function maybeCreateSessionDir() {
        if ( 'cake' !== Configure::read( 'Session.defaults' ) ) {
            // no session directory needed
            return;
        }

        $sessionsDir = TMP . 'sessions';

        if (!is_dir($sessionsDir)
            && !mkdir($sessionsDir, 0700, true)
            && !is_dir($sessionsDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $sessionsDir));
        }
    }

    private function setResourcesUrlV1(): void
    {
        $branch = Configure::read('debug') ? 'dev' : 'dist';

        Configure::write('App.cssBaseUrl', "v1/$branch/".Configure::read('App.cssBaseUrl'));
        Configure::write('App.jsBaseUrl', "v1/$branch/".Configure::read('App.jsBaseUrl'));
        Configure::write('App.imgBaseUrl', "v1/$branch/".Configure::read('App.imgBaseUrl'));
    }

    private function setResourcesUrlV2(): void
    {
        $dev = (bool) Configure::read('debug');
        $localhost = $_SERVER['SERVER_NAME'] === 'localhost';

        if ($dev && $localhost) {
            $css = []; //['http://localhost:8080/vendor.css', 'http://localhost:8080/app.css'];
            $js = ['http://localhost:8080/vendor.js', 'http://localhost:8080/app.js'];
        } else {
            $css = $this->getV2ResourceRelUrls('css');
            $js  = $this->getV2ResourceRelUrls('js');
        }

        $this->set('css', $css);
        $this->set('js', $js);
    }

    /**
     * @return array|string|string[]|null
     */
    private function getV2ResourceRelUrls(string $type): string|array|null
    {
        $glob  = "{app,vendor}.*.$type";
        $path  = WWW_ROOT."v2/dist/spa/$type/$glob";
        $files = glob($path, GLOB_BRACE | GLOB_ERR);

        return preg_replace('/^.*?\/webroot(?=\/)/', '', $files);
    }
}
