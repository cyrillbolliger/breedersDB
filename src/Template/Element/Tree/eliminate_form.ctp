<?= $this->Form->create( $tree, [ 'url' => [ 'action' => 'update' ] ] ) ?>

<?php if ( $tree->dont_eliminate ): ?>
    <div class="inline-warning margin-bottom-25"><?= __( "For this tree the don't eliminate flag was set!" ) ?></div>
<?php endif; ?>

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
echo $this->Form->input( 'date_eliminated', [
    'required' => 'required',
    'type'     => 'text',
    'class'    => $tree->dirty( 'date_eliminated' ) ? 'datepicker brain-prefilled' : 'datepicker',
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
