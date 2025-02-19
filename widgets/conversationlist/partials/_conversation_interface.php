<div class="conversation-interface conversation-interface-<?= $templatePath ?>">
    <?php if (isset($inGroup)): ?>
        <!-- TODO: Remove this groups message -->
        <br/>
        <div class="layout-row min-size">
            <div class="callout callout-warning">
                <div class="header">
                    <i class="icon-warning"></i>
                    <h3><?= e(trans('acornassociated.messaging::lang.models.conversation.groups_functionality_not_complete')) ?></h3>
                    <p>
                        <?= e(trans('acornassociated.messaging::lang.models.conversation.busy')) ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif ?>

     <!-- This is the container DIV for the AJAX partial updating -->
    <div id="conversation-<?= $templatePath ?>"
        websocket-listen="messaging"
        websocket-onmessaging-message-<?= $templatePath ?>-update="'conversation': '#conversation-<?= $templatePath ?>'"
        websocket-onmessaging-message-<?= $templatePath ?>-sound="/plugins/acornassociated/messaging/assets/sounds/message-arrived.mp3"
    >
       <?= $this->makePartial('conversation', array(
            'templatePath' => $templatePath,
            'messages'     => $messages,
        )); ?>
    </div>

    <?= $form ? $this->makePartial('conversation_form', array(
        'form'          => $form,
        'authUser'      => $authUser,
        'withUser'      => $withUser,
        'templateType'  => $templateType,
        'templateSubType'  => $templateSubType,
        'templatePath'  => $templatePath,
        'templateTheme' => $templateTheme,
    )) : NULL ?>
</div>
