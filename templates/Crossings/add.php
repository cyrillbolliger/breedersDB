<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>
<div class="crossings form large-9 medium-8 columns content">
	<?= $this->Form->create( $crossing ) ?>
    <fieldset>
        <legend><?= __( 'Add Crossing' ) ?></legend>
		<?php
		echo $this->Form->control( 'code' );
		echo $this->Form->control( 'mother_variety_id', [
			'options'  => $mother_varieties,
			'required' => 'required',
			'class'    => 'select2convar',
			'label'    => __( 'Mother Variety' ),
		] );
		echo $this->Form->control( 'father_variety_id', [
			'options' => $father_varieties,
			'empty'   => true,
			'class'   => 'select2convar',
			'label'   => __( 'Father Variety' ),
		] );
        echo $this->Form->control( 'target' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
