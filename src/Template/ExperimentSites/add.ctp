<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav'); ?>
</nav>
<div class="experimentSites form large-9 medium-8 columns content">
    <?= $this->Form->create($experimentSite) ?>
    <fieldset>
        <legend><?= __('Add Experiment Site') ?></legend>
        <?php
            echo $this->Form->input('name');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
