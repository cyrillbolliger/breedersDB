<?php if ( $meta->aggregated ): ?>
	<?php if ( is_object( $mark ) ): ?>
        <td><?= $mark->value->{$meta->display} ?></td>
        <td class="mark_plot">
			<?php
			$width  = $meta->max - $meta->min;
			$median = ( ( $mark->value->median - $meta->min ) / $width ) * 100;
			$min    = ( ( $mark->value->min - $meta->min ) / $width ) * 100;
			$length = ( ( $mark->value->max - $mark->value->min ) / $width ) * 100;
			?>
            <div class="mark_plot-line mark_plot-extremes" style="left: <?= $min ?>%; width: <?= $length ?>%;"></div>
            <div class="mark_plot-line mark_plot-median" style="left: 0; width: <?= $median ?>%;"></div>
        </td>
        <td class="index_inline_list">
            <ul>
				<?php foreach ( $mark->value as $key => $value ): ?>
                    <li><?= $key . ': ' . $value ?></li>
				<?php endforeach; ?>
            </ul>
        </td>
        <td class="index_inline_list">
            <ul>
				<?php foreach ( $mark->values as $valueobj ): ?>
                    <li><?= $valueobj->value ?>
                        <div class="mark_details"><?php // todo metadata of $valueobj ?></div>
                    </li>
				<?php endforeach; ?>
            </ul>
        </td>
	<?php else: ?>
        <td></td>
        <td class="mark_plot"></td>
        <td class="index_inline_list"></td>
        <td class="index_inline_list"></td>
	<?php endif; ?>
<?php else: ?>
	<?php if ( is_object( $mark ) ): ?>
        <td class="index_inline_list">
            <ul>
				<?php foreach ( $mark->values as $valueobj ): ?>
                    <li><?= $valueobj->value ?>
                        <div class="mark_details"><?php // todo metadata of $valueobj ?></div>
                    </li>
				<?php endforeach; ?>
            </ul>
        </td>
	<?php else: ?>
        <td class="index_inline_list"></td>
	<?php endif; ?>
<?php endif; ?>
