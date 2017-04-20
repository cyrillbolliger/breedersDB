<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Crossing/nav'); ?>
</nav>

<div class="motherTrees form large-9 medium-8 columns content">
    <?= $this->Form->create($motherTree) ?>
    <fieldset>
        <legend><?= __('Edit Mother Tree') ?></legend>
        <?= $this->element('Tree/get_tree', ['tree' => $motherTree->tree]); ?>
        <?php
            $this->Form->unlockField('tree_id');
            echo $this->Form->input('crossing_id', [
                'options' => $crossings,
                'required' => 'required',
            ]);
            echo $this->Form->input('code', [
                'label' => 'Identification'
            ]);
            echo $this->Form->input('planed');
            echo $this->Form->input('date_pollen_harvested', [
                'empty' => true,
                'type' => 'text',
                'class' => 'datepicker '.($motherTree->dirty('date_pollen_harvested') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('date_impregnated', [
                'empty' => true,
                'type' => 'text',
                'class' => 'datepicker '.($motherTree->dirty('date_impregnated') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('date_fruit_harvested', [
                'empty' => true,
                'type' => 'text',
                'class' => 'datepicker '.($motherTree->dirty('date_fruit_harvested') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('numb_portions');
            echo $this->Form->input('numb_flowers');
            echo $this->Form->input('numb_seeds');
            echo $this->Form->input('target');
            echo $this->Form->input('note');
            ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
