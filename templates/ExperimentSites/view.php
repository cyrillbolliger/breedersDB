<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="experimentSites view large-9 medium-8 columns content">
    <h3><?= __( 'Experiment Site:' ) . ' ' . h( $experimentSite->name ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= $this->Number->format( $experimentSite->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Name' ) ?></th>
            <td><?= h( $experimentSite->name ) ?></td>
        </tr>
    </table>
    <div class="related">
		<?php if ( ! empty( $experimentSite->users ) ): ?>
            <h4><?= __( 'Related Users' ) ?></h4>
			<table>
                <tr>
                    <th scope="col" class="id"><?= __( 'Id' ) ?></th>
                    <th scope="col"><?= __( 'Email' ) ?></th>
                    <th scope="col" class="actions"><?= __( 'Actions' ) ?></th>
                </tr>
				<?php foreach ( $experimentSite->users as $user ): ?>
                    <tr>
                        <td class="id"><?= h( $user->id ) ?></td>
                        <td><?= h( $user->email ) ?></td>
                        <td class="actions">
							<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Users', 'action' => 'view', $user->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
							<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Users', 'action' => 'edit', $user->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>
		<?php endif; ?>
    </div>
</div>
