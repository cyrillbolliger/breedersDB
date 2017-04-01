<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Row/nav'); ?>
</nav>

<?php $filter = json_encode([
             'controller' => 'rows',
             'action' => 'filter',
             'fields' => [
                 'code'
             ]]); ?>

<div class="rows index large-9 medium-8 columns content">
    <h3><?= __('Rows') ?></h3>
    <div>
         <input type="text" class="filter" data-filter='<?= $filter ?>' placeholder="<?= __('Filter by code or convar...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('Row/index_table'); ?>
    </div>
</div>
