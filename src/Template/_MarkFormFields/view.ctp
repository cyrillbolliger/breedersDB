<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Mark Form Field'), ['action' => 'edit', $markFormField->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Mark Form Field'), ['action' => 'delete', $markFormField->id], ['confirm' => __('Are you sure you want to delete # {0}?', $markFormField->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Mark Form Fields'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mark Form Field'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Mark Forms'), ['controller' => 'MarkForms', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mark Form'), ['controller' => 'MarkForms', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Mark Form Properties'), ['controller' => 'MarkFormProperties', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mark Form Property'), ['controller' => 'MarkFormProperties', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="markFormFields view large-9 medium-8 columns content">
    <h3><?= h($markFormField->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Mark Form') ?></th>
            <td><?= $markFormField->has('mark_form') ? $this->Html->link($markFormField->mark_form->name, ['controller' => 'MarkForms', 'action' => 'view', $markFormField->mark_form->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mark Form Property') ?></th>
            <td><?= $markFormField->has('mark_form_property') ? $this->Html->link($markFormField->mark_form_property->name, ['controller' => 'MarkFormProperties', 'action' => 'view', $markFormField->mark_form_property->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($markFormField->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Priority') ?></th>
            <td><?= $this->Number->format($markFormField->priority) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($markFormField->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($markFormField->modified) ?></td>
        </tr>
    </table>
</div>
