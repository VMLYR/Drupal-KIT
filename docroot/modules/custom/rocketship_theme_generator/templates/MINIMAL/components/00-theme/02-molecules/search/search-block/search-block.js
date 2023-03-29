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

(function ($, Drupal, window, document) {

  "use strict";

  // set namespace for frontend UI javascript
  if (typeof window.rocketshipUI == 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;

  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////


  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUISearchBlock = {
    attach: function (context, settings) {

      var searchBlock = $('.block--region-header-top.block--search-redirect-block'),
          trigger = searchBlock.find('h2');

      // Use the Mobile menu function drom the rocketshipUI namespace
      if (trigger.length && searchBlock.length) self.search(trigger, searchBlock);
    }
  };

  /**
   * Mobile menu functionality
   *
   */
  self.search = function(trigger, wrapper) {

    // open/close wrapper
    trigger.once('js-once-search').click(function(e) {
      // check for screen size bigger than phone
      // if(self.screen == 'xs'/* || self.screen == 'sm'*/) {
        // add classes (css handles the animation & open/close)
        if (wrapper.hasClass('js-open')) {
          wrapper.removeClass('js-open');
        } else {
          wrapper.addClass('js-open');
        }
      // }

      e.preventDefault();
    });

    // // remove the close class when switching to bigger screen
    // // we don't need it there
    // $(window).on('resize', function() {
    //   if(self.screen != 'xs'/* && self.screen != 'sm'*/) {
    //     if (wrapper.hasClass('js-open')) {
    //       wrapper.removeClass('js-open');
    //     }
    //   }
    // });
  };

})(jQuery, Drupal, window, document);
