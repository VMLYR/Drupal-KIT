import React from 'react';

const merge = require('deepmerge');

import sectionVariantsTwig from './section--4-col--variants.twig';

import sectionBackgroundImage from './background-image.yml';
import sectionData from './section--4-col.yml';
import sectionVariantsData from './section--4-col--variants-background.yml';

// ** Import image data into the correct background variants

for (var key in sectionVariantsData.variants) {
  if (typeof sectionVariantsData.variants[key] !== 'undefined') {
    if (typeof sectionVariantsData.variants[key].background_image !== 'undefined') {
      sectionVariantsData.variants[key].layout_background_image = sectionBackgroundImage;
    }
  }
}

// ** Merge Text block variants data, as well as the sections variants

var cbTextData = window.styleguide.components.molecules.text.alignments;
var sectionVariantsTextObject = sectionVariants.get(cbTextData, 'cb_text', sectionData, sectionVariantsData);

// ** Merge USP block variants data, as well as the sections variants

var cbUSPData = window.styleguide.components.molecules.usp.base;
var sectionVariantsUSPObject = sectionVariants.get(cbUSPData, 'cb_usp', sectionData, sectionVariantsData);


// Libraries
import '../../00-section/styleguide/layouts.js';

// Extra component styling
import '../../../../00-theme/05-pages/00-styleguide/pages.scss';

// documentation
import mdx from './section--4-col.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Content Sections/4 columns/Backgrounds',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** Section in 3 columns

// -- Background color

// Text + other
export const with001Text = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsTextObject, ...{'block_type': 'cb_text'}}) }} />
);

// USP
export const with005USP = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsUSPObject, ...{'block_type': 'cb_usp'}}) }} />
);
