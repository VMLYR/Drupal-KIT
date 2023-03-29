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
  // Behavior for Tabs: triggers
  ///////////////////////////////////////////////////////////////////////

  Drupal.behaviors.rocketshipUITables = {
    attach: function (context, settings) {

      // !!! IF YOU DON'T WANT A SPECIFIC TABLE TO BE RESPONSIVE, SET THE 'RESPONSIVE' ATTRIBUTE TO FALSE
      //     VIA A THEME FUNCTION OR IN AN OVERRIDDEN TWIG TEMPLATE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
      //
      // (except for CKE tables, those are purely done via the JS below)

      var tables = $('table', context);

      // Make responsive tables
      // in the Sass: choose between 2 types: 'reformatted' or 'scroll'
      if (tables.length) self.setresponsiveTables(tables);

    }
  };

  ///////////////////////////////////////////////////////////////////////
  // Behavior for Tabs: functions
  ///////////////////////////////////////////////////////////////////////

  /**
   * Make tables responsive
   *
   */

  self.setresponsiveTables = function(tables) {

    // Add responsive functionality to tables added via WYSIWYG
    // or otherwise inserted in the page (eg. from Commerce)

    // if coming from CKE
    var ckeClass = '.cke_show_borders, .text-long';

    tables.once('js-once-set-responsive-tables').each(function() {

      var table = $(this);
      var pageTable = table.closest('.page').length;

      // for tables in Styleguide as well
      if (!pageTable) {
        pageTable = table.closest('.sb-show-main').length;
      }

      if(pageTable) {

        // if already has a responsive class, define the type
        // based on if it's coming from CKE or not

        // if table in CKE, we need to take into account
        // the style that the user has set on it:
        if (table.closest(ckeClass).length) {

          // set extra attributes to help us with restyling for mobile
          self.tableSetAttributes(table);

          // unlikely situation, but possible:
          // user has set a class that prevents the 'has-scroll' behavior
          // so don't trigger that function and do another behavior
          if (table.hasClass('table--reformatted')) {
            if (!table.closest('div').hasClass('table-responsive')) {
              table.wrap('<div class="table-responsive is-reformatted"></div>');
            }

            // default CKE table behavior we want:
          } else {
            if (!table.closest('div').hasClass('table-responsive')) {
              table.wrap('<div class="table-responsive has-scroll"></div>');
            }

            // set a bunch of stuff using JS to make the table scrollable in different ways based on the th structure
            self.tableScroll(table, table.closest('.table-responsive'));
          }

          // if not in CKE,
          // your table will either have classes coming from Twig & theme functions,
          // or won't have any at all
        } else {

          // if table not already wrapped by the theme's twig, wrap it & add has-scroll class by default
          // if (!table.closest('div').hasClass('table-responsive')) {
          //   table.wrap('<div class="table-responsive has-scroll"></div>');

          //   // set a bunch of stuff using JS to make the table scrollable in different ways based on the th structure
          //   self.tableScroll(table, table.closest('.table-responsive'));

          //   // table is wrapped by twig, and set to 'has-scroll', needs some JS help
          // } else {

          // we only set tables to be responsive, if they have the correct wrapper class
          // which was set in Twig based on the 'responsive' attribute
          // which can be set or overridden using a theme hook
          if (table.closest('.table-responsive').hasClass('has-scroll')) {
            self.tableScroll(table, table.closest('.table-responsive'));
          }

        }
      }

    });
  };

  /**
   * Modifications to table classes to make responsive styling easier
   *
   * - Check various combinations of th's in thead, tfoot or tbody - as first or last row/column
   *   and add classes accordingly
   * @param table
   */
  self.tableScroll = function(table, wrapper) {

    // if th in thead OR tfoot (no combo)
    // make all cells go under each other + space on left (or right, if tfoot)

    // else, if th in tbody + all in 1 column (diff rows but first OR last index of each row)
    // no combo of first or last
    // make the first (or last) cell fixed, with padding on the correct side so user can scroll

    // Move the caption

    var caption = table.find('caption');

    caption.insertBefore(wrapper);

    var trs = table.find('tr'),
      thRow = false,
      thHead = false,
      thFoot = false,
      thCol = false,
      thLeft = false,
      thRight = false,
      heightMax = 0;

    // loop rows
    trs.once('js-once-check-th-position').each(function(i) {

      var tr = $(this);

      // loop cells
      var ths = table.find('th');

      ths.once('js-once-check-th-position').each(function(j) {

        var th = $(this);

        // we have th's in the left column of the table body
        if (j === 0 && th.parent('tr').parent('tbody').length) {
          thCol = true;
          thLeft = true;
        }

        // we have th's in the right column of the table body
        if (j === (ths.length - 1) && th.parent('tr').parent('tbody').length) {
          thCol = true;
          thRight = true;
        }

        // we have th's in the head row
        if ( th.closest('thead').length ) {
          thRow = true;
          thHead = true;
        }

        // we have th's in the head row
        if ( th.closest('tfoot').length ) {
          thRow = true;
          thFoot = true;
        }

      });

    });

    // th's in thead or tfoot => row becomes a fixed column
    if (thRow) {

      wrapper.addClass('js-table--th-row');

      if (thHead) {
        wrapper.addClass('js-table--th-top');
      } else {
        wrapper.addClass('js-table--th-bottom');
      }

      sizeCells(table);

      window.rocketshipUI.optimizedResize().add(function() {
        sizeCells(table);
      });

      // th's in body on left or right (but only without th's in thead or tfoot)
      // column cells become fixed to left or right
    } else if (thCol) {

      wrapper.addClass('js-table--th-col');

      if (thLeft) {
        wrapper.addClass('js-table--th-left');
      } else {
        wrapper.addClass('js-table--th-right');
      }

      // wrap th's in divs to be able to position them
      var ths = table.find('th');

      ths.each(function() {
        var th = $(this),
          thHtml = th.html();

        th.wrapInner('<div class="th__content"></div>');
      });

      sizeTh(ths);

      window.rocketshipUI.optimizedResize().add(function() {
        sizeTh(ths);
      });

    } else {
      wrapper.addClass('js-table--no-th');
    }

    // ** resize cells to match content

    function sizeCells(table) {

      // loop cells
      var ths = table.find('th'),
        cells = table.find('th, td');

      for (var i = 0; i < cells.length; i++) {

        var cell = cells[i];

        $(cell).css('height', 'auto');
        $(cell).css('min-height', '0');

        var cellHeight = $(cell).outerHeight();

        if (cellHeight > heightMax) {
          heightMax = cellHeight;
        }
      }

      cells.each(function() {
        $(this).css('min-height', heightMax + 'px');
      });
    }

    // ** resize th content based on tr
    function sizeTh(ths) {

      ths.each(function() {

        var th = $(this);
        var thContent = th.find('.th__content');

        thContent.css('min-height', th.closest('tr').outerHeight() + 'px');

      });

    }

  };

  /**
   * Modifications to table attributes to make responsive styling easier
   *
   * Normally, this stuff is handled by the default Twig templates for tables
   * but some modules or custom stuff has their own Twig and so they don't get the same treatment
   * This JS should modify those tables so they are in line with what is expected
   *
   * - Add some data-attributes or other elements
   *   to make responsive styling easier
   *   eg. data-title on each cell, that matches a th's text
   * @param table
   */
  self.tableSetAttributes = function(table) {

    // make data-attributes for CSS to use as headings
    // if th's in thead
    var tableHead = table.find('thead');
    if(tableHead.length) {
      tableHead.attr('tabindex', '0');
      var headings = [];
      table.find('th').each(function(){
        headings.push($(this).text());
      });
      var count = 0;
      table.find('tr').each(function(){
        // table.find('td').attr('data-title', headings[count-1]);
        // ++count;
        count = 0;
        $(this).find('td').each(function() {
          $(this).attr('data-title', headings[count]);
          ++count;
        });
      });
    } else {
      // if th's in tbody
      var tableBody = table.find('tbody');
      if(tableBody.length) {
        tableBody.attr('tabindex', '0');
      }
      if(table.find('th').length) {
        table.find('tr').each(function(){
          var heading = $(this).find('th').text();
          table.find('td').each(function(){
            $(this).attr('data-title', heading);
          });
        });
        // if no th's at all, don't need certain styling on mobile
      } else {
        table.addClass('no-th');
      }
    }
  };

})(jQuery, Drupal, window, document);
