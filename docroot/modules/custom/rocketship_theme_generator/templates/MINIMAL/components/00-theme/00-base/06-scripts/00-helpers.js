/**
 * Cooldrops UI JS
 *
 * Helper functions:
 *
 * - checkScreenSize
 * - getBreakpoint
 * - optimizedResize
 * - scrollTo
 * - getScrollTop
 * - imgLoaded
 * - round
 *
 **/

(function($, Drupal, window,document){

  "use strict";

  // set namespace for UI javascript
  if (typeof window.rocketshipUI == 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;


  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////

  self.html = $('html');
  self.body = $('body');
  self.page = $('html, body');
  self.touch = false;
  self.screen = '';
  self.scrollStop = false;

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Base: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUIHelpers = {
    attach: function (context, settings) {

      // Find out our current breakpoint
      // saves it in a variable 'screen'

      self.checkScreenSize();

      window.rocketshipUI.optimizedResize().add(function() {
        self.checkScreenSize();
      });

      // Test for flexboxtweener browsers, such as IE10
      if (typeof Modernizr != 'undefined') {
        Modernizr.addTest('flexboxtweener', Modernizr.testAllProps('flexAlign', 'end', true));
      }

      // add passiveSupported check for use with adding events
      self.checkPassiveSupported();

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Helper functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * add passiveSupported check for use with adding events
   */
  self.checkPassiveSupported = function() {
    self.passiveSupported = false;

    try {
      var options = {
        get passive() { // This function will be called when the browser
          //     attempts to access the passive property.
          self.passiveSupported = true;
        }
      };

      window.addEventListener("test", options, options);
      window.removeEventListener("test", options, options);
    } catch(err) {
      self.passiveSupported = false;
    }
  };

  /**
   *
   * Find out if we're on a small device (phone)
   *
   **/
  self.checkScreenSize = function () {

    var currentBreakpoint = self.getBreakpoint();

    if (currentBreakpoint == 'bp-xs') {
      self.screen = 'xs';
    }

    if (currentBreakpoint == 'bp-sm') {
      self.screen = 'sm';
    }

    if (currentBreakpoint == 'bp-md') {
      self.screen = 'md';
    }

    if (currentBreakpoint == 'bp-lg') {
      self.screen = 'lg';
    }
  };

  /*
   * Get the current breakpoint
   * Refers to the content of the body::after pseudo element (set in set-breakpoints.scss)
   * call with window.rocketshipUI.getBreakpoint().
   */
  self.getBreakpoint = function () {
    var tag = window.getComputedStyle(document.body, '::after').getPropertyValue('content');
    // Firefox bugfix
    tag = tag.replace(/"/g,'');

    return tag.replace(/'/g,'');
  };

  /**
   * Debounce function so event handlers don't get called too many times
   * when fired in quick succession
   *
   * https://davidwalsh.name/javascript-debounce-function
   *
   * @param func
   * @param wait
   * @param immediate
   * @returns {Function}
   *
   */
  // Example usage:
  //
  // var mouseupHandler = self.debounce(function(e) {
  //   // do stuff
  // }, 250);
  // document.body.addEventListener('mouseup', mouseupHandler, self.passiveSupported ? {  capture: false, once: false, passive: true } : false);

  self.debounce = function(func, wait, immediate) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  };


  /**
   * Since resize events can fire at a high rate,
   * the event handler shouldn't execute computationally expensive operations
   * such as DOM modifications.
   * Instead, it is recommended to throttle the event using requestAnimationFrame,
   * setTimeout or customEvent
   *
   * Src: https://developer.mozilla.org/en-US/docs/Web/Events/resize
   *
   * Example:
   *
   * window.rocketshipUI.optimizedResize().add(function() {
   *   do something
   * });
   */
  self.optimizedResize = function() {

    var callbacks = [],
      running = false;

    // Fired on resize event
    function resize() {
      if (!running) {
        running = true;
        if (window.requestAnimationFrame) {
          window.requestAnimationFrame(runCallbacks);
        }
        else {
          setTimeout(runCallbacks, 250);
        }
      }
    }

    // Run the actual callbacks
    function runCallbacks() {
      callbacks.forEach(function(callback) {
        callback();
      });
      running = false;
    }

    // Adds callback to loop
    function addCallback(callback) {
      if (callback) {
        callbacks.push(callback);
      }
    }
    return {
      // Public method to add additional callback
      add: function(callback) {
        if (!callbacks.length) {
          window.addEventListener('resize', resize);
        }
        addCallback(callback);
      }
    };
  };

  /**
   * Function to scroll smoothly to an anchor in the page
   *
   *
   * @el = required!, jquery object, element to scroll to
   * @offset = not required, offset the landing position or set to 'bottom' to scroll to bottom of the element
   * speed = not required, speed with wich to scroll
   * @callback = callback function that can be invoked after scrollto is done
   */
  /**
   * Function to scroll smoothly to an anchor in the page
   *
   * parameters:
   * el = required!, jquery object, element to scroll to
   * offset = not required, offset the landing position or set to 'bottom' to scroll to bottom of the element
   * speed = not required, speed with wich to scroll
   * callback = callback function that can be invoked after scrollto is done
   */
  self.scrollTo = function(params) {

    params.pos = params.el.offset().top;

    if (typeof params.offset === 'undefined') params.offset = 0;
    if (params.offset === 'bottom') params.pos = params.el.offset().top + params.el.outerHeight();
    if (typeof params.speed === 'undefined') params.speed = 1000;
    if (typeof params.callback === 'undefined') params.callback = function() {};

    // when user does any of these events, cancel all running animated scrolls
    self.page.on('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove', function(e){
      self.scrollStop();
    });

    self.page
      .stop()
      .animate({
          scrollTop: params.pos + params.offset
        },
        params.speed,
        function() {
          params.callback();
          self.page.off('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove');
        }
      );

  };

  /**
   * Cancels a running scrollTo call
   *
   * https://stackoverflow.com/questions/18445590/jquery-animate-stop-scrolling-when-user-scrolls-manually#18445654
   *
   */
  self.scrollStop = function() {
    self.page
    // remove queued animation + don't complete current animation => abrupt end of the scroll
      .stop(true, false);
  };

  /*
   * Get the top scroll position
   */
  self.getScrollTop = function() {

    // http://stackoverflow.com/questions/2506958/how-to-find-in-javascript-the-current-scroll-offset-in-mobile-safari-iphon
    var scrollTop;

    // top pos for touch devices
    if (Modernizr.touch) {
      scrollTop = window.pageYOffset;
      // for desktop
    } else {
      scrollTop = $(window).scrollTop();
    }

    return scrollTop;
  };


  /**
   * Detect if all the images withing your object are loaded
   *
   * No longer needs imagesLoaded plugin to work
   */
  self.imgLoaded = function (el, callback)
  {
    var img = el.find('img'),
      iLength = img.length,
      iCount = 0;

    if (iLength) {

      img.each(function() {

        var img = $(this);

        // fires after images are loaded (if not cached)
        img.on('load', function(){

          iCount = iCount + 1;

          if (iCount == iLength) {
            // all images loaded so proceed
            callback();
          }

        }).each(function() {
          // in case images are cached
          // re-enter the load function in order to get to the callback
          if (this.complete) {

            var url = img.attr('src');

            $(this).load(url);

            iCount = iCount + 1;

            if (iCount == iLength) {
              // all images loaded so proceed
              callback();
            }

          }
        });

      });

    } else {
      // no images, so we can proceed
      return callback();
    }
  };

  /*
   * Round numbers to x decimals
   * http://www.jacklmoore.com/notes/rounding-in-javascript/
   */
  self.round = function (value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
  };

})(jQuery, Drupal, window, document);
