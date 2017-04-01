<ul class="side-nav">
        <li class="heading"><?= __('Rootstocks') ?></li>
        <li><?= $this->Html->link(__('List Rootstocks'), ['controller' => 'Rootstocks', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Rootstock'), ['controller' => 'Rootstocks', 'action' => 'add']) ?></li>
        <li>&nbsp;</li>
        <li class="heading"><?= __('Graftings') ?></li>
        <li><?= $this->Html->link(__('List Graftings'), ['controller' => 'Graftings', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Grafting'), ['controller' => 'Graftings', 'action' => 'add']) ?></li>
        <li>&nbsp;</li>
        <li class="heading"><?= __('Experiment Sites') ?></li>
        <li><?= $this->Html->link(__('List Experiment Sites'), ['controller' => 'ExperimentSites', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Experiment Site'), ['controller' => 'ExperimentSites', 'action' => 'add']) ?></li>
        <li>&nbsp;</li>
        <li class="heading"><?= __('Mark Form Property Types') ?></li>
        <li><?= $this->Html->link(__('List Mark Form Property Types'), ['controller' => 'MarkFormPropertyTypes', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mark Form Property Type'), ['controller' => 'MarkFormPropertyTypes', 'action' => 'add']) ?></li>
        <li>&nbsp;</li>
        <li class="heading"><?= __('Users') ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>