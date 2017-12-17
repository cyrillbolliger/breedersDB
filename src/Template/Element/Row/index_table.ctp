<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th scope="col" class="id"><?= $this->Paginator->sort( 'id' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'code', __( 'Name' ) ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'note' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'date_created' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'date_eliminated' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'modified' ) ?></th>
        <th scope="col" class="actions noprint"><?= __( 'Actions' ) ?></th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $rows as $row ): ?>
        <tr>
            <td class="id"><?= $this->Number->format( $row->id ) ?></td>
            <td><?= h( $row->code ) ?></td>
            <td><?= $row->note ? $this->Html->link( __( 'Read' ), [ 'action' => 'view', $row->id ] ) : '' ?></td>
            <td><?= h( $row->date_created ) ?></td>
            <td><?= h( $row->date_eliminated ) ?></td>
            <td><?= h( $this->LocalizedTime->getUserTime( $row->modified ) ) ?></td>
            <td class="actions noprint">
				<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
					[ 'action' => 'view', $row->id ], [ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
				<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
					[ 'action' => 'edit', $row->id ], [ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
				<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
					[ 'action' => 'delete', $row->id ], [
						'escapeTitle' => false,
						'alt'         => __( 'Delete' ),
						'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?', $row->code, $row->id )
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