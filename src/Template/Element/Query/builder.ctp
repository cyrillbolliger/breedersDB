<?php


foreach ($views as $view_key => $view_name) {
    echo $this->Form->input($view_key, [
        'label' => $view_name,
        'type'  => 'checkbox',
        'class' => 'view-selector ' . $view_key . '-view-selector',
    ]);
    echo '<div class="field-selector-container ' . $view_key . '-field-selector-container">';
    foreach ($view_fields[$view_key] as $field_key => $field_name) {
        echo $this->Form->input($field_key, [
            'label'    => $field_name,
            'type'     => 'checkbox',
            'class'    => 'field-selector ' . $field_key . '-field-selector',
            'required' => false,
        ]);
    }
    echo '</div>';
}