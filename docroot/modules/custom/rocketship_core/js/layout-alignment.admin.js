/**
 * @file
 * Behaviors of the background color field.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.RocketshipCoreAdminAlignment = {
      attach: function (context) {

        var groups = $('[id*="edit-layout-settings-vertical-alignment"], [id*="edit-layout-settings-alignments-wrapper-vertical-alignment"]');

        if (groups.length) {

          groups.each(function() {

            var group = $(this);

            group.addClass('layout-field-vertical-alignment');

            group.find('input:radio').each(function() {
              var myGroup = $(this);
              var optionLabel = $(this).next('label');
              var text = $(this).val();
              var className =  'alignment-' + text.replace('/', '-').replace(' ', '');

              optionLabel.addClass(className);

              if (optionLabel.parent().find('.text').length < 1) {
                optionLabel.parent().append('<div class="text">' + optionLabel.html() + '</div>');
              }

            });

          });
        }

      }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
