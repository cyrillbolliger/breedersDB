<div class="mark_form_fields">
    <?php
        if ( ! empty($markFormFields) ) {
            foreach($markFormFields as $markFormField) {
                echo $this->element('Mark/field', ['markFormProperty' => $markFormField->mark_form_property, 'required' => 'required']);
                $this->Form->unlockField('mark_form_fields.mark_form_properties.'.$markFormField->mark_form_property->id.'.mark_values.value');
            }
        }
    ?>
</div>
