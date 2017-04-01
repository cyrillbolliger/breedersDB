<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav'); ?>
</nav>
<div class="graftings form large-9 medium-8 columns content">
    <?= $this->Form->create($grafting) ?>
    <fieldset>
        <legend><?= __('Edit Grafting') ?></legend>
        <?php
            echo $this->Form->input('name');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
