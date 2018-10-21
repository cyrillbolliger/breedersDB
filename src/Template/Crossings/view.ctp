<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Crossing/nav' ); ?>
</nav>
<div class="crossings view large-9 medium-8 columns content">
    <h3><?= __( 'Crossing:' ) . ' ' . h( $crossing->code ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= h( $crossing->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Mother variety' ) ?></th>
            <td><?= $crossing->mother_variety_id ? $this->Html->link( $mother_variety->convar,
					[ 'controller' => 'Varieties', 'action' => 'view', $crossing->mother_variety_id ] ) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Father variety' ) ?></th>
            <td><?= $crossing->father_variety_id ? $this->Html->link( $father_variety->convar,
					[ 'controller' => 'Varieties', 'action' => 'view', $crossing->father_variety_id ] ) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Created' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $crossing->created ) ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Modified' ) ?></th>
            <td><?= h( $this->LocalizedTime->getUserTime( $crossing->modified ) ) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __( 'Target' ) ?></h4>
        <?= $this->Text->autoParagraph( h( $crossing->target ) ); ?>
    </div>
    <div class="related">
        <h4><?= __( 'Related Batches' ) ?></h4>
		<?php if ( ! empty( $crossing->batches ) ): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th scope="col" class="id"><?= __( 'Id' ) ?></th>
                    <th scope="col"><?= __( 'Code' ) ?></th>
                    <th scope="col"><?= __( 'Date Sowed' ) ?></th>
                    <th scope="col"><?= __( 'Numb Seeds Sowed' ) ?></th>
                    <th scope="col"><?= __( 'Seed Tray' ) ?></th>
                    <th scope="col"><?= __( 'Date Planted' ) ?></th>
                    <th scope="col"><?= __( 'Patch' ) ?></th>
                    <th scope="col" class="actions"><?= __( 'Actions' ) ?></th>
                </tr>
				<?php foreach ( $crossing->batches as $batches ): ?>
                    <tr>
                        <td class="id"><?= h( $batches->id ) ?></td>
                        <td><?= h( $batches->code ) ?></td>
                        <td><?= h( $batches->date_sowed ) ?></td>
                        <td><?= h( $batches->numb_seeds_sowed ) ?></td>
                        <td><?= h( $batches->seed_tray ) ?></td>
                        <td><?= h( $batches->date_planted ) ?></td>
                        <td><?= h( $batches->patch ) ?></td>
                        <td class="actions">
							<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Batches', 'action' => 'view', $batches->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
							<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Batches', 'action' => 'edit', $batches->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
							<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Batches', 'action' => 'delete', $batches->id ], [
									'escapeTitle' => false,
									'alt'         => __( 'Delete' ),
									'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
										$batches->code, $batches->id )
								] ) ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>
		<?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __( 'Related Mother Trees' ) ?></h4>
		<?php if ( ! empty( $crossing->mother_trees ) ): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th scope="col" class="id"><?= __( 'Id' ) ?></th>
                    <th scope="col"><?= __( 'Code' ) ?></th>
                    <th scope="col"><?= __( 'Publicid' ) ?></th>
                    <th scope="col"><?= __( 'Planed' ) ?></th>
                    <th scope="col"><?= __( 'Numb Portions' ) ?></th>
                    <th scope="col"><?= __( 'Numb Flowers' ) ?></th>
                    <th scope="col"><?= __( 'Numb Seeds' ) ?></th>
                    <th scope="col" class="actions"><?= __( 'Actions' ) ?></th>
                </tr>
				<?php foreach ( $crossing->mother_trees as $mother_tree ): ?>
                    <tr>
                        <td class="id"><?= h( $mother_tree->id ) ?></td>
                        <td><?= h( $mother_tree->code ) ?></td>
                        <td><?= $mother_tree->has( 'tree' ) ? h( $mother_tree->tree->publicid ) : __( 'Mother tree not set.' ) ?></td>
                        <td><?= h( $mother_tree->planed ) ?></td>
                        <td><?= $this->Number->format( $mother_tree->numb_portions ) ?></td>
                        <td><?= $this->Number->format( $mother_tree->numb_flowers ) ?></td>
                        <td><?= $this->Number->format( $mother_tree->numb_seeds ) ?></td>
                        <td class="actions">
							<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
								[ 'controller' => 'MotherTrees', 'action' => 'view', $mother_tree->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
							<?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
								[ 'controller' => 'MotherTrees', 'action' => 'edit', $mother_tree->id ],
								[ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
							<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
								[ 'controller' => 'Batches', 'action' => 'delete', $mother_tree->id ], [
									'escapeTitle' => false,
									'alt'         => __( 'Delete' ),
									'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1})?',
										$mother_tree->code, $mother_tree->id )
								] ) ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>
		<?php endif; ?>
    </div>
</div>
