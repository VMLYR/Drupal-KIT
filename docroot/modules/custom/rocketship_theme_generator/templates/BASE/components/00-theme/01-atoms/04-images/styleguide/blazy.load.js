(function (Drupal, drupalSettings, $, window, document) {

  Drupal.behaviors.blazyLoad = {
    attach: function (context) {

      // ** Find all images with blazy class
      var blazies = $('.b-lazy');

      blazies.once('js-blazy-load').each(function(i) {

        var el = $(this),
            loadingWrapper = el.closest('.media--loading'),
            field = el.closest('.field.blazy');

        // mark their field
        field.addClass('blazy--on');

        if (i === 0) {
          field.addClass('blazy--first');
        }

        // Mark as loaded after a certain time + when img's are loaded

        var blazyTimout =

          setTimeout(function() {

            clearTimeout(blazyTimout);

            // if it's an image
            if (el[0].tagName === 'IMG') {
              // when img source is loaded, mark as loaded
              window.rocketshipUI.imgLoaded (el, function() {
                el.addClass('b-loaded');
                loadingWrapper.removeClass('media--loading');
                loadingWrapper.addClass('is-b-loaded');
              });
            // if it's a picture
            } else if(el[0].tagName === 'PICTURE') {
              // find the image inside and wait for that to load
              var image = el.find('img');
              // when img source is loaded, mark as loaded
              window.rocketshipUI.imgLoaded (image, function() {
                el.addClass('b-loaded');
                loadingWrapper.removeClass('media--loading');
                loadingWrapper.addClass('is-b-loaded');
              });
            } else {
              el.addClass('b-loaded');
              loadingWrapper.removeClass('media--loading');
              loadingWrapper.addClass('is-b-loaded');
            }
          }, 1000);

      });

    }
  };


}(Drupal, drupalSettings, jQuery, window, document));


// /**
//  * @file
//  * Provides Intersection Observer API, or bLazy loader.
//  *
//  * Custom changes:
//  * - replaced 'this' and 'this.document' at the very bottom with 'window' and 'document'
//  * - reloaded Drupal.blazy variables and globals function in the blazy init() function because this stupid file
//  *   gets read before the drupalSettings data is loaded, for some reason
//  */

// // var drupalSettings = {
// //   blazy: {
// //     loadInvisible: false,
// //     offset: 100,
// //     saveViewportOffsetDelay: 50,
// //     validateDelay: 25,
// //     container: '',
// //     enabled: false,
// //     disconnect: false,
// //     rootMargin: '0px',
// //     threshold: [
// //       0
// //     ],
// //     isUniform: false,
// //     selector: ".b-lazy",
// //     errorClass: "b-error",
// //     successClass: "b-loaded",
// //   },
// //   blazyIo: {
// //     enabled: false,
// //     disconnect: false,
// //     rootMargin: '0px',
// //     threshold: [
// //       0
// //     ]
// //   }
// // };

// (function (Drupal, drupalSettings, _db, window, document) {

//   'use strict';

//   var _dataAnimation = 'data-animation';
//   var _dataDimensions = 'data-dimensions';
//   var _dataBg = 'data-backgrounds';
//   var _dataRatio = 'data-ratio';
//   var _firstBlazy = 'blazy--first';
//   var _isNativeExecuted = false;
//   var _isPictureExecuted = false;
//   var _resizeTick = 0;

//   /**
//    * Blazy public methods.
//    *
//    * @namespace
//    */
//   Drupal.blazy = Drupal.blazy || {
//     context: null,
//     init: null,
//     items: [],
//     ratioItems: [],
//     windowWidth: 0,
//     blazySettings: drupalSettings.blazy || {},
//     ioSettings: drupalSettings.blazyIo || {},
//     revalidate: false,
//     options: {},
//     globals: function () {
//       var me = this;
//       var commons = {
//         isUniform: false,
//         success: me.clearing.bind(me),
//         error: me.clearing.bind(me),
//         selector: '.b-lazy',
//         errorClass: 'b-error',
//         successClass: 'b-loaded'
//       };

//       return _db.extend(me.blazySettings, me.ioSettings, commons);
//     },

//     clearing: function (el) {

//       var me = this;
//       var cn = _db.closest(el, '.media');
//       var an = _db.closest(el, '[' + _dataAnimation + ']');
//       // Clear loading classes.
//       me.clearLoading(el);

//       // Reevaluate the element.
//       me.reevaluate(el);

//       // Container might be the el itself for BG, do not NULL check here.
//       me.updateContainer(el, cn);

//       // Supports blur, animate.css for CSS background, picture, image, media.
//       if (an !== null || me.has(el, _dataAnimation)) {
//         _db.animate(an !== null ? an : el);
//       }

