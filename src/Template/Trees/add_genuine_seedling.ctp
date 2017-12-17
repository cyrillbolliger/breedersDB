<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Tree/nav' ); ?>
</nav>
<div class="trees form large-9 medium-8 columns content">
	<?= $this->Form->create( $tree ) ?>
    <fieldset>
        <legend><?= __( 'Add Genuine Seedling' ) ?></legend>
		<?php
		echo $this->Form->input( 'variety_id', [
			'options'  => $varieties,
			'required' => 'required',
			'class'    => 'select2convar select2convar_add',
		] );
		echo $this->Form->input( 'publicid' );
		echo $this->Form->input( 'genuine_seedling', [
			'checked' => 'checked',
			'class'   => $tree->dirty( 'genuine_seedling' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->input( 'row_id', [
			'options' => $rows,
			'empty'   => true,
			'class'   => $tree->dirty( 'row_id' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->input( 'offset' );
		echo $this->Form->input( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>