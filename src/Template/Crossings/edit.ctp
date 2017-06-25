<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Crossing/nav'); ?>
</nav>
<div class="crossings form large-9 medium-8 columns content">
    <?= $this->Form->create($crossing) ?>
    <fieldset>
        <legend><?= __('Edit Crossing') ?></legend>
        <?php
        echo $this->Form->input('code');
        echo $this->Form->input('mother_variety_id', [
            'options'  => $mother_varieties,
            'required' => 'required',
            'class'    => 'select2convar',
        ]);
        echo $this->Form->input('father_variety_id', [
            'options' => $father_varieties,
            'empty'   => true,
            'class'   => 'select2convar',
        ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
