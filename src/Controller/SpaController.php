<?php
declare(strict_types=1);

namespace App\Controller;

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
        $this->set('data', []);
        $this->render('/element/ajaxreturn', AppController::LAYOUT_V2);
    }
}
