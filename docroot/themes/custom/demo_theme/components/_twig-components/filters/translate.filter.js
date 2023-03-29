/**
 * Faking the Drupal translation '|t' because TwigJS thinks it's a filter
 */

'use strict';


const translateTwigFilter = function (Twig) {

  Twig.extendFilter("t", function(value) {

    // just return the original string, since we're not going to do actual translations in the Styleguide

    return value;
  });

}

export default translateTwigFilter;
