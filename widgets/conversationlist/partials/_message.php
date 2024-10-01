<li class="<?= implode(' ', $message->typeClasses()); ?>"
    data-item-url="<?= $message->url ?>"
    data-item-path="<?= $message->id ?>"
    data-item-theme="demo"
    data-template-type="<?= $message->source ?: 'message' ?>"
    data-id="<?= ($message->source ?: 'message') . "-$message->id" ?>"
>
    <a href="javascript:;" data-control="dragvalue" data-text-value="" draggable="true">
        <!-- TODO: use getVisibleColumns() -->
        <span class="name"><?= $message->user_from?->first_name ?></span>
        <span class="created-at"><?= $message->created_at ?></span>
        <span class="date"><?= $message->date ?></span>
        <span class="subject"><?= $message->subjectTruncate() ?></span>
        <span class="body"><?= $message->bodyTruncate() ?></span>
        <span class="labels"><?= $message->labels ?></span>
        <span class="source"><?= $message->source ?></span>
    </a>
</li>
