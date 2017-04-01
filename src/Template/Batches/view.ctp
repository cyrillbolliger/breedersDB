<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Batch/nav'); ?>
</nav>
<div class="batches view large-9 medium-8 columns content">
    <h3><?= __('Batch:').' '.h($batch->crossing_batch) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($batch->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Crossing') ?></th>
            <td><?= $batch->has('crossing') ? $this->Html->link($batch->crossing->code, ['controller' => 'Crossings', 'action' => 'view', $batch->crossing->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Sowed') ?></th>
            <td><?= h($batch->date_sowed) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Seeds Sowed') ?></th>
            <td><?= $this->Number->format($batch->numb_seeds_sowed) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Sprouts Grown') ?></th>
            <td><?= $this->Number->format($batch->numb_sprouts_grown) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Seed Tray') ?></th>
            <td><?= h($batch->seed_tray) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Planted') ?></th>
            <td><?= h($batch->date_planted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Sprouts Planted') ?></th>
            <td><?= $this->Number->format($batch->numb_sprouts_planted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Patch') ?></th>
            <td><?= h($batch->patch) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($batch->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($batch->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($batch->note)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Varieties') ?></h4>
        <?php if (!empty($batch->varieties)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col" class="id"><?= __('Id') ?></th>
                <th scope="col"><?= __('Convar') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($batch->varieties as $varieties): ?>
            <tr>
                <td class="id"><?= h($varieties->id) ?></td>
                <td><?= h($batch->crossing_batch.'.'.$varieties->code) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Varieties', 'action' => 'view', $varieties->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Varieties', 'action' => 'edit', $varieties->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Varieties', 'action' => 'delete', $varieties->id], ['confirm' => __('Are you sure you want to delete "{0}" (id: {1})?',$batch->crossing_batch.'.'.$varieties->code, $varieties->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
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
