<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('name') ?></th>
            <th scope="col"><?= $this->Paginator->sort('field_type') ?></th>
            <th scope="col"><?= $this->Paginator->sort('mark_form_property_type_id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($markFormProperties as $markFormProperty): ?>
        <tr>
            <td class="id"><?= $this->Number->format($markFormProperty->id) ?></td>
            <td><?= h($markFormProperty->name) ?></td>
            <td><?= h($markFormProperty->field_type) ?></td>
            <td><?= $markFormProperty->has('mark_form_property_type') ? $markFormProperty->mark_form_property_type->name : '' ?></td>
            <td><?= h($markFormProperty->modified) ?></td>
            <td class="actions">
                <?= $this->Html->link(__('View'), ['action' => 'view', $markFormProperty->id]) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $markFormProperty->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $markFormProperty->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $markFormProperty->name, $markFormProperty->id)]) ?>
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
