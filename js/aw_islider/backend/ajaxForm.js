/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Islider
 * @copyright  Copyright (c) 2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var AWIsliderImagesAjaxForm = Class.create({
    initialize: function(name) {
        this.imageFormSubmitId = 'awis_imagesavebutton';
        this.imageFormErrorsId = 'awis_image_error';
        this.imageFormId = 'awis_imageform';
        this.imageFormContainerId = 'awis_imageformcontainer';
        this.imageFormIframeId = 'awis_loader';
        this.typeSelectorId = 'image_type';
        this.typeFileFileId = 'image_file';
        this.typeFileRemoteId = 'image_remote';
        this.typeFileFile = 1;
        this.typeFileRemote = 2;
        this.pid = null;

        this.selectors = {
            dateFromButton: 'image_from_trig',
            dateToButton: 'image_to_trig'
        }

        this.varienForm = null;

        this.window = null;
        this.global = window;
        this.selfObjectName = name;
        if(typeof name != 'undefined')
            this.global[name] = this;

        this.messages = {
            newTitle: 'Add Image',
            editTitle: 'Edit Image'
        };
        document.observe('dom:loaded', this.prepareSelf.bind(this));
    },

    prepareSelf: function() {
        if(awISSettings) {
            if(awISSettings.getOption('imagesAjaxFormUrl'))
                this.updateAjaxUrl(awISSettings.getOption('imagesAjaxFormUrl'));
            else
                awISSettings.getOption('imagesAjaxFormUrl', this.updateAjaxUrl);
        }
        this.translateMessages();
    },

    prepareCalendar: function() {
        var pos = $(this.selectors.dateFromButton).cumulativeOffset();
        Calendar._TT["TT_DATE_FORMAT"] = Calendar._TT.DEF_DATE_FORMAT;
        Calendar.setup({
            inputField: "image_from",
            ifFormat: Calendar._TT.DEF_DATE_FORMAT,
            showsTime: false,
            button: "image_from_trig",
            align: "Bl",
            singleClick : true,
            position: pos
        });
        pos = $(this.selectors.dateToButton).cumulativeOffset();
        Calendar.setup({
            inputField: "image_to",
            ifFormat: Calendar._TT.DEF_DATE_FORMAT,
            showsTime: false,
            button: "image_to_trig",
            align: "Bl",
            singleClick : true,
            position: pos
        });
    },

    prepareForm: function() {
        this.varienForm = new varienForm(this.imageFormId);
        this._pe = new PeriodicalExecuter(this._resizeWindow.bind(this), 0.1);
        this.typeChanged();
        $(this.typeSelectorId).observe('change', this.global[this._getSelfObjectName()].typeChanged.bind(this));
        /*observe iframe onload and form onsubmit events*/
        $(this.imageFormId).observe('submit', this.global[this._getSelfObjectName()].formBeforePost.bind(this));
        setTimeout(this.prepareCalendar.bind(this), 1000);
    },

    _resizeWindow: function() {
        if(this.imageFormContainerId && $(this.imageFormContainerId) && $(this.imageFormContainerId).getWidth() && $(this.imageFormContainerId).getHeight()) {
            if(this._pe) {
                this._pe.stop();
                this._pe = null;
            }
            if(this.window)
                this.window.setSize(Math.max(550, $(this.imageFormContainerId).getWidth()), $(this.imageFormContainerId).getHeight()+30);
        }
    },

    typeChanged: function() {
        $(this.typeFileFileId).removeClassName('required-entry');
        if($(this.typeSelectorId).value == this.typeFileFile) {
            $(this.typeFileRemoteId).removeClassName('required-entry');
            $(this.typeFileFileId).addClassName('required-entry');
            $(this.typeFileRemoteId).up().up().hide();
            $(this.typeFileFileId).up().up().show();
        }
        if($(this.typeSelectorId).value == this.typeFileRemote) {
            $(this.typeFileRemoteId).addClassName('required-entry');
            $(this.typeFileFileId).removeClassName('required-entry');
            $(this.typeFileFileId).up().up().hide();
            $(this.typeFileRemoteId).up().up().show();
        }
        if($(this.typeSelectorId).value == this.typeFileFile && $('note_image_file')) {
            $(this.typeFileFileId).removeClassName('required-entry');
        }
    },

    _getSelfObjectName: function() {
        return this.selfObjectName;
    },

    updateAjaxUrl: function(ajaxUrl) {
        this.ajaxUrl = typeof ajaxUrl != 'undefined' ? ajaxUrl : '';
        this.ajaxUrl =  this.ajaxUrl.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
    },

    translateMessages: function() {
        if(typeof Translator != 'undefined' && Translator) {
            for(var line in this.messages)
                this.messages[line] = Translator.translate(this.messages[line]);
        }
    },

    showForm: function(pid, id) {
        this.window = new Window({
            className: 'magento',
            width: 550,
            height: 500,
            destroyOnClose: true,
            recenterAuto:false,
            zIndex: 101
        });

        /* showing form for new entry */
        this.window.setTitle(typeof id == 'undefined' || id === null ? this.messages.newTitle : this.messages.editTitle);
        this.window.setAjaxContent(this.ajaxUrl, {
            parameters: {id: id, pid: pid},
            onComplete: this.prepareForm.bind(this)
        }, true, true);
    },

    formAfterPost: function(resp) {
        $(this.imageFormSubmitId).removeClassName('disabled').writeAttribute('disabled', null);
        if(resp.s) {
            this.window.close();
            if(awislider_imagesJsObject)
                awislider_imagesJsObject.reload();
        } else {
            a = resp;
            $(this.imageFormErrorsId).innerHTML = resp.errors;
            this._resizeWindow();
        }
    },

    formBeforePost: function() {
        this._pe = new PeriodicalExecuter(this._resizeWindow.bind(this), 1);
        if(this.varienForm && this.varienForm.validate() == false) {
            return false;
        }
        $(this.imageFormSubmitId).addClassName('disabled').writeAttribute('disabled', 'disabled');
        return true;
    }
});

new AWIsliderImagesAjaxForm('awISAjaxForm');
