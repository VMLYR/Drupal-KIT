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
  // Triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUIBase = {
    attach: function (context, settings) {

      var tabs = $('.tabs__nav', context).first();
      // var iFrames = $('iframe');
      var teasers = $('.view-mode--teaser, .view-mode--teaser-front, .view-mode--frontpage');

      // add a class to body to indicate the page has edit tabs
      // so we can set it fixed if needed
      if (tabs.length) self.body.addClass('has-tabs');

      // smooth scrolling to anchors
      // true if fixed header, false for regular header
      self.scrollToAnchor(0);

      var skipLink = $('a[href="#main-content"]'),
          content = $('#main-content');
      if (skipLink.length && content.length) {
        // when you click on the skiplink, force focus on the target
        skipLink.once('js-once-skip-link').each(function() {
          $(this).on('click', function(e) {
            content.trigger('focus');
            e.preventDefault();
          });
        });
      }

      // make cards clickable: view teasers
      if (teasers.length) self.cardLink(teasers);

      // fluid iframes: redundant because we already have this via a module
      // if (iFrames.length) self.setResponsiveFrames(iFrames);

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Make iFrames responsive
   *
   */
  // self.setResponsiveFrames = function(iFrames) {

  //   iFrames.once('js-once-set-fluid-iframes').each(function() {

  //     var iFrame = $(this);

  //     // if no responsive wrapper, and if in page, make a wrapper
  //     //
  //     if(iFrame.closest('.main__content').length && !iFrame.closest('div').hasClass('video-embed-field-responsive-video')) {
  //       self.responsiveFrames(iFrame);
  //     }

  //     // smooth scrolling to anchors
  //     // can pass a hardcoded offset if needed
  //     self.scrollToAnchor(0);
  //   });

  // };

  // self.responsiveFrames = function(iFrame) {

  //   var iWidth = iFrame.attr('width'),
  //       iHeight = iFrame.attr('height'),
  //       wrapper =  '<div class="iframe-responsive"></div>';

  //   if (typeof iWidth != 'undefined' && typeof iHeight != 'undefined') {
  //     var padding = iHeight/iWidth*100;
  //     wrapper =  '<div class="iframe-responsive" style="padding-bottom:' + padding +'%"></div>';
  //   }

  //   iFrame.wrap(wrapper);

  // };


  /*
   * Make a card clickable, if there is a content link in it to reference
   * If no selector is given, it will try to go for a 'dedicated' link
   * - a 'read more' link
   * - or a button (if there is only 1)
   * variables:
   * - elements: jquery object referencing an element
   * - linkSelector: string (optional)
   */
  self.cardLink = function(elements, linkSelector, fallbackSelector) {

    var newTab = false,
        preventTabReset = false;

    // For each of our elements
    elements.once('js-once-cardlink').each(function() {

      var el = $(this),
          link, hrefProp, targetProp, down, up;

      // is a selector was passed to the function
      // use it to find the dedicated link to go to
      if (typeof linkSelector !== 'undefined' && linkSelector.length) {
        link = el.find(linkSelector).last();
      }

      // if selector not found, look for fallback
      if (typeof link === 'undefined' || link.length < 1) {
        link = el.find(fallbackSelector).last();
      }

      // if fallback selector found, look for other to use
      if (typeof link === 'undefined' || link.length < 1) {
        link = self.getCardLink(el).link;
      }

      // if we have a link, go ahead
      if (typeof link !== 'undefined' && link !== null && link.length) {

        hrefProp = link.attr('href');
        targetProp = link.attr('target');

        // set cursor on the card
        el.css({ cursor: 'pointer' });

        // if there is a dedicated link in the item
        // make the div clickable
        // (ignore if a child link is clicked, so be sure to check target!)

        el.once('js-once-cardlink-mousedown').on('mousedown', function(e) {
          down = +new Date();

          if (e.ctrlKey || e.metaKey) {
            newTab = true;
          }
        });

        el.once('js-once-cardlink-mouseup').on('mouseup', function(e) {

          up = +new Date();

          // only fire if really clicked on element
          if ((up - down) < 250) {

            var myEl = $(this);

            // if the target of the click is the el...
            // ... or a descendant of the el
            if (!myEl.is(e.target) || myEl.has(e.target).length === 0) {

              // if it's a link tag,
              if( $(e.target).is('a, a *') ) {
                // do nothing
                // else, trigger the dedicated link
              } else{
                // if new tab key is pressed
                // open in new tab
                if (newTab) {
                  window.open(hrefProp, '_blank');

                } else {
                  // if target defined, open in target
                  if (typeof targetProp !== 'undefined') {
                    // fire a click event on the original link
                    window.open(hrefProp, targetProp);
                  } else {
                    window.location.href = hrefProp;
                  }
                }

              }
            }
          }

          // reset the flags that determine opening the link in a new browser tab
          newTab = false;

        });

      }

    });
  };

  self.getCardLink = function(el) {

    var link;

    // 'read more'
    link = el.find('.field--name-node-link a').first();

    if (link.length) {
      return {
        link: link
      };
    }

    // singular button

    link = el.find('.field--buttons a');

    if (link.length === 1) {
      return {
        link: link
      };
    }

    // other link field

    link = el.find('[class*="field--name-field-link-"] a').last();

    if (link.length) {
      return {
        link: link
      };
    }

    return {
      link: null
    };

  };

  /**
   * Scroll to anchor in page
   *
   * offset: number to offset when scrolling to destination
   */
  self.scrollToAnchor = function(offset) {

    var animatedScrolling = false;
    var exceptions = null;

    if (typeof drupalSettings !== 'undefined' && drupalSettings !== null) {
      if (typeof drupalSettings.theme_settings !== 'undefined' && drupalSettings.theme_settings !== null) {
        if (typeof drupalSettings.theme_settings.scroll_to !== 'undefined' && drupalSettings.theme_settings.scroll_to !== null && drupalSettings.theme_settings.scroll_to) {
          animatedScrolling = true;
        }

        if (typeof drupalSettings.theme_settings.scroll_to_exceptions !== 'undefined' && drupalSettings.theme_settings.scroll_to_exceptions !== null && drupalSettings.theme_settings.scroll_to_exceptions.length) {
          exceptions = drupalSettings.theme_settings.scroll_to_exceptions;
        }
      }
    }

    // only do animated scrolling if set in theme settings

    if (animatedScrolling) {

      // on page load, check if hash in the url matches with an anchor on page
      // and scroll to it

      var newOffset,
        tabs = $('.tabs'),
        tabsHeight = 0,
        adminHeight = 0,
        header = $('.sticky-top'); // use whatever wrapper you need

      // check for fixed elements on top of site
      // to compensate offset of the anchor when scrolling
      if ( $('body').hasClass('toolbar-fixed') ) {
        adminHeight = $('#toolbar-bar').outerHeight();

        if (tabs.length) {
          tabsHeight = tabs.outerHeight();
        }
      }

      // only add offset if fixed header and/or value passed in function
      if (typeof offset == 'undefined') offset = 0;

      // function to calculate the offset, in case of fixed header or not
      var calcOffset = function() {
        if (header.css('position') === 'fixed') {
          offset = header.outerHeight() + offset + adminHeight + tabsHeight; // compensate for fixed header height

          // compare offset to height of header to see if it's smaller
          // must at least use the header height as offset or we'll get a gap
          newOffset = header.outerHeight();
          if (newOffset > offset) offset = newOffset;

        } else {
          // calculate offset for a normal page (no fixed header)
          offset = 15 + offset + adminHeight + tabsHeight; // compensate with bit of space above the element with the anchor
        }

        // need negative value for the scroll calculations
        offset = -(offset);

        return offset;
      };

      // get hash from page url (includes the hash sign)
      var urlHash = window.location.hash;

      if ( $(urlHash).length ) {

        // possible anchor links that refer to that hash
        var myAnchorLinks = $( 'a[href$="' + urlHash + '"]' );

        // trigger the scrollTo, only if NO anchorLink selector on the page
        // OR anchorLink selector is not part of exceptions

        // 1) No anchor link
        if (myAnchorLinks.length < 1) {
          // recalculate offset if fixed header
          offset = calcOffset();

          // el, offset, speed, callback
          var scrollParams = {
            el: $(urlHash),
            offset: offset,
            speed: 1000
          };

          self.scrollTo(scrollParams);
        }

        // 2) check all of anchorLinks if there are any + if not an exception
        myAnchorLinks.once('js-once-scrollable-anchors-active').each(function() {

          var myLink = $(this);

          // set an active class & scrollTo (if not part of the exclusion list)
          if (exceptions === null || !myLink.is(exceptions)) {

            // recalculate offset if fixed header
            offset = calcOffset();

            // el, offset, speed, callback
            var scrollParams = {
              el: $(urlHash),
              offset: offset,
              speed: 1000
            };

            self.scrollTo(scrollParams);

            myLink.addClass('js-active-anchor');
          }

        });
      }

      // when clicking an anchor, animate scroll to that anchor and add to history
      // if that anchor is not excluded

      var anchorLinks = $('a[href*="#"]').not('a[href="#"]');

      anchorLinks.once('js-once-scrollable-anchors').each(function() {

        var anchorLink = $(this);

        if (exceptions === null || !anchorLink.is(exceptions)) {

          // remove active classes on anchor links
          anchorLink.removeClass('active').removeClass('active-trail');
        }

        // click anchor link and animate scroll to it
        anchorLink.once('js-once-scrollable-anchor-click').click(function (e) {

          // if there are links to ignore for smooth scroll
          // or anchorLink is not an exception
          //
          if (exceptions === null || !anchorLink.is(exceptions)) {
            var path = this.href,
                id, hash, target;

            if (typeof e.target.href !== 'undefined') {
              id = e.target.href.split('#')[1];
            }

            else if (typeof e.currentTarget.href !== 'undefined') {
              id = e.currentTarget.href.split('#')[1];
            }

            hash = '#' + id;
            target = $(hash);

            // recalculate offset if fixed header
            offset = calcOffset();

            if (target.length) {

              // set an active class
              anchorLink.addClass('js-active-anchor');

              // el, offset, speed, callback
              var scrollParamsForTarget = {
                el: target,
                offset: offset,
                speed: 1000,
                callback: function () {

                  // change url without refresh
                  document.location.hash = hash;
                }
              };

              self.scrollTo(scrollParamsForTarget);

              // If supported by the browser we can also update the URL
              if (window.history && window.history.pushState) {
                history.pushState("", document.title, hash);
              }

              // if no anchor with id matchin href
              // check for anchor with a name matching the href
            } else {
              var newTarget = $('a[name="' + id + '"]');

              if (newTarget.length) {
                // set an active class
                anchorLink.addClass('js-active-anchor');

                var scrollParamsForNewTarget = {
                  el: newTarget,
                  offset: offset,
                  speed: 1000,
                  callback: function () {

                    // change url without refresh
                    document.location.hash = hash;
                  }
                };

                // el, offset, speed, callback
                self.scrollTo(scrollParamsForNewTarget);

              }
            }

            // the url, minus stuff after hash or parameters
            var currentUrl = window.location.href.split(/[?#]/)[0];
            // the path, minus stuff after hash or parameters
            var pathBase = path.split(/[?#]/)[0];

            // if the URLs (stripped of hashes and parameters) don't match up, it's
            // a link to a different page.
            // don't scroll to anchor on same page on click.
            if (currentUrl.replace(/\/$/, "") !== pathBase.replace(/\/$/, "")) {
              return true;
            }
            // we're on the same page and don't need a page reload
            e.preventDefault();
          }
        });
      });

      // when going back/forward in history
      // make sure the events associated with the anchor animations are fired
      // if that anchor is not excluded
      window.onpopstate = function(event) {

        anchorLinks.once('js-once-scrollable-anchors-popstate').each(function() {

          var anchorLink = $(this);

          if (exceptions === null || !anchorLink.is(exceptions)) {

            var path = this.href;
            // get hash from page url
            var urlHash = window.location.hash;
            // get hash & ID from href
            var linkHash = path.substring(path.indexOf("#"));

            // recalculate offset if fixed header
            offset = calcOffset();

            if ($(linkHash).length) {
              // if hash of current path matches hash of the url
              // scroll to it
              if (urlHash == linkHash) {

                if (exceptions === null || !anchorLink.is(exceptions)) {
                  anchorLink.addClass('js-active-anchor');

                  var scrollParams = {
                    el: $(linkHash),
                    offset: offset,
                    speed: 1000
                  };

                  // el, offset, speed, callback
                  self.scrollTo(scrollParams);
                }
              }
            }
          }
        });
      };

    }

  };

})(jQuery, Drupal, window, document);

