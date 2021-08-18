<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\I18n;

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
        $locale = str_replace('_', '-', I18n::getLocale());
        $this->set('data', ['locale' => $locale]);
        $this->render('/element/ajaxreturn', AppController::LAYOUT_V2);
    }
}
