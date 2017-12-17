<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>
<div class="markScannerCodes form large-9 medium-8 columns content">
	<?= $this->Form->create( $markScannerCode ) ?>
    <fieldset>
        <legend><?= __( 'Edit Scanner Code' ) ?></legend>
		<?php
		echo $this->Form->input( 'code', [ 'disabled' => 'disabled' ] );
		echo $this->element( 'Mark/field', [
			'markFormProperty' => $markScannerCode->mark_form_property,
			'required'         => 'required',
			'name'             => 'mark_value',
			'markValue'        => $markScannerCode->mark_value,
		] );
		echo $this->Form->input( 'mark_form_property_id', [
			'options' => $markFormProperties,
			'label'   => __( 'Property' ),
		] );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
