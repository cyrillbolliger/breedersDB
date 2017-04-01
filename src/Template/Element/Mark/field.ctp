<?php
$required = isset( $required ) ? $required : false;
$value = isset($markValue) ? $markValue : '';
$name = isset($name) ? $name : 'mark_form_fields.mark_form_properties.'.$markFormProperty->id.'.mark_values.value';

switch ($markFormProperty->field_type) {
    case 'DATE':
        echo $this->Form->input($name, [
            'empty' => true,
            'type' => 'text',
            'class' => 'datepicker',
            'label' => $markFormProperty->name,
            'required' => $required,
            'value' => $value,
        ]);
        break;

    case 'BOOLEAN':
        $value = isset($markValue) ? (int) $markValue : null;
        echo $this->Form->label($name, $markFormProperty->name);
        echo $this->Form->radio($name, [
            ['value' => 1, 'text' => __('True'), 'required' => $required, 'checked' => 1 === $value],
            ['value' => 0, 'text' => __('False'), 'required' => $required, 'checked' => 0 === $value],
        ]);
        break;

    case 'VARCHAR':
        echo $this->Form->input($name, [
            'type' => 'text',
            'maxlength' => 255,
            'label' => $markFormProperty->name,
            'required' => $required,
            'value' => $value,
        ]);
        break;

    default:
        echo $this->Form->input($name, [
            'type' => 'number',
            'label' => $markFormProperty->name,
            'min' => $markFormProperty->validation_rule['min'],
            'max' => $markFormProperty->validation_rule['max'],
            'step' => $markFormProperty->validation_rule['step'],
            'required' => $required,
            'value' => $value,
        ]);
        break;
}
?>
