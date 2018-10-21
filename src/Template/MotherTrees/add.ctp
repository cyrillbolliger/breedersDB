<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>

<?php $filter = json_encode( [
	'controller' => 'trees',
	'action'     => 'getTree',
	'element'    => 'get_tree',
	'fields'     => [
		'publicid'
	]
] ); ?>

<div class="motherTrees form large-9 medium-8 columns content">
	<?= $this->Form->create( $motherTree ) ?>
    <fieldset>
        <legend><?= __( 'Add Mother Tree' ) ?></legend>
        <div>
            <input type="text" class="get_tree" data-filter='<?= $filter ?>'
                   placeholder="<?= __( 'Enter publicid...' ) ?>">
        </div>
        <div id="tree_container" class=""></div>
		<?php
		$this->Form->unlockField( 'tree_id' );
		echo $this->Form->input( 'crossing_id', [
			'options'  => $crossings,
			'required' => 'required',
		] );
		echo $this->Form->input( 'code', [
			'label' => __( 'Identification' ),
		] );
		echo $this->Form->input( 'planed' );
		echo $this->Form->input( 'date_pollen_harvested', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->dirty( 'date_pollen_harvested' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->input( 'date_impregnated', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->dirty( 'date_impregnated' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->input( 'date_fruit_harvested', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->dirty( 'date_fruit_harvested' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->input( 'numb_portions' );
		echo $this->Form->input( 'numb_flowers' );
		echo $this->Form->input( 'numb_fruits' );
		echo $this->Form->input( 'numb_seeds' );
		echo $this->Form->input( 'target' );
		echo $this->Form->input( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
