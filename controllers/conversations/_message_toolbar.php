<div class="form-buttons loading-indicator-container">
    <?php
    if ($templatePath): ?>
        <a
            href="javascript:;"
            class="btn btn-primary wn-icon-check reply"
            data-request="conversationList::onReply"
            data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
            data-hotkey="ctrl+s, cmd+s">
            <?= e(trans('acorn.messaging::lang.models.message.reply')) ?>
        </a>
    <?php else: ?>
        <!-- TODO: data-request-update="'conversations': '.conversation-list'" -->
        <a
            href="javascript:;"
            class="btn btn-primary wn-icon-check send"
            data-request="onSend"
            data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
            data-hotkey="ctrl+s, cmd+s">
            <?= e(trans('acorn.messaging::lang.models.message.send')) ?>
        </a>
    <?php endif ?>

    <!-- a
        href="javascript:;"
        class="btn btn-primary wn-icon-crosshairs save-draft"
        data-request="onSaveDraft"
        data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
        data-hotkey="ctrl+s, cmd+s">
        <?= e(trans('acorn.messaging::lang.models.message.save_draft')) ?>
    </a -->
</div>