//       // Provides event listeners for easy overrides without full overrides.
//       _db.trigger(el, 'blazy.done', {options: me.options});

//       // Initializes the native lazy loading once the first found is loaded.
//       if (!_isNativeExecuted) {
//         _db.trigger(me.context, 'blazy.native', {options: me.options});

//         _isNativeExecuted = true;
//       }
//     },

//     clearLoading: function (el) {
//       // The .b-lazy element can be attached to IMG, or DIV as CSS background.
//       // The .(*)loading can be .media, .grid, .slide__content, .box, etc.
//       var loaders = [
//         el,
//         _db.closest(el, '.is-loading'),
//         _db.closest(el, '[class*="loading"]')
//       ];

//       _db.forEach(loaders, function (loader) {
//         if (loader !== null) {
//           loader.className = loader.className.replace(/(\S+)loading/, '');
//         }
//       });
//     },

//     isLoaded: function (el) {
//       return el !== null && el.classList.contains(this.options.successClass);
//     },

//     reevaluate: function (el) {
//       var me = this;
//       var ie = el.classList.contains('b-responsive') && el.hasAttribute('data-pfsrc');

//       // In case an error, try forcing it.
//       if (me.init !== null && _db.hasClass(el, me.options.errorClass)) {
//         el.classList.remove(me.options.errorClass);

//         // This is a rare case, hardly called, just nice to have for errors.
//         window.setTimeout(function () {
//           me.init.load(el);
//         }, 10);
//       }

//       // @see http://scottjehl.github.io/picturefill/
//       if (window.picturefill && ie) {
//         window.picturefill({
//           reevaluate: true,
//           elements: [el]
//         });
//       }
//     },

//     has: function (el, attribute) {
//       return el !== null && el.hasAttribute(attribute);
//     },

//     contains: function (el, name) {
//       return el !== null && el.classList.contains(name);
//     },

//     updateContainer: function (el, cn) {
//       var me = this;
//       var isPicture = _db.equal(el.parentNode, 'picture') && me.has(cn, _dataDimensions);

//       // Fixed for effect Blur messes up Aspect ratio Fluid calculation.
//       window.setTimeout(function () {
//         if (me.isLoaded(el)) {
//           // Adds context for effetcs: blur, etc. considering BG, or just media.
//           (me.contains(cn, 'media') ? cn : el).classList.add('is-b-loaded');

//           if (isPicture) {
//             me.updatePicture(el, cn);
//           }

//           if (me.has(el, _dataBg)) {
//             _db.updateBg(el, me.options.mobileFirst);
//           }
//         }
//       });
//     },

//     updatePicture: function (el, cn) {
//       var me = this;
//       var pad = Math.round(((el.naturalHeight / el.naturalWidth) * 100), 2);

//       cn.style.paddingBottom = pad + '%';

//       // Swap all aspect ratio once to reduce abrupt ratio changes for the rest.
//       if (!_isPictureExecuted) {
//         _db.trigger(me.context, 'blazy.uniform', {pad: pad});
//         _isPictureExecuted = true;
//       }
//     },

//     /**
//      * Updates the dynamic multi-breakpoint aspect ratio: bg, picture or image.
//      *
//      * This only applies to Responsive images with aspect ratio fluid.
//      * Static ratio (media--ratio--169, etc.) is ignored and uses CSS instead.
//      *
//      * @param {HTMLElement} cn
//      *   The .media--ratio--fluid container HTML element.
//      */
//     updateRatio: function (cn) {
//       var me = this;
//       var dimensions = _db.parse(cn.getAttribute(_dataDimensions)) || ('dimensions' in me.options ? me.options.dimensions : false);

//       if (!dimensions) {
//         me.updateFallbackRatio(cn);
//         return;
//       }

//       // For picture, this is more a dummy space till the image is downloaded.
//       var isPicture = cn.querySelector('picture') !== null && _resizeTick > 0;
//       var pad = _db.activeWidth(dimensions, isPicture);

//       if (pad !== 'undefined') {
//         cn.style.paddingBottom = pad + '%';
//       }

//       // Fix for picture or bg element with resizing.
//       if (_resizeTick > 0 && (isPicture || me.has(cn, _dataBg))) {
//         me.updateContainer((isPicture ? cn.querySelector('img') : cn), cn);
//       }
//     },

//     updateFallbackRatio: function (cn) {
//       // Only rewrites if the style is indeed stripped out by Twig, and not set.
//       if (!cn.hasAttribute('style') && cn.hasAttribute(_dataRatio)) {
//         cn.style.paddingBottom = cn.getAttribute(_dataRatio) + '%';
//       }
//     },

