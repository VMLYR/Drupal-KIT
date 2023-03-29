import React from 'react';

const merge = require('deepmerge');

import cbTestimonialTwig from './testimonial.twig';
import cbTestimonialVariantsTwig from './testimonial--variants.twig';

import cbTestimonialData from './testimonial.yml';
import cbTestimonialVariantsData from './testimonial--variants.yml';

// ** Merge B001 testimonial variants data with the main block data

var cbTestimonialVariantsObject = {variants: {}};
// loop the variants
for (var key in cbTestimonialVariantsData.variants) {
  if (typeof cbTestimonialVariantsData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbTestimonialVariantsObject.variants[key] = merge(cbTestimonialData, cbTestimonialVariantsData.variants[key]);
  }
}

// save in a global var for reuse in other components
window.styleguide.components.molecules.testimonial = {
  base: JSON.parse(JSON.stringify(cbTestimonialData)),
  variants: JSON.parse(JSON.stringify(cbTestimonialVariantsObject))
};

// documentation
import mdx from './testimonial.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/004-Testimonial',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTestimonialTwig(cbTestimonialData) }} />
);

export const variants = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTestimonialVariantsTwig(cbTestimonialVariantsObject) }} />
);
