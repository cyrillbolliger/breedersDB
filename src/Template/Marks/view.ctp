<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>

<?php 
    if ( $mark->has('tree') ) {
        $object = $this->Html->link(__('Tree:') .' '. $mark->tree->publicid, ['controller' => 'Trees', 'action' => 'view', $mark->tree->id]);
    } elseif( $mark->has('variety') ) {
        $object = $this->Html->link(__('Variety:') .' '. $mark->variety->convar, ['controller' => 'Varieties', 'action' => 'view', $mark->variety->id]);
    } elseif ( $mark->has('batch') ) {
        $object = $this->Html->link(__('Batch:') .' '. $mark->batch->crossing_batch, ['controller' => 'Batches', 'action' => 'view', $mark->batch->id]);
    } else {
        $object = '';
    }
?>

<div class="marks view large-9 medium-8 columns content">
    <h3><?= __('Mark:') .' '.h($mark->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($mark->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date') ?></th>
            <td><?= h($mark->date) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Author') ?></th>
            <td><?= h($mark->author) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mark Form') ?></th>
            <td><?= $mark->has('mark_form') ? $this->Html->link($mark->mark_form->name, ['controller' => 'MarkForms', 'action' => 'view', $mark->mark_form->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Object') ?></th>
            <td><?= $object ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($mark->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($mark->modified) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Values') ?></h4>
        <?php if (!empty($mark->mark_values)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col" class="id"><?= __('Id') ?></th>
                <th scope="col"><?= __('Property') ?></th>
                <th scope="col"><?= __('Value') ?></th>
                <th scope="col"><?= __('Exceptional Mark') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($mark->mark_values as $markValues): ?>
            <?php 
                $value = substr($markValues->value, 0, 35) != $markValues->value ? substr($markValues->value, 0, 25) .'...' : $markValues->value; 
                $exceptional = $markValues->exceptional_mark ? __('Yes') : '';
            ?>
            <tr>
                <td class="id"><?= h($markValues->id) ?></td>
                <td><?= h($markValues->mark_form_property->name) ?></td>
                <td><?= h($value) ?></td>
                <td><?= $exceptional ?></td>
                <td><?= h($markValues->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>', ['controller' => 'MarkValues', 'action' => 'edit', $markValues->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                    <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>', ['controller' => 'MarkValues', 'action' => 'delete', $markValues->id], ['escapeTitle' => false, 'alt' => __('Delete'), 'confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $markValues->mark_form_property->name, $markValues->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
