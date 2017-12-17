<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <?php foreach ($columns as $column_key => $column_name): ?>
            <th scope="col"><?= $this->Paginator->sort($column_key, $column_name) ?></th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $result): ?>
        <tr>
            <?php foreach ($columns as $column_key => $column_translation): ?>
                <td class="index_inline_list"><?= $this->DataExtractor->getCell($column_key, $result); ?></td>
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


