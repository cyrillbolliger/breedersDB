<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>

<div class="motherTrees view large-9 medium-8 columns content">
    <h3><?= __( 'Mother Tree:' ) . ' ' . h( $motherTree->code ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= $this->Number->format( $motherTree->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Mother Tree' ) ?></th>
            <td><?= $motherTree->has( 'tree' ) ? $this->Html->link( h( $motherTree->tree->publicid ),
					[ 'controller' => 'Trees', 'action' => 'view', $motherTree->tree->id ] ) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Crossing' ) ?></th>
            <td><?= $motherTree->has( 'crossing' ) ? $this->Html->link( h( $motherTree->crossing->code ),
					[ 'controller' => 'Crossings', 'action' => 'view', $motherTree->crossing->id ] ) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Planed' ) ?></th>
            <td><?= $motherTree->planed ? __( 'Yes' ) : __( 'No' ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Portions' ) ?></th>
            <td><?= $this->Number->format( $motherTree->numb_portions ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Flowers' ) ?></th>
            <td><?= $this->Number->format( $motherTree->numb_flowers ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Fruits' ) ?></th>
            <td><?= $this->Number->format( $motherTree->numb_fruits ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Seeds' ) ?></th>
            <td><?= $this->Number->format( $motherTree->numb_seeds ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date Pollen Harvested' ) ?></th>
            <td><?= h( $motherTree->date_pollen_harvested ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date Impregnated' ) ?></th>
            <td><?= h( $motherTree->date_impregnated ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date Fruit Harvested' ) ?></th>
            <td><?= h( $motherTree->date_fruit_harvested ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Created' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $motherTree->created ) ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Modified' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $motherTree->modified ) ) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __( 'Target' ) ?></h4>
		<?= $this->Text->autoParagraph( h( $motherTree->target ) ); ?>
    </div>
    <div class="row">
        <h4><?= __( 'Note' ) ?></h4>
		<?= $this->Text->autoParagraph( h( $motherTree->note ) ); ?>
    </div>
</div>
