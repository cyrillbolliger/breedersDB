<nav class="large-3 medium-4 columns" id="actions-sidebar">
	<?= $this->element( 'Setting/nav' ); ?>
</nav>
<div class="rootstocks view large-9 medium-8 columns content">
    <h3><?= __( 'Rootstock:' ) . ' ' . h( $rootstock->name ) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __( 'Id' ) ?></th>
            <td><?= $this->Number->format( $rootstock->id ) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __( 'Name' ) ?></th>
            <td><?= h( $rootstock->name ) ?></td>
        </tr>
    </table>
</div>
