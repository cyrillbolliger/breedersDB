<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Mark/nav'); ?>
</nav>
<div class="markScannerCodes view large-9 medium-8 columns content">
    <h3><?= __('Scanner Code:').' '.h($markScannerCode->code) ?></h3>
    <?= $this->Html->link(
        __('Print label'),
        ['action' => 'print', $markScannerCode->id, 'view', $markScannerCode->id],
        ['class'=>'button']);
    ?>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($markScannerCode->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Property') ?></th>
            <td><?= $markScannerCode->has('mark_form_property') ? $this->Html->link($markScannerCode->mark_form_property->name, ['controller' => 'MarkFormProperties', 'action' => 'view', $markScannerCode->mark_form_property->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Value') ?></th>
            <td><?= h($markScannerCode->mark_value) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Code') ?></th>
            <td><?= h($markScannerCode->code) ?></td>
        </tr>
    </table>
</div>
