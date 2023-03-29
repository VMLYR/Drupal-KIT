'use strict';

import { values } from "ramda";

/**
 * Note 1: not really working as expected, need to fix this to get 'addClass' working in Storybook
 * Note 2: we lose our original attributes
 *       (much like how the BEM function works and - possibly - the 'add_attributes' function)
 *       need to try and find a workaround somehow
 *
 * @param {} Twig
 */

const addClassTwigExtension = function (Twig) {

  Twig.extendFunction("addClass", function(myClass = '', attributes = '') {

    attributes = [];

    if (typeof myClass === 'string') {
      if (myClass.includes('=')) {
        attributes.push(myClass);
      }
      else {
        attributes.push(' class' + '="' + myClass + '"');
      }
    } else if (myClass.isArray()) {
      var classes = myClass.join(' ');
      attributes.push(' class' + '="' + classes + '"');
    }

    return attributes.join(' ');

  });
}

export default addClassTwigExtension;
