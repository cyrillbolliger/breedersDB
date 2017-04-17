<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>

<?php $filter = json_encode([
             'controller' => 'trees',
             'action' => 'getTree',
             'element' => 'get_tree',
             'fields' => [
                 'publicid'
             ]]); ?>

<div class="marks form large-9 medium-8 columns content">
    <?= $this->Form->create($mark) ?>
    <fieldset>
        <legend><?= __('Mark Tree') ?></legend>
        <div>
             <input type="text" class="get_tree" autofocus="autofocus" data-filter='<?= $filter ?>' placeholder="<?= __('Enter publicid...') ?>">
        </div>
        <div id="tree_container" class=""></div>
        
        <?php
            $this->Form->unlockField('tree_id');
            
            echo $this->element('Mark/basic_mark_form');
?>

