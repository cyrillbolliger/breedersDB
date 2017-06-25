<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>

<?php $filter = json_encode([
    'controller' => 'mark-scanner-codes',
    'action'     => 'filter',
    'fields'     => [
        'mark_form_property_id'
    ]
]); ?>

<div class="markScannerCodes index large-9 medium-8 columns content">
    <h3><?= __('Scanner Codes') ?></h3>

    <div>
        <?php
        echo $this->Form->label('filter', __('Filter by property'));
        echo $this->Form->select('filter', $mark_form_properties, [
            'default'     => 0,
            'class'       => 'filter',
            'data-filter' => $filter,
        ]);
        ?>
    </div>

    <div id="index_table">
        <?= $this->element('MarkScannerCode/index_table'); ?>
    </div>
</div>
