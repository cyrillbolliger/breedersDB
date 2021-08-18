<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html lang="<?= Cake\I18n\I18n::getLocale() ?>">
<head>
	<?= $this->Html->charset() ?>
    <meta name=viewport content="user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1,width=device-width">
    <title>Breeders DB</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#89d55b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <?= $css ? $this->Html->css( $css ) : '' ?>
    <?= $js ? $this->Html->script( $js, ['defer' => true] ) : '' ?>

	<?= $this->fetch( 'meta' ) ?>
	<?= $this->fetch( 'css' ) ?>
	<?= $this->fetch( 'script' ) ?>

    <script>
        let csrfToken = '<?= $this->request->getParam('_csrfToken') ?>';
        const webroot = '<?= $this->Url->build( '/', ['fullBase' => true] ) ?>';
        const urlbase = '<?= $this->Url->build( '/', ['fullBase' => false] ) ?>';
        const trans = {
            dateformat: '<?= __x( 'Date format', 'dd.mm.yy' ) ?>',
        };
    </script>
</head>
<body>
<script>// noinspection JSAnnotator
    const cake = <?= $this->fetch( 'content' ) ?></script>
<div id="q-app"></div>
</body>
</html>
