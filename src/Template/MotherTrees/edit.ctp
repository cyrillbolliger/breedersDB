<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>

<div class="motherTrees form large-9 medium-8 columns content">
	<?= $this->Form->create( $motherTree ) ?>
    <fieldset>
        <legend><?= __( 'Edit Mother Tree' ) ?></legend>
		<?= $this->element( 'Tree/get_tree', [ 'tree' => $motherTree->tree ] ); ?>
		<?php
		$this->Form->unlockField( 'tree_id' );
		echo $this->Form->control( 'crossing_id', [
			'options'  => $crossings,
			'required' => 'required',
		] );
		echo $this->Form->control( 'code', [
			'label' => __( 'Identification' ),
		] );
		echo $this->Form->control( 'planed' );
		echo $this->Form->control( 'date_pollen_harvested', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->setDirty( 'date_pollen_harvested' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->control( 'date_impregnated', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->setDirty( 'date_impregnated' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->control( 'date_fruit_harvested', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $motherTree->setDirty( 'date_fruit_harvested' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->control( 'numb_portions' );
		echo $this->Form->control( 'numb_flowers' );
		echo $this->Form->control( 'numb_fruits' );
		echo $this->Form->control( 'numb_seeds' );
		echo $this->Form->control( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
