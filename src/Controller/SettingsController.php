<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Domain\Settings;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
    }

    /**
     * Edit method
     *
     * @param string|null $key Setting key.
     *
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function edit(string $key)
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $value = $this->request->getData($key);
            $valid = false;
            $error = __('Invalid setting.');

            switch ($key) {
                case Settings::ZPL_DRIVER_OFFSET_LEFT:
                    $value = (int)$value;
                    $valid = $value >= 0 && $value <= 100;
                    $error = __('Invalid offset. Accepted values are between 0 and 100.');
                    if ($valid) {
                        Settings::setZplDriverOffsetLeft($value);
                    }
                    break;

                default:
                    $this->Flash->error(__('Setting not found.'));
                    return $this->redirect(['action' => 'index']);
            }

            if ($valid) {
                $this->Flash->success(__('Setting saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error($error);
            }
        }

        switch ($key) {
            case Settings::ZPL_DRIVER_OFFSET_LEFT:
                $name = __('Printer label offset left');
                $type = 'number';
                $label = __('How many millimeters should the label be shifted to the right? Value must be between 0 and 100.');
                $value = Settings::getZplDriverOffsetLeft();
                break;

            default:
                $this->Flash->error(__('Setting not found.'));
                return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('key', 'value', 'name', 'type', 'label'));
    }
}
