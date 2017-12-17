<?= $this->Form->create( $tree, [ 'url' => [ 'action' => 'update' ] ] ) ?>
<?php
echo $this->Form->input( 'variety_id', [
	'options'  => $varieties,
	'required' => 'required',
	'class'    => 'select2convar',
	'disabled' => 'disabled',
] );
echo $this->Form->input( 'publicid', [
	'disabled' => 'disabled'
] );
echo $this->Form->input( 'date_planted', [
	'type'     => 'text',
	'class'    => $tree->dirty( 'date_planted' ) ? 'datepicker brain-prefilled' : 'datepicker',
	'required' => 'required',
] );
echo $this->Form->input( 'row_id', [
	'options'  => $rows,
	'required' => 'required',
	'class'    => $tree->dirty( 'row_id' ) ? 'brain-prefilled' : '',
] );
echo $this->Form->input( 'offset', [
	'required' => 'required',
] );
echo $this->Form->input( 'note' );
?>
<?= $this->Form->button( __( 'Submit' ) ) ?>
<?= $this->Form->end() ?>

<script>
    $(document).ready(function () {
        app.instantiateDatepicker();
        app.instantiateSelect2();
        app.instantiatePrefillMarker();
    });
</script>