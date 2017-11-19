<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Query/nav'); ?>
</nav>
<div class="queries form large-9 medium-8 columns content">
    <?= $this->Form->create($query, ['id'=>'query_builder_form']) ?>
    <fieldset>
        <legend><?= __('Add Query') ?></legend>
        <?php
        echo $this->Form->input('code');
        echo $this->Form->input('query_group_id', [
            'type' => 'hidden',
            'default' => $query_group_id,
        ]);
        echo $this->Form->input('description');
        echo $this->element('Query/builder');
        echo $this->element('Query/validation_message');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'), ['class' => 'validate_query_where_builder']) ?>
    <?= $this->Form->end() ?>
</div>

