<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Tree/nav'); ?>
</nav>
<div class="trees form large-9 medium-8 columns content">
    <?= $this->Form->create($tree) ?>
    <fieldset>
        <legend><?= __('Edit Tree') ?></legend>
        <?php
            echo $this->Form->input('variety_id', [
                'options' => $varieties, 
                'required' => 'required',
                'class' => 'select2convar select2convar_add',
            ]);
            echo $this->Form->input('publicid');
            echo $this->Form->input('date_grafted', [
                'empty' => true, 
                'type' => 'text', 
                'class' => 'datepicker '.($tree->dirty('date_grafted') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('rootstock_id', [
                'options' => $rootstocks, 
                'empty' => true,
                'class' => $tree->dirty('rootstock_id') ? 'brain-prefilled': '',
            ]);
            echo $this->Form->input('grafting_id', [
                'options' => $graftings, 
                'empty' => true,
                'class' => $tree->dirty('grafting_id') ? 'brain-prefilled': '',
            ]);
            echo $this->Form->input('date_planted', [
                'empty' => true, 
                'type' => 'text', 
                'class' => 'datepicker '.($tree->dirty('date_planted') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('date_eliminated', [
                'empty' => true, 
                'type' => 'text', 
                'class' => 'datepicker '.($tree->dirty('date_eliminated') ? 'brain-prefilled': ''),
            ]);
            echo $this->Form->input('genuine_seedling');
            echo $this->Form->input('migrated_tree');
            echo $this->Form->input('experiment_site_id', [
                'options' => $experimentSites, 
                'empty' => true,
                'class' => $tree->dirty('experiment_site_id') ? 'brain-prefilled': '',
            ]);
            echo $this->Form->input('row_id', [
                'options' => $rows, 
                'empty' => true,
                'class' => $tree->dirty('row_id') ? 'brain-prefilled': '',
            ]);
            echo $this->Form->input('offset');
            echo $this->Form->input('note');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
