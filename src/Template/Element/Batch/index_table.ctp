<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('crossing_batch', __('Crossing.Batch')) ?></th>
            <th scope="col"><?= $this->Paginator->sort('date_sowed') ?></th>
            <th scope="col"><?= $this->Paginator->sort('seed_tray') ?></th>
            <th scope="col"><?= $this->Paginator->sort('date_planted') ?></th>
            <th scope="col"><?= $this->Paginator->sort('patch') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($batches as $batch): ?>
        <tr>
            <td class="id"><?= h($batch->id) ?></td>
            <td><?= h($batch->crossing_batch) ?></td>
            <td><?= h($batch->date_sowed) ?></td>
            <td><?= h($batch->seed_tray) ?></td>
            <td><?= h($batch->date_planted) ?></td>
            <td><?= h($batch->patch) ?></td>
            <td><?= h($batch->modified) ?></td>
            <td class="actions">
                <?= $this->Html->link(__('View'), ['action' => 'view', $batch->id]) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $batch->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $batch->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $batch->crossing_batch, $batch->id)]) ?>
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