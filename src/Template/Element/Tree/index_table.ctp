<table>
    <thead>
    <tr>
        <th scope="col" class="id"><?= $this->Paginator->sort( 'id' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'publicid' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'convar' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'row' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'offset' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'note' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'date_eliminated', __( 'Eliminated' ) ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'modified' ) ?></th>
        <th scope="col" class="actions noprint"><?= __( 'Actions' ) ?></th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $trees as $tree ): ?>
        <tr>
            <td class="id"><?= $this->Number->format( $tree->id ) ?></td>
            <td><?= h( $tree->publicid ) ?></td>
            <td><?= $tree->has( 'Convar' ) ? $this->Html->link( $tree->convar,
					[ 'controller' => 'Varieties', 'action' => 'view', $tree->variety_id ] ) : '' ?></td>
            <td><?= $tree->has( 'row' ) ? $this->Html->link( $tree->row,
					[ 'controller' => 'Rows', 'action' => 'view', $tree->row_id ] ) : '' ?></td>
            <td><?= $this->Number->format( $tree->offset ) ?></td>
            <td><?= $tree->note ? $this->Html->link( __( 'Read' ), [ 'action' => 'view', $tree->id ] ) : '' ?></td>
            <td><?= $tree->date_eliminated ? __( 'eliminated' ) : '' ?></td>
            <td><?= h( $this->LocalizedTime->getUserTime( $tree->modified ) ) ?></td>
            <td class="actions noprint">
				<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
					[ 'action' => 'view', $tree->id ], [ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
				<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
					[ 'action' => 'edit', $tree->id ], [ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
				<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
					[ 'action' => 'delete', $tree->id ], [
						'escapeTitle' => false,
						'alt'         => __( 'Delete' ),
						'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?', $tree->publicid,
							$tree->id )
					] ) ?>
            </td>
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>
<div class="paginator">
    <ul class="pagination">
		<?= $this->Paginator->first( '<< ' . __( 'first' ) ) ?>
		<?= $this->Paginator->prev( '< ' . __( 'previous' ) ) ?>
		<?= $this->Paginator->numbers() ?>
		<?= $this->Paginator->next( __( 'next' ) . ' >' ) ?>
		<?= $this->Paginator->last( __( 'last' ) . ' >>' ) ?>
    </ul>
    <p><?= $this->Paginator->counter( [ 'format' => __( 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total' ) ] ) ?></p>
</div>


