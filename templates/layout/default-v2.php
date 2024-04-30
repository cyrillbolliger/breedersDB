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

    <style>:root{
            --base-color:<?= \Cake\Core\Configure::read('Org.color') ?>;
            --color-primary-1: hsl(var(--base-color) 97% 18%);
            --color-primary-2: hsl(var(--base-color) 74% 26%);
            --color-primary-3: hsl(var(--base-color) 74% 32%);
            --color-primary-4: hsl(var(--base-color) 51% 39%);
            --color-primary-5: hsl(var(--base-color) 32% 44%);
            --color-primary-6: hsl(var(--base-color) 75% 36%);
            --color-primary-7: hsl(var(--base-color) 100% 32%);
            --color-primary-8: hsl(var(--base-color) 61% 76%);
            --color-primary-9: hsl(var(--base-color) 58% 82%);
            --color-primary-35: hsla(var(--base-color) 74% 32% / 50%);
}</style>

    <?= $css ? $this->Html->css( $css ) : '' ?>
    <?= $js ? $this->Html->script( $js, ['defer' => true] ) : '' ?>

	<?= $this->fetch( 'meta' ) ?>
	<?= $this->fetch( 'css' ) ?>
	<?= $this->fetch( 'script' ) ?>
</head>
<body>
<script>// noinspection JSAnnotator
    const webroot = '<?= $this->Url->build( '/', ['fullBase' => true] ) ?>';
    const csrfToken = '<?= $this->request->getAttribute('csrfToken') ?>';
    const cake = <?= $this->fetch( 'content' ) ?>;
</script>
<div id="q-app"></div>
</body>
</html>
