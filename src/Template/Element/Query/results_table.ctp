<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <?php foreach ($columns as $column): ?>
            <th scope="col"><?= $column ?></th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $result): ?>
        <tr>
            <?php foreach ($columns as $column_key => $column_translation): ?>
                <?php
                    $path = explode('.', $column_key);
                    $class_path = '\App\Model\Entity\\'.$path[0];
                    $class = new $class_path();
                    if ( $result instanceof $class ) {
                        $cell = $result->{$path[1]};
                    } else {
                        $cell = $result;
                        foreach($path as $p) {
                            $property = \Cake\Utility\Inflector::underscore($p);
                            $cell = $cell->$property;
                            if (null === $cell) {
                                break;
                            }
                        }
                    }
                ?>
                <td><?= h($cell); ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->first('<< ' . __('first')) ?>
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
        <?= $this->Paginator->last(__('last') . ' >>') ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>


