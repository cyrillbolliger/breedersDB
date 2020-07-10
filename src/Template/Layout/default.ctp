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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
		<?= __( $this->fetch( 'title' ) ) ?>
    </title>
	<?= $this->Html->meta( 'icon' ) ?>

	<?= $this->Html->css( 'base.css' ) ?>
	<?= $this->Html->css( 'cake.css' ) ?>
	<?= $this->Html->css( 'jquery-ui.min.css' ) ?>
	<?= $this->Html->css( 'select2.min.css' ) ?>
	<?= $this->Html->css( 'font-awesome.min.css' ) ?>
	<?= $this->Html->css( 'query-builder.default.min.css' ) ?>
	<?= $this->Html->css( 'app.css' ) ?>
	<?= $this->Html->css( 'print.css' ) ?>

	<?= $this->Html->script( 'app.js' ) ?>

	<?= $this->fetch( 'meta' ) ?>
	<?= $this->fetch( 'css' ) ?>
	<?= $this->fetch( 'script' ) ?>

    <script>
        var csrfToken = '<?= $this->request->getParam('_csrfToken') ?>';
        var webroot = '<?= $this->Url->build( '/', true ); ?>';
        var urlbase = '<?= $this->Url->build( '/', false ); ?>';
        var trans = {
            dateformat: '<?= __x( 'Date format', 'dd.mm.yy' ) ?>',
            searching: '<?= __( 'Searching...' ) ?>',
            loading: '<?= __( 'Loading...' ) ?>',
            loading_error: '<?= __x( '{0} is replaced by an entity name.', 'There was an error loading {0}' ) ?>',
            create_new_variety: '<?= __( 'Nothing found. Create new covar of:' ) ?>',
            mark: '<?= __x( 'The entitiy', 'mark' ) ?>',
            uc_new: '<?= __( 'NEW' ) ?>',
            brain_prefill: '<?= __( 'Automatically prefilled' ) ?>',
            delete_element: '<?= __x( 'after the : the name of the element to delete is inserted',
				'Are you sure you want to delete:' )?>',
            matching_elements: '<?= __( '{0} matching elements for this mark were found.' ) ?>',
            no_tree_found: '<?= __( 'No tree could be found' ) ?>',
            impossible_selection: "<?= __x( 'Query builder. {0} is replaced by the table name.',
				"{0} will be deselected, since they aren't connected to the other selected entities." ) ?>",
            preparing_report: "<?= __( 'Please wait, while your report is generated. This may take up to few minutes.' ) ?>",
            preparing_report_failed: "<?= __( "Sorry, your report couldn't be generated. Please try again." ) ?>",
            invalid_query_builder_rules: "<?= __( "There were invalid rules in the query builder we could not restore. Please rebuild the ruleset." ) ?>",
            no_marks_selected: "<?= __( 'If marks are your main table, you must select some marks to display.' ) ?>",
            multiple_forms_error: "<?= __( 'There are multiple forms on this page. Submitting by scanner does not work!' ) ?>"
        };
    </script>
</head>
<body>
<nav class="top-bar expanded" data-topbar role="navigation">
	<?= $this->element( 'top_nav' ); ?>
    <div class="top-bar-section">
        <ul class="right meta-nav">
			<?php
			$location = $this->request->getSession()->read( 'experiment_site_name' );
			if ( $location ) :
				?>
                <li><?= $this->Html->link( __( 'Location: {0}', $location ),
						[ 'controller' => 'ExperimentSites', 'action' => 'select' ] ) ?></li>
			<?php endif; ?>
            <li><?= $this->Html->link( __( 'Settings' ), [ 'controller' => 'Settings' ] ) ?></li>
            <li><?= $this->Html->link( __( 'Logout' ), [ 'controller' => 'Users', 'action' => 'logout' ] ) ?></li>
        </ul>
    </div>
</nav>
<?= $this->Flash->render() ?>
<div class="clearfix">
	<?= $this->fetch( 'content' ) ?>
</div>
<footer>
</footer>
</body>
</html>
