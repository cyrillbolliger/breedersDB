<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>

<?php $filter = json_encode( [
	'controller' => 'marks',
	'action'     => 'filter',
	'fields'     => [
		'mark_form_property_type_id'
	]
] ); ?>

<div class="marks index large-9 medium-8 columns content">
    <h3><?= __( 'Marks' ) ?></h3>

    <div>
		<?php
		echo $this->Form->label( 'filter', __( 'Filter by mark type' ) );
		echo $this->Form->select( 'filter', $mark_form_property_types, [
			'default'     => 0,
			'class'       => 'filter',
			'data-filter' => $filter,
		] );
		?>
    </div>
    <div id="index_table">
		<?= $this->element( 'Mark/index_table' ); ?>
    </div>
</div>
