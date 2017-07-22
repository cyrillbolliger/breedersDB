<ul class="side-nav">
    <?php foreach ($queryGroups as $queryGroup): ?>
        <li class="heading">
            <?= h($queryGroup->code); ?>
            <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                ['controller' => 'QueryGroups', 'action' => 'edit', $queryGroup->id],
                ['escapeTitle' => false, 'alt' => __('Edit'), 'class' => 'nav-action-icon']) ?>
            <?php if (empty($queryGroup->queries)): ?>
                <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                    ['controller' => 'QueryGroups', 'action' => 'delete', $queryGroup->id], [
                        'escapeTitle' => false,
                        'alt'         => __('Delete'),
                        'confirm'     => __('Are you sure you want to delete this group: {0}?', $queryGroup->code),
                        'class'       => 'nav-action-icon'
                    ]) ?>
            <?php endif; ?>
        </li>
        <?php foreach ($queryGroup->queries as $query): ?>
            <li><?= $this->Html->link(h($query->code), ['action' => 'view', $query->id]) ?></li>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <li class="heading"><?= $this->Html->link(__('New Group'),
                ['controller' => 'QueryGroups', 'action' => 'add'],
                ['class' => 'nav-title-link']); ?></li>
</ul>