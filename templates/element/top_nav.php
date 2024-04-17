<ul class="top-nav">
    <li><?= $this->Html->link( __( 'Crossings' ), [ 'controller' => 'Crossings', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Batches' ), [ 'controller' => 'Batches', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Varieties' ), [ 'controller' => 'Varieties', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Trees' ), [ 'controller' => 'Trees', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Rows' ), [ 'controller' => 'Rows', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Marks' ), [ 'controller' => 'Marks', 'action' => 'index' ] ) ?></li>
    <?php if ($userLevel === 0): ?><li><?= $this->Html->link( __( 'Queries' ), [ 'controller' => 'Spa', 'action' => 'index', '#' => '/queries' ] ) ?></li><?php endif; ?>
</ul>
