<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <?= $this->element($nav); ?>
</nav>
<div class="print form large-9 medium-8 columns content">
    <h1><?= __('Print a label?') ?></h1>
    <?= $this->Html->link(
            __('Yes'),
            ['controller' => $controller, 'action' => $action, $params],
            ['class'=>'button zpl_print','data-zpl'=>$zpl]);
    ?>
    <?= $this->Html->link(
            __('No'),
            ['controller' => $controller, 'action' => $action, $params],
            ['class'=>'button']);
    ?>
</div>
