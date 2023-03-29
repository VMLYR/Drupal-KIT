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
  // Behavior for Forms: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUIForms = {
    attach: function (context, settings) {

      var input = $('input', context),
        select = $('select', context),
        textarea = $('textarea', context);

      // handle focus on form elements
      if (input.length || textarea.length) self.focusFields(input, textarea, select);

      // wrap select tag to add custom styling and arrow
      if (select.length) self.customSelect(select);

      // detect text scroll in textarea so we can hide floating label
      if (textarea.length) self.textareaScroll(textarea);

      // check form states
      self.stateCheck();
    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Forms: functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Add focus styles to form elements
   *
   */
  self.focusFields = function (input, textarea, select) {

    // if input exists
    if (input.length) {
      // iterate all inputs
      input.once('js-once-input').each(function() {
        var input = $(this);

        var wrapper = '.form__element';

        if ( input.closest(wrapper).length < 1 ) {
          wrapper = '.form-wrapper';
        }

        // add an active class on wrapper and label if focus
        input.focus(function(e) {
          var input = $(this);
          input.closest(wrapper).addClass('is-active').find('label').addClass('is-active');

          // some forms don't have wrappers with a class,
          // just use parent instead
          if (input.attr('name') == 'search_block_form') {
            input.parent().addClass('is-active').find('label').addClass('is-active');
          }

          // remove active class on blur
        }).blur(function(e) {
          var input = $(this);

          input.closest(wrapper).removeClass('is-active').find('label').removeClass('is-active');
          if (input.attr('name') == 'search_block_form') {
            input.parent().removeClass('is-active').find('label').removeClass('is-active');
          }

          // if field has a value, add a has-value class (handy for floating labels)
          if (input.val()) {
            input.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
            if (input.attr('name') == 'search_block_form') {
              input.parent().addClass('has-value').find('label').addClass('has-value');
            }
            // remove value class if empty
          } else {
            input.closest(wrapper).removeClass('has-value').find('label').removeClass('has-value');
            if (input.attr('name') == 'search_block_form') {
              input.parent().removeClass('has-value').find('label').removeClass('has-value');
            }
          }
        });

        // if field has a value, add a has-value class (handy for floating labels)
        if (input.val()) {
          input.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
          if (input.attr('name') == 'search_block_form') {
            input.parent().addClass('has-value').find('label').addClass('has-value');
          }
        }
      });
    }

    if (textarea.length) {
      textarea.once('js-once-textarea').each(function() {
        var textarea = $(this);

        var wrapper = '.form__element';

        if ( textarea.closest(wrapper).length < 1 ) {
          wrapper = '.form-wrapper';
        }

        // add an active class on wrapper and label if focus
        textarea.focus(function(e) {
          var textarea = $(this);
          textarea.closest(wrapper).addClass('is-active').find('label').addClass('is-active');

          // remove active class on blur
        }).blur(function(e) {
          var textarea = $(this);
          textarea.closest(wrapper).removeClass('is-active').find('label').removeClass('is-active');

          // if textarea has a value, add a has-value class (handy for floating labels)
          if (textarea.val()) {
            textarea.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
            // remove value class if empty
          } else {
            textarea.closest(wrapper).removeClass('has-value').find('label').removeClass('has-value');
          }
        });

        // if field has a value, add a has-value class (handy for floating labels)
        if (textarea.val()) {
          textarea.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
        }
      });
    }

    if (select.length) {
      select.once('js-once-select').each(function() {

        var select = $(this);

        var wrapper = '.form__element';

        if ( select.closest(wrapper).length < 1 ) {
          wrapper = '.form-wrapper';
        }

        select.focus(function(e) {
          var select = $(this);
          select.parent().parent().find('label').addClass('is-active');

        }).blur(function(e) {
          var select = $(this);
          select.closest(wrapper).removeClass('is-active').find('label').removeClass('is-active');

          if (select.val()) {
            select.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
          } else {
            select.closest(wrapper).removeClass('has-value').find('label').removeClass('has-value');
          }
        });

        if (select.val()) {

          // if value is not a string but data of some sort, check for it to be filled
          if ((typeof select.val() === 'object' && select.val().length) > 0) {

            select.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
          }

          // if value as a string
          if (typeof select.val() !== 'object') {

            select.closest(wrapper).addClass('has-value').find('label').addClass('has-value');
          }

        }
      });
    }
  };

  /*
   * wrap select in order to create custom styling for arrow and such
   */
  self.customSelect = function (select) {
    select.once('js-once-select-wrap').each(function() {
      if($(this).closest('.form__dropdown').length < 1) {
        $(this).wrap('<div class="form__dropdown"></div>');
      }
    });
  };

  /*
   * detect text scroll in textarea so we can hide floating label
   *
   * when text top is not 0, hide the floating label (set a class on field)
   */
  self.textareaScroll = function (textarea) {

    textarea.once('js-once-textarea-scroll').each(function() {

      var myTextarea = $(this);

      var checkScrollPosition = function(self) {
        // if text scrolls by, set a class on field
        // could be used to hide the label or do something else
        if (self.scrollTop > 0) {
          myTextarea.closest('.form__element').addClass('js-scrolling');
        } else {
          myTextarea.closest('.form__element').removeClass('js-scrolling');
        }
      };

      checkScrollPosition(myTextarea[0]);

      myTextarea.on('scroll', function() {
        checkScrollPosition(this);
      });
    });
  };

   /*
   * Make our optional functionality work with states
   */
  self.stateCheck = function() {

    $(document).on('state:required', function (e) {

      if (e.trigger && typeof $(e.target).isWebform === 'function' && $(e.target).isWebform()) {
        var target = $(e.target),
            label = target.parent('.form-item').find('.form__label'),
            requiredEl = label.find('.form__label__required'),
            optionalEl = label.find('.form__label__not-required');

        if (e.value) {
          // hide the 'optional' element via aria & show the 'required' one
          if (typeof requiredEl !== 'undefined') {
            requiredEl.removeAttr('aria-hidden');
          }
          if (typeof optionalEl !== 'undefined') {
            optionalEl.attr('aria-hidden', true);
          }
        } else {
          // hide the 'required' element via aria & show the 'optional' one
          if (typeof optionalEl !== 'undefined') {
            optionalEl.removeAttr('aria-hidden');
          }
          if (typeof requiredEl !== 'undefined') {
            requiredEl.attr('aria-hidden', true);
          }
        }
      }
    });
  };

})(jQuery, Drupal, window, document);
