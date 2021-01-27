<div class="query_results_table" style="grid-template-columns: repeat(<?= count( $columns ) ?>, 1fr)">
    <?php foreach ( $columns as $column_key => $column_name ): ?>
        <div class="query_table_header query_table_header_last">
            <?= $this->Paginator->sort( $column_key, $column_name ) ?>
        </div>
    <?php endforeach; ?>

    <?php foreach ( $results as $result ): ?>
        <?php foreach ( $columns as $column_key => $column_translation ): ?>
            <?php if ( false !== strpos( $column_key, 'MarksView.value' ) ) {
                $cell = $this->DataExtractor->getMarkValueCell( $column_key, $result );
            } else {
                $cell = $this->DataExtractor->getCell( $column_key, $result );
            }
            ?>
            <div class="query_result_cell index_inline_list"><?= $cell ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

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


