<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>
<div class="markScannerCodes form large-9 medium-8 columns content">
	<?= $this->Form->create( $markScannerCode ) ?>
    <fieldset>
        <legend><?= __( 'Add Scanner Code' ) ?></legend>
		<?php
		echo $this->Form->control( 'mark_form_property_id', [
			'options'  => $markFormProperties,
			'required' => 'required',
			'label'    => __( 'Property' ),
			'class'    => 'select_property',
			'empty'    => true,
		] );
		?>
        <div id="mark_value_wrapper"><?php
			echo $this->Form->unlockField( 'mark_value' );
			echo $this->Form->control( 'mark_value', [
				'label'    => __( 'Value' ),
				'class'    => 'replace_me',
				'disabled' => 'disabled',
			] );
			?></div><?php
		echo $this->Form->control( 'code', [
			'value'    => '(auto)',
			'disabled' => 'disabled'
		] );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
