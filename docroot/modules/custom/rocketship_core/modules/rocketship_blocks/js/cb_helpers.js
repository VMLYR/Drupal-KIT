/**
 * Rocketship UI JS
 *
 * contains: triggers for functions
 * Functions themselves are split off and grouped below each behavior
 *
 * Drupal behaviors:
 *
 * Means the JS is loaded when page is first loaded
 * + during AJAX requests (for newly added content)
 * use jQuery's "once" to avoid processing the same element multiple times
 * http: *api.jquery.com/one/
 * use the "context" param to limit scope, by default this will return document
 * use the "settings" param to get stuff set via the theme hooks and such.
 *
 *
 * Avoid multiple triggers by using jQuery Once
 *
 * EXAMPLE 1:
 *
 * $('.some-link', context).once('js-once-my-behavior').click(function () {
 *   // Code here will only be applied once
 * });
 *
 * EXAMPLE 2:
 *
 * $('.some-element', context).once('js-once-my-behavior').each(function () {
 *   // The following click-binding will only be applied once
 * * });
 */

 (function ($, Drupal, window, document) {

  "use strict";

  // set namespace for frontend UI javascript
  if (typeof window.rocketshipUI == 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;

  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////

  //

  ///////////////////////////////////////////////////////////////////////
  // triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUI_cbHelpers = {
    attach: function (context, settings) {

      // Find out our current breakpoint
      // saves it in a variable 'screen'

      if (typeof self.screen === 'undefined') {

        // set global var if does not exist
        self.screen = '';

        self.checkScreenSize();

        window.rocketshipUI.optimizedResize().add(function() {
          self.checkScreenSize();
        });
      }

    }
  };


  ///////////////////////////////////////////////////////////////////////
  // functions
  ///////////////////////////////////////////////////////////////////////

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
            iCount = 0;
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
              iCount = 0;
            }

          }
        });

      });

    } else {
      // no images, so we can proceed
      return callback();
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
   * call with window.rocketshipUI.helpers().getBreakpoint().
   */
  self.getBreakpoint = function () {

    var tag = window.getComputedStyle(document.body, '::after').getPropertyValue('content');
    // Firefox bugfix
    tag = tag.replace(/"/g,'');


    return tag.replace(/'/g,'');
  };

})(jQuery, Drupal, window, document);
