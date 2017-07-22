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
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= __($this->fetch('title')) ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    
    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('jquery-ui.min.css') ?>
    <?= $this->Html->css('select2.min.css') ?>
    <?= $this->Html->css('font-awesome.min.css') ?>
    <?= $this->Html->css('app.css') ?>
    <?= $this->Html->css('print.css') ?>
    
    <?= $this->Html->script('jquery-3.1.1.min.js') ?>
    <?= $this->Html->script('jquery-ui.min.js') ?>
    <?= $this->Html->script('select2.min.js') ?>
    <?= $this->Html->script('app.js') ?>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <script>
        var csrfToken = '<?= $this->request->params['_csrfToken'] ?>';
        var webroot = '<?= $this->Url->build('/', true); ?>';
        var trans = {
            dateformat: '<?= __x('Date format', 'dd.mm.yy') ?>',
            searching: '<?= __('Searching...') ?>',
            create_new_variety: '<?= __('Nothing found. Create new covar of:') ?>',
            uc_new: '<?= __('NEW') ?>',
            brain_prefill: '<?= __('Automatically prefilled') ?>',
            delete_element: '<?= __x('after the : the name of the element to delete is inserted',
                'Are you sure you want to delete:')?>',
            matching_elements: '<?= __('{0} matching elements for this mark were found.') ?>',
            no_tree_found: '<?= __('No tree could be found') ?>'
        };
    </script>
</head>
<body>
<nav class="top-bar expanded" data-topbar role="navigation">
    <?= $this->element('top_nav'); ?>
    <div class="top-bar-section">
        <ul class="right meta-nav">
            <?php
            $location = $this->request->session()->read('experiment_site_name');
            if ($location) :
                ?>
                <li><?= $this->Html->link(__('Location: {0}', $location),
                        ['controller' => 'ExperimentSites', 'action' => 'select']) ?></li>
            <?php endif; ?>
            <li><?= $this->Html->link(__('Settings'), ['controller' => 'Settings']) ?></li>
            <li><?= $this->Html->link(__('Logout'), ['controller' => 'Users', 'action' => 'logout']) ?></li>
        </ul>
    </div>
</nav>
<?= $this->Flash->render() ?>
<div class="container clearfix">
    <?= $this->fetch('content') ?>
</div>
<footer>
</footer>
</body>
</html>
