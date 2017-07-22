<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Query Group'), ['action' => 'edit', $queryGroup->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Query Group'), ['action' => 'delete', $queryGroup->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queryGroup->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Query Groups'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Query Group'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="queryGroups view large-9 medium-8 columns content">
    <h3><?= h($queryGroup->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Code') ?></th>
            <td><?= h($queryGroup->code) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($queryGroup->id) ?></td>
        </tr>
    </table>
</div>
