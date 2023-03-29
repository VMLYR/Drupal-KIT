import React from 'react';

const merge = require('deepmerge');

import cbUSPTwig from './usp.twig';
// import cbUSPVariantsAlignmentTwig from './text--variants-alignment.twig';

import cbUSPData from './usp.yml';
import imageData from './image-override.yml';
// import cbUSPAlignmentData from './text--variants-alignment.yml';

// ** Media with image

// merge globally stored Media field (image variant) with the Media field of our block
cbUSPData.fields.media = window.styleguide.components.atoms.media.image;
// we want to override the image size, so we merge old and new image data
const imageField = {...cbUSPData.fields.media.reference.field, ...imageData};
// replace the image with our prev. merged image data
cbUSPData.fields.media.reference.field = imageField;


// // ** Merge USP variants data with the main block data

// var cbUSPVariantsAlignmentObject = {variants: {}};
// // loop the variants
// for (var key in cbUSPAlignmentData.variants) {
//   if (typeof cbUSPAlignmentData.variants[key] !== 'undefined') {
//     // merge the block data into the variant and add to a new object
//     cbUSPVariantsAlignmentObject.variants[key] = merge(cbUSPData, cbUSPAlignmentData.variants[key]);
//   }
// }

// save in a global var for reuse in other components
window.styleguide.components.molecules.usp = {
  base: JSON.parse(JSON.stringify(cbUSPData))/*,
  alignments: JSON.parse(JSON.stringify(cbUSPVariantsAlignmentObject))*/
};

// documentation
import mdx from './usp.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/005 USP',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbUSPTwig(cbUSPData) }} />
);
