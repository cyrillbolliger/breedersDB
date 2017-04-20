<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="marks form large-9 medium-8 columns content">
    <?= $this->Form->create($mark) ?>
    <fieldset>
        <legend><?= __('Edit Mark') ?></legend>
        <?php
            if ( $mark->tree ) {
                echo $this->element('Tree/get_tree', ['tree' => $mark->tree]);
                $this->Form->unlockField('tree_id');
            }
            
            if ( $mark->batch ) {
                echo $this->Form->input('batch_id', [
                    'options' => [$mark->batch->id => $mark->batch->crossing_batch],
                    'required' => 'required',
                    'class' => 'select2batch_id',
                    'label' => __('Crossing.Batch'),
                ]);
            }

            if ( $mark->variety ) {
                echo $this->Form->input('variety_id', [
                    'options' => [$mark->variety->id => $mark->variety->convar],
                    'required' => 'required',
                    'class' => 'select2convar select2convar_add',
                ]);
            }
            
            echo $this->Form->input('date', [
                'type' => 'text', 
                'class' => $mark->dirty('date') ? 'datepicker brain-prefilled': 'datepicker',
                'required' => 'required',
            ]);    
            echo $this->Form->input('author', [
                'class' => $mark->dirty('author') ? 'brain-prefilled': '',
                'required' => 'required',
            ]);
            echo $this->Form->input('mark_form_id', [
                'options' => $markForms,
                'class' => $mark->dirty('mark_form_id') ? 'brain-prefilled form-field-selector': 'form-field-selector',
                'required' => 'required',
                'empty' => true,
                'disabled' => 'disabled',
            ]);
        ?>
    </fieldset>
    <fieldset>
        <?php if (!empty($marks)): ?>
            <?php foreach ($marks as $mark_type => $mark_values): ?>
                <?php if ( ! empty($mark_values->toArray()) ) : ?>
                    <legend><?= h($mark_type) ?></legend>
                    <?= $this->element('Mark/list', ['markValues' => $mark_values]); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </fieldset>
     
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>