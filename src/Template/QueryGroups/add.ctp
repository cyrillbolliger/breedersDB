<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Query Groups'), ['action' => 'index']) ?></li>
    </ul>
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
