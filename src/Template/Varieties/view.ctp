<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Variety/nav'); ?>
</nav>

<div class="varieties view large-9 medium-8 columns content">
    <h3><?= __('Variety:') .' '. h($variety->convar) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($variety->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Official Name') ?></th>
            <td><?= h($variety->official_name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Plant Breeder') ?></th>
            <td><?= h($variety->plant_breeder) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Registration') ?></th>
            <td><?= h($variety->registration) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($variety->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($variety->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($variety->description)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Scions Bundles') ?></h4>
        <?php if (!empty($variety->scions_bundles)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col" class="id"><?= __('Id') ?></th>
                <th scope="col"><?= __('Code') ?></th>
                <th scope="col"><?= __('Numb Scions') ?></th>
                <th scope="col"><?= __('Date Scions Harvest') ?></th>
                <th scope="col"><?= __('External Use') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($variety->scions_bundles as $scionsBundles): ?>
            <tr>
                <td class="id"><?= h($scionsBundles->id) ?></td>
                <td><?= h($scionsBundles->code) ?></td>
                <td><?= h($scionsBundles->numb_scions) ?></td>
                <td><?= h($scionsBundles->date_scions_harvest) ?></td>
                <td><?= h($scionsBundles->external_use) ?></td>
                <td><?= h($scionsBundles->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'ScionsBundles', 'action' => 'view', $scionsBundles->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'ScionsBundles', 'action' => 'edit', $scionsBundles->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'ScionsBundles', 'action' => 'delete', $scionsBundles->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $scionsBundles->code, $scionsBundles->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Trees') ?></h4>
        <?php if (!empty($variety->trees)): ?>
            <?= $this->element('Tree/related_table', ['trees' => $variety->trees]); ?>
        <?php endif; ?>
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
