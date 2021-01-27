<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Tree/nav' ); ?>
</nav>

<?php $filter = json_encode( [
	'controller' => 'trees',
	'action'     => 'filter',
	'fields'     => [
		'publicid',
		'convar'
	],
    'options'   => [
        'show_eliminated' => false
    ]
] ); ?>

<div class="trees index large-9 medium-8 columns content">
    <h3><?= __( 'Trees' ) ?></h3>
    <div>
        <input type="text" class="filter noprint" data-filter='<?= $filter ?>'
               placeholder="<?= __( 'Filter by publicid or convar...' ) ?>">
    </div>
    <div id="index_table">
		<?= $this->element( 'Tree/index_table' ); ?>
    </div>
</div>
