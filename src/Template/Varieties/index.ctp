<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Variety/nav'); ?>
</nav>

<?php $filter = json_encode([
    'controller' => 'varieties',
    'action'     => 'filter',
    'fields'     => [
        'convar',
        'breeder_variety_code'
    ]
]); ?>

<div class="varieties index large-9 medium-8 columns content">
    <h3><?= __('Varieties') ?></h3>
    <div>
        <input type="text" class="filter" data-filter='<?= $filter ?>'
               placeholder="<?= __('Filter by convar or breeder variety code...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('Variety/index_table'); ?>
    </div>
</div>

