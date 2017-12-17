<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>

<?php $filter = json_encode( [
	'controller' => 'crossings',
	'action'     => 'filter',
	'fields'     => [
		'code'
	]
] ); ?>

<div class="crossings index large-9 medium-8 columns content">
    <h3><?= __( 'Crossings' ) ?></h3>
    <div>
        <input type="text" class="filter noprint" data-filter='<?= $filter ?>'
               placeholder="<?= __( 'Filter by code...' ) ?>">
    </div>
    <div id="index_table">
		<?= $this->element( 'Crossing/index_table' ); ?>
    </div>
</div>
