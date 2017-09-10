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
        'label'   => $view_name,
        'type'    => 'checkbox',
        'class'   => 'view-selector ' . $view_key . '-view-selector',
        'checked' => in_array($view_key, $active_views),
    ]);
    echo '<div class="field-selector-container ' . $view_key . '-field-selector-container">';
    foreach ($view_fields[$view_key] as $field_key => $field_name) {
        echo $this->Form->input($field_key, [
            'label'    => $field_name,
            'type'     => 'checkbox',
            'class'    => 'field-selector ' . $field_key . '-field-selector',
            'required' => false,
            'checked'  => in_array($field_key, $active_fields),
        ]);
    }
    echo '</div>';
}
?>

<?php

$this->Form->unlockField('where_query');
echo $this->Form->input('where_query', [
        'type' => 'hidden',
]);

?>

&nbsp;
<legend class="sub-legend-h3"><?= __('Set filter criteria') ?></legend>
&nbsp;
<div id="query_where_builder"></div>

<script>
    var query_builder_associations = $.parseJSON('<?= json_encode($associations)?>');
    var query_where_builder_filters = $.parseJSON('<?= json_encode($filter_data)?>');
    var query_where_builder_rules = $.parseJSON('<?= $where_rules ?>');
</script>