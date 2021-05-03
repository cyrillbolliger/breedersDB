<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="graftings view large-9 medium-8 columns content">
    <h3><?= __( 'Grafting:' ) . ' ' . h( $grafting->name ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= $this->Number->format( $grafting->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Name' ) ?></th>
            <td><?= h( $grafting->name ) ?></td>
        </tr>
    </table>
</div>
