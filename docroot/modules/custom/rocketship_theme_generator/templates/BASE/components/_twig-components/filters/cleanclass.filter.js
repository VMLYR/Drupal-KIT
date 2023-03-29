/**
 * Faking the Drupal translation '|t' because TwigJS thinks it's a filter
 */

'use strict';


const cleanClassTwigFilter = function (Twig) {

  Twig.extendFilter("clean_class", function(value) {

    // TO DO: remove invalid characters (+ doublecheck if Drupal's filter does more)

    if (typeof value !== 'undefined' && typeof value === 'string') {

      let re = new RegExp('_', 'g');
      let re2 = new RegExp(' ', 'g');

      let newVal = value.replace(re, '-');
      let newVal2 = newVal.replace(re2, '-');
      let newVal3 = newVal2.toLowerCase();

      return newVal3;
    }

    return value;

  });

}

export default cleanClassTwigFilter;
