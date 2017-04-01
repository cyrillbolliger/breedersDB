<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Mark Form Fields'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Mark Forms'), ['controller' => 'MarkForms', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mark Form'), ['controller' => 'MarkForms', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mark Form Properties'), ['controller' => 'MarkFormProperties', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mark Form Property'), ['controller' => 'MarkFormProperties', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="markFormFields form large-9 medium-8 columns content">
    <?= $this->Form->create($markFormField) ?>
    <fieldset>
        <legend><?= __('Add Mark Form Field') ?></legend>
        <?php
            echo $this->Form->input('priority');
            echo $this->Form->input('mark_form_id', ['options' => $markForms]);
            echo $this->Form->input('mark_form_property_id', ['options' => $markFormProperties]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
