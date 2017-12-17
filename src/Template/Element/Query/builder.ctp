<legend class="sub-legend-h2"><?= __( 'Search Query' ) ?></legend>
&nbsp;

<?php

echo $this->Form->input( 'root_view', [
	'label'   => __( 'Main table' ),
	'options' => $views,
	'default' => isset( $query->query->root_view ) ? $query->query->root_view : 'MarksView',
	'class'   => 'no-select2', // because of select 2 bug https://github.com/select2/select2/issues/3992
] );

echo '<div class="breeding-object-aggregation-mode-selector">';
$this->Form->unlockField( 'breeding_object_aggregation_mode' );
echo $this->Form->input( 'breeding_object_aggregation_mode', [
	'label'   => __( 'Group marks by' ),
	'options' => $query->breeding_object_aggregation_modes,
	'default' => $query->breeding_object_aggregation_mode,
	'empty'   => false,
	'class'   => 'no-select2', // because of select 2 bug https://github.com/select2/select2/issues/3992
] );
echo '</div>';

?>

<legend class="sub-legend-h3"><?= __( 'Select columns shown in the results' ) ?></legend>
&nbsp;

<?php
foreach ( $views as $view_key => $view_name ) {
	$this->Form->unlockField( $view_key );
	echo $this->Form->input( $view_key, [
		'label'   => $view_name,
		'type'    => 'checkbox',
		'class'   => 'view-selector ' . $view_key . '-view-selector',
		'checked' => in_array( $view_key, $active_views ),
	] );
	echo '<div class="field-selector-container ' . $view_key . '-field-selector-container">';
	foreach ( $view_fields[ $view_key ] as $field_key => $field_name ) {
		echo '<div class="regular-property">';
		echo $this->Form->input( $field_key, [
			'label'    => $field_name,
			'type'     => 'checkbox',
			'class'    => 'field-selector ' . $field_key . '-field-selector',
			'required' => false,
			'checked'  => in_array( $field_key, $active_regular_fields ),
		] );
		echo '</div>';
	}
	if ( 'MarksView' === $view_key ) {
		foreach ( $mark_selectors as $field ) {
			echo '<div class="mark-property">';
			echo $this->Form->checkbox( 'MarkProperties[' . $field->id . '][check]', [
				'class'    => 'field-selector ' . 'mark-property-' . $field->id . '-field-selector mark-property-selector',
				'required' => false,
				'checked'  => array_key_exists( (string) $field->id, $mark_fields ) ? $mark_fields[$field->id]->check : false,
			] );
			echo $this->Form->label( 'MarkProperties[' . $field->id . '][check]',
				__( 'Mark Property' ) . ' -> ' . $field->name );
			echo $this->element( 'Query/mark_property_filter', [
				'field' => $field,
				'data'  => array_key_exists( (string) $field->id, $mark_fields ) ? $mark_fields[$field->id] : null
			] );
			echo '</div>';
		}
	}
	echo '</div>';
}
?>

<?php

$this->Form->unlockField( 'where_query' );
echo $this->Form->input( 'where_query', [
	'type' => 'hidden',
] );

?>

&nbsp;
<legend class="sub-legend-h3"><?= __( 'Set filter criteria' ) ?></legend>
&nbsp;
<div id="query_where_builder"></div>

<script>
    var query_builder_associations = $.parseJSON('<?= json_encode( $associations )?>');
    var query_where_builder_filters = $.parseJSON('<?= json_encode( $filter_data )?>');
    var query_where_builder_rules = $.parseJSON('<?= $where_rules ?>');
</script>