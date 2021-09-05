<ul class="side-nav">
    <li class="heading"><?= __( 'Marks' ) ?></li>
    <li><?= $this->Html->link( __( 'Mark Tree' ), [ 'controller' => 'Marks', 'action' => 'addTreeMark' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Mark Tree (Scanner)' ),
			[ 'controller' => 'Marks', 'action' => 'addTreeMarkByScanner' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Mark Tree (Mobile)' ),
            [ 'controller' => 'Spa', 'action' => 'index', '#' => '/marks/select-form' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Mark Variety' ), [ 'controller' => 'Marks', 'action' => 'addVarietyMark' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Mark Batch' ), [ 'controller' => 'Marks', 'action' => 'addBatchMark' ] ) ?></li>
    <li><?= $this->Html->link( __( 'List Marks' ), [ 'controller' => 'Marks', 'action' => 'index' ] ) ?></li>
    <li class="heading"><?= __( 'Forms' ) ?></li>
    <li><?= $this->Html->link( __( 'New Form' ), [ 'controller' => 'MarkForms', 'action' => 'add' ] ) ?></li>
    <li><?= $this->Html->link( __( 'List Forms' ), [ 'controller' => 'MarkForms', 'action' => 'index' ] ) ?></li>
    <li class="heading"><?= __( 'Properties' ) ?></li>
    <li><?= $this->Html->link( __( 'New Property' ),
			[ 'controller' => 'MarkFormProperties', 'action' => 'add' ] ) ?></li>
    <li><?= $this->Html->link( __( 'List Properties' ),
			[ 'controller' => 'MarkFormProperties', 'action' => 'index' ] ) ?></li>
    <li class="heading"><?= __( 'Scanner Codes' ) ?></li>
    <li><?= $this->Html->link( __( 'New Scanner Code' ),
			[ 'controller' => 'MarkScannerCodes', 'action' => 'add' ] ) ?></li>
    <li><?= $this->Html->link( __( 'List Scanner Codes' ),
			[ 'controller' => 'MarkScannerCodes', 'action' => 'index' ] ) ?></li>
    <li><?= $this->Html->link( __( 'Print Submit Form Label' ),
			[ 'controller' => 'MarkScannerCodes', 'action' => 'printSubmit' ] ) ?>
    </li>
</ul>
