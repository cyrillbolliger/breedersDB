<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
        <th scope="col"><?= $this->Paginator->sort('variety_id') ?></th>
        <th scope="col"><?= $this->Paginator->sort('code', __('Identification')) ?></th>
        <th scope="col"><?= $this->Paginator->sort('numb_scions') ?></th>
        <th scope="col"><?= $this->Paginator->sort('date_scions_harvest') ?></th>
        <th scope="col"><?= $this->Paginator->sort('external_use') ?></th>
        <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
        <th scope="col" class="actions noprint"><?= __('Actions') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($scionsBundles as $scionsBundle): ?>
        <tr>
            <td class="id"><?= $this->Number->format($scionsBundle->id) ?></td>
            <td><?= $scionsBundle->has('variety') ? $this->Html->link($scionsBundle->convar,
                    ['controller' => 'Varieties', 'action' => 'view', $scionsBundle->variety->id]) : '' ?></td>
            <td><?= h($scionsBundle->code) ?></td>
            <td><?= $this->Number->format($scionsBundle->numb_scions) ?></td>
            <td><?= h($scionsBundle->date_scions_harvest) ?></td>
            <td><?= h($scionsBundle->external_use) ?></td>
            <td><?= h($this->LocalizedTime->getUserTime($scionsBundle->modified)) ?></td>
            <td class="actions noprint">
                <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                    ['action' => 'view', $scionsBundle->id], ['escapeTitle' => false, 'alt' => __('View')]) ?>
                <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                    ['action' => 'edit', $scionsBundle->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                    ['action' => 'delete', $scionsBundle->id], [
                        'escapeTitle' => false,
                        'alt'         => __('Delete'),
                        'confirm'     => __('Are you sure you want to delete "{0}" (id: {1})?', $scionsBundle->code,
                            $scionsBundle->id)
                    ]) ?>
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