<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>

<?php $filter = json_encode([
             'controller' => 'mark-form-properties',
             'action' => 'filter',
             'fields' => [
                 'name'
             ]]); ?>

<div class="markFormProperties index large-9 medium-8 columns content">
    <h3><?= __('Mark Form Properties') ?></h3>
    <div>
         <input type="text" class="filter" data-filter='<?= $filter ?>' placeholder="<?= __('Filter by name...') ?>">
    </div>
    <div id="index_table">
        <?= $this->element('MarkFormProperty/index_table'); ?>
    </div>
</div>
