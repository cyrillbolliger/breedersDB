<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="markForms view large-9 medium-8 columns content">
    <h3><?= __('Mark Form:') . ' ' . h($markForm->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($markForm->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($markForm->created)) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($this->LocalizedTime->getUserTime($markForm->modified)) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($markForm->description)); ?>
    </div>
    <div class="related">
        <h4><?= __('Form Fields') ?></h4>
        <?php if ( ! empty($markForm->mark_form_fields)): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th scope="col" class="id"><?= __('Id') ?></th>
                    <th scope="col"><?= __('Mark Property') ?></th>
                    <th scope="col"><?= __('Priority') ?></th>
                    <th scope="col"><?= __('Modified') ?></th>
                </tr>
                <?php foreach ($markForm->mark_form_fields as $markFormFields): ?>
                    <tr>
                        <td class="id"><?= h($markFormFields->id) ?></td>
                        <td><?= h($markFormFields->mark_form_property->name ?? '') ?></td>
                        <td><?= h($markFormFields->priority) ?></td>
                        <td><?= h($this->LocalizedTime->getUserTime($markFormFields->modified)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
