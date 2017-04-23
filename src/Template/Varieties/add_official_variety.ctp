<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Variety/nav'); ?>
</nav>

<div class="varieties form large-9 medium-8 columns content">
    <?= $this->Form->create($variety) ?>
    <fieldset>
        <legend><?= __('Add Official Variety') ?></legend>
        <?php
            echo $this->Form->input('official_name', [
                'required' => 'required',
                'class' => 'official_name',
            ]);
            echo $this->Form->input('acronym');
            echo $this->Form->input('batch_id', [
                'options' => array( '1' => 'SORTE.000'),
                'required' => '',
                'disabled' => 'disabled',
                'label' => __('Crossing.Batch')
            ]);
            echo $this->Form->input('code', [
                'disabled' => 'disabled',
                'required' => '',
            ]);
            echo $this->Form->input('plant_breeder');
            echo $this->Form->input('registration');
            echo $this->Form->input('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
