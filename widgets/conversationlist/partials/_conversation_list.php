<?php if ($conversations): ?>
    <ul>
        <?php foreach ($conversations as $conversation): ?>
            <li
                class="item conversation-<?= $conversation->itemType ?>"
                data-item-path="<?= $conversation->id ?>"
                data-item-theme="<?= e($this->theme->getDirName()) ?>"
                data-item-type="<?= $conversation->itemType ?>"
                data-id="conversation-<?= "$conversation->itemType-" . $this->theme->getDirName() . "-$conversation->id" ?>"
            >
                <a href="javascript:;">
                    <span class="title"><?= e($conversation->title) ?></span>
                    <span class="description" title="<?= e($conversation->description) ?>">
                        <?= e($conversation->description) ?>
                        <?php foreach ($conversation->descriptions as $name => $description): ?>
                            <?php if (is_iterable($description)): ?>
                                <?php foreach ($description as $idescription): ?>
                                    <span class="<?= e($name) ?>" title="<?= e($name) ?>"><?= e($idescription->name) ?></span>
                                <?php endforeach ?>
                            <?php else: ?>
                                <span class="<?= e($name) ?>" title="<?= e($name) ?>"><?= e($description) ?></span>
                            <?php endif ?>
                        <?php endforeach ?>
                    </span>
                    <span class="borders"></span>
                </a>

                <input type="hidden" name="message-[<?= e($conversation->id) ?>]" value="0" />
                <!-- div class="checkbox custom-checkbox nolabel">
                    <?php $cbId = 'cb' . md5($this->itemType . '/' . $conversation->id) ?>
                    <input
                        id="<?= $cbId ?>"
                        type="checkbox"
                        name="message-[<?= e($conversation->id) ?>]"
                        <?= $this->isItemSelected($conversation->id) ? 'checked' : null ?>
                        data-request="<?= $this->getEventHandler('onSelect') ?>"
                        value="1">
                    <label for="<?= $cbId ?>">Select</label>
                </div -->
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
<?php endif ?>

<?php if (!isset($nested)): ?>
    <input type="hidden" name="theme" value="<?= e($this->theme->getDirName()) ?>">
<?php endif ?>
