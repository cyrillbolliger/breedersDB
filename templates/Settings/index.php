<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav') ?>
</nav>

<div class="settings index large-9 medium-8 columns content">
    <h3><?= __('Settings') ?></h3>
    <table>
        <thead>
        <tr>
            <th scope="col"><?= __('Key') ?></th>
            <th scope="col"><?= __('Value') ?></th>
            <th scope="col" class="actions noprint"><?= __('Actions') ?></th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= __('Printer label offset left') ?></td>
                <td><?= $this->Number->format(\App\Domain\Settings::getZplDriverOffsetLeft()) ?></td>
                <td class="actions noprint">
                    <?= $this->Html->link('<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                                          ['action' => 'edit', \App\Domain\Settings::ZPL_DRIVER_OFFSET_LEFT], ['escapeTitle' => false, 'alt' => __('Edit')]
                    ) ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
