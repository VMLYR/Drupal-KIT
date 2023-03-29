'use strict';

import { values } from "ramda";

/**
 *
 *
 *
 * @param {} Twig
 */

const iconTwigExtension = function (Twig) {

  Twig.extendFunction("icon", function(name = '', title = false, classes = []) {

    function toTitleCase(str) {
      return str.replace(
        /\w\S*/g,
        function(txt) {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        }
      );
    }

    // if no title added, use the icon name but capitalize
    if (typeof title === 'undefined' || title === false || title.trim().length === 0) {
      title = toTitleCase(name);
    }

    // if $classes overridden with false or is a string, make it an empty array
    // to avoid getting errors or printing blank space
    if (typeof classes === 'undefined' || classes === false || typeof classes === 'string') {
      classes = [];
    }

    // flatten the array to a string to better print it in the html blob
    let flatClasses = '';
    if (Array.isArray(classes) && classes.length) {
      for (var i in classes) {
        if (classes.hasOwnProperty(i)) {
          // append each class with a space, to create the flattened list
          var myClass = classes[i];
          flatClasses = flatClasses + ' ' + myClass;
        }
      }
    }

    const template = '<span class="wrapper--rs-icon"><svg class="rs-icon rs-icon--' + name + flatClasses + '" role="img" aria-hidden="true" title="' + title + '" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="images/generated/sprite-inline.svg#rs-icon--' + name + '"></use></svg></span>';

    return template;

  });
}

export default iconTwigExtension;
