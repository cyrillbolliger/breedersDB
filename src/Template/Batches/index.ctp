<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Batch/nav'); ?>
</nav>

<?php $filter = json_encode([
    'controller' => 'batches',
    'action'     => 'filter',
    'fields'     => [
        'crossing_batch'
    ]
]); ?>

<div class="batches index large-9 medium-8 columns content">
    <h3><?= __('Batches') ?></h3>
    <div>
        <input type="text" class="filter noprint" data-filter='<?= $filter ?>'
               placeholder="<?= __('Filter by Crossing.Batch...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('Batch/index_table'); ?>
    </div>
</div>
