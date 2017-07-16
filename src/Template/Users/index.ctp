<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav'); ?>
</nav>
<div class="users index large-9 medium-8 columns content">
    <h3><?= __('Users') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('email') ?></th>
            <th scope="col"><?= $this->Paginator->sort('level') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td class="id"><?= h($user->id) ?></td>
                <td><?= h($user->email) ?></td>
                <td><?= $this->Number->format($user->level) ?></td>
                <td><?= h($this->LocalizedTime->getUserTime($user->created)) ?></td>
                <td><?= h($this->LocalizedTime->getUserTime($user->modified)) ?></td>
                <td class="actions">
                    <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                        ['action' => 'view', $user->id], ['escapeTitle' => false, 'alt' => __('View')]) ?>
                    <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                        ['action' => 'edit', $user->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                    <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                        ['action' => 'delete', $user->id], [
                            'escapeTitle' => false,
                            'alt'         => __('Delete'),
                            'confirm'     => __('Are you sure you want to delete this user: {0}?', $user->email)
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
</div>
