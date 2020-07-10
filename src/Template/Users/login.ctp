<div class="users login large-9 medium-8 columns content">
    <div class="login">
        <h3><?= __( 'Login' ) ?></h3>
		<?= $this->Form->create() ?>
		<?= $this->Form->control( 'email' ) ?>
		<?= $this->Form->control( 'password' ) ?>
		<?= $this->Form->button( __( 'Login' ) ) ?>
		<?= $this->Form->end() ?>
    </div>
</div>
