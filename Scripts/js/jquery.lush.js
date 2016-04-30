/*!
 * Lush Content Slider
 * http://geedmo.com
 *
 * Version: 1.6
 * Created: 04/02/2013
 * Updated: 07/05/2013
 *
 * Copyright (c) 2013, Geedmo. All rights reserved.
 * Released under CodeCanyon Regular License: http://codecanyon.net/licenses
 *
 * News: http://codecanyon.net/user/geedmo/portfolio
 * ======================================================= */

;(function ( $, window, document, undefined ) {


    /**
     *  Plugin Helpers
     *******************************/
    $.fn.forceReflow = function(){
      return this.each(function(){
        var reflow = this.offsetWidth;
      });
    };

    $.fn.clearState = function(state){
      return this.each(function(){
        $(this).removeClass( $(this).data('activeClass') ? $(this).data('activeClass') : '' )
          .removeClass( state )
          .removeClass('live');
      });
    };

    $.fn.prepareEffect = function(duration, easing){
      return this.each(function(){
        var $this = $(this);
        if($this.css($.support.css3feature.animation.name+'FillMode') === 'both') {
          $this.clearTransition()
               .css($.support.css3feature.animation.name+'Duration', duration+ 'ms')
               .css($.support.css3feature.animation.name+'TimingFunction', easing)
        }
        else {
          var transition = {};
          // separate transitions property 
          transition[$.support.css3feature.transition.name+'Property'] = 'all';
          transition[$.support.css3feature.transition.name+'Duration'] = duration + 'ms';
          transition[$.support.css3feature.transition.name+'TimingFunction'] = easing;
          transition[$.support.css3feature.transition.name+'Delay'] = '0s' 

          $this.css(transition)
        }
      });
    };

    $.fn.clearTransition = function() {
      return this.each(function(){
        $(this).css($.support.css3feature.transition.name+'Duration', '0s')
            .css($.support.css3feature.transition.name, 'none')
      });
    }
  
    /**
     *  Plugin Globals
     *******************************/

    var pluginName      = 'lush',
        sliderClass     = 'lush-slider',
        flexsliderClass = 'flexslider',
        containerClass  = 'lush',
        classPrev       = 'lush-prev',
        classNext       = 'lush-next',
        classPage       = 'lush-page',
        classNav        = 'lush-nav',
        classShadow     = 'lush-shadow',
        classPaging     = 'lush-external',
        sliderData      = 'lushSlider',
        flexsliderData  = 'lushFlexslider',
        fadeMargin      = 50;



    function Lush( element, option ) {

        /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
         * - run here when document is ready
         * ======================================================= */
        $.support.css3feature=(function(){var b=document.createElement("lush"),d=(function(){var f={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"},e;for(e in f){if(b.style[e]!==undefined){return{end:f[e],name:e}}}}()),c=(function(){var f={WebkitAnimation:"webkitAnimationEnd",MozAnimation:"animationend",OAnimation:"oAnimationEnd",MSAnimation:"MSAnimationEnd",animation:"animationend"},e;for(e in f){if(b.style[e]!==undefined){return{end:f[e],name:e}}}}());b=null;return (d || c) && {transition:d,animation:c}})();


        // a footprint to know that we have already started
        $.data(element, pluginName, this);

        this.container = $(element);
        this.elements  = this.container.children().not('.ignore, .lush-nav');

        this.options = option;

        this.sliding     = false;   // lock a slide when doing animations
        this.stopped     = false;   // slider stopped and needs to advance
        this.paused      = false;   // global paused state
        this.outRendered = false;   // to check if animation out was rendered
    
        // ensure properties as number
        this.options.deadtime = isNaN(parseInt(this.options.deadtime)) ? 0 : parseInt(this.options.deadtime); 
        this.options.delayed = isNaN(parseInt(this.options.delayed)) ? 0 : parseInt(this.options.delayed); 

        // carousel only works with slider mode
        if( ! this.options.slider && this.options.carousel)
            this.options.carousel = false;

        // translate classic easing function to css3 cubic-bezier
        this.cssEasing = {
          'linear':         'linear',
          'swing':          'ease-out',
          'ease':           'ease',
          'ease-in':        'ease-in',
          'ease-out':       'ease-out',
          'ease-in-out':    'ease-in-out',
          'snap':           'cubic-bezier(0,1,.5,1)',
          'easeOutCubic':   'cubic-bezier(.215,.61,.355,1)',
          'easeInOutCubic': 'cubic-bezier(.645,.045,.355,1)',
          'easeInCirc':     'cubic-bezier(.6,.04,.98,.335)',
          'easeOutCirc':    'cubic-bezier(.075,.82,.165,1)',
          'easeInOutCirc':  'cubic-bezier(.785,.135,.15,.86)',
          'easeInExpo':     'cubic-bezier(.95,.05,.795,.035)',
          'easeOutExpo':    'cubic-bezier(.19,1,.22,1)',
          'easeInOutExpo':  'cubic-bezier(1,0,0,1)',
          'easeInQuad':     'cubic-bezier(.55,.085,.68,.53)',
          'easeOutQuad':    'cubic-bezier(.25,.46,.45,.94)',
          'easeInOutQuad':  'cubic-bezier(.455,.03,.515,.955)',
          'easeInQuart':    'cubic-bezier(.895,.03,.685,.22)',
          'easeOutQuart':   'cubic-bezier(.165,.84,.44,1)',
          'easeInOutQuart': 'cubic-bezier(.77,0,.175,1)',
          'easeInQuint':    'cubic-bezier(.755,.05,.855,.06)',
          'easeOutQuint':   'cubic-bezier(.23,1,.32,1)',
          'easeInOutQuint': 'cubic-bezier(.86,0,.07,1)',
          'easeInSine':     'cubic-bezier(.47,0,.745,.715)',
          'easeOutSine':    'cubic-bezier(.39,.575,.565,1)',
          'easeInOutSine':  'cubic-bezier(.445,.05,.55,.95)',
          'easeInBack':     'cubic-bezier(0.6,-0.28,0.735,0.045)',
          'easeOutBack':    'cubic-bezier(.175, .885,.32,1.275)',
          'easeInOutBack':  'cubic-bezier(.68,-.55,.265,1.55)'
        }

        // hidden until starts
        this.container.css({visibility : 'hidden'})

        if(this.options.slider)
          this.container.hide();

        this.preload(this.container, $.proxy(this.init, this));
    }

    Lush.prototype = {

      init: function() {
          var prevTimeOut = 0,
              prevTimeIn  = 0,
              oldDisplay,
              self = this;

          if ( ! this.container.hasClass('lush') )
            this.container.addClass('lush')

          this.container.css({visibility : 'visible'})
      
  
      this.updatePos();

          // flex slider fade animation
          if(this.options.flexslider) {
            oldDisplay = this.container.css('display');
            //this.container.show()
          }
          if(this.options.slider)
            this.container.show()
      
          $.each(this.elements, $.proxy(function(i, element){
          // e :   the current element
          // origin: where to get the slide data
          // $el:    the jQueryzed element
          // origin: auxiliar to save element data
          var e = this.elements[i].$el = $(element),
              origin;
          
          /////// SLIDE IN DATA 
          
              e.slideIn = {};
              e.slideOut = {};

          // get data from element if set, then from container
          origin = e.data('slide-in') || self.container.data('slide-in')

              if(origin) 
                  e.dataIn = origin.split(' '); // split on space

              // Elements in - property value
              e.slideIn.at     = parseInt(this.get('at', e.dataIn));
              e.slideIn.from   = this.get('from', e.dataIn);
              e.slideIn.use    = this.get('use', e.dataIn);
              e.slideIn.during = parseInt(this.get('during', e.dataIn));
              e.slideIn.plus   = parseInt(this.get('plus', e.dataIn));
              e.slideIn.force  = this.get('force', e.dataIn);

              if(e.slideIn.plus > 0)
                e.slideIn.at += e.slideIn.plus + prevTimeIn;

              prevTimeIn = e.slideIn.at;

          /////// SLIDE OUT DATA
          
          // get data from element if set, then from container
          origin = e.data('slide-out') || self.container.data('slide-out')

              if(origin) { // split on space
                  e.dataOut = origin.split(' ');

                  // Elements out - property value
                  e.slideOut.at     = parseInt(this.get('at', e.dataOut));
                  e.slideOut.to     = this.get('to', e.dataOut);
                  e.slideOut.use    = this.get('use', e.dataOut);
                  e.slideOut.during = parseInt(this.get('during',e.dataOut));
                  e.slideOut.plus   = parseInt(this.get('plus', e.dataOut));
                  e.slideOut.force  = this.get('force', e.dataOut);

                  if(e.slideOut.plus > 0)
                    e.slideOut.at += e.slideOut.plus + prevTimeOut;

                  prevTimeOut = e.slideOut.at;
              }

              // make sure all elements are absolute
              e.css({ 'position': 'absolute' })
        
              e = null;

          }, this))
    
          this.saveSize();
          

          //this.elements.filter('img').css('width','auto');

          //if(this.options.flexslider)
            //this.container.css('display', oldDisplay);

          if(this.options.slider)
              this.container.hide();

          if ( this.options.autostart )
              this.start();

          this.options.onInit.call(this.container);

          this.container.trigger('lushInit');

          // responsive handler
          $(window).resize($.proxy(this.resize, this));

      },

      /* *************************************
       * GET TIMELINE INFORMATION
       * *************************************/
      get: function(prop, data) {
          var pos = $.inArray(prop, data);

          // if not exists a property, use default
          if(pos < 0)
              return this.options.param[prop];
          // found attribute, return value
          // attributes without parameter are undefined when found
          return data[pos + 1] || !data[pos + 1];
      },

      /* *************************************
       * PREPARE TO RENDER IN ANIMATIONS
       * *************************************/
       renderIn: function(){
        var self = this,
            isInverted = this.carouselInvert(),
            elementCount = this.elements.length;

            this.container.addClass('running');
            //this.container.trigger('slideIn');

            this.elements.each($.proxy(function(i, element) {

                var el = element.$el, i;

                // Queue In Animations
                el.delay(parseInt( isInverted ? self.elements[--elementCount].$el.slideIn.at : el.slideIn.at ))

                this.from(el);

                //if(i === 0) {
                if((i == this.elements.length-1)) {
                  el.queue( function(){
                        if( self.options.onSlideIn &&
                            self.options.onSlideIn.call(self.container) === false) 
                              return;
                          
                          self.container.trigger('slideIn');
                          
                          $(this).dequeue();

                        })
                }
                // add an extra delay before start slide out
                if(this.options.deadtime > 0 && !el.slideOut.force)
                  el.delay(this.options.deadtime)

                if ($.support.css3feature)
                  el.show()

            }, this))
            return this;
      },

      /* *************************************
       * PREPARE TO RENDER OUT ANIMATIONS
       * *************************************/
       renderOut: function() {
        var self = this,
            isInverted = this.carouselInvert(),
            elementCount = this.elements.length;

            if(!this.container.hasClass('running'))
              this.container.addClass('running');

            this.container.trigger('slideOut');
      
            // controls if out animationa has been rendered
            this.outRendered = true;
            
            // flag when out animations has been started
            this.outStarted = false;
      
            // Queue Out Animations
            this.elements.each($.proxy(function(i, element){
                var el = element.$el, i;

                if(el.slideOut.at >= 0) {

                  //el.delay(parseInt(this.carouselInvert() ? el.slideIn.at : el.slideOut.at))
                  el.delay(parseInt( isInverted ? self.elements[--elementCount].$el.slideOut.at : el.slideOut.at ))

                  el.queue(function() {
                    if(self.paused && !el.slideOut.force && !self.outStarted)
                      i = setInterval($.proxy(function() {
                        if(!self.paused) {
                          clearInterval(i)
                          $(this).dequeue()
                      }
                    }, this), 50)
                    else {
                      $(this).dequeue()
                      if(!el.slideOut.force) self.outStarted = true;
                    }
                  
                  })
            
                  this.to(el);

                  if ((i == this.elements.length-1)) 
                      el.queue(function(){
                              self.options.onSlideOut.call(self.container);
                              $(this).dequeue()
                            });
                
                } // slideout.at

            }, this)) // proxy
            
            return this;
        },

        /* *************************************
         * START ANIMATING A NEW SLIDE
         * *************************************/
        start: function() {


            if(this.sliding) return;

            this.sliding = true;

            this.container.show();

            this.container.addClass('active');

            //this.elements.show()
            this.resize();

            this.container.trigger('slideStart')
            this.options.onSlide.call(this.container);

            // render in animations
            this.renderIn();

            // if not manual advance
            // render out animations
            if( ! this.options.manual ) {
  
              this.renderOut();

            }

            this.end();

            // save default direction
            if(this.options.carousel)
              this.container.parent().data('from-direction',this.options.direction);

        },

        /* *************************************
         * DETECTS WHEN A SLIDE ENDS
         * @halt: slider stopped but doesn't need to advance
         * *************************************/
        end: function(halt) {
            var that = this;

            $.when(this.elements).done($.proxy(function() {
        
              if (!this.outRendered) {
                this.container.removeClass('running')
                this.container.trigger('slideStop');
                return;
              }

              this.outRendered = false;

              this.endslide(halt);

            }, this ));
            
            return this;
        },

        /* *************************************
         * CLEAR SLIDING STATUS
         * @halt: slider stopped but doesn't need to advance
         * *************************************/
        endslide: function(halt) {
          

            // if callbacks returns false, stop sliding
            if (this.options.onSlided && 
              this.options.onSlided.call(this.container) === false) 
                return;

            this.sliding = false;
            
            this.container.removeClass('active running').trigger('slideEnd');

            // if in slider mode and not forced to stop and no halt
            if ( this.options.slider && !this.stopped && !halt )
                this.advance();

            this.stopped = false;
        },

        /* *************************************
         * ADVANCE TO GIVEN DIRECTION
         * *************************************/
        advance: function(direction) {
            var nextDirection = direction || this.options.direction,
                nextSlide;

            // not advance if slide in progress
            if ( ! this.sliding ) {
              
              nextSlide = (nextDirection == 'next') ? this.options.syncNext : this.options.syncPrev;
              $(nextSlide).lush('start'); 

            }
        },

        /* *************************************
         * SLIDE IFRAME & IMAGE PRELOAD
         * *************************************/
        preload: function(el, callback) {

            var preSrc = [],
                imgcnt = 0

            this.container.find('*').each(function(i, el){
                var $this = $(el), bg, src;
                // preload image tag
                if($this[0].tagName === 'IMG') {
                    src = {src : $this.attr('src'), tag : $this[0].tagName};
                    preSrc.push(src);
                } else if($this[0].tagName === 'IFRAME') {
                    src = {src : $this.attr('src'), tag : $this[0]};
                    preSrc.push(src);
                } else { // preload elements with url as bg image
                    bg = $this.css('background-image');
                    if( bg !== 'none' && bg.indexOf('url') >= 0 ) {
                      src = { src: bg.match(/url\((.*)\)/)[1].replace(/"/gi, ''), tag: 'IMG' };
                      preSrc.push(src);
                    }
                }
            })

            if ( ! preSrc.length) {
                callback();
            } else {
                $.each(preSrc, function(i, src) {

                    if(src.tag === 'IMG')
                      $('<'+src.tag+'>').load(function() {
                          if( ++imgcnt == preSrc.length )
                               callback();
                      }).attr('src', src.src);
                    else
                      $(src.tag).load(function() {
                          if( ++imgcnt == preSrc.length )
                              callback();
                      })//.attr('src', src.src);
                });
            }

        },

        /* *************************************
         * FORCE TO STOP ANIMATION
         * *************************************/
        stop: function() {
            this.stopped = true; // stops auto advance
            this.elements.each(function(i, element){
                while (element.$el.queue().length)
                    element.$el.stop(false, (!$.support.css3feature)); // jump to end when using fallbacks 
            });
            return this;
        },

        /* *************************************
         * SLIDE TO DIRECTION
         * *************************************/
        go: function(direction) {
          var that = this;

          if(this.options.carousel)
            this.container.parent().data('from-direction',direction);

          if(this.options.manual) {

            if(this.outRendered) return;
      
            this.renderOut().end()
            
          } 
          else {

            this.state('resume').stop().advance(direction);
            
          }

        },

        /* *************************************
         * PAUSED - RESUMED STATE
         * *************************************/
        state: function(state) {

          this.paused = (state === 'pause');
          if(this.paused)
            this.container.addClass('paused');
          else {
            this.container.removeClass('paused');
          }
          return this;
        },

        /* *************************************
         * RETURNS THE CONTAINER SIZE
         * *************************************/
        size: function() {
          return {
            width: this.container.width(),
            height: this.container.height()
          }
        },

        /* *************************************
         * HIDE ALL ELEMENTS IN A SLIDE
         * *************************************/
        hide: function(){
            this.elements.hide();
        },

        /* *************************************
         * SHOW ALL ELEMENTS IN A SLIDE
         * *************************************/
        show: function(){
            this.elements.show();
        },

        /* *************************************
         * SAVE ORIGINAL SIZES
         * *************************************/
        saveSize: function() {
          var properties = {};

          // save originaal container size
          this.containerSize = {
            width: this.options.baseWidth,
            height: this.options.baseHeight
          }

          this.elements.each(function(i, element){
            var $el =  $(element);
            // font
            properties.fs = parseInt($el.css('font-size') ,0) || 0;
            properties.lh = parseInt($el.css('line-height')) || 0;

            // padding
            properties.pt = parseInt($el.css('paddingTop')   ,0) || 0;
            properties.pb = parseInt($el.css('paddingBottom'),0) || 0;
            properties.pl = parseInt($el.css('paddingLeft')  ,0) || 0;
            properties.pr = parseInt($el.css('paddingRight') ,0) || 0;
            // margin
            properties.mt = parseInt($el.css('marginTop')   ,0) || 0;
            properties.mb = parseInt($el.css('marginBottom'),0) || 0;
            properties.ml = parseInt($el.css('marginLeft')  ,0) || 0;
            properties.mr = parseInt($el.css('marginRight') ,0) || 0;
            // border
            properties.btw = parseInt($el.css('borderTopWidth')   ,0) || 0;
            properties.bbw = parseInt($el.css('borderBottomWidth'),0) || 0;
            properties.blw = parseInt($el.css('borderLeftWidth')  ,0) || 0;
            properties.brw = parseInt($el.css('borderRightWidth') ,0) || 0;

            properties.bts = $el.css('borderTopStyle');
            properties.bbs = $el.css('borderBottomStyle');
            properties.bls = $el.css('borderLeftStyle');
            properties.brs = $el.css('borderRightStyle');

            properties.btc = $el.css('borderTopColor');
            properties.bbc = $el.css('borderBottomColor');
            properties.blc = $el.css('borderLeftColor');
            properties.brc = $el.css('borderRightColor');
            //size

            properties.hg = parseInt($el.height()) || properties.lh || 0;
            properties.wd = parseInt($el.width())  || 0;

            properties.b = $el.css('bottom');
            properties.l = $el.css('left');
            properties.r = $el.css('right');
      
            $el.data('properties', $.extend({}, properties));

          })
        },

    updatePos: function() {
          var self = this;
          this.elements.each(function(i, element){
            var $el =  $(element);

      if(!self.isUnit(element.style.left, '%'))
        $el.css('left', (parseFloat(element.style.left) * 100 / self.options.baseWidth) + '%');

      if(!self.isUnit(element.style.top, '%'))
        $el.css('top',(parseFloat(element.style.top) * 100 / self.options.baseHeight) + '%');

      })      
    },

    isUnit: function (value, unit) {
      return ((value.indexOf(unit) > 0) || (unit == 'px' || value == 'auto')); 
    },

        /* *************************************
         * SET NEW SIZE BY RESIZE RATIO
         * *************************************/
        resize: function() {
          var properties, ratio, $el;

          ratio = this.container.width() / this.containerSize.width;

          if ( !this.options.flexslider) {
            this.container.css({
                height: this.containerSize.height * ratio
                });
            this.container.parent().css({
                height: this.containerSize.height * ratio
                });
            
          }
    
          this.elements.each(function(i, element) {
            $el =  $(element); 
            properties = $el.data('properties');
      
      if(!properties) return false;
      
            $el.css({
               'font-size':     (Math.floor(properties.fs * ratio)) + "px",
               'line-height':   (Math.floor(properties.lh * ratio)) + "px",

               'padding-top':   (properties.pt * ratio) + "px",
               'padding-bottom':(properties.pb * ratio) + "px",
               'padding-left':  (properties.pl * ratio) + "px",
               'padding-right': (properties.pr * ratio) + "px",
               /*
               'margin-top':    (properties.mt * ratio) + "px",
               'margin-bottom': (properties.mb * ratio) + "px",
               'margin-left':   (properties.ml * ratio) + "px",
               'margin-right':  (properties.mr * ratio) + "px",
               */
               'border-top':    (properties.btw * ratio) + "px " + properties.bts + ' ' + properties.btc,
               'border-bottom': (properties.bbw * ratio) + "px " + properties.bbs + ' ' + properties.bbc,
               'border-left':   (properties.blw * ratio) + "px " + properties.bls + ' ' + properties.blc,
               'border-right':  (properties.brw * ratio) + "px " + properties.brs + ' ' + properties.brc,

               'height':        (properties.hg * ratio) + 'px',
               'width':         (properties.wd * ratio) + 'px',
               'white-space':   'nowrap'
             });
      
            if(element.tagName === 'IFRAME')
              $el.attr({
                width: (properties.wd * ratio),
                height: (properties.hg * ratio)
              })

          });
        },

        /* *************************************
         * RETURN IF DIRECTION WAS INVERTED
         * *************************************/
    
        carouselInvert: function() {
          var fromdir = this.container.parent().data('from-direction');
          return ( this.options.carousel && fromdir &&
               fromdir !== this.options.direction )     
        },
    
        /* *************************************
         * ANIMATE FROM DIRECTION
         * *************************************/
        from: function(el) {
            var objFrom, objTo, 
              justFade = false,
              self   = this,
              effect;
      
            effect = this.carouselInvert() ? 
            el.slideOut.to :
            el.slideIn.from;
      
            if ($.support.css3feature) {
              el.clearTransition()
                .clearState('out')
                .addClass('in')
                .data('activeClass', effect)
                .addClass(effect)
                .forceReflow()
                .prepareEffect(el.slideIn.during, self.cssEasing[el.slideIn.use])
                .queue( function() {
                  $(this)
                    .forceReflow()
                    .addClass('live')
                    .forceReflow()
                    .dequeue();
                })
                .delay(parseInt(el.slideIn.during)-100)
            }
            else {

              switch(effect) {
                  case 'left':
                  case 'l':
                    objFrom = {'margin-left' : - this.container.width(), 'margin-top' : 0};
                    objTo   = {'margin-left' : 0 }
                  break;
                  case 'right':
                  case 'r':
                    objFrom = {'margin-left' : this.container.width(), 'margin-top' : 0};
                    objTo   = {'margin-left' : 0 }
                  break;
                  case 'top':
                  case 't':
                    objFrom = {'margin-top' : - this.container.height(), 'margin-left' : 0};
                    objTo   = {'margin-top' : 0 }
                  break;
                  case 'bottom':
                  case 'b':
                    objFrom = {'margin-top' : this.container.height(), 'margin-left' : 0};
                    objTo   = {'margin-top' : 0 }
                  break;
                  case 'left-fade':
                  case 'lf':
                    objFrom = {'margin-left' : -fadeMargin, 'opacity' : 0, 'margin-top' : 0};
                    objTo   = {'margin-left' : 0, 'opacity' : 1 }
                  break;
                  case 'right-fade':
                  case 'rf':
                    objFrom = {'margin-left' : fadeMargin, 'opacity' : 0, 'margin-top' : 0};
                    objTo   = {'margin-left' : 0, 'opacity' : 1 }
                  break;
                  case 'top-fade':
                  case 'tf':
                    objFrom = {'margin-top' : -fadeMargin, 'opacity' : 0, 'margin-left': 0 };
                    objTo   = {'margin-top' : 0, 'opacity' : 1 }
                  break;
                  case 'bottom-fade':
                  case 'bf':
                    objFrom = {'margin-top' : fadeMargin, 'opacity' : 0, 'margin-left': 0};
                    objTo   = {'margin-top' : 0, 'opacity' : 1 }
                  break;
                  default: // fade by default
                    justFade = true;
                  break;
              }

              if ( justFade ) {
                el.css('margin', 0)
                  .hide()
                  .fadeTo(parseInt(el.slideIn.during), 1)
              }
              else {
                el.css(objFrom) // positionate and animate
                  .show()
                  .animate(objTo, {
                    duration: parseInt(el.slideIn.during),
                    easeing:  el.slideIn.use
                });
              }
            }
            return {from : objFrom, to : objTo}
        },

        /* *************************************
         * ANIMATE TO DIRECTION
         * *************************************/
        to: function(el) {
            var objTo, objFrom, 
              justFade = false,
              self   = this,
              effect;

            effect = this.carouselInvert() ? 
            el.slideIn.from :
            el.slideOut.to;

            if ($.support.css3feature) {

              el.queue( function() {
                  $(this)
                    .clearState('in')
                    .addClass('out')
                    .data('activeClass', effect)
                    .addClass(effect)
                    .prepareEffect(el.slideOut.during, self.cssEasing[el.slideOut.use])
                    .forceReflow()
                    .dequeue();
              })
              el.delay(50)
              el.queue( function() {
                  $(this)
                    .forceReflow()
                    .addClass('live')
                    .forceReflow()
                    .dequeue();
              });
              el.delay(parseInt(el.slideOut.during)-100)

            }
            else {

              switch(effect) {
                  case 'left':
                  case 'l':
                    objFrom  = {'margin-left' : 0};
                    objTo   = {'margin-left' : - this.container.width()};
                  break;
                  case 'right':
                  case 'r':
                    objFrom  = {'margin-left' : 0};
                    objTo   = {'margin-left' : this.container.width()};
                  break;
                  case 'top':
                  case 't':
                    objFrom  = {'margin-top' : 0};
                    objTo   = {'margin-top' : -this.container.height()};
                  break;
                  case 'bottom':
                  case 'b':
                    objFrom  = {'margin-top' : 0};
                    objTo   = {'margin-top' : this.container.height()};
                  break;
                  case 'left-fade':
                  case 'lf':
                    objTo = {'margin-left' : -fadeMargin, 'opacity' : 0};
                    objFrom = {'margin-left' : 0, 'opacity' : 1 }
                  break;
                  case 'right-fade':
                  case 'rf':
                    objTo = {'margin-left' : fadeMargin, 'opacity' : 0};
                    objFrom = {'margin-left' : 0, 'opacity' : 1 }
                  break;
                  case 'top-fade':
                  case 'tf':
                    objTo = {'margin-top' : -fadeMargin, 'opacity' : 0};
                    objFrom = {'margin-top' : 0, 'opacity' : 1 }
                  break;
                  case 'bottom-fade':
                  case 'bf':
                    objTo = {'margin-top' : fadeMargin, 'opacity' : 0};
                    objFrom = {'margin-top' : 0, 'opacity' : 1 }
                  break;
                  default:
                    justFade = true;
                  break;
              }
              if ( justFade ) {
                el.fadeOut(parseInt(el.slideOut.during));
              }
              else {
                el
                  .animate(objTo, {
                    duration: parseInt(el.slideOut.during),
                    easing:   el.slideOut.use
                });
              }
            }
            return { to: objTo }
        }
    };


/*===================================================
  LUSH SLIDER MODE
  ===================================================*/


    function Slider( element, option ) {

      this.container = $(element);
      this.items     = this.container.children('li, .lush');

      this.itemCount = this.items.length;
      this.loopCount = 0;

      this.options   = option.slider;
      this.lush    = option;

      this.sliding = false;
    
    // allows to set manual in slider properties
    this.options.manual = !!this.lush.manual
    
    // if manual slider, remove pauseOnHover
    if ( this.options.manual )
      this.options.pauseOnHover = false;
    
      this.preload($.proxy(this.init, this));

    }

    Slider.prototype = {

        init: function() {
          var slideNext, slidePrev,
              initCounter = 0,
              that = this;
      
      // before start preload all slides
      if(this.options.fullPreload) {
            this.items.one('lushInit', function() {
              if( ++ initCounter == that.itemCount) {
                that.items.eq(0).lush('start');
                that.addstuff();
                that.activePage(1);
              }
                 
            });
      }
      else { // start when first slide load
        this.items.eq(0).one('lushInit', function() {
                $(this).lush('start');
                that.addstuff();
                that.activePage(1);
            });
      }

      this.items.each($.proxy(function(i, el) {
      
            $(el).data('slide-index', i + 1);
      
            slideNext = i + 1;
            slidePrev = i - 1;

            if ( i == (this.itemCount - 1) )
                slideNext = 0;
            if ( i == 0 )
                slidePrev = (this.itemCount - 1);

            $(el)
              .width( this.container.width() )
              .lush( $.extend(this.lush, {
                  autostart:  false
                , slider:     true
                , flexslider: false
                , carousel:  (!!this.options.carousel)
                , syncNext:  this.items[slideNext]
                , syncPrev:  this.items[slidePrev]
                , onSlide:   function() {

                    that.activePage(this.data('slide-index'));

                    if(that.loopEnds.call(that)) {
                      this.lush('pause');
                      that.container.off('.lushhover');
                      this.trigger('loopEnd');
                    }
                  }
              }))
          }, this));
    },

    addstuff: function() {

        /* Create navigation items */
        if(this.options.navigation)
          this.addnav();

        /* Create shadow container */
        if(this.options.shadow)
          $('<div/>', {
            'class': classShadow
          }).appendTo(this.container);

        this.addevents();

        this.updateNav();
    },    

    preload: function(callback) {
      
            var src = false,
              bg = this.container.css('background-image'); 

            if( bg !== 'none' && bg.indexOf('url') >= 0 )
              src = bg.match(/url\((.*)\)/)[1].replace(/"/gi, '');

            if ( ! src ) {
        callback();
            } else {
        $('<img>')
          .load(callback)
          .attr('src', src);
            }
      
    },
    
    
        addnav: function() {
          var that = this;

          this.nav = $('<div/>').appendTo(this.container).addClass(classNav);

      $('<a href="#" class="'+classPrev+'">&lt;</a>').appendTo(this.nav)

      if(this.options.pager) {
            for (i=0; i < this.itemCount; i++){
        $('<a href="#" class="'+classPage+'" rel="'+(i+1)+'">'+(i+1)+'</a>').appendTo(this.nav)           
            }
      }
      
          $('<a href="#" class="'+classNext+'">&gt;</a>').appendTo(this.nav);

        },

    updateNav: function() {
      var lh = this.nav && parseInt(this.nav.css('line-height'));

      this.nav && this.nav.css({
          left: this.container.width() /2 - this.nav.width()/2,
          lineHeight: lh === 0 ? 0 : this.container.height() /2 + 'px'
        })      
    },

    activePage: function(index) {
        if ( this.nav ) {
          this.nav.children('.current').removeClass('current')
          this.nav.children('a[rel=' + index + ']').addClass('current')
        }
    },
        addevents: function() {
          var that = this,
              advanceNext  = function(){ that.items.filter('.active').lush('next'); },
              advancePrev  = function(){ that.items.filter('.active').lush('prev'); },
              pauseSlider  = function(){ that.items.lush('pause'); },
              resumeSlider = function(){ that.items.lush('resume'); };

          if(this.options.responsive)
            $(window).resize(function() {
              that.items.each(function(i, el) {
                $(el).width(that.container.width())
              });
              setTimeout($.proxy(that.updateNav,that),100);
            })

          if(this.options.navigation)
            this.nav.on('click.lush', function(event) {
              event.preventDefault();
              var $target = $(event.target);

              if(that.sliding) return;
              if( $target.is('.'+classPrev) ) advancePrev();
              if( $target.is('.'+classNext) ) advanceNext();
              if( $target.is('.'+classPage) ) that.slideto.call(that, parseInt($target.attr('rel')) )
              return false;
            })

          // extra navigation items
          $('.'+classPaging).on('click.lush',function(e){
            var target = parseInt($(this).data('slideto'))
            that.slideto(target);
          })

          if(this.options.pauseOnHover)
            this.container
              .on('mouseenter.lushhover.in',  pauseSlider)
              .on('mouseleave.lushhover.out', resumeSlider);

          if(this.options.keyboard)
            $(document).on('keyup.lush',   function(event) {
              var keycode = event.keyCode;
              if(that.sliding) return;
              if(keycode == 37) advancePrev();
              if(keycode == 39) advanceNext();
            });
        },

        loopEnds: function() {
          return ( this.options.loop && (this.options.loop * this.itemCount) <= (this.loopCount++) );
        },

        slideto: function(target) {

          var that = this,
              current = this.items.filter('.active'),
              isRunning = current.hasClass('running'),
              action  = this.lush.manual ? 'slideout' : 'stop', // isRunning ? 'stop' : 'slideout' ,
              nextSlide;

          // dont allow to advance until animation stops
          if(this.lush.manual && isRunning) return;

          if( !this.sliding && (target > 0 && target <= this.itemCount)) {

            this.sliding = true;

            nextSlide = this.items.eq(target - 1);

            if(nextSlide.hasClass('active')) {
              this.sliding = false;
              return;
            }

            /* For manual advance */
            nextSlide.one('slideStop', function() {
                  that.sliding = false;
            })

            /* When current ends, start next slide */
            current.one('slideEnd',
              function() {

                    nextSlide.one('slideStart',
                      function() {
                                
                                that.sliding = false;
                              
                    }).lush('start');
                    
                }).lush(action)
            }
        }
    } /* end Slider proptotype */


/*===================================================
  LUSH FLEXSLIDER MODE
  ===================================================*/
 
 
    function goFlexslider( options ) {

      var sel       = $(this),
          fsOptions   = $.extend({}, $.flexslider.defaults, options.flexslider),
          items     = sel.find(fsOptions.selector),
          count     = items.length,
          fsNamespace = fsOptions.namespace ? fsOptions.namespace : 'flex-',
          fsActive    = fsNamespace + 'active-slide',
          fsNext      = fsNamespace + 'next',
          fsPrev      = fsNamespace + 'prev',
          paused    = false,
          pauseSlider  = function(){ items.lush('pause'); },
          resumeSlider = function(){ items.lush('resume'); },
          restOfSlide;
    
    
    
      // hide all items so FS can calcualte correct size
      items.each(function(i, el){
        $(el).children().not('.ignore').hide()
      })
  
    sel.flexslider($.extend(fsOptions, {
          start : function(slider) {
                slider.slides.eq(0).one('lushInit', function(){
                  $(this).lush('start');
                });

                slider.slides.lush($.extend(options, {
                      autostart: false,
                      slider: false,
                      onSlided: goNextSlide
                    }));
            },
            after : function(slider) {
                //restOfSlide.lush('hide')
                slider.slides.filter('.'+fsActive).lush('start');
            },
            before : function(slider) {
                restOfSlide = slider.slides.not(':eq('+slider.animatingTo+')').lush('stop');
            }
        }));

    // make flexslider advance to next slide
    function goNextSlide() {

        // no autoslide on manual advance
        if ( ! sel.data('flexslider').animating && !paused)
            sel.flexslider("next");
        
        return true;
      }
      
      // 1.2: Added pauseonhover for flexslider mode
    if(options.flexslider.pauseOnHover){
      sel.hover( function() {
        paused = true;
        pauseSlider();
      },function() {
        paused = false;
        resumeSlider();
        if(!sel.find('.running').length)
          goNextSlide();
      })
    }
      }



/*===================================================
  PLUGIN INITIALIZATION
  ===================================================*/

    $.fn[pluginName] = function ( option ) {

        if ((typeof option).match("object|undefined")) {

            return this.each(function () {
        
        var $this = $(this),
          settings = $.extend(true, {}, $.fn[pluginName].defaults, $this.data(), typeof option == 'object' && option)
        
                if ( $this.hasClass(sliderClass) ) {

                  if( ! $.data(this, sliderData) )
                      $.data(this, sliderData, new Slider(this, settings));

                }
                else if ( $this.hasClass(flexsliderClass) ) {
                  
                  if(!$.data(this, flexsliderData) && $.flexslider) {
            $.data(this, flexsliderData, 1);
            goFlexslider.call(this, settings);
                  }
                }
                else {

                  if ( ! $.data(this, pluginName) ) {
                      new Lush( this, settings )
                  }
                }
            });
        }
        else {
            return this.each(function (t) {
                
                if (typeof option == "string") {
                    
                    var obj = $.data(this, pluginName);
                    
                    if ( ! obj ) return;

                    switch (option) {
                        case 'start' :
                        case 'stop'  :
                        case 'hide'  :
                        case 'show'  :
                        case 'resize':   obj[option]();         break;
                        case 'prev'  :
                        case 'next'  :   obj.go(option);        break;
                        case 'pause' :
                        case 'resume':   obj.state(option);     break;
                        case 'slidein' : obj.renderIn().end();      break;
                        case 'slideout': obj.renderOut().end(true); break;
                    }
                    
                }

                if (typeof option == "number") {
                  var slider = $.data(this, sliderData);
                  slider.slideto(option)
                }

            });
        }
    };

  
  
/*===================================================
  PLUGIN DEFAULTS
  ===================================================*/  

  $.fn[pluginName].defaults = {
          
          // ANIMATION PARAMS
          param : {
                at:     0
              , from:   'left'
              , to:     'right'
              , use:    'swing'
              , during: 1000
              , plus:   0
              , force:  false
          }
          
          // PLUGIN OPTIONS
          , autostart:  true
          , baseWidth:  1140
          , baseHeight: 450    // slider aspect ratio ~2.5
          , direction:  'next' // i.e. right
          , manual:     false
          , slider:     false
          , flexslider: false
          , syncNext:   ''
          , syncPrev:   ''
          , delayed:    600      // ms to delay before advance (internal)
          
          // SLIDER OPTIONS
          , slider : {
              pauseOnHover: false
            , navigation:   true
            , shadow:       true
            , keyboard:     true
            , pager:        true
            , responsive:   true
            , loop:         0
            , fullPreload:  false
            , carousel:     false
            , deadtime:     0
          }            
          
          // CALLBACKS
          , onInit:      function() {}
          , onSlide:     function() {}
          , onSlideIn:   function() {}
          , onSlideOut:  function() {}
          , onSlided:    function() {}
      };
  


})( jQuery, window, document );
