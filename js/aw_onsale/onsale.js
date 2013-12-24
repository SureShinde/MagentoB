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
 * @package    AW_Onsale
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */


AwOnSale = Class.create();
AwOnSale.prototype = {
    initialize: function(){
        this.helements = new Array();
        this.velements = new Array();
        document.observe("dom:loaded", onPageLoad);
        Event.observe(window, 'load',onPageLoad);
    },
    setVertPos: function(element, position){
        var eH = element.offsetHeight;
        var pH = element.parentNode.offsetHeight;
        var eT = 0;

        if (eH != pH){
            switch (position){
                case 'top':
                    eT = 0;
                    break;
                case 'middle':
                    eT = (pH - eH)/2;
                    break;
                case 'bottom':
                    eT = pH - eH;
                    break;
                default:
                    eT = 0;
            }
        } else if (eH == pH) {
            eT = 0;
        }
        element.style.bottom = null;
        element.style.top = eT + 'px';

    },
    setHorPos: function(element, position){
        var eW = element.offsetWidth;
        var pW = element.parentNode.offsetWidth;
        var eL = 0;

        if (eW != pW){
            switch (position){
                case 'left':
                    eL = 0;
                    break;
                case 'center':
                    eL = (pW - eW)/2;
                    break;
                case 'right':
                    eL = pW - eW;
                    break;
                default:
                    eL = 0;
            }
        } else if (eW == pW) {
            eL = 0;
        }
        element.style.right = null;
        element.style.left = eL + 'px';

    },
    registerVertPosition: function(element, position) {
        element.vposition = position;
        this.velements.push(element);
    },
    registerHorPosition: function(element, position) {
        element.hposition = position;
        this.helements.push(element);
    }
}

var onPageLoad = function(){
    /* Set hor. position to labels */
    if (onsale.helements.length > 0){
        for (var i = 0; i < onsale.helements.length; i++){
            onsale.setHorPos(onsale.helements[i], onsale.helements[i].hposition);
        }
    }
    /* Set vert. position to labels */
    if (onsale.velements.length > 0){
        for (var j = 0; j < onsale.velements.length; j++){
            onsale.setVertPos(onsale.velements[j], onsale.velements[j].vposition);
        }
    }
}

function onsaleinit(id,hpos,vpos) {
    try{
    lcontainer = $('category-container-'+id);
    llabel = $('category-onsale-label-'+id);
    onsale.registerHorPosition(lcontainer, hpos);
    onsale.registerVertPosition(lcontainer, vpos);
    onsale.registerHorPosition(llabel, 'center');
    onsale.registerVertPosition(llabel, 'middle');
    onsale.setHorPos(lcontainer, lcontainer.hposition);
    onsale.setVertPos(lcontainer, lcontainer.vposition);
    onsale.setHorPos(llabel, llabel.hposition);
    onsale.setVertPos(llabel, llabel.vposition);
    }catch(e){}
}

if(typeof onsale=='undefined') {
    var onsale = new AwOnSale();
}