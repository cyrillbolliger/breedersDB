<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('convar') ?></th>
            <th scope="col"><?= $this->Paginator->sort('official_name') ?></th>
            <th scope="col"><?= $this->Paginator->sort('acronym') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($varieties as $variety): ?>
        <tr>
            <td class="id"><?= $this->Number->format($variety->id) ?></td>
            <td><?= h($variety->convar) ?></td>
            <td><?= h($variety->official_name) ?></td>
            <td><?= h($variety->acronym) ?></td>
            <td><?= h($variety->created) ?></td>
            <td><?= h($variety->modified) ?></td>
            <td class="actions">
                <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>', ['action' => 'view', $variety->id], ['escapeTitle' => false, 'alt' => __('View')]) ?>
                <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>', ['action' => 'edit', $variety->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>', ['action' => 'delete', $variety->id], ['escapeTitle' => false, 'alt' => __('Delete'), 'confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $variety->convar, $variety->id)]) ?>
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
