<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="users form large-9 medium-8 columns content">
	<?= $this->Form->create( $user ) ?>
    <fieldset>
        <legend><?= __( 'Edit User' ) ?></legend>
		<?php
		echo $this->Form->control( 'email' );
		echo $this->Form->control( 'password' );
		echo $this->Form->control( 'level' );
		echo $this->Form->control( 'time_zone', [
			'options' => $time_zones,
		] );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
