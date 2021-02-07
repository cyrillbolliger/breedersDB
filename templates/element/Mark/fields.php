<div class="mark_form_fields">
	<?php
	if ( ! empty( $markFormFields ) ) {
		foreach ( $markFormFields as $markFormField ) {
			echo $this->element( 'Mark/field',
				[ 'markFormProperty' => $markFormField->mark_form_property, 'required' => false ] );
		}
	}
	?>
</div>
