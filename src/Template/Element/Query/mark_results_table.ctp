<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
		<?php foreach ( $regular_columns as $column_key => $column_name ): ?>
            <th scope="col" rowspan="2"><?= $column_name ?></th>
		<?php endforeach; ?>
		<?php foreach ( $mark_columns as $column ): ?>
			<?php $colspan = $column->is_numerical ? 3 : 2 ?>
            <th scope="col" colspan="<?= $colspan ?>"><?= $column->name ?></th>
		<?php endforeach; ?>
    </tr>
    <tr>
		<?php foreach ( $mark_columns as $column ): ?>
			<?php if ( $column->is_numerical ): ?>
                <td><?= __( 'Plot' ) ?></td>
                <td><?= __( 'Stats' ) ?></td>
			<?php else: ?>
                <td><?= __( 'Value' ) ?></td>
			<?php endif; ?>
            <td><?= __( 'Values' ) ?></td>
		<?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $results as $result ): ?>
        <tr>
			<?php foreach ( $regular_columns as $column_key => $column_value ): ?>
                <td><?= $result->$column_key; ?></td>
			<?php endforeach; ?>
			
			<?php foreach ( $mark_columns as $column ): ?>
                <?php $marks = $result->marks->toArray(); ?>
				<?= $this->element( 'Query/mark_value', [
					'mark' => array_key_exists( $column->id, $marks ) ? $marks[ $column->id ] : null,
					'column' => $column
				] ); ?>
			<?php endforeach; ?>
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