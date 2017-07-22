<ul class="side-nav">
    <li class="heading"><?= __('Crossings') ?></li>
    <li><?= $this->Html->link(__('List Crossings'), ['controller' => 'Crossings', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New Crossing'), ['controller' => 'Crossings', 'action' => 'add']) ?></li>
    <li class="heading"><?= __('Mother Trees') ?></li>
    <li><?= $this->Html->link(__('List Mother Trees'), ['controller' => 'MotherTrees', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New Mother Tree'), ['controller' => 'MotherTrees', 'action' => 'add']) ?></li>
</ul>