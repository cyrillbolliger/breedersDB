<?php
echo '<div class="mark-property-filter-selector">';
echo $this->Form->select( 'MarkProperties[' . $field['id'] . '][mode]', $field['aggregations'], [
	'empty' => false,
	'class' => 'mark-property-mode no-select2',
] );
echo $this->Form->select( 'MarkProperties[' . $field['id'] . '][operator]', $field['operators'], [
	'empty' => true,
	'class' => 'mark-property-mode no-select2',
] );

switch ( $field['input'] ) {
	case 'number':
		echo $this->Form->number( 'MarkProperties[' . $field['id'] . '][value]', [
			'min'  => $field['validation']['min'],
			'max'  => $field['validation']['max'],
			'step' => $field['validation']['step'],
		] );
		break;
		
	case 'radio':
		echo $this->Form->radio( 'MarkProperties[' . $field['id'] . '][value]',
			$field['values']
		);
		break;
		
	case 'text':
		if ('date'===$field['type']) {
			echo $this->Form->text( 'MarkProperties[' . $field['id'] . '][value]', [
				'class' => 'datepicker',
				'pattern' => '/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',
			] );
		} else {
			echo $this->Form->text( 'MarkProperties[' . $field['id'] . '][value]' );
		}
}
echo '</div>';