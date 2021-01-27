<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>
<div class="markFormProperties form large-9 medium-8 columns content">
	<?= $this->Form->create( $markFormProperty ) ?>
    <fieldset>
        <legend><?= __( 'Add Property' ) ?></legend>
		<?php
		echo $this->Form->control( 'name' );
		echo $this->Form->control( 'mark_form_property_type_id', [
			'options' => $markFormPropertyTypes,
			'label'   => __( 'Property Type' ),
		] );
		echo $this->Form->control( 'field_type', [
			'options' => $fieldTypes,
			'class'   => 'mark_field_type',
			'label'   => __( 'Data Type' ),
		] );
		echo $this->Form->control( 'min', [
			'type'     => 'number',
			'step'     => 'any',
			'class'    => 'mark_validation_rule mark_validation_rule_min',
			'required' => 'required',
			'label'    => __( 'Minimum Value' ),
		] );
		$this->Form->unlockField( 'min' );
		echo $this->Form->control( 'max', [
			'type'     => 'number',
			'step'     => 'any',
			'class'    => 'mark_validation_rule mark_validation_rule_max',
			'required' => 'required',
			'label'    => __( 'Maximum Value' ),
		] );
		$this->Form->unlockField( 'max' );
		echo $this->Form->control( 'step', [
			'type'     => 'number',
			'step'     => 'any',
			'class'    => 'mark_validation_rule mark_validation_rule_step',
			'required' => 'required',
		] );
		$this->Form->unlockField( 'step' );
		echo $this->Form->control( 'note' );
		?>
        <fieldset>
            <legend><?= __( 'This property serves to mark:' ) ?></legend>
			<?php
			echo $this->Form->control( 'tree_property', [ 'label' => __( 'Trees' ) ] );
			echo $this->Form->control( 'variety_property', [ 'label' => __( 'Varieties' ) ] );
			echo $this->Form->control( 'batch_property', [ 'label' => __( 'Batches' ) ] );
			?>
        </fieldset>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
