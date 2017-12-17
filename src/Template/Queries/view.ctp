<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Query/nav'); ?>
</nav>
<div class="queries view large-9 medium-8 columns content">
    <?= $this->Html->link(
        __('Export to excel'),
        ['action' => 'export', $query->id],
        ['class' => 'button export-button']);
    ?>
    <h3><?= __('Query:') .' '. h($query->code) ?></h3>
    <div class="clearfix"></div>
	<?= $this->Text->autoParagraph(h($query->description)); ?>
    <div class="row">
        <?= $this->element('Query/results_table'); ?>
    </div>
</div>