//     doNativeLazy: function () {
//       var me = this;

//       var doNative = function (el) {
//         // Reset attributes, and let supportive browsers lazy load natively.
//         _db.setAttrs(el, ['srcset', 'src'], true);

//         // Also supports PICTURE or (future) VIDEO which contains SOURCEs.
//         _db.setAttrsWithSources(el, false, true);

//         // Mark it loaded to prevent Blazy/IO to do any further work.
//         el.classList.add(me.options.successClass);
//         me.clearing(el);
//       };

//       var onNative = function () {
//         _db.forEach(me.items, doNative);
//       };

//       _db.bindEvent(me.context, 'blazy.native', onNative, {once: true});
//     },

//     isNativeLazy: function () {
//       return 'loading' in HTMLImageElement.prototype;
//     },

//     isIo: function () {
//       return this.ioSettings && this.ioSettings.enabled && 'IntersectionObserver' in window;
//     },

//     isBlazy: function () {
//       return !this.isIo() && 'Blazy' in window;
//     },

//     forEach: function (context) {
//       var el = context.querySelector('[data-blazy]');
//       var blazies = context.querySelectorAll('.blazy:not(.blazy--on)');

//       // Various use cases: w/o formaters, custom, or basic, and mixed.
//       // The [data-blazy] is set by the module for formatters, or Views gallery.
//       if (blazies.length > 0) {
//         _db.forEach(blazies, doBlazy, context);
//       }

//       // Runs basic Blazy if no [data-blazy] found, probably a single image or
//       // a theme that does not use field attributes, or (non-grid) BlazyFilter.
//       if (el === null) {
//         initBlazy(context);
//       }
//     },

//     run: function (opts) {
//       return this.isIo() ? new BioMedia(opts) : new Blazy(opts);
//     },

//     afterInit: function (context) {
//       var me = this;
//       me.ratioItems = context.querySelector('.media--ratio') === null ? [] : context.querySelectorAll('.media--ratio');
//       var shouldLoop = me.ratioItems.length > 0;

//       var swapRatio = function (e) {
//         var pad = e.detail.pad;

//         if (pad > 10) {
//           _db.forEach(me.ratioItems, function (cn) {
//             cn.style.paddingBottom = pad + '%';
//           }, context);
//         }
//       };

//       var checkRatio = function () {
//         me.windowWidth = _db.windowWidth();

//         if (shouldLoop) {
//           _db.forEach(me.ratioItems, me.updateRatio.bind(me), context);
//         }

//         // BC with bLazy, native/IO doesn't need to revalidate, bLazy does.
//         // Scenarios: long horizontal containers, Slick carousel slidesToShow >
//         // 3. If any issue, add a class `blazy--revalidate` manually to .blazy.
//         if (!me.isNativeLazy() && (me.isBlazy() || me.revalidate)) {
//           me.init.revalidate(true);
//         }

//         // Provides event listeners for easy overrides without full overrides.
//         // Checks for weird contexts, in case spit out during AJAX, etc.
//         if (context.classList && context.classList.contains(_firstBlazy)) {
//           _db.trigger(context, 'blazy.afterInit', {
//             items: me.items || me.ratioItems,
//             windowWidth: me.windowWidth
//           });
//         }
//         _resizeTick++;
//       };

//       // Checks for aspect ratio, onload event is a bit later.
//       // @todo use Drupal.debounce if it makes any difference.
//       checkRatio();
//       _db.bindEvent(window, 'resize', Drupal.debounce(checkRatio, 200, true));
//       // var resizeHandler = window.rocketshipUI.debounce(function(e) {
//       //   checkRatio();
//       // }, 200);
//       // _db.bindEvent(window, 'resize', resizeHandler);

//       // Reduces abrupt ratio changes for the rest after the first loaded.
//       if (me.options.isUniform && shouldLoop) {
//         _db.bindEvent(context, 'blazy.uniform', swapRatio, {once: true});
//       }
//     }

//   };

//   /**
//    * Initialize the blazy instance, either basic, advanced, or native.
//    *
//    * The initialization may take once for basic (not using module formatters),
//    * or per .blazy/[data-blazy] formatter when there are one or many on a page.
//    *
//    * @param {HTMLElement} context
//    *   This can be document, or .blazy container w/o [data-blazy].
//    * @param {Object} opts
//    *   The options might be empty for basic blazy, not using formatters.
//    */
//   var initBlazy = function (context, opts) {
//     var me = Drupal.blazy;
//     // Set docroot in case we are in an iframe.
//     var documentElement = context instanceof HTMLDocument ? context : _db.closest(context, 'html');

