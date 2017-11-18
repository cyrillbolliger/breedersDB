<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <?php foreach ($regular_columns as $column_key => $column_name): ?>
            <th scope="col" rowspan="2"><?= $column_name ?></th>
        <?php endforeach; ?>
        <?php foreach ($mark_columns as $column): ?>
            <?php $rowspan = $column->aggregated ? '' : ' rowspan="2"' ?>
            <?php $colspan = $column->aggregated ? ' colspan="4"' : '' ?>
            <th scope="col"<?= $rowspan . $colspan ?>><?= $column->name ?></th>
        <?php endforeach; ?>
    </tr>
    <tr>
        <?php foreach ($mark_columns as $column): ?>
            <?php if ($column->aggregated): ?>
                <td><?= $column->display ?></td>
                <td><?= __('Plot') ?></td>
                <td><?= __('Stats') ?></td>
                <td><?= __('Values') ?></td>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $result): ?>
        <tr>
            <?php foreach ($regular_columns as $column_key => $column_value): ?>
                <td><?= $result->$column_key; ?></td>
            <?php endforeach; ?>
            
            <?php foreach ($mark_columns as $column): ?>
                <?= $this->element('Query/mark_value', [
                        'mark' => $result->marks[$column->name],
                        'meta' => $column
                ]); ?>
            <?php endforeach; ?>
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