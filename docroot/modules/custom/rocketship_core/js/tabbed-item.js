(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.tabbed_item = {
    attach: function (context, settings) {

      function handleTabTitle() {
        var value = $(this).val();
        var tab = $(this).parents('details').first().find('summary');
        var default_value = tab.html().substr(0, tab.html().indexOf(': ') + 2);
        var html = default_value + value;
        if (html.length > 125) {
          html = html.substr(0, 125) + '...';
        }
        tab.html(html);
      }

      $('.tabbed-title').each(handleTabTitle);

      $('.tabbed-title').once('tabbed-item').keyup(handleTabTitle);
    }
  }
})(jQuery, Drupal);
