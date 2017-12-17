<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="markFormPropertyTypes index large-9 medium-8 columns content">
    <h3><?= __( 'Mark Property Types' ) ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort( 'id' ) ?></th>
            <th scope="col"><?= $this->Paginator->sort( 'name' ) ?></th>
            <th scope="col" class="actions noprint"><?= __( 'Actions' ) ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $markFormPropertyTypes as $markFormPropertyType ): ?>
            <tr>
                <td class="id"><?= $this->Number->format( $markFormPropertyType->id ) ?></td>
                <td><?= h( $markFormPropertyType->name ) ?></td>
                <td class="actions noprint">
					<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
						[ 'action' => 'view', $markFormPropertyType->id ],
						[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
					<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
						[ 'action' => 'edit', $markFormPropertyType->id ],
						[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
					<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
						[ 'action' => 'delete', $markFormPropertyType->id ], [
							'escapeTitle' => false,
							'alt'         => __( 'Delete' ),
							'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
								$markFormPropertyType->name, $markFormPropertyType->id )
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
</div>
