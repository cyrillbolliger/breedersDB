<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav'); ?>
</nav>
<div class="markFormPropertyTypes view large-9 medium-8 columns content">
    <h3><?= __('Mark Property Type:') . ' ' . h($markFormPropertyType->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($markFormPropertyType->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($markFormPropertyType->name) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Mark Properties') ?></h4>
        <?php if ( ! empty($markFormPropertyType->mark_form_properties)): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th scope="col" class="id"><?= __('Id') ?></th>
                    <th scope="col"><?= __('Name') ?></th>
                    <th scope="col"><?= __('Modified') ?></th>
                    <th scope="col" class="actions"><?= __('Actions') ?></th>
                </tr>
                <?php foreach ($markFormPropertyType->mark_form_properties as $markFormProperties): ?>
                    <tr>
                        <td class="id"><?= h($markFormProperties->id) ?></td>
                        <td><?= h($markFormProperties->name) ?></td>
                        <td><?= h($markFormProperties->modified) ?></td>
                        <td class="actions">
                            <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                                ['controller' => 'MarkFormProperties', 'action' => 'view', $markFormProperties->id],
                                ['escapeTitle' => false, 'alt' => __('View')]) ?>
                            <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                                ['controller' => 'MarkFormProperties', 'action' => 'edit', $markFormProperties->id],
                                ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                            <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
                                ['controller' => 'MarkFormProperties', 'action' => 'delete', $markFormProperties->id], [
                                    'escapeTitle' => false,
                                    'alt'         => __('Delete'),
                                    'confirm'     => __('Are you sure you want to delete "{0}" (id: {1})?',
                                        $markFormPropertyType->name, $markFormProperties->id)
                                ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
