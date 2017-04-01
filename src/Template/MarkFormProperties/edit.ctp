<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="markFormProperties form large-9 medium-8 columns content">
    <?= $this->Form->create($markFormProperty) ?>
    <fieldset>
        <legend><?= __('Add Mark Form Property') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('mark_form_property_type_id', [
              'options' => $markFormPropertyTypes
            ]);
            echo $this->Form->input('field_type', [
              'options' => $fieldTypes,
              'class'   => 'mark_field_type'
            ]);
            echo $this->Form->input('min', [
              'type' => 'number',
              'step' => 'any',
              'class' => 'mark_validation_rule mark_validation_rule_min',
              'required' => 'required',
              'value' => isset( $markFormProperty->validation_rule['min'] ) ? $markFormProperty->validation_rule['min'] : '',
            ]);
            $this->Form->unlockField('min');
            echo $this->Form->input('max', [
              'type' => 'number',
              'step' => 'any',
              'class' => 'mark_validation_rule mark_validation_rule_max',
              'required' => 'required',
              'value' => isset( $markFormProperty->validation_rule['max'] ) ? $markFormProperty->validation_rule['max'] : '',
            ]);
            $this->Form->unlockField('max');
            echo $this->Form->input('step', [
              'type' => 'number',
              'step' => 'any',
              'class' => 'mark_validation_rule mark_validation_rule_step',
              'required' => 'required',
              'value' => isset( $markFormProperty->validation_rule['step'] ) ? $markFormProperty->validation_rule['step'] : '',
            ]);
            $this->Form->unlockField('step');
            ?>
            <fieldset>
                <legend><?= __('This property may be used to mark') ?></legend>
                <?php
                echo $this->Form->input('tree_property', ['label' => __('Trees')]);
                echo $this->Form->input('variety_property', ['label' => __('Varieties')]);
                echo $this->Form->input('batch_property', ['label' => __('Batches')]);
                ?>
            </fieldset>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
