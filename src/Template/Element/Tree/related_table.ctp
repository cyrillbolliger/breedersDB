<table cellpadding="0" cellspacing="0">
    <tr>
        <th scope="col" class="id"><?= __('Id') ?></th>
        <th scope="col"><?= __('Publicid') ?></th>
        <th scope="col"><?= __('Convar') ?></th>
        <th scope="col"><?= __('Row') ?></th>
        <th scope="col"><?= __('Offset') ?></th>
        <th scope="col"><?= __('Note') ?></th>
        <th scope="col"><?= __('Eliminated') ?></th>
        <th scope="col"><?= __('Modified') ?></th>
        <th scope="col" class="actions"><?= __('Actions') ?></th>
    </tr>
    <?php foreach ($trees as $tree): ?>
        <tr>
            <td class="id"><?= h($tree->id) ?></td>
            <td><?= h($tree->publicid) ?></td>
            <td><?= h($tree->convar) ?></td>
            <td><?= h($tree->row_code) ?></td>
            <td><?= h($tree->offset) ?></td>
            <td><?= $tree->note ? $this->Html->link(__('Read'), ['controller' => 'trees', 'action' => 'view', $tree->id]) : '' ?></td>
            <td><?= $tree->date_eliminated ? 'eliminated' : '' ?></td>
            <td><?= h($tree->modified) ?></td>
            <td class="actions">
                <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                    ['controller' => 'Trees', 'action' => 'view', $tree->id],
                    ['escapeTitle' => false, 'alt' => __('View')]) ?>
                <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                    ['controller' => 'Trees', 'action' => 'edit', $tree->id],
                    ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                    ['controller' => 'Trees', 'action' => 'delete', $tree->id], [
                        'escapeTitle' => false,
                        'alt'         => __('Delete'),
                        'confirm'     => __('Are you sure you want to delete "{0}" (id: {1})?', $tree->publicid,
                            $tree->id)
                    ]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>