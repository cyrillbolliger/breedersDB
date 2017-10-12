<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="markFormProperties view large-9 medium-8 columns content">
    <h3><?= __('Mark Form Property:') . ' ' . h($markFormProperty->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($markFormProperty->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Property Type') ?></th>
            <td><?= $markFormProperty->has('mark_form_property_type') ? $this->Html->link($markFormProperty->mark_form_property_type->name,
                    [
                        'controller' => 'MarkFormPropertyTypes',
                        'action'     => 'view',
                        $markFormProperty->mark_form_property_type->id
                    ]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Data Type') ?></th>
            <td><?= h($markFormProperty->field_type) ?></td>
        </tr>
        <?php if (isset($markFormProperty->validation_rule['min'])): ?>
            <tr>
                <th scope="row"><?= __('Minimum Value') ?></th>
                <td><?= h($markFormProperty->validation_rule['min']) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (isset($markFormProperty->validation_rule['max'])): ?>
            <tr>
                <th scope="row"><?= __('Maximum Value') ?></th>
                <td><?= h($markFormProperty->validation_rule['max']) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (isset($markFormProperty->validation_rule['step'])): ?>
            <tr>
                <th scope="row"><?= __('Step') ?></th>
                <td><?= h($markFormProperty->validation_rule['step']) ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($markFormProperty->created)) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($markFormProperty->modified)) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($markFormProperty->note)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Mark Forms') ?></h4>
        <?php if ( ! empty($markFormProperty->mark_form_fields)): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th scope="col" class="id"><?= __('Id') ?></th>
                    <th scope="col"><?= __('Mark Form') ?></th>
                    <th scope="col"><?= __('Modified') ?></th>
                    <th scope="col" class="actions"><?= __('Actions') ?></th>
                </tr>
                <?php foreach ($markFormProperty->mark_form_fields as $markFormFields): ?>
                    <tr>
                        <td class="id"><?= h($markFormFields->mark_form->id) ?></td>
                        <td><?= h($markFormFields->mark_form->name) ?></td>
                        <td><?= h($this->LocalizedTime->getUserTime($markFormFields->mark_form->modified)) ?></td>
                        <td class="actions">
                            <?= $this->Html->link('<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
                                ['controller' => 'MarkForms', 'action' => 'view', $markFormFields->mark_form->id],
                                ['escapeTitle' => false, 'alt' => __('View')]) ?>
                            <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                                ['controller' => 'MarkForms', 'action' => 'edit', $markFormFields->mark_form->id],
                                ['escapeTitle' => false, 'alt' => __('Edit')]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
