<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="users form large-9 medium-8 columns content">
	<?= $this->Form->create() ?>
    <fieldset>
        <legend><?= sprintf( __( 'Edit %s' ), $name ) ?></legend>
		<?php
		echo $this->Form->control( $key, [
            'type' => $type,
            'label' => $label,
            'value' => $value,
        ] );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Save' ) ) ?>
	<?= $this->Form->end() ?>
</div>
