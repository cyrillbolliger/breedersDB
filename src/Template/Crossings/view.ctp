<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Crossing/nav'); ?>
</nav>
<div class="crossings view large-9 medium-8 columns content">
    <h3><?= __('Crossing:').' '.h($crossing->code) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($crossing->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mother') ?></th>
            <td><?= $crossing->mother_variety_id ? $this->Html->link($crossing->mother_convar, ['controller' => 'Varieties', 'action' => 'view', $crossing->mother_variety_id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Father') ?></th>
            <td><?= $crossing->father_variety_id ? $this->Html->link($crossing->father_convar, ['controller' => 'Varieties', 'action' => 'view', $crossing->father_variety_id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Planed') ?></th>
            <td><?= $crossing->planed ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mother Tree') ?></th>
            <td><?= $crossing->has('tree') ? $this->Html->link($crossing->tree->id, ['controller' => 'Trees', 'action' => 'view', $crossing->tree->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Portions') ?></th>
            <td><?= $this->Number->format($crossing->numb_portions) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Flowers') ?></th>
            <td><?= $this->Number->format($crossing->numb_flowers) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Seeds') ?></th>
            <td><?= $this->Number->format($crossing->numb_seeds) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Pollen Harvested') ?></th>
            <td><?= h($crossing->date_pollen_harvested) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Impregnated') ?></th>
            <td><?= h($crossing->date_impregnated) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Fruit Harvested') ?></th>
            <td><?= h($crossing->date_fruit_harvested) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($crossing->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($crossing->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Target') ?></h4>
        <?= $this->Text->autoParagraph(h($crossing->target)); ?>
    </div>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($crossing->note)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Batches') ?></h4>
        <?php if (!empty($crossing->batches)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col" class="id"><?= __('Id') ?></th>
                <th scope="col"><?= __('Code') ?></th>
                <th scope="col"><?= __('Date Sowed') ?></th>
                <th scope="col"><?= __('Numb Seeds Sowed') ?></th>
                <th scope="col"><?= __('Seed Tray') ?></th>
                <th scope="col"><?= __('Date Planted') ?></th>
                <th scope="col"><?= __('Patch') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($crossing->batches as $batches): ?>
            <tr>
                <td class="id"><?= h($batches->id) ?></td>
                <td><?= h($batches->code) ?></td>
                <td><?= h($batches->date_sowed) ?></td>
                <td><?= h($batches->numb_seeds_sowed) ?></td>
                <td><?= h($batches->seed_tray) ?></td>
                <td><?= h($batches->date_planted) ?></td>
                <td><?= h($batches->patch) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Batches', 'action' => 'view', $batches->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Batches', 'action' => 'edit', $batches->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Batches', 'action' => 'delete', $batches->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?', $batches->code, $batches->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
