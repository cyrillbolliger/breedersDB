<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('ScionsBundle/nav'); ?>
</nav>

<?php $filter = json_encode([
    'controller' => 'scions-bundles',
    'action'     => 'filter',
    'fields'     => [
        'code',
        'convar'
    ]
]); ?>

<div class="scionsBundles index large-9 medium-8 columns content">
    <h3><?= __('Scions Bundles') ?></h3>
    <div>
        <input type="text" class="filter noprint" data-filter='<?= $filter ?>'
               placeholder="<?= __('Filter by code or convar...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('ScionsBundle/index_table'); ?>
    </div>
</div>
