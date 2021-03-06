<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>
<div class="markForms form large-9 medium-8 columns content">
	<?= $this->Form->create( $markForm ) ?>
    <fieldset>
        <legend><?= __( 'Add Mark Form' ) ?></legend>
		<?= $this->Form->control( 'name' ); ?>
		<?= $this->Form->control( 'description' ); ?>
        <fieldset>
            <legend><?= __( 'Fields' ) ?></legend>
            <div class="mark_form_fields sortable">
				<?php
				if ( ! empty( $markForm->mark_form_fields ) ) {
					foreach ( $markForm->mark_form_fields as $markFormField ) {
						echo $this->element( 'Mark/field_edit_form_mode',
							[ 'markFormProperty' => $markFormField->mark_form_property ] );
					}
				}
				?>
            </div>
            <div class="mark_form_fields_adder">
				<?php
				echo $this->Form->control( 'mark_form_properties', [
					'options'   => $markFormProperties,
					'class'     => 'add_mark_form_field',
					'label'     => __( 'Add mark field to form' ),
					'empty'     => true,
					'data-mode' => 'field_edit_form_mode',
				] );
				?>
            </div>
        </fieldset>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
