<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= __('Object'); ?></th>
            <th scope="col" class="index_inline_list"><?= __('Marks') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($marks as $mark): ?>
            <?php
                if ( $mark->has('tree') ) {
                    $object = $this->Html->link(__('Tree:').' '.$mark->tree->publicid, ['controller' => 'Trees', 'action' => 'view', $mark->tree->id]);
                } elseif( $mark->has('variety') ) {
                    $object = $this->Html->link(__('Variety:').' '.$mark->variety->convar, ['controller' => 'Varieties', 'action' => 'view', $mark->variety->id]);
                } elseif ( $mark->has('batch') ) {
                    $object = $this->Html->link(__('Batch:').' '.$mark->batch->crossing_batch, ['controller' => 'Batches', 'action' => 'view', $mark->batch->id]);
                } else {
                    $object = '';
                }
        
                $mark_data = '<ul>';
                foreach($mark->mark_values as $mark_value) {
                    $value = substr($mark_value->value, 0, 35) != $mark_value->value ? substr($mark_value->value, 0, 25) .'...' : $mark_value->value;
                    $mark_data .= '<li>'.$mark_value->mark_form_property->name.': '.$value.'</li>';
                }
                $mark_data .= '</ul>';
            ?>
            <tr>
                <td class="id"><?= $this->Number->format($mark->id) ?></td>
                <td><?= $object ?></td>
                <td class="index_inline_list"><?= $mark_data ?></td>
                <td><?= h($mark->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>', ['action' => 'view', $mark->id], ['escapeTitle' => false, 'alt' => __('View')]) ?>
                    <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>', ['action' => 'edit', $mark->id], ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                    <?= $this->Form->postLink('<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>', ['action' => 'delete', $mark->id], ['escapeTitle' => false, 'alt' => __('Delete'), 'confirm' => __('Are you sure you want to delete the Mark with the id {0}?', $mark->id)]) ?>
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