<ul class="side-nav">
    <li class="heading"><?= __( 'Trees' ) ?></li>
    <li><?= $this->Html->link( __( 'List Trees' ), [ 'action' => 'index' ] ) ?> </li>
    <li><?= $this->Html->link( __( 'New Tree' ), [ 'action' => 'add' ] ) ?> </li>
    <li><?= $this->Html->link( __( 'New Genuine Seedling' ), [ 'action' => 'addGenuineSeedling' ] ) ?> </li>
    <li><?= $this->Html->link( __( 'New Graft Tree' ), [ 'action' => 'addGraftTree' ] ) ?> </li>
    <li><?= $this->Html->link( __( 'Plant Tree' ), [ 'action' => 'plant' ] ) ?> </li>
    <li><?= $this->Html->link( __( 'Eliminate Tree' ), [ 'action' => 'eliminate' ] ) ?> </li>
</ul>