import React from 'react';

const merge = require('deepmerge');

import cbImageTwig from './image.twig';
import cbImageVariantsLayoutsTwig from './image--variants.twig';

import imageData from './image-override.yml';
import cbImageData from './image.yml';
import cbLayoutsData from './image--variants-layout.yml';
import cbRatiosData from './image--variants-ratios.yml';


// // ** Media with image

// // merge globally stored Media field (image variant) with the Media field of our block
// cbImageData.fields.media = window.styleguide.components.atoms.media.image;
// // we want to override the image size, so we merge old and new image data
// const imageField = {...cbImageData.fields.media.reference.field, ...imageData};
// // replace the image with our prev. merged image data
// cbImageData.fields.media.reference.field = imageField;

// // ** Merge B002 Image variants data with the main block data

// var cbImageVariantsLayoutsObject = {variants: {}};
// // loop the variants
// for (var key in cbLayoutsData.variants) {
//   if (typeof cbLayoutsData.variants[key] !== 'undefined') {
//     // merge the block data into the variant and add to a new object
//     cbImageVariantsLayoutsObject.variants[key] = merge(cbImageData, cbLayoutsData.variants[key]);
//   }
// }


// ** Media with image

// merge globally stored Media field (image variant) with the Media field of our block
cbImageData.fields.media = window.styleguide.components.atoms.media.image;
// we want to override the image size, so we merge old and new image data
const imageField = {...cbImageData.fields.media.reference.field, ...imageData};
// replace the image with our prev. merged image data
cbImageData.fields.media.reference.field = imageField;

// ** Merge Media variants data with the main block data

var cbImageVariantsLayoutsObject = {variants: {}};
// loop the variants
for (var key in cbLayoutsData.variants) {
  if (typeof cbLayoutsData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbImageVariantsLayoutsObject.variants[key] = merge(cbImageData, cbLayoutsData.variants[key]);
  }
}

// ** Merge Media variants data with the main block data

var cbImageVariantsRatiosObject = {variants: {}};
// loop the variants
for (var key in cbRatiosData.variants) {
  if (typeof cbRatiosData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbImageVariantsRatiosObject.variants[key] = merge(cbImageData, cbRatiosData.variants[key]);
  }
}

// save in a global var for reuse in other components
window.styleguide.components.molecules.media = window.styleguide.components.molecules.media || {};
window.styleguide.components.molecules.media.image = {
  base: JSON.parse(JSON.stringify(cbImageData)),
  layouts: JSON.parse(JSON.stringify(cbImageVariantsLayoutsObject)),
  ratios: JSON.parse(JSON.stringify(cbImageVariantsRatiosObject)),
};

// import libary files - TO DO: import from RS Core library files
import './cb_media.js'

// documentation
import mdx from './image.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/002a-Image',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbImageTwig(cbImageData) }} />
);

export const layouts = () => (
  <div dangerouslySetInnerHTML={{ __html: cbImageVariantsLayoutsTwig(cbImageVariantsLayoutsObject) }} />
);
