<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Row/nav' ); ?>
</nav>
<div class="rows form large-9 medium-8 columns content">
	<?= $this->Form->create( $row ) ?>
    <fieldset>
        <legend><?= __( 'Edit Row' ) ?></legend>
		<?php
		echo $this->Form->control( 'code', [
			'label' => __( 'Name' ),
		] );
		echo $this->Form->control( 'date_created', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker',
		] );
		echo $this->Form->control( 'date_eliminated', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker',
		] );
		echo $this->Form->control( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
