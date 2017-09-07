<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element($nav); ?>
</nav>
<div class="print form large-9 medium-8 columns content">
    <h1><?= __('Print a label?') ?></h1>
    <?php
    foreach ($buttons as $id => $options) {
        $button = $this->Html->link(
            $options['label'],
            ['controller' => $controller, 'action' => $action, $params],
            ['class' => 'button zpl_print', 'data-zpl' => $options['zpl'], 'id' => 'print_button_'.$id]);
        echo $button . ' ';
    }
    ?>
    <?= $this->Html->link(
        __("Don't print"),
        ['controller' => $controller, 'action' => $action, $params],
        ['class' => 'button']);
    ?>
</div>

<script>
    $('#print_button_<?= $focus ?>').focus();
</script>