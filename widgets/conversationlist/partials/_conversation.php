<ul class="conversation">
<?php foreach ($messages as $message): ?>
    <?= $this->makePartial('message', array(
        'message' => $message,
    )); ?>
<?php endforeach ?>
</ul>
