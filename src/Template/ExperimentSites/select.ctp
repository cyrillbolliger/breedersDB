<div class="experimentSites select large-9 medium-8 columns content">
    <h3><?= __( 'Select Experiment Site' ) ?></h3>
	<?= $this->Form->create( null ) ?>
	<?= $this->Form->control( 'experiment_site_id', [ 'options' => $experimentSites, 'empty' => false ] ); ?>
	<?= $this->Form->button( __( 'Ok' ) ) ?>
	<?= $this->Form->end() ?>
</div>
