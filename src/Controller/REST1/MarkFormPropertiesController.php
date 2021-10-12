<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;

/**
 * MarkForms Controller
 *
 * @property \App\Model\Table\MarkFormPropertiesTable $MarkFormProperties
 * @method \App\Model\Entity\MarkFormProperty[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MarkFormPropertiesController extends REST1Controller
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $data = $this->MarkFormProperties
            ->find()
            ->orderAsc('name')
            ->all();
        $this->set('data', $data);
    }
}
