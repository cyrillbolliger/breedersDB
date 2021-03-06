<table>
    <thead>
    <tr>
        <th scope="col" class="id"><?= $this->Paginator->sort( 'id' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'name' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'field_type', __( 'Data Type' ) ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'mark_form_property_type_id', __( 'Property Type' ) ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'note' ) ?></th>
        <th scope="col"><?= $this->Paginator->sort( 'modified' ) ?></th>
        <th scope="col" class="actions noprint"><?= __( 'Actions' ) ?></th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $markFormProperties as $markFormProperty ): ?>
        <tr>
            <td class="id"><?= $this->Number->format( $markFormProperty->id ) ?></td>
            <td><?= h( $markFormProperty->name ) ?></td>
            <td><?= h( $markFormProperty->field_type ) ?></td>
            <td><?= $markFormProperty->has( 'mark_form_property_type' ) ? $markFormProperty->mark_form_property_type->name : '' ?></td>
            <td><?= $markFormProperty->note ? $this->Html->link( __( 'Read' ),
					[ 'action' => 'view', $markFormProperty->id ] ) : '' ?></td>
            <td><?= h( $this->LocalizedTime->getUserTime( $markFormProperty->modified ) ) ?></td>
            <td class="actions noprint">
				<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
					[ 'action' => 'view', $markFormProperty->id ],
					[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
				<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
					[ 'action' => 'edit', $markFormProperty->id ],
					[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
				<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
					[ 'action' => 'delete', $markFormProperty->id ], [
						'escapeTitle' => false,
						'alt'         => __( 'Delete' ),
						'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
							$markFormProperty->name,
							$markFormProperty->id )
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
    <p><?= $this->Paginator->counter( __( 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total' ) ) ?></p>
</div>
