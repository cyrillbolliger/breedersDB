<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('ScionsBundle/nav'); ?>
</nav>
<div class="scionsBundles form large-9 medium-8 columns content">
    <?= $this->Form->create($scionsBundle) ?>
    <fieldset>
        <legend><?= __('Add Scions Bundle') ?></legend>
        <?php
            echo $this->Form->input('variety_id', [
                'options' => $varieties, 
                'required' => 'required',
                'class' => 'select2convar',
            ]);
            echo $this->Form->input('code');
            echo $this->Form->input('numb_scions');
            echo $this->Form->input('date_scions_harvest', [
                'empty' => true, 
                'type' => 'text', 
                'class' => 'datepicker',
            ]);
            echo $this->Form->input('descents_publicid_list', [
                'label' => __('List of publicids of the descent trees, separeted by a comma')
            ]);
            echo $this->Form->input('external_use');
            echo $this->Form->input('note');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
