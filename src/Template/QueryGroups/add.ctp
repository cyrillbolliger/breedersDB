<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Query/nav'); ?>
</nav>
<div class="queryGroups form large-9 medium-8 columns content">
    <?= $this->Form->create($queryGroup) ?>
    <fieldset>
        <legend><?= __('Add Query Group') ?></legend>
        <?php
            echo $this->Form->input('code');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
