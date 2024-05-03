<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Batch/nav' ); ?>
</nav>
<div class="batches view large-9 medium-8 columns content">
	<?= $this->Html->link(
		__( 'Print label' ),
		[ 'action' => 'print', $batch->id, 'view', $batch->id ],
		[ 'class' => 'button print-button' ] );
	?>
    <h3><?= __( 'Batch:' ) . ' ' . h( $batch->crossing_batch ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= $this->Number->format( $batch->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Crossing' ) ?></th>
            <td><?= $batch->has( 'crossing' ) ? $this->Html->link( $batch->crossing->code,
					[ 'controller' => 'Crossings', 'action' => 'view', $batch->crossing->id ] ) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date Sowed' ) ?></th>
            <td><?= h( $batch->date_sowed ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Seeds Sowed' ) ?></th>
            <td><?= $this->Number->format( $batch->numb_seeds_sowed ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Sprouts Grown' ) ?></th>
            <td><?= $this->Number->format( $batch->numb_sprouts_grown ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Seed Tray' ) ?></th>
            <td><?= h( $batch->seed_tray ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date Planted' ) ?></th>
            <td><?= h( $batch->date_planted ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Numb Sprouts Planted' ) ?></th>
            <td><?= $this->Number->format( $batch->numb_sprouts_planted ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Patch' ) ?></th>
            <td><?= h( $batch->patch ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Created' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $batch->created ) ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Modified' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $batch->modified ) ) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __( 'Note' ) ?></h4>
		<?= $this->Text->autoParagraph( h( $batch->note ) ); ?>
    </div>
    <div class="related">
        <h4><?= __( 'Related Varieties' ) ?></h4>
		<?php if ( ! empty( $batch->varieties ) ): ?>
            <table>
                <tr>
                    <th scope="col" class="id"><?= __( 'Id' ) ?></th>
                    <th scope="col"><?= __( 'Convar' ) ?></th>
                    <th scope="col" class="actions"><?= __( 'Actions' ) ?></th>
                </tr>
				<?php foreach ( $batch->varieties as $varieties ): ?>
                    <tr>
                        <td class="id"><?= h( $varieties->id ) ?></td>
                        <td><?= h( $varieties->convar ) ?></td>
                        <td class="actions">
							<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Varieties', 'action' => 'view', $varieties->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
							<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Varieties', 'action' => 'edit', $varieties->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
							<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Varieties', 'action' => 'delete', $varieties->id ], [
									'escapeTitle' => false,
									'alt'         => __( 'Delete' ),
									'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
										$varieties->convar, $varieties->id )
								] ) ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>
		<?php endif; ?>
    </div>

    <div class="related">
		<?php if ( ! empty( $marks ) ): ?>
            <?= $this->element( 'Mark/photos', [ 'marks' => $marks ] ) ?>

			<?php foreach ( $marks as $mark_type => $mark_values ): ?>
				<?php if ( $mark_values->count() ): ?>
                    <h4><?= h( $mark_type ) ?></h4>
					<?= $this->element( 'Mark/list', [ 'markValues' => $mark_values ] ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
    </div>
</div>
