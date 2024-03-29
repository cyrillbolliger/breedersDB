<?php
$required = isset( $required ) ? $required : false;
$value    = isset( $markValue ) ? $markValue : '';
$name     = isset( $name ) ? $name : 'mark_form_fields.mark_form_properties.' . $markFormProperty->id . '.mark_values.value';
$context  = $context ?? null;
$is_scanner_code = 'scanner_code' === $context;

switch ( $markFormProperty->field_type ) {
	case 'DATE':
	    $class = $is_scanner_code ? 'allow-text' : '';
	    $format = $is_scanner_code ? ' ' . __("or ''{now}'' (literally)", ['now' => 'now'] ) : '';
		echo $this->Form->control( $name, [
			'empty'    => true,
			'type'     => 'text',
			'class'    => "datepicker $class",
			'label'    => $markFormProperty->name,
			'required' => $required,
			'value'    => $value,
			'pattern'  => $this->LocalizedTime->getDateValidationRegex($context),
			'title'    => __( "The date must be valid and match one of the following formats: 'dd.mm.yyyy' or 'dd-mm-yyyy' or 'dd/mm/yyyy'" ).$format,
            'data-date'=> true,
        ] );
		if ($is_scanner_code) {
		    echo '<p>'.__( 'Enter "{now}" (literally) to get a scanner code that produces always the current date.', ['now' => 'now'] ).'</p>';
        }
		break;

	case 'BOOLEAN':
		$value = isset( $markValue ) ? (int) $markValue : null;
		echo $this->Form->label( $name, $markFormProperty->name );
		echo $this->Form->radio( $name, [
			[ 'value' => 1, 'text' => __( 'True' ), 'required' => $required, 'checked' => 1 === $value ],
			[ 'value' => 0, 'text' => __( 'False' ), 'required' => $required, 'checked' => 0 === $value ],
		] );
		break;

	case 'VARCHAR':
		echo $this->Form->control( $name, [
			'type'      => 'text',
			'maxlength' => 255,
			'label'     => $markFormProperty->name,
			'required'  => $required,
			'value'     => $value,
		] );
		break;

    case 'PHOTO':
        echo $this->Form->control($name, [
            'type' => 'file',
            'label' => $markFormProperty->name,
            'required' => $required,
            'accept' => 'image/*'
        ]);
        break;

	default:
		echo $this->Form->control( $name, [
			'type'     => 'number',
			'label'    => $markFormProperty->name,
			'min'      => $markFormProperty->validation_rule['min'],
			'max'      => $markFormProperty->validation_rule['max'],
			'step'     => $markFormProperty->validation_rule['step'],
			'required' => $required,
			'value'    => $value,
		] );
		break;
}

