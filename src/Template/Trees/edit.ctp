<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Tree/nav' ); ?>
</nav>
<div class="trees form large-9 medium-8 columns content">
	<?= $this->Form->create( $tree ) ?>
    <fieldset>
        <legend><?= __( 'Edit Tree' ) ?></legend>
		<?php
		echo $this->Form->control( 'variety_id', [
			'options'  => $varieties,
			'required' => 'required',
			'class'    => 'select2convar select2convar_add',
		] );
		echo $this->Form->control( 'publicid' );
		echo $this->Form->control( 'date_grafted', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $tree->setDirty( 'date_grafted' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->control( 'rootstock_id', [
			'options' => $rootstocks,
			'empty'   => true,
			'class'   => $tree->setDirty( 'rootstock_id' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->control( 'grafting_id', [
			'options' => $graftings,
			'empty'   => true,
			'class'   => $tree->setDirty( 'grafting_id' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->control( 'date_planted', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $tree->setDirty( 'date_planted' ) ? 'brain-prefilled' : '' ),
		] );
		echo $this->Form->control( 'date_eliminated', [
			'empty' => true,
			'type'  => 'text',
			'class' => 'datepicker ' . ( $tree->setDirty( 'date_eliminated' ) ? 'brain-prefilled' : '' ),
		] );
        echo $this->Form->control( 'date_labeled', [
            'empty' => true,
            'type'  => 'text',
            'class' => 'datepicker ' . ( $tree->setDirty( 'date_labeled' ) ? 'brain-prefilled' : '' ),
        ] );
		echo $this->Form->control( 'genuine_seedling' );
		echo $this->Form->control( 'migrated_tree' );
		echo $this->Form->control( 'experiment_site_id', [
			'options' => $experimentSites,
			'empty'   => true,
			'class'   => $tree->setDirty( 'experiment_site_id' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->control( 'row_id', [
			'options' => $rows,
			'empty'   => true,
			'class'   => $tree->setDirty( 'row_id' ) ? 'brain-prefilled' : '',
		] );
		echo $this->Form->control( 'offset' );
        echo $this->Form->control( 'dont_eliminate' );
		echo $this->Form->control( 'note' );
		?>
    </fieldset>
	<?= $this->Form->button( __( 'Submit' ) ) ?>
	<?= $this->Form->end() ?>
</div>
