<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element( 'Tree/nav' ); ?>
</nav>

<?php $filter = json_encode( [
    'controller' => 'trees',
    'action'     => 'getTree',
    'element'    => 'eliminate_form',
    'fields'     => [
        'publicid'
    ]
] ); ?>

<div class="large-9 medium-8 columns content">
    <h3><?= __( 'Eliminate Tree (by scanner)' ) ?></h3>
    <div>
        <input type="text" class="get_tree scanner" autofocus="autofocus" data-filter='<?= $filter ?>'
               placeholder="<?= __( 'Enter publicid...' ) ?>">
    </div>
</div>

<div id="tree_container" class="trees form plant large-9 medium-8 columns content">
</div>
