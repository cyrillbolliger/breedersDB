<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\I18n;
use Cake\Routing\Router;

/**
 * Spa Controller
 */
class SpaController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $apiUrl = Router::fullBaseUrl().'/api/1';
        $locale = str_replace('_', '-', I18n::getLocale());
        $this->set('data', ['locale' => $locale, 'apiUrl' => $apiUrl, 'user' => $this->Auth->user()]);
        $this->render('/element/ajaxreturn', AppController::LAYOUT_V2);
    }
}
