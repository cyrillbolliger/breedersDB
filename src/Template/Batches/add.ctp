<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Batch/nav' ); ?>
</nav>

<div class="batches form large-9 medium-8 columns content">
	<?= $this->Form->create( $batch ) ?>
    <fieldset>
        <legend><?= __( 'Add Batch' ) ?></legend>
		<?php
		echo $this->Form->control( 'crossing_id', [
			'options' => $crossings,
			'class'   => 'select2',
		] );
		echo $this->Form->control( 'code' );
		echo $this->Form->control( 'date_sowed', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker',
		] );
		echo $this->Form->control( 'numb_seeds_sowed' );
		echo $this->Form->control( 'numb_sprouts_grown' );
		echo $this->Form->control( 'seed_tray' );
		echo $this->Form->control( 'date_planted', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker',
		] );
		echo $this->Form->control( 'numb_sprouts_planted' );
		echo $this->Form->control( 'patch' );
		echo $this->Form->control( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
