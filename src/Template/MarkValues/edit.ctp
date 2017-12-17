<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>
<div class="markValues form large-9 medium-8 columns content">
	<?= $this->Form->create( $markValue ) ?>
    <fieldset>
        <legend><?= __( 'Edit Mark Value' ) ?></legend>
		<?php
		echo $this->element( 'Mark/field', [
			'markFormProperty' => $markValue->mark_form_property,
			'required'         => false,
			'markValue'        => $markValue->value
		] );
		echo $this->Form->input( 'exceptional_mark' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
