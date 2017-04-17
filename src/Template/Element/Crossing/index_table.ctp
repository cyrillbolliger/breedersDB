<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort('id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('code') ?></th>
            <th scope="col"><?= __('Mother trees') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col" class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($crossings as $crossing): ?>
            <?php
                $list = '<ul>';
                foreach ($crossing->mother_trees as $mother_tree) {
                    $list .= '<li>'.$this->Html->link(h($mother_tree->code), ['controller' => 'MotherTrees', 'action' => 'view', $mother_tree->id]).'</li>';
                }
                $list .= '</ul>';
            ?>
            <tr>
                <td class="id"><?= h($crossing->id) ?></td>
                <td><?= h($crossing->code) ?></td>
                <td class="index_inline_list"><?= $list ?></td>
                <td><?= h($crossing->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $crossing->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $crossing->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $crossing->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $crossing->code, $crossing->id)]) ?>
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