//     opts = opts || {};
//     opts.mobileFirst = opts.mobileFirst || false;
//     documentElement = documentElement || document;
//     if (!document.documentElement.isSameNode(documentElement)) {
//       opts.root = documentElement;
//     }

//     me.options = _db.extend({}, me.globals(), opts);
//     me.context = context;

//     // Old bLazy, not IO, might need scrolling CSS selector like Modal library.
//     // A scrolling modal with an iframe like Entity Browser has no issue since
//     // the scrolling container is the entire DOM. Another use case is parallax.
//     var scrollElms = '#drupal-modal, .is-b-scroll';
//     if (me.options.container) {
//       scrollElms += ', ' + me.options.container.trim();
//     }
//     me.options.container = scrollElms;

//     // Swap lazy attributes to let supportive browsers lazy load them.
//     // This means Blazy and even IO should not lazy-load them any more.
//     // Ensures to not touch lazy-loaded AJAX, or likely non-supported elements:
//     // Video, DIV, etc. Only IMG and IFRAME are supported for now.
//     var nativeSelector = me.options.selector + '[loading]:not(.' + me.options.successClass + ')';
//     me.items = documentElement.querySelector(nativeSelector) === null ? [] : documentElement.querySelectorAll(nativeSelector);

//     if (me.isNativeLazy()) {
//       // Intentionally on the second line to not hit it till verified.
//       if (me.items.length > 0) {
//         me.doNativeLazy();
//       }
//     }

//     // Put the blazy/IO instance into a public object for references/ overrides.
//     // If native lazy load is supported, the following will skip internally.
//     me.init = me.run(me.options);

//     // Reacts on resizing per 200ms.
//     me.afterInit(context);
//   };

//   /**
//    * Blazy utility functions.
//    *
//    * @param {HTMLElement} elm
//    *   The .blazy/[data-blazy] container, not the lazyloaded .b-lazy element.
//    */
//   function doBlazy(elm) {
//     var me = Drupal.blazy;
//     var dataAttr = elm.getAttribute('data-blazy');
//     var opts = (!dataAttr || dataAttr === '1') ? {} : (_db.parse(dataAttr) || {});

//     me.revalidate = me.revalidate || elm.classList.contains('blazy--revalidate');
//     elm.classList.add('blazy--on');

//     // Initializes native, IntersectionObserver, or Blazy instance.
//     // @todo attempts to optimize nested blazies, remove check if any issue.
//     if (_db.closest(elm, '.blazy') === null) {
//       elm.classList.add(_firstBlazy);
//       opts.isUniform = me.contains(elm, 'blazy--field') || me.contains(elm, 'blazy--grid') || me.contains(elm, 'blazy--uniform');
//       initBlazy(elm, opts);
//     }
//   }

//   /**
//    * Attaches blazy behavior to HTML element identified by .blazy/[data-blazy].
//    *
//    * The .blazy/[data-blazy] is the .b-lazy container, might be .field, etc.
//    * The .b-lazy is the individual IMG, IFRAME, PICTURE, VIDEO, DIV, BODY, etc.
//    * The lazy-loaded element is .b-lazy, not its container. Note the hypen (b-)!
//    *
//    * @type {Drupal~behavior}
//    */
//   Drupal.behaviors.blazy = {
//     attach: function (context) {

//       // force loading of drupalSettings and other stuff in Drupal.blazy
//       // because it was firt loaded BEFORE data from drupalSettings was ready
//       Drupal.blazy.blazySettings = drupalSettings.blazy;
//       Drupal.blazy.ioSettings = drupalSettings.blazyIo;
//       Drupal.blazy.globals = function () {
//         var me = this;
//         var commons = {
//           isUniform: false,
//           success: me.clearing.bind(me),
//           error: me.clearing.bind(me),
//           selector: '.b-lazy',
//           errorClass: 'b-error',
//           successClass: 'b-loaded'
//         };

//         return _db.extend(me.blazySettings, me.ioSettings, commons);
//       };

//       // Drupal.attachBehaviors already does this so if this is necessary,
//       // someone does an invalid call. But let's be robust here.
//       // Note: context can be unexpected <script> element with Media library.
//       context = context || document;

//       // Originally identified at D7, yet might happen at D8 with AJAX.
//       // Prevents jQuery AJAX messes up where context might be an array.
//       if ('length' in context) {
//         context = context[0];
//       }

//       // Runs Blazy with multi-serving images, and aspect ratio supports.
//       // W/o [data-blazy] to address various scenarios like custom simple works,
//       // or within Views UI which is not easy to set [data-blazy] via UI.
//       _db.once(Drupal.blazy.forEach(context));
//     }
//   };

// }(Drupal, drupalSettings, dBlazy, window, document));
