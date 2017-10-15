<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Query/nav'); ?>
</nav>
<div class="queries view large-9 medium-8 columns content">
    <?= $this->Html->link(
        __('Export to excel'),
        ['action' => 'export', $query->id],
        ['class' => 'button export-button']);
    ?>
    <h3><?= __('Query:') .' '. h($query->code) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($query->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Query Group') ?></th>
            <td><?= $query->has('query_group') ? $query->query_group->code : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($query->created)) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($query->modified)) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Query') ?></h4>
        <?php //debug(($query->query)); ?>
    </div>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($query->description)); ?>
    </div>
    <div class="row">
        <h4><?= __('Results') ?></h4>
        <?= $this->element('Query/results_table'); ?>
    </div>
</div>