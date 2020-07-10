<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="experimentSites index large-9 medium-8 columns content">
    <h3><?= __( 'Experiment Sites' ) ?></h3>
    <table>
        <thead>
        <tr>
            <th scope="col" class="id"><?= $this->Paginator->sort( 'id' ) ?></th>
            <th scope="col"><?= $this->Paginator->sort( 'name' ) ?></th>
            <th scope="col" class="actions noprint"><?= __( 'Actions' ) ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $experimentSites as $experimentSite ): ?>
            <tr>
                <td class="id"><?= $this->Number->format( $experimentSite->id ) ?></td>
                <td><?= h( $experimentSite->name ) ?></td>
                <td class="actions noprint">
					<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
						[ 'action' => 'view', $experimentSite->id ],
						[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
					<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
						[ 'action' => 'edit', $experimentSite->id ],
						[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
					<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
						[ 'action' => 'delete', $experimentSite->id ], [
							'escapeTitle' => false,
							'alt'         => __( 'Delete' ),
							'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
								$experimentSite->name, $experimentSite->id )
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
