if ( window.Prototype ) {
    
    /**
     * 
     */
    if ( typeof AjaxLogin == 'undefined' ) {
        AjaxLogin = {};
    }
    
    /**
     * 
     */
    AjaxLogin.DOB = Class.create();
    AjaxLogin.DOB.prototype = {
        initialize: function(selector, required, format) {
            var el = $$(selector)[0];
            var container       = {};
            container.day       = Element.select(el, '#ajaxlogin-day')[0];
            container.month     = Element.select(el, '#ajaxlogin-month')[0];
            container.year      = Element.select(el, '#ajaxlogin-year')[0];
            container.full      = Element.select(el, '#ajaxlogin-full')[0];
            container.advice    = Element.select(el, '#ajaxlogin-advice')[0];
            
            new Varien.DateElement('container', container, required, format);
        }
    };
}