<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $queryGroup->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $queryGroup->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Query Groups'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="queryGroups form large-9 medium-8 columns content">
    <?= $this->Form->create($queryGroup) ?>
    <fieldset>
        <legend><?= __('Edit Query Group') ?></legend>
        <?php
            echo $this->Form->input('code');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
