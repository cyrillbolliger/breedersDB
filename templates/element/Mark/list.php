<table>
    <tr>
        <th scope="col" class="id"><?= __( 'Id' ) ?></th>
        <th scope="col"><?= __( 'Date' ) ?></th>
        <th scope="col"><?= __( 'Property' ) ?></th>
        <th scope="col"><?= __( 'Value' ) ?></th>
        <th scope="col"><?= __( 'Author' ) ?></th>
        <th scope="col"><?= __( 'Marked object' ) ?></th>
        <th scope="col"><?= __( 'Exceptional' ) ?></th>
        <th scope="col" class="actions"><?= __( 'Actions' ) ?></th>
    </tr>
	<?php foreach ( $markValues as $mark_value ): ?>
		<?php
		switch ( true ) {
			case $mark_value->mark->tree_id:
				$markedOn = $this->Html->link( __( 'Tree:' ) . ' ' . h( $mark_value->mark->tree->publicid ),
					[ 'controller' => 'Trees', 'action' => 'view', $mark_value->mark->tree_id ] );
				break;

			case $mark_value->mark->variety_id:
				$markedOn = $this->Html->link( __( 'Variety:' ) . ' ' . h( $mark_value->mark->variety->convar ),
					[ 'controller' => 'Varieties', 'action' => 'view', $mark_value->mark->variety_id ] );
				break;

			case $mark_value->mark->batch_id:
				$markedOn = __( 'Batch' );
				break;

			default:
				$markedOn = '';
				break;
		}
		?>
        <?php
        if ('PHOTO' === $mark_value->mark_form_property->field_type) {
            $imgUrl = $this->Url->build(['prefix' => 'REST1', 'controller' => 'Photos', 'action' => 'view', $mark_value->value]);
            $value = '<a href="'. $imgUrl .'" target="_blank"><i class="fa fa-picture-o" aria-hidden="true"></i></a>';
        } else {
            $value = h( $mark_value->value );
        }
        ?>
        <tr>
            <td class="id"><?= h( $mark_value->id ) ?></td>
            <td><?= h( $mark_value->mark->date ) ?></td>
            <td><?= h( $mark_value->mark_form_property->name ) ?></td>
            <td><?= $value ?></td>
            <td><?= h( $mark_value->mark->author ) ?></td>
            <td><?= $markedOn ?></td>
            <td><?= h( $mark_value->exceptional_mark ? __( 'True' ) : '' ) ?></td>
            <td class="actions">
				<?= $this->Html->link( '<i class="fa fa-eye view-icon" aria-hidden="true"></i>',
					[ 'controller' => 'Marks', 'action' => 'view', $mark_value->mark->id ],
					[ 'escapeTitle' => false, 'alt' => __( 'View' ) ] ) ?>
                <?php if (! 'PHOTO' === $mark_value->mark_form_property->field_type): ?>
                    <?= $this->Html->link( '<i class="fa fa-pencil edit-icon" aria-hidden="true"></i>',
                                           [ 'controller' => 'MarkValues', 'action' => 'edit', $mark_value->id ],
                                           [ 'escapeTitle' => false, 'alt' => __( 'Edit' ) ] ) ?>
                <?php endif; ?>
				<?= $this->Form->postLink( '<i class="fa fa-trash-o delete-icon" aria-hidden="true"></i>',
					[ 'controller' => 'MarkValues', 'action' => 'delete', $mark_value->id ], [
						'escapeTitle' => false,
						'alt'         => __( 'Delete' ),
//						'block'       => true,
						'confirm'     => __( 'Are you sure you want to delete "{0}" (id: {1,number,integer})?',
							$mark_value->mark_form_property->name, $mark_value->id )
					] ) ?>
            </td>
        </tr>
	<?php endforeach; ?>
</table>
