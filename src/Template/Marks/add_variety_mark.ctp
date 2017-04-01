<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>

<div class="marks form large-9 medium-8 columns content">
    <?= $this->Form->create($mark) ?>
    <fieldset>
        <legend><?= __('Mark Variety') ?></legend>
        <?php
            echo $this->Form->input('variety_id', [
                'options' => $varieties, 
                'required' => 'required',
                'class' => 'select2convar select2convar_add',
            ]);
            
            echo $this->element('Mark/basic_mark_form');
?>