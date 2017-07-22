<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Query Group'), ['action' => 'add']) ?></li>
    </ul>
</nav>
<div class="queryGroups index large-9 medium-8 columns content">
    <h3><?= __('Query Groups') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('code') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($queryGroups as $queryGroup): ?>
            <tr>
                <td><?= $this->Number->format($queryGroup->id) ?></td>
                <td><?= h($queryGroup->code) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $queryGroup->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queryGroup->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $queryGroup->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queryGroup->id)]) ?>
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
