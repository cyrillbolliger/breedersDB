<h3><?= __( 'Mark details' ) ?></h3>
<table class="vertical-table">
    <tr>
        <th scope="row"><?= __( 'Date' ) ?></th>
        <td><?= h( $marksView->date ); ?></td>
    </tr>
    <tr>
        <th scope="row"><?= __( 'Author' ) ?></th>
        <td><?= h( $marksView->author ); ?></td>
    </tr>
    <tr>
        <th scope="row"><?= __( 'Exceptional Mark' ) ?></th>
        <td><?= $marksView->exceptional_mark ? __( 'Yes' ) : __( 'No' ); ?></td>
    </tr>
</table>
<h3><?= __( 'Details about the marked object' ) ?></h3>
<?php if ( ! empty( $marksView->batches_view ) ): ?>
	<?php $batch = $marksView->batches_view; ?>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Marked object' ) ?></th>
            <td><?= __( 'Batch' ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Crossing' ) ?></th>
            <td><?= h( $batch->crossing_batch ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Sowed') ?></th>
            <td><?= h($batch->date_sowed) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Seeds Sowed') ?></th>
            <td><?= $this->Number->format($batch->numb_seeds_sowed) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Sprouts Grown') ?></th>
            <td><?= $this->Number->format($batch->numb_sprouts_grown) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Seed Tray') ?></th>
            <td><?= h($batch->seed_tray) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Date Planted') ?></th>
            <td><?= h($batch->date_planted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Numb Sprouts Planted') ?></th>
            <td><?= $this->Number->format($batch->numb_sprouts_planted) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Patch') ?></th>
            <td><?= h($batch->patch) ?></td>
        </tr>
        <tr>
            <td colspan="2" class="inline-note">
                <span class="inline-note-title"><?= __( 'Note' ) ?></span>
				<?= $this->Text->autoParagraph( h( $batch->note ) ); ?>
            </td>
        </tr>
    </table>
<?php elseif ( ! empty( $marksView->varieties_view ) ): ?>
	<?php $variety = $marksView->varieties_view; ?>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Marked object' ) ?></th>
            <td><?= __( 'Variety' ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Convar' ) ?></th>
            <td><?= h( $variety->convar ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Official Name' ) ?></th>
            <td><?= h( $variety->official_name ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Acronym') ?></th>
            <td><?= h($variety->acronym) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Breeder Variety Code') ?></th>
            <td><?= h($variety->breeder_variety_code) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Plant Breeder') ?></th>
            <td><?= h($variety->plant_breeder) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Registration') ?></th>
            <td><?= h($variety->registration) ?></td>
        </tr>
        <tr>
            <td colspan="2" class="inline-note">
                <span class="inline-note-title"><?= __( 'Description' ) ?></span>
				<?= $this->Text->autoParagraph( h( $variety->description ) ); ?>
            </td>
        </tr>
    </table>
<?php else: ?>
	<?php $tree = $marksView->trees_view; ?>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Marked object' ) ?></th>
            <td><?= __( 'Tree' ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Publicid' ) ?></th>
            <td><?= $this->Html->link( h( $tree->publicid ),
					[ 'controller' => 'trees', 'action' => 'view', $tree->id ],
					[ 'target' => '_blank' ] ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Convar' ) ?></th>
            <td><?= h( $tree->convar ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date planted' ) ?></th>
            <td><?= h( $tree->date_planted ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Date eliminated' ) ?></th>
            <td><?= h( $tree->date_eliminated ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Experiment site' ) ?></th>
            <td><?= h( $tree->experiment_site ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Row' ) ?></th>
            <td><?= h( $tree->row ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Offset' ) ?></th>
            <td><?= h( $tree->offset ); ?></td>
        </tr>
        <tr>
            <td colspan="2" class="inline-note">
                <span class="inline-note-title"><?= __( 'Note' ) ?></span>
                <?= $this->Text->autoParagraph( h( $tree->note ) ); ?>
            </td>
        </tr>
    </table>
<?php endif; ?>
