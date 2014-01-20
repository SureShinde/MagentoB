var afpSlider = Class.create({
    initialize: function(params) {
        this._canSlide = true;
        if(typeof params != 'undefined') {
            this.blockId = this._getValue(params.blockId);
            this.effect = this._getValue(params.effect);
            this.productCount = parseInt(this._getValue(params.productCount));
            this.animationSpeed = parseInt(this._getValue(params.animationSpeed));
            this.autohidenavi = this._getValue(params.autohidenavi);
            this.height = this._getValue(params.height);
        }
        
        this.global = window;
        this.selfObjectName = 'afpSlider' + this.blockId;
        this.global[this.selfObjectName] = this;
        if(!this.blockId || !this.effect || isNaN(this.productCount)) this._canSlide = false;
        if(this._canSlide) {
            this.slideIndexMin = this.slideIndexCurrent = 0;
            this.slideIndexMax = this.productCount - 1;
            /* Collecting all slide blocks */
            this.slides = [];

            this.selectors = {
                sliderItem: 'afp-slider-item',
                sliderControls: 'afp-slider-controls',
                sliderControlsCenter: 'afp-center',
                sidebarCurrent: 'afp-s-current'
            };

            $$('#'+this.blockId+' div.'+this.selectors.sliderItem).each(function(element) {
                this.slides.push(element);
                $(element).setStyle({width: $(this.blockId).getWidth()+'px'});
            }, this);
            /* Collecting all slide buttons */
            this.sidebarSlides = [];
            $$('#'+this.blockId+' .'+this.selectors.sliderControls+' .'+this.selectors.sliderControlsCenter+' button').each(function(element) {
                this.sidebarSlides.push(element);
            }, this);
            
            this._effectQueueScope = 'afpqueue'+this.blockId;
            this._flagButton = false;
            this._slideEffectInAction = false;
            
            this._timer = null;
            if(this.animationSpeed) {
                this._baseTime = Math.min(Math.max(this.animationSpeed/3, 0.1), 1);
                if(!this.isDomLoaded())
                    Event.observe(document, 'dom:loaded', this._startPE.bind(this));
                else 
                    this._startPE();
                $(this.blockId).observe('mouseover', this._stopPE.bind(this));
                $(this.blockId).observe('mouseout', this._startPE.bind(this));
            } else {
                this._baseTime = 1;
            }
            this._slidesContainer = $$('#'+this.blockId+' div.afp-slides-container').first();
            /* Controls section */
            this._controlsShowed = true;
            this._controlLeft = $$('#'+this.blockId+' .'+this.selectors.sliderControls+' .afp-left').first();
            this._controlRight = $$('#'+this.blockId+' .'+this.selectors.sliderControls+' .afp-right').first();
            this._controlsHiderPE = null;
            if(this.autohidenavi) {
                $(this.blockId).observe('mouseover', this._showControls.bind(this));
                $(this.blockId).observe('mouseout', this._hideControls.bind(this));
            } else if(this.productCount == 1) {
                this._hideControls(this, true);
            }
            this._setBlockHeight();
            this._initEffect();
        }
        Event.observe(document, 'dom:loaded', this.setDomLoaded.bind(this, true));
    },
    
    _setBlockHeight: function() {
        if(!this.height) {
            var blockHeight = 0;
            for(var i = 0; i < this.slides.length; i++) {
                blockHeight = Math.max(blockHeight, this.slides[i].getHeight());
            }
            $(this.blockId).setStyle({height: blockHeight+'px'});
        }
    },
    
    isDomLoaded: function() {
        return this.global._awfp3_isDomLoaded ? true : false;
    },
    
    setDomLoaded: function(val) {
        this.global._awfp3_isDomLoaded = val ? true : false;
    },
    
    _getValue: function(variable) {
        if(typeof variable == 'undefined') return null;
        return variable;
    },

    _showControls: function() {
        if(this._controlsHiderPE) {
            this._controlsHiderPE.stop();
            this._controlsHiderPE = null;
        }
        if(!this._controlsShowed && this.productCount > 1) {
            this._controlsShowed = true;
            new Effect.Appear(this._controlLeft, {duration: 0.5});
            new Effect.Appear(this._controlRight, {duration: 0.5});
        }
    },

    _hideControls: function(event, forcedHide, byTimer) {
        if(typeof forcedHide == 'undefined') forcedHide = false;
        if(typeof byTimer == 'undefined') byTimer = false;
        if(!byTimer && !forcedHide) {
            this._controlsHiderPE = new PeriodicalExecuter(this._hideControls.bind(this, null, false, true), 1);
            return ;
        } else if(this._controlsHiderPE) {
            this._controlsHiderPE.stop();
            this._controlsHiderPE = null;
        }
        if(this._controlsShowed) {
            if(forcedHide) {
                this._controlLeft.hide();
                this._controlRight.hide();
                this._controlsShowed = false;
            } else {
                new Effect.Fade(this._controlLeft, {
                    afterFinish: this._setControlsHided.bind(this),
                    duration: 0.5
                });
                new Effect.Fade(this._controlRight, {duration: 0.5});
            }
        }
    },

    _setControlsHided: function() {
        this._controlsShowed = false;
    },
    
    _startPE: function() {
        if(this.animationSpeed)
            this._timer = new PeriodicalExecuter(this.next.bind(this), this.animationSpeed);
    },
    
    _stopPE: function() {
        if(this.animationSpeed && this._timer) {
            this._timer.stop();
            this._timer = null;
        }
    },
    
    _initEffect: function() {
        if(this.autohidenavi) this._hideControls(null, true);
        this.effectsList = {
            fadeappear: 'fade-appear',
            simpleslider: 'simple-slider',
            blindUpDown: 'blind-up-down',
            slideUpDown: 'slide-up-down'
        };
        switch(this.effect) {
            case this.effectsList.fadeappear:
                this._initEffectFadeAppear();
                break;
            case this.effectsList.simpleslider:
                this._initEffectSimpleSlider();
                break;
            case this.effectsList.blindUpDown:
                this._initEffectBlindUpDown();
                break;
            case this.effectsList.slideUpDown:
                this._initEffectSlideUpDown();
                break;
        }
        $$('#'+this.blockId+' div.'+this.selectors.sliderControls+' div.afp-left button').first().setStyle({
            top: Math.max(0, this._getControlsHeight()/2-11)+'px'
        });
        $$('#'+this.blockId+' div.'+this.selectors.sliderControls+' div.afp-right button').first().setStyle({
            top: Math.min(0, -this._getControlsHeight()/2+11)+'px'
        });
    },

    _getControlsHeight: function() {
        return $$('#'+this.blockId+' div.'+this.selectors.sliderControls+'').first().getHeight();
    },
    
    _initEffectSlideUpDown: function() {
        for(var i = 0; i<this.slides.length; i++) {
            this.slides[i].innerHTML = '<div>'+this.slides[i].innerHTML+'</div>';
            if(i>0) this.slides[i].hide();
        }
        $(this.blockId).setStyle({paddingBottom: (this._getControlsHeight()+5)+'px'});
        $$('#'+this.blockId+' div.'+this.selectors.sliderControls).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-5)+'px'
        });
    },
    
    _initEffectBlindUpDown: function() {
        for(var i = 1; i<this.slides.length; i++)
            this.slides[i].hide();
        $(this.blockId).setStyle({paddingBottom: (this._getControlsHeight()+5)+'px'});
        $$('#'+this.blockId+' div.'+this.selectors.sliderControls).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-5)+'px'
        });
    },
    
    _initEffectFadeAppear: function() {
        for(var i = 1; i<this.slides.length; i++)
            this.slides[i].hide();
        $(this.blockId).setStyle({paddingBottom: (this._getControlsHeight()+5)+'px'});
        $$('#'+this.blockId+' div.'+this.selectors.sliderControls).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-5)+'px'
        });
    },
    
    _initEffectSimpleSlider: function() {
        this._baseTime = 0.5;
        $$('#'+this.blockId+' div.afp-slides-container').each(function(element) {
            element.setStyle({
                width: $(this.blockId).getWidth()*this.productCount+'px',
                height: $(this.blockId).getHeight()+'px'
            });
        }, this);
        $(this.blockId).setStyle({paddingBottom: (this._getControlsHeight()+5)+'px'});
    },
    
    previous: function() {
        this._flagButton = {
            next: false,
            prev: true
        };
        this.show(this.slideIndexCurrent == 0 ? this.slideIndexMax : this.slideIndexCurrent-1);
    },
    
    next: function() {
        this._flagButton = {
            next: true,
            prev: false
        };
        this.show(this.slideIndexCurrent == this.slideIndexMax ? 0 : this.slideIndexCurrent+1);
    },

    _setSlideEffectLock: function() {
        this._slideEffectInAction = true;
    },

    _resetSlideEffectLock: function() {
        this._slideEffectInAction = false;
    },

    _getSlideEffectLocked: function() {
        return this._slideEffectInAction;
    },
    
    show: function(index) {
        if(this._getSlideEffectLocked() || !$(this.blockId)) return;
        if(!this._canSlide || index == this.slideIndexCurrent) return;
        switch(this.effect) {
            case this.effectsList.fadeappear:
                this._showFadeAppear(index);
                break;
            case this.effectsList.simpleslider:
                this._showSimpleSlider(index);
                break;
            case this.effectsList.blindUpDown:
                this._showBlindUpDown(index);
                break;
            case this.effectsList.slideUpDown:
                this._showSlideUpDown(index);
                break;
        }
        this._flagButton = false;
    },
    
    _showSlideUpDown: function(index) {
        this._setSlideEffectLock();
        new Effect.SlideUp(this.slides[this.slideIndexCurrent], {
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._switchCurrentClass.bind(this, this.slideIndexCurrent, index)
        });
        new Effect.SlideDown(this.slides[index], {
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._resetSlideEffectLock.bind(this)
        });
    },
    
    _showBlindUpDown: function(index) {
        this._setSlideEffectLock();
        new Effect.BlindUp(this.slides[this.slideIndexCurrent], {
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._switchCurrentClass.bind(this, this.slideIndexCurrent, index)
        });
        new Effect.BlindDown(this.slides[index], {
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._resetSlideEffectLock.bind(this)
        });
    },

    _showFadeAppear: function(index, durationPerItem) {
        this._setSlideEffectLock();
        if(typeof durationPerItem == 'undefined' || isNaN(durationPerItem)) durationPerItem = this._baseTime;
        new Effect.Fade(this.slides[this.slideIndexCurrent], {
            duration: durationPerItem,
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._switchCurrentClass.bind(this, this.slideIndexCurrent, index)
        });
        new Effect.Appear(this.slides[index], {
            duration: durationPerItem,
            queue: {position: 'end', scope: this._effectQueueScope},
            afterFinish: this._resetSlideEffectLock.bind(this)
        });
    },
    
    _showSimpleSlider: function(index) {
        this._setSlideEffectLock();
        if(index == 0 && this.slideIndexCurrent == this.slideIndexMax && this._flagButton && this._flagButton.next) {
            for(var i = this.slideIndexMax-1; i >= 0; i--)
                this.slides[this.slideIndexCurrent].insert({after: this.slides[i]});
            $(this._slidesContainer).setStyle({left: '0px'});
            var _afterMove = function() {
                this.slides[this.slideIndexMax-1].insert({after: this.slides[this.slideIndexMax]});
                $(this._slidesContainer).setStyle({left: '0px'});
                this._switchCurrentClass(this.slideIndexCurrent, index);
                this.slideIndexCurrent = index;
                this._resetSlideEffectLock();
            };
            new Effect.Move(this._slidesContainer, {
                x:-$(this.blockId).getWidth(),
                mode: 'absolute',
                transition: Effect.Transitions.sinoidal,
                afterFinish: _afterMove.bind(this)
            });
            return;
        }
        if(index == this.slideIndexMax && this.slideIndexCurrent == this.slideIndexMin && this._flagButton && this._flagButton.prev) {
            this.slides[this.slideIndexMax].insert({after: this.slides[this.slideIndexMin]});
            var _offset = -$(this.blockId).getWidth()*(this.productCount-1);
            $(this._slidesContainer).setStyle({
                left: _offset+'px',
                position: 'relative'
            });
            var _afterMove = function() {
                for(var i = this.slideIndexMax; i >= 0 ; i--)
                    this.slides[this.slideIndexCurrent].insert({after: this.slides[i]});
                $(this._slidesContainer).setStyle({left: _offset+'px'});
                this._switchCurrentClass(this.slideIndexCurrent, index);
                this.slideIndexCurrent = index;
                this._resetSlideEffectLock();
            };
            new Effect.Move(this._slidesContainer, {
                x: _offset+$(this.blockId).getWidth(),
                mode: 'absolute',
                transition: Effect.Transitions.sinoidal,
                afterFinish: _afterMove.bind(this)
            });
            this._resetSlideEffectLock();
            return;
        }
        new Effect.Move(this._slidesContainer, {
            x:-index*$(this.blockId).getWidth(),
            mode: 'absolute',
            transition: Effect.Transitions.sinoidal,
            afterFinish: this._resetSlideEffectLock.bind(this)
        });
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },
    
    _switchCurrentClass: function(oldItem, newItem) {
        if(typeof oldItem == 'undefined' || oldItem == null) {
            for(var i = 0; i<this.sidebarSlides.length; i++)
                this.sidebarSlides[i].removeClassName(this.selectors.sidebarCurrent);
        } else {
            this.sidebarSlides[oldItem].removeClassName(this.selectors.sidebarCurrent);
        }
        this.sidebarSlides[newItem].addClassName(this.selectors.sidebarCurrent);
        this.slideIndexCurrent = newItem;
    }
});
