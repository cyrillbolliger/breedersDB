<?php if ( $column->is_numerical ): ?>
    <?php if ( empty( $mark ) ): ?>
        <div class="query_result_cell"></div>
        <div class="query_result_cell"></div>
    <?php else: ?>
        <div class="query_result_cell mark_plot">
            <?php
            $min    = $column->validation_rule['min'];
            $max    = $column->validation_rule['max'];
            $width  = $max - $min;
            $median = ( ( $mark->value->median - $min ) / $width ) * 100;
            $min    = ( ( $mark->value->min - $min ) / $width ) * 100;
            $length = ( ( $mark->value->max - $mark->value->min ) / $width ) * 100;
            ?>
            <div class="mark_plot-line mark_plot-extremes" style="left: <?= $min ?>%; width: <?= $length ?>%;"></div>
            <div class="mark_plot-line mark_plot-median" style="left: 0; width: <?= $median ?>%;"></div>
        </div>
        <div class="query_result_cell mark_col">
            <table class="mark_stats">
                <tr>
                    <td><?= __x( 'short: median', 'med' ) ?></td>
                    <td><?= number_format( $mark->value->median, 1 ) ?></td>
                    <td><?= __x( 'short: count', 'cnt' ) ?></td>
                    <td><?= $mark->value->count ?></td>
                </tr>
                <tr>
                    <td><?= __x( 'short: average', 'avg' ) ?></td>
                    <td><?= number_format( $mark->value->avg, 1 ) ?></td>
                    <td><?= __x( 'short: minimum', 'min' ) ?></td>
                    <td><?= $mark->value->min ?></td>
                </tr>
                <tr>
                    <td><?= __x( 'short: standard deviation', 'std' ) ?></td>
                    <td><?= number_format( $mark->value->std, 1 ) ?></td>
                    <td><?= __x( 'short: maximum', 'max' ) ?></td>
                    <td><?= $mark->value->max ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>
<?php elseif ( ! $column->is_text ): ?>
    <?php if ( empty( $mark ) ): ?>
        <div class="query_result_cell"></div>
    <?php else: ?>
        <div class="mark_col query_result_cell"><?= $mark->value ?></div>
    <?php endif; ?>
<?php endif; ?>
<?php if ( empty( $mark ) ): ?>
    <div class="query_result_cell"></div>
<?php else: ?>
    <div class="query_result_cell mark_col">
        <table class="mark_values">
            <?php
            $values = $mark->values->toArray();
            $count  = 0;
            $perRow = $column->is_text ? 1 : 4;
            $rows   = ceil( count( $values ) / $perRow );
            for ( $i = 0; $i < $rows && isset( $values[ $count ] ); $i ++ ): ?>
                <tr>
                    <?php for ( $j = 0; $j < $perRow; $j ++ ): ?>
                        <td>
                            <?php
                            if ( isset( $values[ $count ] ) ) {
                                echo '<span class="mark_value mark_value-' . key( $values[ $count ] ) . '">' .
                                     current( $values[ $count ] ) .
                                     '</span>';
                            }
                            $count ++;
                            ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
    </div>
<?php endif; ?>
