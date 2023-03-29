// Global javascript (loaded on all pages in Pattern Lab and Drupal)
// Should be used sparingly because javascript files can be used in components
// See https://github.com/fourkitchens/dropsolid_fix_base_8/wiki/Drupal-Components#javascript-in-drupal for more details on using component javascript in Drupal.

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth

/**
 * Cooldrops UI JS
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

(function($, Drupal, drupalSettings, window,document){

  "use strict";

  // set namespace for UI javascript
  if (typeof window.rocketshipUI == 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;


  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////

  // track if scrollHandling has already been fired to prevent multiples
  self.scrollHandlingVar = false;
  // track if there's a fixed header
  self.hasFixedHeader = false;


  ///////////////////////////////////////////////////////////////////////
  // Behavior: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUIStickyHeader = {
    attach: function (context, settings) {

      // Cache variables
      var stickyHeader = $('.wrapper--page-top'),
        headerTop = $('.header-top');

      // sticky header js is only loaded if theme setting is checked
      // Appearance Settings in /admin/appearance/settings/my_theme_name

      if( stickyHeader.length ) self.fixedTop(stickyHeader, headerTop);

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Functions
  ///////////////////////////////////////////////////////////////////////


  /**
   * fixedTop (with custom JS)
   *
   * Set a fixed element while scrolling e.g. a fixed header
   */
  self.fixedTop = function(pin, offsetElement) {

    // variable for other functions to user
    self.hasFixedHeader = true;

    // keep track of scrolling (needed for fixed stuff);
    self.scrollHandling(pin, offsetElement);

    if (typeof offsetElement == 'undefined') {
      offsetElement = false;
    }

    // on window resize, check again for the right position
    // and recalculate the height
    self.optimizedResize().add(function() {
      self.handleStickyHeader(pin, offsetElement);

      self.resizeSpacer(pin);

      var handler = setTimeout(function() {
        clearTimeout(handler);
        self.resizeSpacer(pin);
      }, 300);
    });

    // same on scroll

    $(window).scroll(function() {
      self.handleStickyHeader(pin, offsetElement);

      self.resizeSpacer(pin);

      var handler = setTimeout(function() {
        clearTimeout(handler);
        self.resizeSpacer(pin);
      }, 300);
    });
  };

  self.resizeSpacer = function(pin) {
    var spacer = $('.sticky-spacer'),
      pinHeight = pin.outerHeight();
    spacer.height(pinHeight);
  };

  self.handleStickyHeader = function(pin, offsetElement) {

    // if scrolling below the height of the top navigation,
    // make pin fixed

    var //spacer = $('.sticky-spacer'),
      // pinHeight = pin.outerHeight(),
      adminToolbar = $('#toolbar-bar'),
      adminToolbarTray = $('#toolbar-item-administration-tray'),
      adminToolbarHeight = 0,
      adminToolbarTrayHeight = 0,
      offsetHeader,
      offset;

    // check for elements before the fixed header,
    // to push fixed header down

    if (adminToolbar.length ) {
      adminToolbarHeight = adminToolbar.outerHeight();
    }

    if (adminToolbarTray.length && adminToolbarTray.hasClass('toolbar-tray-horizontal') ) {
      adminToolbarTrayHeight = adminToolbarTray.outerHeight();
    }

    // offset by space from admin toolbar
    // and also the tray, but only if it's a horizontal one

    offsetHeader = adminToolbarHeight + adminToolbarTrayHeight;

    // save open and collapsed header heights

    if (offsetElement === false) offset = 0;
    else offset = offsetElement.outerHeight();

    // move header down with space from admin and stuff
    pin.css('top', offsetHeader + 'px');

    pin.addClass('is-fixed');

    // spacer.height(pinHeight);

    var topHeight = 0;

    if (typeof offsetElement !== 'undefined') topHeight = offsetElement.outerHeight();

    // check if we're at top of the page or not
    if (self.getScrollTop() > offset + topHeight) {
      pin.addClass('not-top');
    } else {
      pin.removeClass('not-top');
    }
  };

  /**
   * scrollHandling
   *
   * Adds various classes to track scrolling
   */
  self.scrollHandling = function(pin, offsetElement) {

    // make sure this function hasn't already been fired
    if ( self.scrollHandlingVar === false ) {

      self.scrollHandlingVar = true;

      // at page load, only set scrolling stuff if scrolled partly down the page
      if (self.getScrollTop() > 28) {
        // force window resize
        window.dispatchEvent(new Event('resize'));
        // set fixed stuff, after giving admin toolbar time to recalc its height
        var timoutResize = setTimeout(function(){
          clearTimeout(timoutResize);
          self.handleStickyHeader(pin, offsetElement);
          self.body.addClass('js-scrolling').addClass('js-scrolling-up');
        }, 50);
      }

      $(window).scroll(function() {
        self.scrollDirection(pin, offsetElement);
      });
    }
  };

  self.scrollDirection = function(pin, offsetElement) {

    var st = self.getScrollTop();

    // we are scrolling down from our previous position
    if (st > self.lastScrollTop){

      // we are scrolling past the page top
      if (st > 0) {
        self.body.addClass('js-scrolling').removeClass('js-scrolling-up').addClass('js-scrolling-down');
        // pull up header top so it is hidden
        pin.css('margin-top', -offsetElement.outerHeight() + 'px');
      }

      // we are scrolling up
    } else if (st < self.lastScrollTop) {

      // we are scrolling past the page top
      if (st > 0) {
        self.body.addClass('js-scrolling').removeClass('js-scrolling-down').addClass('js-scrolling-up');
        // reset header top so it is visible
        pin.css('margin-top', 0);

        // we're at the top, reset the body classes
      } else {
        self.body.removeClass('js-scrolling').removeClass('js-scrolling-up').removeClass('js-scrolling-down');
      }
    }

    self.lastScrollTop = st;
  };

})(jQuery, Drupal, drupalSettings, window, document);
