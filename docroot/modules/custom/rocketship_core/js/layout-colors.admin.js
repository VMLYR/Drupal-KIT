/**
 * @file
 * Behaviors of the background color field.
 */

(function ($, _, Drupal, drupalSettings) {
    "use strict";
    Drupal.behaviors.RocketshipCoreAdminColors = {
        attach: function (context) {

          var groups = $('[id*="edit-layout-settings-background-color"], [id*="edit-layout-settings-background-wrapper-background-color"]');

          if (groups.length) {

            groups.each(function() {

              var group = $(this);

              group.addClass('layout-field-bg-color');

              group.find('input:radio').each(function() {
                var optionLabel = $(this).next('label');
                var colors = $(this).val().split('/');

                if (colors[0] === '_none') {
                  optionLabel.addClass(colors[0]);

                } else {

                  var name = colors[0];
                  var foreground = colors[1];
                  var background = colors[2];

                  optionLabel.addClass(name);
                  optionLabel.css({'background-color': '#' + background, 'color': '#' + foreground});

                }

              });

            });
          }

        }
    };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
