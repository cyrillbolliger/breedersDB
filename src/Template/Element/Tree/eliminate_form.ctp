<?= $this->Form->create( $tree, [ 'url' => [ 'action' => 'update' ] ] ) ?>

<?php if ( $tree->dont_eliminate ): ?>
    <div class="inline-warning margin-bottom-25"><?= __( "For this tree the don't eliminate flag was set!" ) ?></div>
<?php endif; ?>

<?php
echo $this->Form->control( 'variety_id', [
    'options'  => $varieties,
    'required' => 'required',
    'class'    => 'select2convar',
    'disabled' => 'disabled',
] );
echo $this->Form->control( 'publicid', [
    'disabled' => 'disabled'
] );
echo $this->Form->control( 'date_eliminated', [
    'required' => 'required',
    'type'     => 'text',
    'class'    => $tree->setDirty( 'date_eliminated' ) ? 'datepicker brain-prefilled' : 'datepicker',
] );
echo $this->Form->control( 'note' );
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
