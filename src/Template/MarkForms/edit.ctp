<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="markForms form large-9 medium-8 columns content">
    <?= $this->Form->create($markForm) ?>
    <fieldset>
        <legend><?= __('Edit Mark Form') ?></legend>
        <?= $this->Form->input('name'); ?>
        <fieldset>
            <legend><?= __('Fields') ?></legend>
            <div class="mark_form_fields sortable">
                <?php
                    if ( ! empty($markForm->mark_form_fields) ) {
                        foreach($markForm->mark_form_fields as $markFormField) {
                            echo $this->element('Mark/field_edit_form_mode', ['markFormProperty' => $markFormField->mark_form_property]);
                            $this->Form->unlockField('mark_form_fields.mark_form_properties.'.$markFormField->mark_form_property->id.'.mark_values.value');
                        }
                    }
                ?>
            </div>
            <div class="mark_form_fields_adder">
                <?php
                    echo $this->Form->input('mark_form_properties', [
                        'options' => $markFormProperties,
                        'class'   => 'add_mark_form_field',
                        'label'   => __('Add mark field to form'),
                        'empty'   => true,
                        'data-mode'=> 'field_edit_form_mode',
                    ]);
                ?>
            </div>
        </fieldset>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>

<script>
    Marks.initNewField();
</script>