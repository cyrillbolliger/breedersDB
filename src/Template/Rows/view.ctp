<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Row/nav'); ?>
</nav>
<div class="rows view large-9 medium-8 columns content">
    <h3><?= __('Row:') . ' ' . h($row->code) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($row->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Created') ?></th>
            <td><?= h($row->date_created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Eliminated') ?></th>
            <td><?= h($row->date_eliminated) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($row->created)) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($row->modified)) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($row->note)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Trees') ?></h4>
        <?php if ( ! empty($row->trees)): ?>
            <?= $this->element('Tree/related_table', ['trees' => $row->trees]); ?>
        <?php endif; ?>
    </div>
</div>
