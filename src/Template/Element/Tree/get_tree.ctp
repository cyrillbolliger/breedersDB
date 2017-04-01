<input type="hidden" name="tree_id" id="tree_id" value="<?= $tree->id ?>">
<table>
    <tr>
        <th scope="row"><?= __('Publicid') ?></th>
        <th scope="row"><?= __('Variety') ?></th>
        <th scope="row"><?= __('Experiment Site') ?></th>
        <th scope="row"><?= __('Row') ?></th>
        <th scope="row"><?= __('Offset') ?></th>
    </tr>
    <tr>
        <td><?= h($tree->publicid) ?></td>
        <td><?= $tree->has('variety') ? $tree->convar : '' ?></td>
        <td><?= $tree->has('experiment_site') ? $tree->experiment_site->name : '' ?></td>
        <td><?= $tree->has('row') ? $tree->row->code : '' ?></td>
        <td><?= $this->Number->format($tree->offset) ?></td>
    </tr>
</table>