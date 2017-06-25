<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element('Setting/nav'); ?>
</nav>
<div class="experimentSites view large-9 medium-8 columns content">
    <h3><?= __('Experiment Site:') . ' ' . h($experimentSite->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($experimentSite->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($experimentSite->name) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Trees') ?></h4>
        <?php if ( ! empty($experimentSite->trees)): ?>
            <?= $this->element('Tree/related_table', ['trees' => $experimentSite->trees]); ?>
        <?php endif; ?>
    </div>
</div>
