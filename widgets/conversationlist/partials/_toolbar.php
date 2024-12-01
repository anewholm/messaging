<div class="layout-row min-size">
    <div class="control-toolbar toolbar-padded <?= $toolbarClass ?>">

        <!-- Control Panel messagelist -->
        <div class="toolbar-item" data-calculate-width>
            <div class="btn-group"
                    data-content-id="templates"
                    data-object-type="template"
                    data-type-icon="oc-icon-files-o"
                    >
                <button
                    type="button"
                    class="btn btn-default wn-icon-plus <?= !$this->sortingProperties ? 'last' : null ?>"
                    data-control="create-message"
                >
                   <?= e(trans('acorn.messaging::lang.models.conversation.label')) ?>
                </button>

                <?php if ($this->sortingProperties): ?>
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn btn-default empty wn-icon-sort-alpha-asc"
                            data-toggle="dropdown"></button>
                        <ul
                            class="dropdown-menu offset-left"
                            data-dropdown-title="<?= e(trans('cms::lang.message.order_by')) ?>"
                            id="<?= $this->getId('sorting-options') ?>"
                            role="menu">
                            <?= $this->makePartial('sorting-options') ?>
                        </ul>
                    </div>
                <?php endif?>

                <button type="button" class="btn btn-danger empty wn-icon-trash-o hide"
                    id="<?= $this->getId('delete-button') ?>"
                    data-control="delete-message"
                    data-confirmation="<?= e(trans($this->deleteConfirmation)) ?>"
                    data-trigger-action="show"
                    data-trigger="<?= '#'.$this->getId('message-list') ?> input[type=checkbox]"
                    data-trigger-condition="checked"></button>
            </div>
        </div>

        <!-- Template Search -->
        <div class="relative toolbar-item loading-indicator-container size-input-text">
            <input placeholder="<?= e(trans('cms::lang.sidebar.search')) ?>" type="text" name="search" value="<?= e($this->getSearchTerm()) ?>"
                class="form-control icon search" autocomplete="off"
                data-track-input
                data-load-indicator
                data-load-indicator-opaque
                data-request-success="$('<?= '#'.$this->getId('delete-button') ?>').trigger('oc.triggerOn.update')"
                data-request="<?= $this->getEventHandler('onSearch') ?>"
            />
        </div>

    </div>
</div>
