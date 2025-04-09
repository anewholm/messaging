<?= Form::open([
    'class' => "layout conversation-form-$templatePath",
    'data-change-monitor' => 'true',
    'data-window-close-confirm' => e(trans('backend::lang.form.confirm_tab_close')),
    'data-inspector-external-parameters' => true
]) ?>
    <?= $form->render() ?>

    <!-- These values set the tabId when submitted 
        see acornassociated.messaging.js updateIdentifier
    -->
    <input type="hidden" value="<?= e($form->alias) ?>" name="formWidgetAlias" />
    <input type="hidden" value="<?= ($templateType) ?>" name="templateType" />
    <input type="hidden" value="<?= ($templateSubType) ?>" name="templateSubType" />
    <input type="hidden" value="<?= ($templatePath) ?>" name="templatePath" />
    <input type="hidden" value="<?= ($templateTheme) ?>" name="theme" />
    <input type="hidden" value="0" name="templateForceSave" />

<?= Form::close() ?>
