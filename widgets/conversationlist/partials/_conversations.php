<div id="conversation-list" class="layout-absolute">
    <div class="control-scrollbar" data-control="scrollbar">
        <!-- 
            #ConversationList-conversationList-messages 
            winter.css CSS styles this .control-filelist
        -->
        <div
            class="control-filelist <?= $this->controlClass ?>"
            data-control="filelist"
            data-template-type="conversation"
            data-group-status-handler="<?= $this->getEventHandler('onSetCollapseStatus') ?>"
            id="<?= $this->getId('messages') ?>"
            websocket-listen="messaging"
            websocket-onmessaging-message-<?= $authUser->id ?>-update="'conversation_list': '#<?= $this->getId('messages') ?>'"
            websocket-onmessaging-message-<?= $authUser->id ?>-request="conversationList::onUpdate"
            websocket-onmessaging-message-<?= $authUser->id ?>-sound="/plugins/acornassociated/messaging/assets/sounds/conversation-arrived.mp3"
        >
            <?= $conversations
                ? $this->makePartial('conversation_list', [
                    'conversations' => $conversations
                  ])
                : $this->makePartial('hint_no_friends')
            ?>
        </div>
    </div>
</div>
