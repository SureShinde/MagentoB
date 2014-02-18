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

var awiSlider = Class.create({
    initialize: function(object, params) {
        this.selfObjectName = object;
        this._canSlide = true;
        if(typeof params != 'undefined') {
            this.blockId = this._getValue(params.blockId);
            this.effect = this._getValue(params.effect);
            this.slidesCount = parseInt(this._getValue(params.slidesCount));
            this.animationSpeed = parseInt(this._getValue(params.animationSpeed));
            this.firstFrameTimeout = this._getValue(params.firstFrameTimeout);
            this.autohideNavigation = parseInt(this._getValue(params.autohideNavigation));
            this.needCalculateWH = this._getValue(params.needCalculateWH);
            
            if(this.firstFrameTimeout) this.firstFrameTimeout = parseInt(this.firstFrameTimeout);

            this.global = this._getValue(params.global) ? this._getValue(params.global) : window;
        }

        this.selectors = {
            item: 'awis-r-default-item',
            slidesContainer: 'awis-slides-container',
            controlsContainer: 'awis-slides-controls',
            controlsCenter: 'awis-c-center',
            controlsLeft: 'awis-c-left',
            controlsRight: 'awis-c-right',
            sidebarCurrent: 'awis-s-current'
        };

        this.settings = {
            jalousie: {
                boxCols: 10,
                sliceDuration: 0.15,
                sliceTimeout: 100
            }
        };

        this.global[this.selfObjectName] = this;
        if(!this.blockId || !this.effect || isNaN(this.slidesCount) || this.slidesCount == 1) this._canSlide = false;
        if(this._canSlide) {
            this.slideIndexMin = this.slideIndexCurrent = 0;
            this.slideIndexMax = this.slidesCount - 1;
            this.slides = [];
            $$('#'+this.blockId+' div.'+this.selectors.item).each(function(element) {
                this.slides.push(element);
            }, this);
            this.sidebarSlides = [];
            $$('#'+this.blockId+' .'+this.selectors.controlsContainer+' .'+this.selectors.controlsCenter+' button').each(function(element) {
                this.sidebarSlides.push(element);
            }, this);
            if(this.needCalculateWH && this.slides.length) {
                this._whSetter = new PeriodicalExecuter(this._setWH.bind(this), 0.1);
                this._canSlide = false;
            }
            this._effectQueueScope = 'awisQueue'+this.blockId;
            this._flagButton = false;
            this._slideEffectInAction = false;
            this.firstAutoSwitch = true;
            this._timer = null;
            if(this.animationSpeed) {
                this._baseTime = Math.min(Math.max(this.animationSpeed/3, 0.1), 1);
                Event.observe(document, 'dom:loaded', this._startPE.bind(this));
                $(this.blockId).observe('mouseover', this._stopPE.bind(this));
                $(this.blockId).observe('mouseout', this._startPE.bind(this));
            } else {
                this._baseTime = 1;
            }
            this._slidesContainer = $$('#'+this.blockId+' div.'+this.selectors.slidesContainer).first();
            this._switchEffectPrefix = 'awis-effect-';
            this._randomEffectClass = 'awis-effect-random';
            /* Controls section */
            this._controlsShowed = true;
            if(this.autohideNavigation == 2) {
                $(this.blockId).observe('mouseover', this._showControls.bind(this));
                $(this.blockId).observe('mouseout', this._hideControls.bind(this));
            }
            this._controlLeft = $$('#'+this.blockId+' .'+this.selectors.controlsContainer+' .'+this.selectors.controlsLeft).first();
            this._controlRight = $$('#'+this.blockId+' .'+this.selectors.controlsContainer+' .'+this.selectors.controlsRight).first();
            this._controlsHiderPE = null;
            this._initEffect();
        }
    },

    _getValue: function(variable) {
        if(typeof variable == 'undefined') return null;
        return variable;
    },

    _setWH: function() {
        if(this.slides[0].getWidth() && this.slides[0].getHeight()) {
            if(this._whSetter) {
                this._whSetter.stop();
                this._whSetter = null;
            }
            $(this.blockId).setStyle({
                width: this.slides[0].getWidth()+'px',
                height: this.slides[0].getHeight()+'px'
            });
            for(var i = 1; i < this.slides.length; i++)
                this.slides[i].setStyle({
                    width: this.slides[0].getWidth()+'px',
                    height: this.slides[0].getHeight()+'px'
                });
            this._initEffect();
            this._canSlide = true;
        }
    },

    _showControls: function() {
        if(this._controlsHiderPE) {
            this._controlsHiderPE.stop();
            this._controlsHiderPE = null;
        }
        if(!this._controlsShowed) {
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
                if(this.autohideNavigation == 0) {
                    $$('#'+this.blockId+' .'+this.selectors.controlsContainer).first().hide();
                }
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
        if(this.animationSpeed) {
            var _timeout = this.animationSpeed;
            if(this.firstAutoSwitch && this.firstFrameTimeout) {
                _timeout = this.firstFrameTimeout;
            }
            this._timer = new PeriodicalExecuter(this.next.bind(this), _timeout);
        }
    },

    _stopPE: function() {
        if(this.animationSpeed && this._timer) {
            this._timer.stop();
            this._timer = null;
        }
    },

    _initEffect: function() {
        if(this.autohideNavigation != 1) this._hideControls(null, true);
        this.effectsList = {
            simpleslider: 'simple-slider',
            fadeappear: 'fade-appear',
            blindUpDown: 'blind-up-down',
            slideUpDown: 'slide-up-down',
            jalousie: 'jalousie',
            random: 'random'
        };
        this.effectNames = [];
        for(var _en in this.effectsList)
            this.effectNames.push(_en);
        switch(this.effect) {
            case this.effectsList.simpleslider:
                this._initEffectSimpleSlider();
                break;
            case this.effectsList.fadeappear:
                this._initEffectFadeAppear();
                break;
            case this.effectsList.blindUpDown:
                this._initEffectBlindUpDown();
                break;
            case this.effectsList.slideUpDown:
                this._initEffectSlideUpDown();
                break;
            case this.effectsList.jalousie:
                this._initEffectJalousie();
                break;
            case this.effectsList.random:
                this._initEffectRandom();
                break;
        }
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer+' div.'+this.selectors.controlsLeft+' button').first().setStyle({
            top: Math.max(0, this._getControlsHeight()/2-11)+'px'
        });
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer+' div.'+this.selectors.controlsRight+' button').first().setStyle({
            top: Math.min(0, -this._getControlsHeight()/2+11)+'px'
        });
    },

    _randomGetEffect: function(checkPrev) {
        if(typeof checkPrev == 'undefined') checkPrev = true;
        if(checkPrev && (!this.settings || !this.settings.random || typeof this.settings.random.prevEffect == 'undefined'))
            checkPrev = false;
        if(!this._randomEffectNames) {
            this._randomEffectNames = [];
            for(var _en in this.effectsList)
                if(_en != this.effectsList.random)
                    this._randomEffectNames.push(_en);
        }
        var rndIndex = -1;
        while(typeof this._randomEffectNames[rndIndex] == 'undefined' || (checkPrev ? this.settings.random.prevEffect == this._randomEffectNames[rndIndex] : false))
            rndIndex = Math.floor(Math.random()*(this._randomEffectNames.length));
        return this._randomEffectNames[rndIndex].toString();
    },

    _randomCallInit: function(index, effect) {
        if(typeof effect == 'undefined') effect = this.settings.random.prevEffect;
        switch(effect) {
            case 'jalousie':
                this._initEffectJalousie(index);
                break;
            case 'slideUpDown':
                this._initEffectSlideUpDown(index);
                break;
            case 'blindUpDown':
                this._initEffectBlindUpDown(index);
                break;
            case 'fadeappear':
                this._initEffectFadeAppear(index);
                break;
            case 'simpleslider':
                this._initEffectSimpleSlider(index)
                break;
        }
    },

    _initEffectRandom: function() {
        this._randomPE = [];
        this.settings.random = {
            prevEffect: this._randomGetEffect()
        };
        this._randomCallInit(0, this.settings.random.prevEffect);
    },

    _showAllSlides: function() {
        for(var i = 0; i<this.slides.length; i++)
            this.slides[i].show();
    },

    _resetSliderBlock: function() {
        this._showAllSlides();
        for(var i = 0; i < this.effectNames.length; i++)
            $(this.blockId).removeClassName(this._switchEffectPrefix+this.effectsList[this.effectNames[i]]);
        $$('#'+this.blockId+' div.'+this.selectors.slidesContainer).first().writeAttribute('style');
    },

    _initEffectJalousie: function(index) {
        if(typeof index == 'undefined') index = this.slideIndexMin;
        this._jalousieSlices = [];
        this._resetSliderBlock();
        $(this.blockId).addClassName(this._switchEffectPrefix+this.effectsList.jalousie);
        for(var i = 0; i<this.slides.length; i++) {
            this.slides[i].setStyle({zIndex: 1});
        }
        this.slides[index].setStyle({zIndex: 3});
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-25)+'px'
        });
        this.settings.jalousie.baseTime = Math.max(this._baseTime, 3);
        this.settings.jalousie.boxCols = Math.ceil($(this.blockId).getWidth()/50);
        this.settings.jalousie.sliceDuration = this.settings.jalousie.baseTime / this.settings.jalousie.boxCols;
        this.settings.jalousie.sliceTimeout = 75 * this.settings.jalousie.sliceDuration;
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },

    _initEffectSlideUpDown: function(index) {
        if(typeof index == 'undefined') index = this.slideIndexMin;
        this._resetSliderBlock();
        $(this.blockId).addClassName(this._switchEffectPrefix+this.effectsList.slideUpDown);
        for(var i = 0; i<this.slides.length; i++) {
            if(!this._slideUpDownAlreadyWrapped) {
                this.slides[i].innerHTML = '<div>'+this.slides[i].innerHTML+'</div>';
                this._slideUpDownAlreadyWrapped = true;
            }
            if(i!=index) this.slides[i].hide();
        }
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-25)+'px'
        });
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },

    _initEffectBlindUpDown: function(index) {
        if(typeof index == 'undefined') index = this.slideIndexMin;
        this._resetSliderBlock();
        $(this.blockId).addClassName(this._switchEffectPrefix+this.effectsList.blindUpDown);
        for(var i = 0; i<this.slides.length; i++)
            if(i != index) this.slides[i].hide();
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-25)+'px'
        });
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },

    _initEffectFadeAppear: function(index) {
        if(typeof index == 'undefined') index = this.slideIndexMin;
        this._resetSliderBlock();
        $(this.blockId).addClassName(this._switchEffectPrefix+this.effectsList.fadeappear);
        for(var i = 0; i<this.slides.length; i++)
            if(i != index) this.slides[i].hide();
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().setStyle({
            top: ($(this.blockId).getHeight()-this._getControlsHeight()-25)+'px'
        });
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },

    _initEffectSimpleSlider: function(index) {
        if(typeof index == 'undefined') index = this.slideIndexMin;
        this._resetSliderBlock();
        $(this.blockId).addClassName(this._switchEffectPrefix+this.effectsList.simpleslider);
        this._baseTime = 0.5;
        $$('#'+this.blockId+' div.'+this.selectors.slidesContainer).first().setStyle({
            width: $(this.blockId).getWidth()*this.slidesCount+'px',
            height: $(this.blockId).getHeight()+'px',
            left: -$(this.blockId).getWidth()*index+'px'
        });
        $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().setStyle({
            top: (-(this._getControlsHeight()+25))+'px'
        });
        this._switchCurrentClass(this.slideIndexCurrent, index);
    },

    _getControlsHeight: function() {
        return $$('#'+this.blockId+' div.'+this.selectors.controlsContainer).first().getHeight();
    },

    previous: function() {
        this._flagButton = {
            next: false,
            prev: true
        };
        this.show(this.slideIndexCurrent == 0 ? this.slideIndexMax : this.slideIndexCurrent-1);
    },

    next: function() {
        if(this.firstAutoSwitch) {
            this.firstAutoSwitch = false;
            this._stopPE();
            this._startPE();
        }
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
        if(this._getSlideEffectLocked()) return;
        if(!this._canSlide || index == this.slideIndexCurrent) return;
        switch(this.effect) {
            case this.effectsList.simpleslider:
                this._showSimpleSlider(index);
                break;
            case this.effectsList.fadeappear:
                this._showFadeAppear(index);
                break;
            case this.effectsList.blindUpDown:
                this._showBlindUpDown(index);
                break;
            case this.effectsList.slideUpDown:
                this._showSlideUpDown(index);
                break;
            case this.effectsList.jalousie:
                this._showJalousie(index);
                break;
            case this.effectsList.random:
                this._showRandom(index);
                break;
        }
        this._flagButton = false;
    },

    _randomReInit: function(index) {
        if(!this._getSlideEffectLocked()) {
            if(this._randomPE && this._randomPE[index]) {
                this._randomPE[index].stop();
                delete this._randomPE[index];
            } else {
                this._randomPE = [];
            }
            this.settings.random.prevEffect = this.settings.random.nextEffect;
            this._randomCallInit(index, this.settings.random.prevEffect);
        }
    },

    _showRandom: function(index) {
        this.settings.random.nextEffect = this._randomGetEffect();
        switch(this.settings.random.prevEffect) {
            case 'jalousie':
                this._showJalousie(index);
                break;
            case 'slideUpDown':
                this._showSlideUpDown(index);
                break;
            case 'blindUpDown':
                this._showBlindUpDown(index);
                break;
            case 'fadeappear':
                this._showFadeAppear(index);
                break;
            case 'simpleslider':
                this._showSimpleSlider(index);
                break;
        }
        this._randomPE[index] = new PeriodicalExecuter(this._randomReInit.bind(this, index), 0.1);
    },

    _jalousieGetSliceId: function(slide, index) {
        return this.blockId+'slide'+slide+index;
    },

    _jalousieGetSliceElement: function(slide, index) {
        var newEl = new Element('div', {
            id: this._jalousieGetSliceId(slide, index)
        });
        newEl.addClassName('awis-jalousie-slice');
        return newEl;
    },

    _jalousieCreateSlices: function(index) {
        if(!this._jalousieSlices[index]) {
            this._jalousieSlices[index] = [];
        }
        var sliceWidth = Math.round($(this.blockId).getWidth() / this.settings.jalousie.boxCols);
        var sliceHeight = $(this.blockId).getHeight();
        var currentImage = $$('#'+this.blockId+'div'+index+' img').first();
        if(!currentImage || !(currentImage = currentImage.readAttribute('src'))) return;
        for(var i = 0; i<this.settings.jalousie.boxCols; i++) {
            Element.insert(this.slides[index], {after: this._jalousieGetSliceElement(index, i)});
            this._jalousieSlices[index].push($(this._jalousieGetSliceId(index, i)));
            this._jalousieSlices[index][i].setStyle({
                zIndex: 3,
                width: sliceWidth+'px',
                height: sliceHeight+'px',
                marginLeft: i*sliceWidth+'px',
                backgroundImage: "url('"+currentImage+"')",
                backgroundPosition: -i*sliceWidth+'px'
            });
        }
    },

    _jalousieShowSlice: function(slide, index) {
        if(this._jalousieSlices[slide] && this._jalousieSlices[slide][index]) {
            new Effect.Morph(this._jalousieGetSliceId(slide, index), {style: 'width: 0px',
                duration: this.settings.jalousie.sliceDuration
            });
        }
    },

    _jalousieSlicesShowed: function(slide) {
        for(var i = 0; i<this._jalousieSlices[slide].length; i++)
            this._jalousieSlices[slide][i].remove();
        delete this._jalousieSlices[slide];
        this._resetSlideEffectLock();
        this._switchCurrentClass(this.slideIndexCurrent, this._jalousieNewSlide);
        this._jalousieNewSlide = null;
    },

    _showJalousie: function(index) {
        this._jalousieNewSlide = index;
        this.slides[index].setStyle({zIndex: 2});
        for(var i = 0; i<this.slides.length; i++)
            if(i != index)
                this.slides[i].setStyle({zIndex: 1});
        this._jalousieCreateSlices(this.slideIndexCurrent);
        this._setSlideEffectLock();
        for(var i = 0; i<this._jalousieSlices[this.slideIndexCurrent].length; i++) {
            setTimeout(this._jalousieShowSlice.bind(this, this.slideIndexCurrent, i), i*this.settings.jalousie.sliceTimeout);
        }
        setTimeout(this._jalousieSlicesShowed.bind(this, this.slideIndexCurrent), i*this.settings.jalousie.sliceTimeout+i*this.settings.jalousie.sliceDuration*100);
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
            }
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
            var _offset = -$(this.blockId).getWidth()*(this.slidesCount-1);
            $(this._slidesContainer).setStyle({left: _offset+'px'});
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
