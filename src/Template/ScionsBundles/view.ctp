<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('ScionsBundle/nav'); ?>
</nav>
<div class="scionsBundles view large-9 medium-8 columns content">
    <h3><?= __('Scions Bundle:') . ' ' . h($scionsBundle->code) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($scionsBundle->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Descents Publicid List') ?></th>
            <td><?= h($scionsBundle->descents_publicid_list) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Variety') ?></th>
            <td><?= $scionsBundle->has('variety') ? $this->Html->link($scionsBundle->convar,
                    ['controller' => 'Varieties', 'action' => 'view', $scionsBundle->variety->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Scions') ?></th>
            <td><?= $this->Number->format($scionsBundle->numb_scions) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Scions Harvest') ?></th>
            <td><?= h($scionsBundle->date_scions_harvest) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Reserved for external partners') ?></th>
            <td><?= $scionsBundle->external_use ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($scionsBundle->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($scionsBundle->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Note') ?></h4>
        <?= $this->Text->autoParagraph(h($scionsBundle->note)); ?>
    </div>
</div>
