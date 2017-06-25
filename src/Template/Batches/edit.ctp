<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Batch/nav'); ?>
</nav>

<div class="batches form large-9 medium-8 columns content">
    <?= $this->Form->create($batch) ?>
    <fieldset>
        <legend><?= __('Edit Batch') ?></legend>
        <?php
        echo $this->Form->input('crossing_id', [
            'options' => $crossings,
            'class'   => 'select2',
        ]);
        echo $this->Form->input('code');
        echo $this->Form->input('date_sowed', [
            'empty' => true,
            'type'  => 'text',
            'class' => 'datepicker',
        ]);
        echo $this->Form->input('numb_seeds_sowed');
        echo $this->Form->input('numb_sprouts_grown');
        echo $this->Form->input('seed_tray');
        echo $this->Form->input('date_planted', [
            'empty' => true,
            'type'  => 'text',
            'class' => 'datepicker',
        ]);
        echo $this->Form->input('numb_sprouts_planted');
        echo $this->Form->input('patch');
        echo $this->Form->input('note');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
