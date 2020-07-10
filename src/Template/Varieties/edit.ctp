<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Variety/nav' ); ?>
</nav>

<div class="varieties form large-9 medium-8 columns content">
	<?= $this->Form->create( $variety ) ?>
    <fieldset>
        <legend><?= __( 'Edit Variety' ) ?></legend>
		<?php
		echo $this->Form->control( 'batch_id', [
			'options'  => $batches,
			'required' => 'required',
			'class'    => 'select2batch_id',
			'label'    => __( 'Crossing.Batch' ),
		] );
		echo $this->Form->control( 'code' );
		echo $this->Form->control( 'official_name' );
		echo $this->Form->control( 'acronym' );
		echo $this->Form->control( 'plant_breeder' );
		echo $this->Form->control( 'registration' );
		echo $this->Form->control( 'description' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
