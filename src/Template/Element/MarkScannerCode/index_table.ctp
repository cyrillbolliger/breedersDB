<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('code') ?></th>
            <th scope="col"><?= $this->Paginator->sort('mark_form_property_id', __('Property')) ?></th>
            <th scope="col"><?= $this->Paginator->sort('mark_value', __('Value')) ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($markScannerCodes as $markScannerCode): ?>
        <tr>
            <td class="id"><?= $this->Number->format($markScannerCode->id) ?></td>
            <td><?= h($markScannerCode->code) ?></td>
            <td><?= $markScannerCode->has('mark_form_property') ? $this->Html->link($markScannerCode->mark_form_property->name, ['controller' => 'MarkFormProperties', 'action' => 'view', $markScannerCode->mark_form_property->id]) : '' ?></td>
            <td><?= h($markScannerCode->mark_value) ?></td>
            <td class="actions">
                <?= $this->Html->link(__('View'), ['action' => 'view', $markScannerCode->id]) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $markScannerCode->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $markScannerCode->id], ['confirm' => __('Are you sure you want to delete {0} (id: {1})?', $markScannerCode->code, $markScannerCode->id)]) ?>
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