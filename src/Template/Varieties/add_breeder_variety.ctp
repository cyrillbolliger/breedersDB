<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Variety/nav'); ?>
</nav>

<div class="varieties form large-9 medium-8 columns content">
    <?= $this->Form->create($variety) ?>
    <fieldset>
        <legend><?= __('Add Breeder Variety') ?></legend>
        <?php
            echo $this->Form->input('batch_id', [
                'options' => $batches,
                'required' => 'required',
                'class' => 'select2batch_id',
                'label' => 'Crossing.Batch',
            ]);
            echo $this->Form->input('code', [
                'disabled' => $disabled,
                'pattern' => '\d{3}',
            ]);
            echo $this->Form->input('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
