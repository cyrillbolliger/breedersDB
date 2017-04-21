<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('ScionsBundle/nav'); ?>
</nav>
<div class="scionsBundles form large-9 medium-8 columns content">
    <?= $this->Form->create($scionsBundle) ?>
    <fieldset>
        <legend><?= __('Edit Scions Bundle') ?></legend>
        <?php
            echo $this->Form->input('variety_id', [
                'options' => $varieties, 
                'required' => 'required',
                'class' => 'select2convar',
            ]);
            echo $this->Form->input('code', [
                'label' => __('Identification'),
            ]);
            echo $this->Form->input('numb_scions');
            echo $this->Form->input('date_scions_harvest', [
                'empty' => true, 
                'type' => 'text', 
                'class' => 'datepicker',
            ]);
            echo $this->Form->input('descents_publicid_list', [
                'label' => __('List of publicids of the descent trees, separeted by a comma')
            ]);
            echo $this->Form->input('external_use', [
                'label' => __('Reserved for external partners'),
            ]);
            echo $this->Form->input('note');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
