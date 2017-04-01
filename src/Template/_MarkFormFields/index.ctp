<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Mark Form Field'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mark Forms'), ['controller' => 'MarkForms', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mark Form'), ['controller' => 'MarkForms', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mark Form Properties'), ['controller' => 'MarkFormProperties', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mark Form Property'), ['controller' => 'MarkFormProperties', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="markFormFields index large-9 medium-8 columns content">
    <h3><?= __('Mark Form Fields') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('priority') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mark_form_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mark_form_property_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($markFormFields as $markFormField): ?>
            <tr>
                <td><?= $this->Number->format($markFormField->id) ?></td>
                <td><?= $this->Number->format($markFormField->priority) ?></td>
                <td><?= $markFormField->has('mark_form') ? $this->Html->link($markFormField->mark_form->name, ['controller' => 'MarkForms', 'action' => 'view', $markFormField->mark_form->id]) : '' ?></td>
                <td><?= $markFormField->has('mark_form_property') ? $this->Html->link($markFormField->mark_form_property->name, ['controller' => 'MarkFormProperties', 'action' => 'view', $markFormField->mark_form_property->id]) : '' ?></td>
                <td><?= h($markFormField->created) ?></td>
                <td><?= h($markFormField->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $markFormField->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $markFormField->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $markFormField->id], ['confirm' => __('Are you sure you want to delete # {0}?', $markFormField->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
