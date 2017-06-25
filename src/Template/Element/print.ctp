<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element($nav); ?>
</nav>
<div class="print form large-9 medium-8 columns content">
    <h1><?= __('Print a label?') ?></h1>
    <?php
    foreach ($zpl as $label => $code) {
        $button = $this->Html->link(
            $label,
            ['controller' => $controller, 'action' => $action, $params],
            ['class' => 'button zpl_print', 'data-zpl' => $code]);
        echo $button . ' ';
    }
    ?>
    <?= $this->Html->link(
        __("Don't print"),
        ['controller' => $controller, 'action' => $action, $params],
        ['class' => 'button']);
    ?>
</div>
