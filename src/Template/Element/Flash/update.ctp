<?php
if ( ! isset( $params['escape'] ) || $params['escape'] !== false ) {
	$message = h( $message );
}
?>
<div class="message error">
	<?= $this->Html->link(
		$message,
		$this->Url->build( '/autoupdate.php', true ),
		[ 'style' => 'color: #fff;' ]
	) ?>
</div>
