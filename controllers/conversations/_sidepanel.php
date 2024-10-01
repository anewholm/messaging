<div class="layout control-scrollpanel" id="messaging-side-panel">
    <div class="layout-cell">
        <div class="layout-relative fix-button-container">
            <!-- ?php TODO: if ($this->user->hasAccess('cms.manage_pages')): ? -->
            <?php if (TRUE): ?>
                <form
                    role="form"
                    class="layout"
                    data-content-id="conversations"
                    data-template-type="conversation"
                    data-type-icon="wn-icon-copy"
                    onsubmit="return false">
                    <?= $this->widget->conversationList->render() ?>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>
