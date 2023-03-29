// Global javascript (loaded on all pages in Pattern Lab and Drupal)
// Should be used sparingly because javascript files can be used in components
// See https://github.com/fourkitchens/dropsolid_fix_base_8/wiki/Drupal-Components#javascript-in-drupal for more details on using component javascript in Drupal.

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth

/**
 * Dropsolid UI JS
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

  Drupal.behaviors.rocketshipUILanguageMenu = {
    attach: function (context, settings) {

      var langNav = $('.nav--language-interface');

      if (langNav.length) {

        // Check the theme settings if it is set to 'dropdown'
        // located at: /admin/appearance/settings/theme_machine_name

        if (typeof drupalSettings !== 'undefined' && typeof drupalSettings.theme_settings !== 'undefined' && typeof drupalSettings.theme_settings.language_dropdown !== 'undefined') {

          if (drupalSettings.theme_settings.language_dropdown === true ||  drupalSettings.theme_settings.language_dropdown === 1) {

            langNav.each(function() {
              self.languageDropdown($(this));
            });

          }
        }
      }

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Dropdown menu
   *
   */
  self.languageDropdown = function(nav) {
    var activeLangeHolder = nav.find('.nav__active--language');

    // touch event to open/close
    // includes touch detection

    activeLangeHolder.on('touchstart', function(e) {
      self.touch = true;

      if (nav.hasClass('js-open')) {
        self.navLangClose(nav);
      } else {
        self.navLangOpen(nav);
      }

      e.preventDefault();
    });

    // reset the touch variable afterwards

    activeLangeHolder.on('touchend', function(e) {

      // end
      setTimeout(function() {
        self.touch = false; // reset bc we might be on a device that has mouse as well as touch capability
      }, 500); // time it until after a 'click' would have fired on mobile (>300ms)

      e.preventDefault();

    });

    // open/close on hover
    // if not in touch modus

    nav.on('mouseenter', function(e) {

      // if no touch triggered
      if (!self.touch) {

        self.navLangOpen(nav);

        e.preventDefault();

      }

    });

    // close for normal menu
    // if not megamenu or small screen,
    nav.on('mouseleave', function(e) {

      self.navLangClose(nav);
      e.preventDefault();

    });

    // on window resize, reset the menu to closed state
    window.rocketshipUI.optimizedResize().add(function() {
      self.navLangClose(nav);
    });
  };

  self.navLangOpen = function(target)
  {
    target.addClass('js-open');
  };

  self.navLangClose = function(target)
  {
    target.removeClass('js-open');
  };

})(jQuery, Drupal, window, document);
