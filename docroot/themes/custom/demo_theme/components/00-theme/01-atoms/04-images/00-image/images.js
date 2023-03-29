/**
 * Cooldrops UI JS: Forms
 *
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
  // Behavior for Images: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUIImages = {
    attach: function (context, settings) {

      self.checkLazyLoad(context);

      if (typeof drupalSettings.theme_settings !== 'undefined' && drupalSettings.theme_settings !== null) {
        if (typeof drupalSettings.theme_settings.prevent_layout_shift !== 'undefined' && drupalSettings.theme_settings.prevent_layout_shift !== null && drupalSettings.theme_settings.prevent_layout_shift) {
          self.imgLayoutShift(context);
        }
      }

      // Edge is being a baby about recoloring the symbols of the inline SVG
      // after DOM is loaded.
      // We need to force it to redraw or reload the symbols
      // NOTE: this is similar stuff to what the polyfill 'svg4everybody' does,
      //       but that one doesn't target Edge
      //       so if you need this to work in older browsers, either use that polyfill
      //       or extend the 'if' statement to include those browsers as well and see if that works
      if (typeof navigator.userAgent !== 'undefined' && navigator.userAgent.indexOf("Edg") > -1 ) {

        self.reloadIcons(context);

      }

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Images: functions
  ///////////////////////////////////////////////////////////////////////

  self.reloadIcons = function(context) {

    var icons = document.querySelectorAll('svg.rs-icon', context);

    icons.forEach(function(icon) {
      var use = icon.querySelector('use');
      var url = use.getAttribute('xlink:href'); // Might wanna look for href also

      var symbol = $(url);
      $(icon).append(symbol);

    });

  };

  /**
   * Reset some properties on images or their parent
   * after they have loaded
   *
   * @param {*} el
   */
  self.imagesLoadedCallback = function(parent, image) {

    if (parent !== null && typeof parent !== 'undefined') {

      // for img layout shift prevention
      if (parent.classList.contains('img-layout-shift')) {
        // parent: remove class + inline style
        parent.classList.remove('img-layout-shift');

        parent.style.removeProperty('padding-bottom');
        if (parent.getAttribute('style') === '') {
          parent.removeAttribute('style');
        }

      }

      // mark images as loaded
      if (!parent.classList.contains('js-loaded')) {
        parent.classList.add('js-loaded');
      }

    }

  };

  /**
   * For images with layout shift prevention
   * remove the inline styling & extra class
   * so the image is reset when fully loaded
   *
   * @param {*} context
   */
  self.imgLayoutShift = function(context) {

    if (typeof context === 'object' && typeof context.querySelectorAll === 'function') {

      var imagesWithPlaceholders = context.querySelectorAll(
        '.img-layout-shift'
      );

      imagesWithPlaceholders.forEach(function (el) {

        var img = el.querySelectorAll('img');

        // Fun fact: if lazy loading is turned on
        //           imagesLoadedCallback might first get triggered via checkLazyLoad
        //           and reset is handled then
        self.imagesLoadedCallback(el, img);

      });
    }
  };

  /**
   * Detect if image is loaded
   *
   */
   self.imgAtomLoaded = function (img) {

    if (typeof img !== 'undefined' && img instanceof HTMLImageElement) {

      var loaded = false;

      // fires after images are loaded (if not cached)
      img.onload = function() {

        if (!loaded) {

          // if we have done something about layout shift, this is also the place to remove those changes
          // or if it is simply resetting the lazy loaded images to their proper settings after loading
          self.imagesLoadedCallback(img.parentNode, img);

          loaded = true;
        }
      };

      if (typeof img.complete == 'boolean' && img.complete) {

        $(img).load(img.src);

        if (!loaded) {

          // if we have done something about layout shift, this is also the place to remove those changes
          // or if it is simply resetting the lazy loaded images to their proper settings after loading
          self.imagesLoadedCallback(img.parentNode, img);

          loaded = true;
        }
      }

    }
  };

  self.checkLazyLoad = function(context) {

    // In an ideal world, we could detect if the browser supports lazy loading,
    // and then we can safely assign the src attributes without instantly triggering an eager image load.
    // However, the implementation is not consistent accross browsers
    // so for now (june 2021), we check support and load a fallback if it's not supported
    if ("loading" in HTMLImageElement.prototype) {

      // find images that should be lazy loaded (and some imgs from formatters we take over the preloader for)
      var lazyImages = document.querySelectorAll('img[loading="lazy"], .drimage img', context);

      lazyImages.forEach(function(image) {

        // check image has finished loading to finish things

        self.imgAtomLoaded(image);

        // af a dataset exists but no src
        if (typeof image.dataset.src !== 'undefined' && (typeof image.src === 'undefined' || image.src === null || image.src === '')) {
          // set the src attribute to trigger a load
          image.src = image.dataset.src;
        }

      });

    } else if (typeof drupalSettings.theme_settings.lazy_loading_fallback !== 'undefined' &&
      (drupalSettings.theme_settings.lazy_loading_fallback === true || drupalSettings.theme_settings.lazy_loading_fallback === 1)) {
      // Use our own lazyLoading with Intersection Observers and all that jazz
      // Lazy load images that have a 'loading = "lazy"' prop
      // IF YOU WANT THIS FALLBACK TO WORK, YOU NEED TO SET UP data-src ON THE IMG TAG IN TWIG !!!
      self.lazyLoadFallback(context);

    } else {

      // if no lazy loading support and no lazy loading enabled
      // we need to reset all the lazy preloaders (because they get set no matter what, via CSS on existing image selectors)

      var images = document.querySelectorAll('img[loading="lazy"], .drimage img', context);

      images.forEach(function(image) {

        // check image has finished loading to finish things
        self.imgAtomLoaded(image);
      });

    }

  };

  /**
   * Lazy load images
   * Src: https://css-tricks.com/tips-for-rolling-your-own-lazy-loading
   * modified for our use
   */
  self.lazyLoadFallback = function(context) {

    function lazyLoad (elements) {
      elements.forEach(function(image) {

        // for normal images, just check the visibility
        if (image.intersectionRatio > 0) {

          // check image has finished loading to finish things
          self.imgAtomLoaded(image.target);

          if (typeof image.target.dataset.src !== 'undefined') {

            // set the src attribute to trigger a load
            image.target.src = image.target.dataset.src;
          }

          // stop observing this element. Our work here is done!
          observer.unobserve(image.target);

        }

      });
    }

    // check for IntersectionObserver support
    var obserserverSupport = false;
    if ('isIntersecting' in window.IntersectionObserverEntry.prototype) {
      obserserverSupport = true;
    }

    if (obserserverSupport) {
      // Set up the intersection observer to detect when to define
      // and load the real image source
      var options = {
        rootMargin: "100px",
        threshold: 1.0
      };
      var observer = new IntersectionObserver(lazyLoad, options);

      // Tell our observer to observe all image fields that need lazy loading

      // find images that should be lazy loaded (and some imgs from formatters we take over the preloader for)
      var lazyImages = document.querySelectorAll('img[loading="lazy"], .drimage img', context);

      lazyImages.forEach(function(image) {
        observer.observe(image);
      });
    }

    var lazyExceptions = null;

    if (obserserverSupport) {
      // Some images, we'll just skip the preloading
      // because they cause issues with other JS and are fetched on the spot already anyway
      lazyExceptions = document.querySelectorAll('.modal-content .lazy-wrapper, .modal-content .drimage, .slick-cloned .lazy-wrapper, .slick-cloned .drimage', context);
    } else {
      // if no support for our observers, simply reset ALL relevant images
      lazyExceptions = document.querySelectorAll('img[loading="lazy"], .drimage img', context);
    }

    if (lazyExceptions !== null) {

      lazyExceptions.forEach(function(image) {

        // check image has finished loading to finish things
        self.imgAtomLoaded(image.target);

        if (typeof image.dataset.src !== 'undefined' && !image.classList.contains('js-loaded')) {
          // set the src attribute to trigger a load
          image.src = image.dataset.src;
        }
      });
    }

  };

})(jQuery, Drupal, window, document);
