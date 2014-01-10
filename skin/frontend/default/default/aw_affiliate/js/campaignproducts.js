/**
 *
 *
 * @category   AW
 * @package    AW_Affiliate
 * @copyright  Copyright (c) 2013 (http://www.bilna.com)
 * 
 * @license    
 */

var AWGenerateLinkProducts = Class.create();
AWGenerateLinkProducts = {
    initialize: function(config) {
        this.config = config;
        this.url = config.url;
        this.cat = config.cat;  
        this.wh  = config.wh;
        this.catOpt = config.catOpt;
        Event.observe(window, 'load', function(){
            $('generate-link-btn').observe('click', this.generateLink.bind(this));
        }.bind(this));
    },

    generateLink: function() {
          this._generateScript();        
    },

    _generateScript: function(){
        var _url = this.url;
        var _cat = this.cat;
        var _num = this.num;
        var _wh = this.wh;
        var __wh = _wh.split('x')
        var _catOpt = this.catOpt;
        var _params = 'traffic_source_generate/' + encodeURIComponent($('traffic-source-generate').getValue())
                +'/width_to_generate/'+ encodeURIComponent(_wh)
                +'/category_to_generate/'+ encodeURIComponent(_cat)
                +'/category_option_to_generate/'+ encodeURIComponent(_catOpt)
        $('result').setValue('<iframe width="'+__wh[0]+'" scrolling="no" height="'+__wh[1]+'" frameborder="0" src="'+_url+ _params+'"></iframe>');
        $('result').focus();
        $('result').select();

    }

}