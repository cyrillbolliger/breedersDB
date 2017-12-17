<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Mark/nav' ); ?>
</nav>

<?php $filter = json_encode( [
	'controller' => 'marks',
	'action'     => 'process_scan',
] ); ?>

<div class="marks form large-9 medium-8 columns content">
	<?= $this->Form->create( $mark ) ?>
    <fieldset>
        <legend><?= __( 'Mark Tree by Scanner' ) ?></legend>
        <div>
            <input type="text" disabled="disabled" class="scanner_mark_field" data-filter='<?= $filter ?>'
                   placeholder="<?= __( 'Fill out form then scan tree and marks in here' ) ?>">
        </div>
        <div id="searching" style="display: none;"><?= __( 'Please wait...' ); ?></div>
        <div id="tree_container" class=""></div>
		
		<?php
		$this->Form->unlockField( 'tree_id' );
		
		echo $this->element( 'Mark/basic_mark_form' );
		?>

