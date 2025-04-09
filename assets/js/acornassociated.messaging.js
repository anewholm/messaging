function acornassociated_public_conversation(id) {
    // #!/conversation/<x> direct open up of a conversation tab
    // TODO: Make group/user sub-type aware
    console.log('open conversation [' + id + ']');
    var attr  = 'data-item-path=' + id;
    var jConv = $('#ConversationList-conversationList-messages > ul > li[' + attr + '] > a');
    jConv.trigger('click');
}

+function ($) { "use strict";
    // NOTE: this file is based on winter.cmspage.js
    var Base = $.wn.foundation.base,
        BaseProto = Base.prototype

    var MessagingPage = function() {
        Base.call(this)
        this.init()
    }

    MessagingPage.prototype = Object.create(BaseProto)
    MessagingPage.prototype.constructor = MessagingPage

    MessagingPage.prototype.init = function() {
        $(document).ready(this.proxy(this.registerHandlers))
    }

    MessagingPage.prototype.registerHandlers = function() {
        // NOTE: this file is based on winter.cmspage.js
        var $document = $(document),
            $masterTabs = $('#messaging-master-tabs')

        // Tab management
        $masterTabs.on('closed.oc.tab', this.proxy(this.onTabClosed))
        $masterTabs.on('beforeClose.oc.tab', this.proxy(this.onBeforeTabClose))
        $masterTabs.on('shown.bs.tab', this.proxy(this.onTabShown))
        $masterTabs.on('initTab.oc.tab', this.proxy(this.onInitTab))
        $masterTabs.on('afterAllClosed.oc.tab', this.proxy(this.onAfterAllTabsClosed))

        // Request handlers TODO: why not use data attributes?
        $document.on('open.oc.list', '#messaging-side-panel', this.proxy(this.onOpenTab)) // => conversation tab
        $document.on('click', '.conversation > li',     this.proxy(this.onOpenTab)) // => message tab
        $document.on('click', '#messaging-side-panel form button[data-control=create-message], #messaging-side-panel form li a[data-control=create-message]', this.proxy(this.onCreateMessageClick))

        $document.on('ajaxUpdate', '[data-control=filelist]', this.proxy(this.onAjaxUpdate))
        $document.on('ajaxSuccess', '#messaging-master-tabs form', this.proxy(this.onAjaxSuccess))
        // NOTE: Dropped in from assetlist.js
        $document.on('oc.list.setActiveItem', this.proxy(this.onSetActiveItem, this));
    }

    // EVENT HANDLERS
    // ============================

    MessagingPage.prototype.onSetActiveItem = function(event, dataId) {
        $('#messaging-side-panel ul li.item').removeClass('active')
        if (dataId)
            $('#messaging-side-panel ul li.item[data-id="'+dataId+'"]').addClass('active')
    }

    MessagingPage.prototype.onOpenTab = function(event) {
        // Note: this file is based on winter.cmspage.js
        var $item = $(event.relatedTarget || event.currentTarget),
            $form = $item.closest('[data-template-type]'),
            data  = {
                url:     $form.data('item-url'),
                type:    $form.data('template-type'),
                subType: $item.data('item-type') || '',
                theme:   $item.data('item-theme'),
                path:    $item.data('item-path'),
            },
            tabId   = data.type + '-' + data.subType + '-' + data.theme + '-' + data.path,
            handler = 'onOpen' + data.type.toUCFirst() + data.subType.toUCFirst();

        if ($item.length == 0) {
            if (window.console) console.error('onOpenTab: $item empty');
            return false;
        }
        if ($form.length == 0) {
            if (window.console) console.error('onOpenTab: $form empty');
            return false;
        }

        if (data.url) return window.open(data.url, data.type);

        if ($('#messaging-master-tabs').data('oc.tab').goTo(tabId)) {
            // Tab is already opened
        } else {
            // Open a new tab
            $.wn.stripeLoadIndicator.show()

            $form.request(handler, {
                data: data
            }).done(function(data) {
                $.wn.stripeLoadIndicator.hide()
                $('#messaging-master-tabs').ocTab('addTab', data.tabTitle, data.tab, tabId, $form.data('type-icon'))
            }).always(function() {
                $.wn.stripeLoadIndicator.hide()
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $.wn.stripeLoadIndicator.hide()
            })
        }

        return false;
    }

    MessagingPage.prototype.onTabClosed = function(ev) {
        if ($('> div.tab-content > div.tab-pane', '#messaging-master-tabs').length == 0)
            this.setPageTitle('')
    }

    MessagingPage.prototype.onBeforeTabClose = function(ev) {
    }

    MessagingPage.prototype.onTabShown = function(ev) {
        /*
         * Listen for the tabs "shown" event to track the current template in the list
         */

        var $target = $(ev.target)

        if ($target.closest('[data-control=tab]').attr('id') != 'messaging-master-tabs')
            return

        var dataId = $target.closest('li').attr('data-tab-id'),
            title = $target.attr('title'),
            $sidePanel = $('#messaging-side-panel')

        if (title)
            this.setPageTitle(title)

        // TODO: Update this setActiveItem
        $sidePanel.find('[data-control=filelist]').fileList('markActive', dataId)
        $sidePanel.find('form').trigger('oc.list.setActiveItem', [dataId])
    }

    MessagingPage.prototype.onInitTab = function(ev, data) {
        /*
         * Listen for the tabs "initTab" event to inject extra controls to the tab
         */

        if ($(ev.target).attr('id') != 'messaging-master-tabs')
            return

        var $collapseIcon = $('<a href="javascript:;" class="tab-collapse-icon tabless"><i class="icon-chevron-up"></i></a>'),
            $panel = $('.form-tabless-fields', data.pane)

        $panel.append($collapseIcon);

        $collapseIcon.click(function(){
            $panel.toggleClass('collapsed')

            if (typeof(localStorage) !== 'undefined')
                localStorage.ocCmsTablessCollapsed = $panel.hasClass('collapsed') ? 1 : 0

            window.setTimeout(function(){
                $(window).trigger('oc.updateUi')
            }, 500)

            return false
        })

        var $primaryCollapseIcon = $('<a href="javascript:;" class="tab-collapse-icon primary"><i class="icon-chevron-down"></i></a>'),
            $primaryPanel = $('.control-tabs.primary-tabs', data.pane),
            $secondaryPanel = $('.control-tabs.secondary-tabs', data.pane)

        if ($primaryPanel.length > 0) {
            $secondaryPanel.append($primaryCollapseIcon);

            $primaryCollapseIcon.click(function(){
                $primaryPanel.toggleClass('collapsed')
                $secondaryPanel.toggleClass('primary-collapsed')
                $(window).trigger('oc.updateUi')
                if (typeof(localStorage) !== 'undefined')
                    localStorage.ocCmsPrimaryCollapsed = $primaryPanel.hasClass('collapsed') ? 1 : 0
                return false
            })
        }

        if (typeof(localStorage) !== 'undefined') {
            if (!$('a', data.tab).hasClass('new-template') && localStorage.ocCmsTablessCollapsed == 1)
                $panel.addClass('collapsed')

            if (localStorage.ocCmsPrimaryCollapsed == 1) {
                $primaryPanel.addClass('collapsed')
                $secondaryPanel.addClass('primary-collapsed')
            }
        }

        var $form = $('form', data.pane),
            self = this

        $form.on('changed.oc.changeMonitor', function() {
            $panel.trigger('modified.oc.tab')
            $panel.find('[data-control=commit-button]').addClass('hide');
            $panel.find('[data-control=reset-button]').addClass('hide');
        })

        $form.on('unchanged.oc.changeMonitor', function() {
            $panel.trigger('unmodified.oc.tab')
        })
    }

    MessagingPage.prototype.onAfterAllTabsClosed = function(ev) {
        var $sidePanel = $('#messaging-side-panel')

        $sidePanel.find('[data-control=filelist]').fileList('markActive', null)
        $sidePanel.find('form').trigger('oc.list.setActiveItem', [null])
    }

    MessagingPage.prototype.onAjaxUpdate = function(ev) {
        var dataId = $('#messaging-master-tabs .nav-tabs li.active').attr('data-tab-id'),
            $sidePanel = $('#messaging-side-panel')

        $sidePanel.find('[data-control=filelist]').fileList('markActive', dataId)
        $sidePanel.find('form').trigger('oc.list.setActiveItem', [dataId])
    }

    MessagingPage.prototype.onAjaxSuccess = function(ev, context, data) {
        var element = ev.target

        // Update the visibilities of the commit & reset buttons
        $('[data-control=commit-button]', element).toggleClass('hide', !data.canCommit)
        $('[data-control=reset-button]',  element).toggleClass('hide', !data.canReset)

        if (data.templatePath !== undefined) {
            $('input[name=templatePath]',      element).val(data.templatePath)
            $('[data-control=delete-button]',  element).removeClass('hide')
            $('[data-control=preview-button]', element).removeClass('hide')

            if (data.pageUrl !== undefined)
                $('[data-control=preview-button]', element).attr('href', data.pageUrl)
        }

        if (data.tabTitle !== undefined) {
            $('#messaging-master-tabs').ocTab('updateTitle', $(element).closest('.tab-pane'), data.tabTitle)
            this.setPageTitle(data.tabTitle)
        }

        var tabId = $('input[name=templateType]', element).val() + '-'
            + $('input[name=templateSubType]', element).val() + '-'
            + $('input[name=theme]', element).val() + '-'
            + $('input[name=templatePath]', element).val();

        // TODO: Disabled because it writes the wrong id on the tab
        // and we don't know what its purpose is anyway
        //$('#messaging-master-tabs').ocTab('updateIdentifier', $(element).closest('.tab-pane'), tabId)

        if (context.handler == 'onSave' && (!data['X_WINTER_ERROR_FIELDS'] && !data['X_WINTER_ERROR_MESSAGE'])) {
            $(element).trigger('unchange.oc.changeMonitor')
        }

        // Reload the form if the server has requested it
        if (data.forceReload) {
            this.reloadForm(element)
        }
    }

    MessagingPage.prototype.onCreateMessageClick = function(ev) {
       var  $form = $(ev.target).closest('[data-template-type]'),
            type = $form.data('template-type'),
            tabId = type + Math.random(),
            self = this

        $.wn.stripeLoadIndicator.show()

        $form.request('onCreateMessage', {
           data: {type: type}
        }).done(function(data) {
            $('#messaging-master-tabs').ocTab('addTab', data.tabTitle, data.tab, tabId, $form.data('type-icon') + ' new-template')
            $('#layout-side-panel').trigger('close.oc.sidePanel')
            self.setPageTitle(data.tabTitle)
        }).always(function(){
            $.wn.stripeLoadIndicator.hide()
        })
    }

    // INTERNAL METHODS
    // ============================

    MessagingPage.prototype.setPageTitle = function(title) {
        if (title.length)
            $.wn.layout.setPageTitle(title + ' | ')
        else
            $.wn.layout.setPageTitle(title)
    }

    $.wn.MessagingPage = new MessagingPage();
}(window.jQuery);
