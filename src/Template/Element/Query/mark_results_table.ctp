<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <?php foreach ($columns as $column): ?>
            <th scope="col"><?= $column ?></th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $result): ?>
        <tr>
            <?php foreach ($columns as $column): ?>
                <td>
                <?php if (in_array($column, $properties)){
                    var_dump($result->marks[$column]->value);
                } else {
                    echo $result->$column;
                }
                ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php var_dump($results->toArray()); ?>

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