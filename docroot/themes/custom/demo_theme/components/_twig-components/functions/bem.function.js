'use strict';

import { values } from "ramda";

/**
 *
 * Some limitations: because the JS function doesn't have access to the Drupal context, there are some things this doesn't do.
 *
 * - this function DOESN'T print out the rest of your item's attributes by default,
 *    so if you need them in the Styleguide, you need to add them yourself (messy)
 *    OR pass them as a 5th variable
 *
 * This is purely for Storybook to handle, in Drupal it works just fine
 *
 * @param {} Twig
 */

const bemTwigExtension = function (Twig) {

  Twig.extendFunction("bem", function(blockname = false, element = false, modifiers = [], extra = [], attributes = {}) {
    let classes = [];

    // If using a blockname to override default class.
    if (blockname !== false && blockname !== null) {
      // Set blockname class.
      if (element !== false && element !== null) {
        classes.push(blockname + '__' + element);
      } else {
        classes.push(blockname);
      }

      // Set blockname--modifier classes for each modifier.
      if (modifiers.length && Array.isArray(modifiers)) {
        modifiers.forEach(function(modifier) {
          if (element !== false && element !== null) {
            classes.push(blockname + '__' + element + '--' + modifier);
          } else {
            classes.push(blockname + '--' + modifier);
          }
        });
      }
    }
    // If not overriding base class.
    else {
      // Set base class.
      if (element !== false && element !== null) {
        classes.push(element);
      }

      // Set base--modifier class for each modifier.
      if (modifiers !== null && modifiers !== false && modifiers.length && Array.isArray(modifiers)) {
        modifiers.forEach(function(modifier) {
          if (element !== false && element !== null) {
            classes.push(element + '--' + modifier);
          }
        });
      }
    }

    // If extra non-BEM classes are added.
    if (extra.length && Array.isArray(extra)) {
      extra.forEach(function(extra_class) {
        classes.push(extra_class);
      });
    }



    // If an attributes array was passed, go look for classes inside of it and push that to our other classes array
    if (typeof attributes !== 'undefined' && typeof attributes === 'object' && attributes !== null) {

      for (var key in attributes) {
        if (attributes.hasOwnProperty(key)) {

          var item = attributes[key];

          if (key === '_keys') {
            delete attributes['_keys'];
          }

          // If there are classes, add them to the classes array.
          if (key === 'class') {

            if (Array.isArray(item)) {

              for (var i in item) {
                if (item.hasOwnProperty(i)) {

                  var myClass = item[i];
                  classes.push(myClass);
                }
              }
            } else {
              classes.push(item);
            }

            // remove 'class' object from existing attributes array
            // attributes.splice(key, 1);
            delete attributes['class'];

          } else {
            // any item values that are themselves an array, turn them into a string
            if (Array.isArray(item)) {
              item.join(' ');
            }
          }

        }
      }

    } else {
      var attributes = {};
    }

    // push our complete classes into attributes array
    attributes.class = classes.join(' ');

    var attributesString = '';

    // turn attributes into a string
    for (var key in attributes) {
      if (attributes.hasOwnProperty(key)) {
        attributesString += ' ' + key + '="' + attributes[key] + '"';
      }
    }
    // print the complete attributes
    return attributesString;
  });
}

export default bemTwigExtension;
