<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
        <th scope="col"><?= $this->Paginator->sort('code', __('Identification')) ?></th>
        <th scope="col"><?= __('Publicid') ?></th>
        <th scope="col"><?= $this->Paginator->sort('planed') ?></th>
        <th scope="col"><?= $this->Paginator->sort('numb_portions') ?></th>
        <th scope="col"><?= $this->Paginator->sort('numb_flowers') ?></th>
        <th scope="col"><?= $this->Paginator->sort('numb_seeds') ?></th>
        <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
        <th scope="col" class="actions"><?= __('Actions') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($motherTrees as $motherTree): ?>
        <tr>
            <td class="id"><?= h($motherTree->id) ?></td>
            <td><?= h($motherTree->code) ?></td>
            <td><?= $motherTree->has('tree') ? h($motherTree->tree->publicid) : __('Mother tree not set.') ?></td>
            <td><?= h($motherTree->planed) ?></td>
            <td><?= $this->Number->format($motherTree->numb_portions) ?></td>
            <td><?= $this->Number->format($motherTree->numb_flowers) ?></td>
            <td><?= $this->Number->format($motherTree->numb_seeds) ?></td>
            <td><?= h($this->LocalizedTime->getUserTime($motherTree->modified)) ?></td>
            <td class="actions">
                <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                    ['action' => 'view', $motherTree->id], ['escapeTitle' => false, 'alt' => __('View')]) ?>
                <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                    ['action' => 'edit', $motherTree->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                    ['action' => 'delete', $motherTree->id], [
                        'escapeTitle' => false,
                        'alt'         => __('Delete'),
                        'confirm'     => __('Are you sure you want to delete "{0}" (id: {1})?', $motherTree->code,
                            $motherTree->id)
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
