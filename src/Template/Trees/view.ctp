<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Tree/nav'); ?>
</nav>
<div class="trees view large-9 medium-8 columns content">
    <?= $this->Html->link(
        __('Print label'),
        ['action' => 'print', $tree->id, 'view', $tree->id],
        ['class'=>'button print-button']);
    ?>
    <h3><?= __('Tree:') . ' ' . h($tree->publicid) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($tree->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Variety') ?></th>
            <td><?= $tree->has('variety') ? $this->Html->link($tree->convar, ['controller' => 'Varieties', 'action' => 'view', $tree->variety->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Experiment Site') ?></th>
            <td><?= $tree->has('experiment_site') ? $this->Html->link($tree->experiment_site->name, ['controller' => 'ExperimentSites', 'action' => 'view', $tree->experiment_site->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Row') ?></th>
            <td><?= $tree->has('row') ? $this->Html->link($tree->row->code, ['controller' => 'Rows', 'action' => 'view', $tree->row->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Offset') ?></th>
            <td><?= $this->Number->format($tree->offset) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Genuine Seedling') ?></th>
            <td><?= $tree->genuine_seedling ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Grafted') ?></th>
            <td><?= h($tree->date_grafted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Rootstock') ?></th>
            <td><?= $tree->has('rootstock') ? $this->Html->link($tree->rootstock->name, ['controller' => 'Rootstocks', 'action' => 'view', $tree->rootstock->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Grafting') ?></th>
            <td><?= $tree->has('grafting') ? $this->Html->link($tree->grafting->name, ['controller' => 'Graftings', 'action' => 'view', $tree->grafting->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Planted') ?></th>
            <td><?= h($tree->date_planted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Eliminated') ?></th>
            <td><?= h($tree->date_eliminated) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Migrated Tree') ?></th>
            <td><?= $tree->migrated_tree ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($tree->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($tree->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($tree->note)); ?>
    </div>
    <div class="related">
        <?php if (!empty($marks)): ?>
            <?php foreach ($marks as $mark_type => $mark_values): ?>
                <?php if ($mark_values->count()): ?>
                    <h4><?= h($mark_type) ?></h4>
                    <?= $this->element('Mark/list', ['markValues' => $mark_values]); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
