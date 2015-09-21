/*!
 * Lazy Load - jQuery plugin for lazy loading images
 *
 * Copyright (c) 2007-2015 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/lazyload
 *
 * Version:  1.9.5
 *
 */

(function($, window, document, undefined) {
    var $window = $(window);

    $.fn.lazyload = function(options) {
        var elements = this;
        var $container;
        var settings = {
            threshold       : 0,
            failure_limit   : 0,
            event           : "scroll",
            effect          : "show",
            container       : window,
            data_attribute  : "src",
            skip_invisible  : false,
            appear          : null,
            load            : null,
            placeholder     : "data:image/gif;base64,R0lGODlhmACYAOZ/APvKk/zTo/q6efzMmveVPM7NzZmXmPzSrP3jy/7py/eQNvWKLfq8gPq2c//58P/y3PWFJfaOMf/47fzau9ra2veSOVlWVv3hvP/26fimV/ikVHt4efmwaP/15f3Yq//+/f3etvihUfebRv7x5f7u2fLy8v/69PmpXPzWs/7+/oaDhGdkZfvHjfedSe3t7Tg0NfvFlPvOpP727viuaycjJP/9+veXQPePNPeeTCMfIP/9+PeZQvWML0tHSP/89vWIKvigT/msYvmuZfWEI/7t1YF/gPWCIP7mxv/++/q5dfWHKPqybfvEh/7s0fq/gf3kwvrChPzQnv/047y7vPq1b/3asPmrYP/z4f3jwP3mz1FNTvz8/P7t3f3gxv/7+P3csvrCjf3q2fvFii8rLPzewP7v4Pq+if3p1fj4+GFeX5GPkPm2efejWfipZPvBiKelpvimYD87POXk5LKxsbi3t/eeUfzXtvegVfebTPicSv7q0cLBwXJwcJCOjv///////yH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlE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"
        };

        function update() {
            var counter = 0;

            elements.each(function() {
                var $this = $(this);
                if (settings.skip_invisible && !$this.is(":visible")) {
                    return;
                }
                if ($.abovethetop(this, settings) ||
                    $.leftofbegin(this, settings)) {
                        /* Nothing. */
                } else if (!$.belowthefold(this, settings) &&
                    !$.rightoffold(this, settings)) {
                        $this.trigger("appear");
                        /* if we found an image we'll load, reset the counter */
                        counter = 0;
                } else {
                    if (++counter > settings.failure_limit) {
                        return false;
                    }
                }
            });

        }

        
        if(options) {
            /* Maintain BC for a couple of versions. */
            if (undefined !== options.failurelimit) {
                options.failure_limit = options.failurelimit;
                delete options.failurelimit;
            }
            if (undefined !== options.effectspeed) {
                options.effect_speed = options.effectspeed;
                delete options.effectspeed;
            }

            $.extend(settings, options);
        }

        /* Cache container as jQuery as object. */
        $container = (settings.container === undefined ||
                      settings.container === window) ? $window : $(settings.container);

        /* Fire one scroll event per scroll. Not one scroll event per image. */
        if (0 === settings.event.indexOf("scroll")) {
            $container.bind(settings.event, function() {
                return update();
            });
        }

        this.each(function() {
            var self = this;
            var $self = $(self);

            self.loaded = false;

            /* If no src attribute given use data:uri. */
            if ($self.attr("src") === undefined || $self.attr("src") === false) {
                if ($self.is("img")) {
                    $self.attr("src", settings.placeholder);
                }
            }

            /* When appear is triggered load original image. */
            $self.one("appear", function() {
                if (!this.loaded) {
                    if (settings.appear) {
                        var elements_left = elements.length;
                        settings.appear.call(self, elements_left, settings);
                    }
                    $("<img />")
                        // ===  mario script
                        /*
                        .bind("load", function() {
                            
                            var original        = $self.attr("data-" + settings.data_attribute);
                            imageClientWidth    = $self.context.width;
                            imageClientHeight   = $self.context.height;
                            
                            $self.hide();
                            if ($self.is("img")) {
                                imagePath           = $self.attr('data-path');
                                imageDomain         = $self.attr('data-original');
                                imageFile           = $self.attr("data-img");
                                if(/^http:\/\/im.bilna.org/.test(imageDomain) || /^https:\/\/im.bilna.org/.test(imageDomain)){
                                    var dataPost = {imageClientWidth    : imageClientWidth, 
                                                    imageClientHeight   : imageClientHeight, 
                                                    imageFile           : imageFile,
                                                    imagePath           : imagePath
                                                   }

                                    $.ajax({
                                        url      : baseUri + 'image/create',
                                        type     : 'POST',
                                        dataType : 'json',
                                        data     : dataPost,
                                        success  : function(result){
                                            if(result.status){
                                                original = result.data;
                                                $self.attr("data-"+settings.data_attribute,original);
                                                $self.attr("src", original);
                                                //console.log(result.message);
                                            }
                                        }
                                    });
                                }else{
                                    $self.attr("data-"+settings.data_attribute,original);
                                    $self.attr("src", original);
                                }   
                                
                            } else {
                                $self.css("background-image", "url('" + original + "')");
                            }
                            $self[settings.effect](settings.effect_speed);

                            self.loaded = true;

                            // Remove image from array so it is not looped next time.
                            var temp = $.grep(elements, function(element) {
                                return !element.loaded;
                            });
                            elements = $(temp);

                            if (settings.load) {
                                var elements_left = elements.length;
                                settings.load.call(self, elements_left, settings);
                            }
                        })
                        */
                        // ===  original script
                    
                        .bind("load", function() {

                            var original = $self.attr("data-" + settings.data_attribute);
                            $self.hide();
                            if ($self.is("img")) {
                                $self.attr("src", original);
                            } else {
                                $self.css("background-image", "url('" + original + "')");
                            }
                            $self[settings.effect](settings.effect_speed);

                            self.loaded = true;

                            /* Remove image from array so it is not looped next time. */
                            var temp = $.grep(elements, function(element) {
                                return !element.loaded;
                            });
                            elements = $(temp);

                            if (settings.load) {
                                var elements_left = elements.length;
                                settings.load.call(self, elements_left, settings);
                            }
                        })
                        //end original script
                        .attr("src", $self.attr("data-" + settings.data_attribute));
                }
            });

            /* When wanted event is triggered load original image */
            /* by triggering appear.                              */
            if (0 !== settings.event.indexOf("scroll")) {
                $self.bind(settings.event, function() {
                    if (!self.loaded) {
                        $self.trigger("appear");
                    }
                });
            }
        });

        /* Check if something appears when window is resized. */
        $window.bind("resize", function() {
            update();
        });

        /* With IOS5 force loading images when navigating with back button. */
        /* Non optimal workaround. */
        if ((/(?:iphone|ipod|ipad).*os 5/gi).test(navigator.appVersion)) {
            $window.bind("pageshow", function(event) {
                if (event.originalEvent && event.originalEvent.persisted) {
                    elements.each(function() {
                        $(this).trigger("appear");
                    });
                }
            });
        }

        /* Force initial check if images should appear. */
        $(document).ready(function() {
            update();
        });

        return this;
    };

    /* Convenience methods in jQuery namespace.           */
    /* Use as  $.belowthefold(element, {threshold : 100, container : window}) */

    $.belowthefold = function(element, settings) {
        var fold;

        if (settings.container === undefined || settings.container === window) {
            fold = (window.innerHeight ? window.innerHeight : $window.height()) + $window.scrollTop();
        } else {
            fold = $(settings.container).offset().top + $(settings.container).height();
        }

        return fold <= $(element).offset().top - settings.threshold;
    };

    $.rightoffold = function(element, settings) {
        var fold;

        if (settings.container === undefined || settings.container === window) {
            fold = $window.width() + $window.scrollLeft();
        } else {
            fold = $(settings.container).offset().left + $(settings.container).width();
        }

        return fold <= $(element).offset().left - settings.threshold;
    };

    $.abovethetop = function(element, settings) {
        var fold;

        if (settings.container === undefined || settings.container === window) {
            fold = $window.scrollTop();
        } else {
            fold = $(settings.container).offset().top;
        }

        return fold >= $(element).offset().top + settings.threshold  + $(element).height();
    };

    $.leftofbegin = function(element, settings) {
        var fold;

        if (settings.container === undefined || settings.container === window) {
            fold = $window.scrollLeft();
        } else {
            fold = $(settings.container).offset().left;
        }

        return fold >= $(element).offset().left + settings.threshold + $(element).width();
    };

    $.inviewport = function(element, settings) {
         return !$.rightoffold(element, settings) && !$.leftofbegin(element, settings) &&
                !$.belowthefold(element, settings) && !$.abovethetop(element, settings);
     };

    /* Custom selectors for your convenience.   */
    /* Use as $("img:below-the-fold").something() or */
    /* $("img").filter(":below-the-fold").something() which is faster */

    $.extend($.expr[":"], {
        "below-the-fold" : function(a) { return $.belowthefold(a, {threshold : 0}); },
        "above-the-top"  : function(a) { return !$.belowthefold(a, {threshold : 0}); },
        "right-of-screen": function(a) { return $.rightoffold(a, {threshold : 0}); },
        "left-of-screen" : function(a) { return !$.rightoffold(a, {threshold : 0}); },
        "in-viewport"    : function(a) { return $.inviewport(a, {threshold : 0}); },
        /* Maintain BC for couple of versions. */
        "above-the-fold" : function(a) { return !$.belowthefold(a, {threshold : 0}); },
        "right-of-fold"  : function(a) { return $.rightoffold(a, {threshold : 0}); },
        "left-of-fold"   : function(a) { return !$.rightoffold(a, {threshold : 0}); }
    });

    
})(jQuery, window, document);

        