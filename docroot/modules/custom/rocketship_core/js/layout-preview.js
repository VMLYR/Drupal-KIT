/**
 * @file
 * Attaches the behaviors for the Layout Builder module.
 */

 (function ($, Drupal, Sortable) {

  // set namespace for frontend UI javascript
  if (typeof window.rocketshipUI === 'undefined') { window.rocketshipUI = {}; }

  var self = window.rocketshipUI;

  ///////////////////////////////////////////////////////////////////////
  // Cache variables available across the namespace
  ///////////////////////////////////////////////////////////////////////

  var ajax = Drupal.ajax,
      behaviors = Drupal.behaviors,
      debounce = Drupal.debounce,
      announce = Drupal.announce,
      formatPlural = Drupal.formatPlural;

  // indicator that 'change' hasn't been triggered yet
  var behaviorReloaded = false;
  // flag to indicate 'change' event on preview dropdown was forced
  var changeForced = false;
  // flag to prevent tracking Sections during scrolling
  var preventTrackSections = false;
  // variable to save the Section that is in view
  var activeScrollElement = null;

  var scrollTimer = null;

  ///////////////////////////////////////////////////////////////////////
  // Behaviors: triggers
  ///////////////////////////////////////////////////////////////////////

  /**
   * Toggles content preview in the Layout Builder UI.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attach content preview toggle to the Layout Builder UI.
   */
  behaviors.RSlayoutBuilderToggleContentPreview = {
    attach(context, settings) {

      var $layoutBuilder = $('#layout-builder');

      // The content preview toggle.
      var $layoutBuilderContentPreview = $('#layout-builder-select-preview-level');

      // data-content-preview-id specifies the layout being edited.
      var contentPreviewId = $layoutBuilderContentPreview.data('content-preview-id');

      // Sections and their actions
      var layoutSections = document.querySelectorAll('.layout-builder__section', context);
      if (typeof layoutSections !== 'undefined' && layoutSections !== null) {
        self.handleLayouts(layoutSections, context);
      }

      // start tracking sections in viewport
      self.trackSections(context);

      /**
       * Tracks if content preview is enabled for this layout. Defaults to true
       * if no value has previously been set.
       */
      var previewLevel = JSON.parse(localStorage.getItem(contentPreviewId)) || 'preview_edit';

      var enableEditOnly = function() {

        // refresh the LB var (in case of caching)
        $layoutBuilderUpdated = $('#layout-builder', context);

        // fall back to the old if nothing specific found
        // because Layout Builder does some strange shit when a block is saved in dialog
        if ($layoutBuilderUpdated.length > 0) {
          $layoutBuilder = $layoutBuilderUpdated;
        }

        if ($layoutBuilder.hasClass('layout-builder--content-preview')) {
          $layoutBuilder.removeClass('layout-builder--content-preview');
        }
        if ($layoutBuilder.hasClass('layout-builder--edit-preview-disabled')) {
          $layoutBuilder.removeClass('layout-builder--edit-preview-disabled');
        }
        if (!$layoutBuilder.hasClass('layout-builder--content-preview-disabled')) {
          $layoutBuilder.addClass('layout-builder--content-preview-disabled');
        }
        if (!$layoutBuilder.hasClass('layout-builder--edit-preview')) {
          $layoutBuilder.addClass('layout-builder--edit-preview');
        }

        /**
         * Iterate over all Layout Builder blocks to hide their content and add
         * placeholder labels.
         */
        $('[data-layout-content-preview-placeholder-label]', context).each(function (i, element) {
          var $element = $(element);
          $element.children(':not([data-contextual-id])').hide(0);
          var contentPreviewPlaceholderText = $element.attr('data-layout-content-preview-placeholder-label');
          var contentPreviewPlaceholderLabel = Drupal.theme('layoutBuilderPrependContentPreviewPlaceholderLabel', contentPreviewPlaceholderText);
          $element.prepend(contentPreviewPlaceholderLabel);
        });

        // reset carousel, based on state, on or off
        self.resetCarousel(false, settings);
      }

      var enableContentPreviewOnly = function() {

        // refresh the LB var (in case of caching)
        $layoutBuilderUpdated = $('#layout-builder', context);

        // fall back to the old if nothing specific found
        // because Layout Builder does some strange shit when a block is saved in dialog
        if ($layoutBuilderUpdated.length > 0) {
          $layoutBuilder = $layoutBuilderUpdated;
        }

        if ($layoutBuilder.hasClass('layout-builder--content-preview-disabled')) {
          $layoutBuilder.removeClass('layout-builder--content-preview-disabled');
        }
        if ($layoutBuilder.hasClass('layout-builder--edit-preview')) {
          $layoutBuilder.removeClass('layout-builder--edit-preview');
        }
        if (!$layoutBuilder.hasClass('layout-builder--content-preview')) {
          $layoutBuilder.addClass('layout-builder--content-preview');
        }
        if (!$layoutBuilder.hasClass('layout-builder--edit-preview-disabled')) {
          $layoutBuilder.addClass('layout-builder--edit-preview-disabled');
        }


        // Remove all placeholder labels.
        $('.js-layout-builder-content-preview-placeholder-label').remove();
        // show the blocks
        $('[data-layout-content-preview-placeholder-label]').each(function (i, element) {
          $(element).children().show();
        });

        // to force masonry to reconfigure
        window.dispatchEvent(new Event('resize'));

        // reset carousel, based on state, on or off
        self.resetCarousel(true, settings);

      }

      var enableContentPreviewWithEditability = function() {

        // refresh the LB var (in case of caching)
        $layoutBuilderUpdated = $('#layout-builder', context);

        // fall back to the old if nothing specific found
        // because Layout Builder does some strange shit when a block is saved in dialog
        if ($layoutBuilderUpdated.length > 0) {
          $layoutBuilder = $layoutBuilderUpdated;
        }

        if ($layoutBuilder.hasClass('layout-builder--content-preview-disabled')) {
          $layoutBuilder.removeClass('layout-builder--content-preview-disabled');
        }
        if ($layoutBuilder.hasClass('layout-builder--edit-preview-disabled')) {
          $layoutBuilder.removeClass('layout-builder--edit-preview-disabled');
        }
        if (!$layoutBuilder.hasClass('layout-builder--edit-preview')) {
          $layoutBuilder.addClass('layout-builder--edit-preview');
        }
        if (!$layoutBuilder.hasClass('layout-builder--content-preview')) {
          $layoutBuilder.addClass('layout-builder--content-preview');
        }


        // Remove all placeholder labels.
        $('.js-layout-builder-content-preview-placeholder-label').remove();
        // show the blocks
        $('[data-layout-content-preview-placeholder-label]').each(function (i, element) {
          $(element).children().show();
        });

        // to force masonry to reconfigure
        window.dispatchEvent(new Event('resize'));

        // reset carousel, based on state, on or off
        self.resetCarousel(true, settings);

      }

      /*
        The 'change' event will happen when user changes value of dropdown,
        when the html of the page is loaded (JS file is loaded, the behavior is triggered)
        or changes (eg. Section or Block was added, which retriggers the behavior)
      */

      var previewChange = function(event, previewLevel) {

        if (typeof previewLevel === 'undefined' || previewLevel === null) {
          previewLevel = $(event.currentTarget).children("option:selected").val();
        }

        localStorage.setItem(contentPreviewId, JSON.stringify(previewLevel));

        // prevent scroll tracking when changing modes
        // because it will confuse which section is the 'active' one
        if (behaviorReloaded && !changeForced) {
          preventTrackSections = true;
        }

        switch (previewLevel) {
          case 'edit_only':
            enableEditOnly();
            announce(
              Drupal.t('Block previews are hidden. Block editing is visible.'),
            );
            break;
          case 'preview_edit':
            enableContentPreviewWithEditability()
            announce(
              Drupal.t('Block previews are visible. Block editing is visible.'),
            );
            break;
          case 'preview_only':
            enableContentPreviewOnly()
            announce(
              Drupal.t('Block previews are visible. Block editing is hidden.'),
            );
            break;
        }

        if (behaviorReloaded && !changeForced) {
          self.goToSection();
          // no need to turn on scroll tracking here,
          // it is enabled again in self.goToSection()
        }

        // reset flag
        changeForced = false;

      };

      // when dropdown is used or triggered, change the preview mode
      $('#layout-builder-select-preview-level', context).on('change', function(event) {
        previewChange(event);
      });

      // on behavior reload, trigger dropdown change
      // for preview mode, use the value from localStorage (or default, if none yet);
      changeForced = true;

      $layoutBuilderContentPreview.val(previewLevel);
      previewChange(null, previewLevel);

      // when using contextual menu to edit a block,
      // it tends to make the page jump up to the top for some reason
      // so we scroll the page back to the original block
      var blockUpdateForm = $('[data-drupal-selector="layout-builder-update-block"]');
      if (blockUpdateForm.length) {

        // stop tracking
        preventTrackSections = true;

        // scroll to block
        var blockId = blockUpdateForm.attr('data-layout-builder-target-highlight-id');

        var block = $('[data-layout-block-uuid="' + blockId + '"]');

        if (block.length) {
          self.goToSection(block[0], function() {

            // var scrollPos = `-${window.scrollY}px`;
            // var closeButton = $('.ui-dialog .ui-dialog-titlebar-close');

            // // don't allow site scrolling when dialog is open
            // document.body.style.position = 'fixed';
            // document.body.style.top = scrollPos;

            // // ** closing dialog resets these settings
            // closeButton.on('click', function() {
            //   const scrollY = document.body.style.top;
            //   document.body.style.position = '';
            //   document.body.style.top = '';
            //   window.scrollTo(0, parseInt(scrollY || '0') * -1);
            // });
          });
        }

      }

      // same happens when editing Section
      var sectionUpdateForm = $('[data-drupal-selector="layout-builder-configure-section"]');
      if (sectionUpdateForm.length) {

        // stop tracking
        preventTrackSections = true;

        // scroll to block
        var sectionId = sectionUpdateForm.attr('data-layout-builder-target-highlight-id');

        var section = $('[data-layout-builder-highlight-id="' + sectionId + '"]');

        if (section.length) {
          self.goToSection(section[0], function() {

            // var scrollPos = `-${window.scrollY}px`;
            // var closeButton = $('.ui-dialog .ui-dialog-titlebar-close');

            // // don't allow site scrolling when dialog is open
            // document.body.style.position = 'fixed';
            // document.body.style.top = scrollPos;

            // // ** closing dialog resets these settings
            // closeButton.on('click', function() {
            //   const scrollY = document.body.style.top;
            //   document.body.style.position = '';
            //   document.body.style.top = '';
            //   window.scrollTo(0, parseInt(scrollY || '0') * -1);
            // });
          });
        }

      }

      // functions to execute only if modal exists
      var lbModal = $("#layout-builder-modal");
      if (typeof lbModal !== 'undefined' && lbModal.length) {
        self.fixCKEtags();
      }

      // make tooltips work in the modal forms
      var tooltips = $('.form__element__tooltip');
      if (tooltips.length) {
        self.tooltips(tooltips);
      }

      if (!behaviorReloaded) {
        behaviorReloaded = true;
      }

    },
  };

  /**
   * set the carousel output in LB preview
   * if preview content is true
   * or remove if false
   */
   self.resetCarousel = function(hasPreviewContent, settings) {

    if (hasPreviewContent) {

      if (typeof window.rocketshipUI !== 'undefined' && typeof window.rocketshipUI.setCarousels !== 'undefined') {
        window.rocketshipUI.setCarousels(settings);
      }

    } else {

      if (typeof window.rocketshipUI !== 'undefined' && typeof window.rocketshipUI.destroyCarousels !== 'undefined') {
        window.rocketshipUI.destroyCarousels();
      }

    }
  };

  /**
   * Keep track of what Section is in view while scrolling
   * so we can return to it when layout shifts
   *
   * @param {} context
   */
  self.trackSections = function(context) {

    var sections = $('.layout-builder__section', context);

    if (typeof sections !== 'undefined' && sections !== null) {

      var elements = {};

      sections.once('js-track-sections').each(function () {

        if (typeof ScrollOut !== 'undefined') {

          ScrollOut({
            targets: '.layout-builder__section',
            onShown(el, ctx, scrollingElement) {

              // only track the sections position if allowed to + there are actual sections visible
              if (preventTrackSections === false && ctx.visible === 1) {
                // add new item (or update existing)
                elements[ctx.index] = {
                  'el': el,
                  'ctx': ctx
                };

                // when preview checkbox is toggled, the 'active section' can be used to scroll to
                // the active section is determined here:

                if (Object.keys(elements).length) {

                  // if multiple Sections, loop all the elements
                  // the one with the biggest visibleY value
                  // will be the scroll-to element
                  // (1 is the highest possible value and means it's completely visible)

                  if (Object.keys(elements).length > 1) {

                    var tempItem = null,
                        tempVisibleY = 0;

                    for (var key in elements) {
                      if (elements.hasOwnProperty(key)) {
                        var item = elements[key],
                            iEl = item.el,
                            iCtx = item.ctx;

                        if (iCtx.visibleY > tempVisibleY) {
                          tempVisibleY = iCtx.visibleY;
                          tempItem = item;
                        }
                      }
                    }

                    activeScrollElement = tempItem;

                  // if only 1 element, make that the scroll-to element
                  } else {
                    // we don't know what the key is,
                    // so we make a loop and use the element that comes out
                    for (var key in elements) {
                      if (elements.hasOwnProperty(key)) {
                        activeScrollElement = elements[key];
                      }
                    }
                  }

                }

              }

            },
            onHidden: function(el, ctx, scrollingElement) {
              // only delete items if allowed to + item exists
              if (preventTrackSections === false && typeof elements[ctx.index] !== 'undefined') {
                delete elements[ctx.index];
              }
            }
          });

        }

      });

    }

  };

  /**
   * Scroll to active Section
   */
  self.goToSection = function(el, callback) {

    preventTrackSections = true;
    var activeEl = null;

    // scroll to element
    if (activeScrollElement !== null) {

      scrollTimer = setTimeout(function() {

        clearTimeout(scrollTimer);

        if (typeof el === 'undefined' || el === null) {
          activeEl = activeScrollElement.el;
        } else {
          activeEl = el;
        }

        // activeScrollElement.el.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
        window.scroll(0, self.findSectionPos(activeEl, -90));

        preventTrackSections = false;

        if (typeof callback !== 'undefined') {
          callback();
        }

      }, 500);

    }

  };

  /**
   * Perform some layout HTML or property changes on the fly
   * because it's either a pain to do by trying to change the markup
   * or because it's dynamic
   *
   * @param {*} layouts
   * @param {*} context
   */
  self.handleLayouts = function(layouts, context) {

    // find the Section actions and wrap them

    Array.prototype.forEach.call(layouts, function(layout, i) {

      var actionWrapper = layout.querySelectorAll('.layout-builder__section__actions');

      if (typeof actionWrapper === 'undefined' || actionWrapper.length === 0 || actionWrapper === null) {
        var actions = layout.querySelectorAll('.layout-builder__link:not(.layout-builder__link--add)');
        var actionsWrapper = document.createElement('DIV');

        actionsWrapper.classList.add('layout-builder__section__actions');

        if (typeof actions !== 'undefined' && actions !== null) {

          Array.prototype.forEach.call(actions, function(action, i) {
            actionsWrapper.appendChild(action);
          });
        }

        layout.insertBefore(actionsWrapper, layout.firstChild);
      }

    });

    // mark the first and last blocks in a region
    // because our styling does some things with child selectors
    // that doesn't work in Preview (since new elements get printed here)

    var layoutRegions = document.querySelectorAll('.layout-builder__region', context);

    if (typeof layoutRegions !== 'undefined' && layoutRegions !== null) {

      Array.prototype.forEach.call(layoutRegions, function(region, i){

        var contentBlocks = region.querySelectorAll('.content-block');
        var sum = 0;

        if (typeof contentBlocks !== 'undefined' && contentBlocks !== null) {
          sum = contentBlocks.length;
        }

        Array.prototype.forEach.call(contentBlocks, function(block, i){
          if (i === 0) {
            block.classList.add('first-child');
          }
          if (i === sum - 1) {
            block.classList.add('last-child');
          }
        });

      });
    }

  };

  /**
   * Finds y value of given object
   *
  */
  self.findSectionPos = function(obj, offset) {
    var curtop = 0 + offset;
    if (obj.offsetParent) {
        do {
          curtop += obj.offsetTop;
        } while (obj = obj.offsetParent);
    return [curtop];
    }
  };

  /**
   * Webform elements for blocks and sections in the layout builder forms
   * have tooltip set to TRUE via _preprocess_form_element
   * If the theme has the correct output in Twig (in _form-element.twig),
   * we can add some interactions for it
   *
   */
   self.tooltips = function(tooltips) {

    tooltips.once('js-once-tooltips').each(function () {
      var tooltip = $(this),
        button = tooltip.find('.form__element__tooltip__button'),
        message = tooltip.find('.form__element__tooltip__message');

      // on click
      button.on('click', function(e) {

        if (button.attr('aria-expanded') === 'true') {
          self.tooltipToggle(button, message, false);
        } else {
          self.tooltipToggle(button, message, true);
        }

        e.preventDefault();

      });

      // on mouseover/out
      button.on('mouseover', function() {
        self.tooltipToggle(button, message, true);
      });
      button.on('mouseout', function() {
        self.tooltipToggle(button, message, false);
      });

      // on focus
      button.on('focus', function() {
        self.tooltipToggle(button, message, true);
      });

      // on blur
      button.on('blur', function() {
        self.tooltipToggle(button, message, false);
      });

      // on escape
      button.on('keydown', function(e) {

        if(e.keyCode==27) {
          self.tooltipToggle(button, message, false);
        }

      });

    });
  };

  self.tooltipToggle = function(button, message, show) {
    if (show) {
      message.css('visibility', 'visible');
      button.attr('aria-expanded', 'true');
    } else {
      message.css('visibility', 'hidden');
      button.attr('aria-expanded', 'false');
    }
  }

  /**
   * issues
   * - https://www.drupal.org/project/drupal/issues/3095304
   * - https://www.drupal.org/project/drupal/issues/3211961
   */
  self.fixCKEtags = function() {
    var origBeforeSubmit = Drupal.Ajax.prototype.beforeSubmit;
    Drupal.Ajax.prototype.beforeSubmit = function (formValues, element, options) {
      if (typeof(CKEDITOR) !== 'undefined' && CKEDITOR.instances) {
        var instances = Object.values(CKEDITOR.instances);
        if (typeof instances === 'object' && instances.length) {
          instances.forEach(editor => {
            if (typeof formValues === 'object' && formValues.length) {
              formValues.forEach(formField => {
                // Get field name from the id in the editor so that it covers all
                // fields using ckeditor.
                var editorElement = document.querySelector(`#${editor.name}`)
                if (editorElement) {
                  var fieldName = editorElement.getAttribute('name');
                  if (formField.name === fieldName) {
                    formField.value = editor.getData();
                  }
                }
              });
            }
          });
        }
      }

      return origBeforeSubmit.apply(this, arguments);
    };

  };

})(jQuery, Drupal, Sortable);
