<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Query/nav'); ?>
</nav>
<div class="queries view large-9 medium-8 columns content">
    <?= $this->Html->link(
        __('Export to excel'),
        ['action' => 'export', 1],//$query->id],
        ['class' => 'button export-button']);
    ?>
    
    <div class="row">
        <h4><?= __('Results') ?></h4>
        <?= $this->element('Query/mark_results_table'); ?>
    </div>
</div>