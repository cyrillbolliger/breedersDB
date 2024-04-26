<input type="hidden" name="tree_id" id="tree_id" value="<?= $tree->id ?>">
<table>
    <tr>
        <th scope="row"><?= __( 'Publicid' ) ?></th>
        <th scope="row"><?= __( 'Name' ) ?></th>
        <th scope="row"><?= __( 'Variety' ) ?></th>
        <th scope="row"><?= __( 'Experiment Site' ) ?></th>
        <th scope="row"><?= __( 'Row' ) ?></th>
        <th scope="row"><?= __( 'Offset' ) ?></th>
		<?php if ( isset( $zpl ) && $zpl ): ?>
            <th scope="row" class="actions noprint"><?= __( 'Actions' ) ?></th>
		<?php endif; ?>
    </tr>
    <tr>
        <td><?= h( $tree->publicid ) ?></td>
        <td><?= h( $tree->name ) ?></td>
        <td><?= $tree->has( 'variety' ) ? $tree->convar : '' ?></td>
        <td><?= $tree->has( 'experiment_site' ) ? $tree->experiment_site->name : '' ?></td>
        <td><?= $tree->has( 'row' ) ? $tree->row->code : '' ?></td>
        <td><?= $this->Number->format( $tree->offset ) ?></td>
		<?php if ( isset( $zpl ) && $zpl ): ?>
            <td class="actions noprint">
				<?= $this->Html->link( '<i class="fa fa-print print-icon" aria-hidden="true"></i>',
					false,
					[
						'escapeTitle' => false,
						'class'       => 'zpl_print prevent_default action-icon-link',
						'data-zpl'    => $zpl,
						'alt'         => __( 'Print' )
					] ) ?>
            </td>
		<?php endif; ?>
    </tr>
</table>

<?php if ( isset( $zpl ) && $zpl ): ?>
    <script>
        app.instantiatePrintButtons();
    </script>
<?php endif; ?>
