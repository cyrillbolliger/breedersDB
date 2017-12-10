<?php if ( $column->is_numerical ): ?>
    <td class="mark_plot">
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
    </td>
    <td class="index_inline_list">
        <ul>
			<?php foreach ( $mark->value as $key => $value ): ?>
                <li><?= $key . ': ' . $value ?></li>
			<?php endforeach; ?>
        </ul>
    </td>
<?php else: ?>
    <td><?= $mark->value ?></td>
<?php endif; ?>
<td class="index_inline_list">
    <ul>
		<?php foreach ( $mark->values as $value ): ?>
			<?php foreach ( $value as $mark_id => $v ): ?>
                <li><?= $v ?>
                    <div class="mark_details"><?php // todo: get mark obj ?></div>
                </li>
			<?php endforeach; ?>
		<?php endforeach; ?>
    </ul>
</td>

