<legend class="sub-legend-h2"><?= __('Search Query') ?></legend>
&nbsp;

<?php

echo $this->Form->input('root_view', [
    'label'   => __('Main table'),
    'options' => $views,
    'default' => $default_root_view,
]);

?>

<legend class="sub-legend-h3"><?= __('Select columns shown in the results') ?></legend>
&nbsp;

<?php
foreach ($views as $view_key => $view_name) {
    $this->Form->unlockField($view_key);
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

?>
<script>
    var query_builder_associations = $.parseJSON('<?= json_encode($associations)?>');
</script>