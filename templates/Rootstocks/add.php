<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="rootstocks form large-9 medium-8 columns content">
	<?= $this->Form->create( $rootstock ) ?>
    <fieldset>
        <legend><?= __( 'Add Rootstock' ) ?></legend>
		<?php
		echo $this->Form->control( 'name' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
