/**
 * Rocketship UI JS
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

  Drupal.behaviors.rocketshipUI_cbFAQ = {
    attach: function (context, settings) {

      var faq = $('.block--type-cb-faq .field__item--type-tabbed-item', context);
      if (faq.length) self.faqCollapsable(faq);

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: functions
  ///////////////////////////////////////////////////////////////////////

  /*
   *
   * Open/close FAQ items
   */
  self.faqCollapsable = function (faq) {

    faq.once('js-once-faq-collapsable').each(function () {

      var faqItem = $(this),
        trigger = faqItem.find('.tab-item__title'),
        target = faqItem.find('.tab-item__content');

      // alternative field names for faq title
      if (typeof trigger == 'undefined' || trigger.length < 1) {
        trigger = faqItem.find('h2:first-child, h3:first-child');
      }

      trigger.on('click', function () {

        // close item
        if (faqItem.hasClass('js-open')) {
          faqItem.removeClass('js-open');
          target.stop( true, true ).slideUp(250, function () {
            //callback
          });
          // open item
        } else {
          faqItem.addClass('js-open');
          target.stop( true, true ).slideDown(250, function () {
            //callback
          });

          // close all siblings
          faqItem.siblings().each(function () {
            var sibling = $(this);

            if (sibling.hasClass('js-open')) {
              sibling.removeClass('js-open');
              sibling.find('.tab-item__content').stop( true, true ).slideUp(250, function () {
                //callback
              });
            }
          });
        }

      });

    });

  };

})(jQuery, Drupal, window, document);
