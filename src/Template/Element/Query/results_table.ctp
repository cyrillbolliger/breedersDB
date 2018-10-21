<div class="scroll-box">
    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <?php foreach ( $columns as $column_key => $column_name ): ?>
                <th scope="col"><?= $this->Paginator->sort( $column_key, $column_name ) ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $results as $result ): ?>
            <tr>
                <?php foreach ( $columns as $column_key => $column_translation ): ?>
                    <?php if ( false !== strpos( $column_key, 'MarksView.value' ) ) {
                        $cell = $this->DataExtractor->getMarkValueCell( $column_key, $result );
                    } else {
                        $cell = $this->DataExtractor->getCell( $column_key, $result );
                    }
                    ?>
                    <td class="index_inline_list"><?= $cell ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
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


