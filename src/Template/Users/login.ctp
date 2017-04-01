<div class="users login large-9 medium-8 columns content">
    <h3><?= __('Login') ?></h3>
    <?= $this->Form->create() ?>
    <?= $this->Form->input('email') ?>
    <?= $this->Form->input('password') ?>
    <?= $this->Form->button(__('Login')) ?>
    <?= $this->Form->end() ?>
</div>