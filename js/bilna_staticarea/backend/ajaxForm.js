

var BilnaStaticareaImagesAjaxForm = Class.create({
    initialize: function(name) {
        this.contentFormSubmitId = 'bilna_contentsavebutton';
        this.contentFormErrorsId = 'awis_image_error';
        this.contentFormId = 'staticarea_contentform';
        this.contentFormContainerId = 'staticarea_contentformcontainer';
        this.contentFormIframeId = 'awis_loader';
        /*this.typeSelectorId = 'image_type';
        this.typeFileFileId = 'image_file';
        this.typeFileRemoteId = 'image_remote';*/
        this.typeFileFile = 1;
        this.typeFileRemote = 2;
        this.pid = null;

        this.selectors = {
            dateFromButton: 'content_from_trig',
            dateToButton: 'content_to_trig'
        }

        this.varienForm = null;

        this.window = null;
        this.global = window;
        this.selfObjectName = name;
        if(typeof name != 'undefined')
            this.global[name] = this;

        this.messages = {
            newTitle: 'Add Content',
            editTitle: 'Edit Content'
        };
        document.observe('dom:loaded', this.prepareSelf.bind(this));
    },

    prepareSelf: function() {
        if(bilnaISSettings) {
            if(bilnaISSettings.getOption('imagesAjaxFormUrl'))
                this.updateAjaxUrl(bilnaISSettings.getOption('imagesAjaxFormUrl'));
            else
                bilnaISSettings.getOption('imagesAjaxFormUrl', this.updateAjaxUrl);
        }
        this.translateMessages();
    },

    prepareCalendar: function() {
        var pos = $(this.selectors.dateFromButton).cumulativeOffset();
        Calendar._TT["TT_DATE_FORMAT"] = Calendar._TT.DEF_DATE_FORMAT;
        Calendar.setup({
            inputField: "active_from",
            ifFormat: Calendar._TT.DEF_DATE_FORMAT,
            showsTime: false,
            button: "content_from_trig",
            align: "Bl",
            singleClick : true,
            position: pos
        });
        pos = $(this.selectors.dateToButton).cumulativeOffset();
        Calendar.setup({
            inputField: "active_to",
            ifFormat: Calendar._TT.DEF_DATE_FORMAT,
            showsTime: false,
            button: "content_to_trig",
            align: "Bl",
            singleClick : true,
            position: pos
        });
    },

    prepareForm: function() {
        this.varienForm = new varienForm(this.contentFormId);
        this._pe = new PeriodicalExecuter(this._resizeWindow.bind(this), 0.1);
        this.typeChanged();
        //$(this.typeSelectorId).observe('change', this.global[this._getSelfObjectName()].typeChanged.bind(this));
        /*observe iframe onload and form onsubmit events*/
        $(this.contentFormId).observe('submit', this.global[this._getSelfObjectName()].formBeforePost.bind(this));
        setTimeout(this.prepareCalendar.bind(this), 1000);
    },

    _resizeWindow: function() {
        if(this.contentFormContainerId && $(this.contentFormContainerId) && $(this.contentFormContainerId).getWidth() && $(this.contentFormContainerId).getHeight()) {
            if(this._pe) {
                this._pe.stop();
                this._pe = null;
            }
            if(this.window)
                this.window.setSize(Math.max(550, $(this.contentFormContainerId).getWidth()), $(this.contentFormContainerId).getHeight()+30);
        }
    },

    typeChanged: function() {
        /*$(this.typeFileFileId).removeClassName('required-entry');
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
        }*/
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
//console.log(pid);
        this.window = new Window({
            className: 'magento',
            width: 900,
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
        $(this.contentFormSubmitId).removeClassName('disabled').writeAttribute('disabled', null);
console.log(resp);        
        if(resp.s) {
            this.window.close();
            if(staticarea_contentsJsObject)
                staticarea_contentsJsObject.reload();
        } else {
            a = resp;
            $(this.contentFormErrorsId).innerHTML = resp.errors;
console.log(resp.errors);            
            this._resizeWindow();
        }
    },

    formBeforePost: function() {
        this._pe = new PeriodicalExecuter(this._resizeWindow.bind(this), 1);
        if(this.varienForm && this.varienForm.validate() == false) {
            return false;
        }
        $(this.contentFormSubmitId).addClassName('disabled').writeAttribute('disabled', 'disabled');
        return true;
    }
});

new BilnaStaticareaImagesAjaxForm('bilnaISAjaxForm');
