// This javascript is a double of the layouts.js that is in Rocketship Core
//  only to use with styleguide components

(function ($, Drupal, window, document) {

  "use strict";

  // set namespace for frontend UI javascript
  if (typeof window.rocketshipUI === 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;

  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////


  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUI_layouts = {
    attach: function (context, settings) {

      var layouts = $('.layout--content-blocks', context);

      // ** No need for this if user can change spacing themselves
      //
      // add class for layouts that border each other and have same bg
      // if (layouts.length) {
      //   self.layoutResets(layouts);
      // }

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Make sure that layouts with the same bg-color
   * don't have too much space, by adding a class we can use for styling overrides/exceptions
   *
   */
  self.layoutResets = function(layouts) {

    layouts.once('js-once-layouts-resets').each(function() {

      var layout = $(this);

      layout.once('js-once-layout-reset').each(function() {

        var layoutItem = $(this),
          p = layoutItem,
          layoutItemNext = layoutItem.next();

        var layoutsHandler = function() {

          self.checkScreenSize();

          // when 2 layouts following each other have the same BG color,
          // add classes to reset paddings to avoid 'double padding' perception
          // under specific circumstances
          //
          if (layoutItemNext.length && typeof layoutItemNext[0] !== 'undefined' && typeof layoutItemNext[0].classList !== 'undefined' && layoutItemNext[0].classList.length) {


            var layoutMarginTop = parseInt(layoutItem.css('marginTop').replace('px', ''), 10);

            // ONLY IF:
            // - both have same BG color (or no bg color)
            // - && neither has a BG image
            // - && both layouts do or do not have bg images
            // - && first layout has no stretched image/video (this behaves a bit like BG image)
            // - && first layout has no bottom margin

            // check for a block with stretched images, in first column only
            var hasStretched = false;
            var stretchedBlocks = layoutItem.find('.cb--layout-mode--stretched ');

            if (stretchedBlocks.length) {

              // only need to know about stretched backgrounds
              // if on a breakpoint that has multiple columns
              // because only then do we need to keep the double spacing

              if (self.screen !== 'xs') {
                hasStretched = true;
              }

            }

            if (!hasStretched && (!layoutItem.hasClass('layout--bg-image') && !layoutItemNext.hasClass('layout--bg-image')) && (layoutItem.hasClass('layout--bg-color') && layoutItemNext.hasClass('layout--bg-color') || !layoutItem.hasClass('layout--bg-color') && !layoutItemNext.hasClass('layout--bg-color')) && (layoutMarginTop < 1)) {

              // get classes of both
              var pClassList = p[0].className.split(/\s+/);
              var layoutItemNextClassList = layoutItemNext[0].className.split(/\s+/);

              // no bg class, so both transparent
              if (!layoutItem.hasClass('layout--bg-color') && !layoutItemNext.hasClass('layout--bg-color')) {

                layoutItem.addClass('has-matching-bg').addClass('has-matching-bg-first');
                layoutItemNext.addClass('has-matching-bg').addClass('has-matching-bg-last');

                // if both have a bg color, check that it's the same bg color
              } else {

                // look for any item that contains color class (layout--bg--) and is same in both layouts
                for (var i = 0; i < pClassList.length; ++i) {

                  for (var j = 0; j < layoutItemNextClassList.length; ++j) {

                    if (pClassList[i] === layoutItemNextClassList[j]) {

                      if (pClassList[i].indexOf('layout--bg--') !== -1) {
                        layoutItem.addClass('has-matching-bg').addClass('has-matching-bg-first');
                        layoutItemNext.addClass('has-matching-bg').addClass('has-matching-bg-last');
                      }
                    }

                  }

                }
              }
            }
          }
        };

        layoutsHandler();

        rocketshipUI.optimizedResize().add(function() {

          layoutsHandler();

        });

      });

    });

  };

  // ========================================================================================
  // Everything below, is also part of Rocketship themes, but since we can't be sure
  // if the content blocks are used with the Rocketship themes, we need to add them here too
  // ========================================================================================

  /**
   *
   * Find out if we're on a small device (phone)
   *
   **/
  self.checkScreenSize = function () {

    var currentBreakpoint = self.getBreakpoint();

    self.screen = null;

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
   * call with window.dropsolidUI.getBreakpoint().
   */
  self.getBreakpoint = function () {
    var tag = window.getComputedStyle(document.body, '::after').getPropertyValue('content');
    // Firefox bugfix
    tag = tag.replace(/"/g,'');

    return tag.replace(/'/g,'');
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
   *   // Resource conscious resize callback
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

})(jQuery, Drupal, window, document);
