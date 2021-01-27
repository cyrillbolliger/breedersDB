<div class="">
    <?php
    // calculate the total number of columns
    $col_num = 0;
    if ( ! empty( $regular_columns ) ) {
        $col_num += count( $regular_columns );
    }
    if ( ! empty( $mark_columns ) ) {
        foreach ( $mark_columns as $column ) {
            if ( $column->is_numerical ) {
                $col_num += 3;
            } else if ( $column->is_text ) {
                $col_num += 1;
            } else {
                $col_num += 2;
            }
        }
    }
    ?>

    <div class="query_results_table" style="grid-template-columns: repeat(<?= $col_num ?>, 1fr)">
        <!-- HEADER COLUMNS -->
        <?php foreach ( $regular_columns as $column_key => $column_name ): ?>
            <div style="grid-row-end: span 2;" class="query_table_header query_table_header_last">
                <?= $this->Paginator->sort( $column_key, $column_name ) ?>
            </div>
        <?php endforeach; ?>
        <?php foreach ( $mark_columns as $column ): ?>
            <?php
            if ( $column->is_numerical ) {
                $colspan = 3;
            } elseif ( $column->is_text ) {
                $colspan = 1;
            } else {
                $colspan = 2;
            }
            ?>
            <div style="grid-column-end: span <?= $colspan ?>;"
                <?php if ( ! $column->is_text ): ?>
                    <?= 'colspan="' . $colspan . '"' ?>
                <?php endif; ?>
                 class="query_table_header mark_col mark_col_header">
                <?= $this->Paginator->sort( 'mark-' . $column->id, $column->name ) ?>
            </div>
        <?php endforeach; ?>
        <?php foreach ( $mark_columns as $column ): ?>
            <?php if ( $column->is_numerical ): ?>
                <div
                    class="query_table_header mark_col mark_col_subheader query_table_header_last"><?= __( 'Plot' ) ?></div>
                <div
                    class="query_table_header mark_col mark_col_subheader query_table_header_last"><?= __( 'Stats' ) ?></div>
            <?php elseif ( ! $column->is_text ): ?>
                <div
                    class="query_table_header mark_col mark_col_subheader query_table_header_last"><?= __( 'Value' ) ?></div>
            <?php endif; ?>
            <div
                class="query_table_header mark_col mark_col_subheader query_table_header_last"><?= __( 'Values' ) ?></div>
        <?php endforeach; ?>

        <!-- BODY COLUMNS -->
        <?php foreach ( $results as $result ): ?>
            <?php foreach ( $regular_columns as $column_key => $column_value ): ?>
                <div class="query_result_cell"><?= $result->$column_key; ?></div>
            <?php endforeach; ?>

            <?php foreach ( $mark_columns as $column ): ?>
                <?php $marks = $result->marks->toArray(); ?>
                <?= $this->element( 'Query/mark_value', [
                    'mark'   => array_key_exists( $column->id, $marks ) ? $marks[ $column->id ] : null,
                    'column' => $column
                ] ); ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>

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
