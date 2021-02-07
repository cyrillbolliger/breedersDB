<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>

<div class="marks form large-9 medium-8 columns content">
	<?= $this->Form->create( $mark ) ?>
    <fieldset>
        <legend><?= __( 'Mark Batch' ) ?></legend>
<?php
echo $this->Form->control( 'batch_id', [
	'options'  => $batches,
	'required' => 'required',
	'class'    => 'select2batch_id',
	'label'    => __( 'Crossing.Batch' ),
] );

echo $this->element( 'Mark/basic_mark_form' );
?>
