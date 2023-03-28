/**
 * @file
 * Behaviors of the background color field.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.RocketshipCoreAdminColumns = {
      attach: function (context) {

        var groups = $('[id*="edit-layout-settings-col-sizing"], [id*="edit-layout-settings-columns-wrapper-col-sizing"]');

        if (groups.length) {

          groups.each(function() {

            var group = $(this);

            group.addClass('layout-field-col-sizing');

            group.find('input:radio').each(function() {
              var myGroup = $(this);
              var optionLabel = $(this).next('label');
              var text = $(this).val();
              var labelParts = text.split(' + ');
              var className =  'columns-' + labelParts[0].replace('/', '-').replace(' ', '');

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
