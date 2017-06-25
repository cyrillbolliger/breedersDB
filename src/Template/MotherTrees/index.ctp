<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Crossing/nav'); ?>
</nav>

<?php $filter = json_encode([
    'controller' => 'mother-trees',
    'action'     => 'filter',
    'fields'     => [
        'code',
        'publicid'
    ]
]); ?>

<div class="motherTrees index large-9 medium-8 columns content">
    <h3><?= __('Mother Trees') ?></h3>
    <div>
        <input type="text" class="filter" data-filter='<?= $filter ?>'
               placeholder="<?= __('Filter by code oder publicid...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('MotherTree/index_table'); ?>
    </div>
</div>
