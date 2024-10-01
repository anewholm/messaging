<?= $this->makePartial('toolbar') ?>
<div class="layout-row">
    <div class="layout-cell">
        <div class="layout-relative">
            <?= $this->makePartial('conversations', [
                'authUser'      => $authUser,
                'conversations' => $conversations,
            ]) ?>
        </div>
    </div>
</div>
