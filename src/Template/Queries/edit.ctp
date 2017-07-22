<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $query->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $query->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Queries'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="queries form large-9 medium-8 columns content">
    <?= $this->Form->create($query) ?>
    <fieldset>
        <legend><?= __('Edit Query') ?></legend>
        <?php
            echo $this->Form->input('code');
            echo $this->Form->input('query');
            echo $this->Form->input('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
