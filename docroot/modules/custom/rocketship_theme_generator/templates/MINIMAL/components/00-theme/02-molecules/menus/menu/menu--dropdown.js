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

  Drupal.behaviors.rocketshipUIDropdown = {
    attach: function (context, settings) {

      var navWrapper = $('.wrapper--navigation', context),
          myNav = $('.nav--main, .nav--secondary', context);

      // Dropdown menu for navigation
      // parameters:
      // 1: navigation wrapper
      // 2: 'click' or 'hover' to open the dropdowns
      if (myNav.length) {
        var navParams = {
          nav: myNav,
          interaction: 'hover'
        };
        self.dropdownMenu(navParams);
      }

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Open new levels op menu
   *
   * parameters:
   * nav: navigation wrapper
   * interaction: 'click' or 'hover' to open the dropdowns
   * megamenu: boolean, fold out all submenus at once
   *
   */

  self.dropdownMenu = function(params) {

    params.nav.once('js-once-dropdown').each(function() {

      var nav = $(this),
          interaction = params.interaction,
          menu =  nav.children('ul'),
          firstLevel = menu.children('li'),
          sum = 0;

      // find the first level children of an menu
      firstLevel.once('js-once-dropdown-item').each(function(index) {

        var item = $(this),
          itemLink = item.children('a, span').first(),
          icon = item.find('.expand-sub'),
          touchTrigger,
          target;

        // if any item has a submenu

        if (item.hasClass('has-sub')) {

          // if there's an icon needed for opening/closing
          // use that as touchTrigger

          if (typeof icon !== 'undefined' && icon.length) {
            // icon becomes touchTrigger
            touchTrigger = icon;
          } else {
            // if no icon, the menu item itself becomes the touchTrigger
            touchTrigger = item;
          }

          // touch detection for li only

          if (touchTrigger !== item || interaction == 'click') {
            item.on('touchstart', function() {
              self.touch = true;
            });

            item.on('touchend', function() {
              // end
              setTimeout(function() {
                self.touch = false; // reset bc we might be on a device that has mouse as well as touch capability
              }, 500); // time it until after a 'click' would have fired on mobile (>300ms)
            });
          }

          // touch event to open/close
          // includes touch detection

          if (itemLink.is('span')) {

            item.on('touchstart', function(e) {
              self.touch = true;

              // if megamenu & wide enough screen,
              // we don't open the individual submenu, but all of them
              if (params.megamenu && (self.screen !== 'xs' && self.screen !== 'sm')) {
                target = nav;
              } else {
                target = item;
              }

              if (target.hasClass('js-open')) {
                self.navPrimaryClose(target);
              } else {
                self.navPrimaryOpen(target);
              }
            });

          } else {
            touchTrigger.on('touchstart', function(e) {
              self.touch = true;

              // if megamenu & wide enough screen,
              // we don't open the individual submenu, but all of them
              if (params.megamenu && (self.screen !== 'xs' && self.screen !== 'sm')) {
                target = nav;
              } else {
                target = item;
              }

              if (target.hasClass('js-open')) {
                self.navPrimaryClose(target);
              } else {
                self.navPrimaryOpen(target);
              }

              e.preventDefault();
            });
          }

          // reset the touch variable afterwards

          touchTrigger.on('touchend', function(e) {

            // end
            setTimeout(function() {
                self.touch = false; // reset bc we might be on a device that has mouse as well as touch capability
            }, 500); // time it until after a 'click' would have fired on mobile (>300ms)

            e.preventDefault();

          });

          if (interaction == 'hover') {

            // open/close on hover
            // if not in touch modus

            item.on('mouseenter', function(e) {

              target = item;

              // if no touch triggered
              if (!self.touch) {

                self.navPrimaryOpen(target);

                e.preventDefault();

              }

            });

            // close for normal menu
            // if not megamenu or small screen,
            item.on('mouseleave', function(e) {

              target = item;
              self.navPrimaryClose(target);
              e.preventDefault();

            });

          } else {

            if (itemLink.is('span')) {

              item.on('click', function(e) {
                // if no megamenu or small screen
                if ((self.screen === 'xs' || self.screen === 'sm')) {

                  target = item;

                  // if no touch triggered
                  if (!self.touch) {
                    if (target.hasClass('js-open')) {
                      self.navPrimaryClose(target);
                    } else {
                      self.navPrimaryOpen(target);
                    }
                    e.preventDefault();
                  }
                }
              });

            } else {
              touchTrigger.on('click', function(e) {

                // if no megamenu or small screen
                if ((self.screen === 'xs' || self.screen === 'sm')) {

                  target = item;

                  // if no touch triggered
                  if (!self.touch) {
                    if (target.hasClass('js-open')) {
                      self.navPrimaryClose(target);
                    } else {
                      self.navPrimaryOpen(target);
                    }
                    e.preventDefault();
                  }
                }
              });
            }

            // click outside to close

            $(document).mouseup(function (e) {

              target = item;

              if (item.hasClass('expanded')) {
                if (target.hasClass('js-open')) {

                  // if the target of the click isn't the container...
                  // ... nor a descendant of the container
                  if (!item.is(e.target) && item.has(e.target).length === 0)
                  {
                    self.navPrimaryClose(target);
                  }
                }
              }
            });
          }
        }

      });

      // on window resize, reset the menu to closed state

      var updateLayout = function(e) {

        // reset on item
        firstLevel.each(function() {

          var firstLevel = $(this);

          self.navPrimaryClose(firstLevel);
        });
      };

      window.addEventListener('resize', updateLayout, false);
    });
  };

  self.navHeight = function(nav) {
    var items = nav.find('li.expanded'),
        height = 0;

    for (var i=0; i < items.length; ++i) {
      var iHeight = items.eq(i).find('ul').outerHeight(true);
      if (iHeight > height) {
        height += iHeight;
      }
    }

    return height;
  };

  self.navPrimaryOpen = function(target)
  {
    target.addClass('js-open');
  };

  self.navPrimaryClose = function(target)
  {
    target.removeClass('js-open');
  };

})(jQuery, Drupal, window, document);
