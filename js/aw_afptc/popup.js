var awAfptcPopup = Class.create({
    
    config: {},
    
    initialize: function(config) {
        
       this.config = config;
       
       this.observeDeclineAction();
        
    },
    
    align: function() { 

        var el = this.config.el;        
        this.config.el.setStyle({
            top: document.viewport.getHeight() / 2 - el.getHeight() / 2 + 'px',
            left: document.viewport.getWidth() / 2 - el.getWidth() / 2 + 'px',
        });   
        
        Event.observe(window, 'resize', function() { this.resizeBlock(el) }.bind(this));
        
        Effect.Appear(el, {duration: 0.4});
        this.config.overlay.show();
    },
            
     collectPos: function(el) {
        var x, y;
        var elWidth = el.getWidth();
        var docWidth = document.viewport.getWidth();

         x = docWidth/2 - elWidth/2;
      

        var elHeight = el.getHeight();
        var docHeight = document.viewport.getHeight();

        y = docHeight/2 - elHeight/2;
        
        return [x, y];
    },
            
    resizeBlock: function(el) {
  
        el.setStyle({
            height: 'auto', width: 'auto'
        });
        var xy = this.collectPos(el);
        
        if (xy[1] < 50) {
            xy[1] = 50;
            el.setStyle({
                height: (document.viewport.getHeight() - 100) + 'px'
            });
        }
        el.setStyle({ 'left': xy[0] + 'px', 'top': xy[1] + 'px'});
    },
       
    observeDeclineAction: function() {
 
        Event.observe(this.config.decline, 'click', function(event) {            
            if(this.config.declineCheck && this.config.declineCheck.checked) {                
                var date = new Date();
                date.setTime(date.getTime() + 3600000*24*360);
                Mage.Cookies.set(this.config.cookie, true, date);
            }
            try {
                 Effect.Fade(this.config.el, {duration: 0.4});
                 Effect.Fade(this.config.overlay, {duration: 0.4});
            } catch(e) {
               
            }                
        }.bind(this));
    }
    
});
 