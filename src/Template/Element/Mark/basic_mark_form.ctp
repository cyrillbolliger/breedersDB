<?php
echo $this->Form->control( 'date', [
	'type'     => 'text',
	'class'    => $mark->setDirty( 'date' ) ? 'datepicker brain-prefilled' : 'datepicker',
	'required' => 'required',
] );
echo $this->Form->control( 'author', [
	'class'    => $mark->setDirty( 'author' ) ? 'brain-prefilled' : '',
	'required' => 'required',
] );
echo $this->Form->control( 'mark_form_id', [
	'options'  => $markForms,
	'class'    => $mark->setDirty( 'mark_form_id' ) ? 'brain-prefilled form-field-selector' : 'form-field-selector',
	'required' => 'required',
	'empty'    => true,
] );
?>
</fieldset>
<fieldset>
    <legend><?= __( 'Fields' ) ?></legend>
    <div class="mark_form_fields_wrapper">
		<?php echo $this->element( 'Mark/fields', [ 'markFormFields' => $markFormFields ] ); ?>
    </div>
    <div class="mark_form_fields_adder">
		<?php
		echo $this->Form->control( 'mark_form_properties', [
			'options'   => $markFormProperties,
			'class'     => 'add_mark_form_field',
			'label'     => __( 'Add mark field to form' ),
			'empty'     => true,
			'data-mode' => 'default'
		] );
		?>
    </div>
</fieldset>
<?= $this->Form->button( __( 'Submit' ) ) ?>
<?= $this->Form->end() ?>
</div>
