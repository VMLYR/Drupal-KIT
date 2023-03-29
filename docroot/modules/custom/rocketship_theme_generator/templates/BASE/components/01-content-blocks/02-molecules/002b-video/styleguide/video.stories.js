import React from 'react';

const merge = require('deepmerge');

import cbVideoTwig from './video.twig';
import cbVideoVariantsTwig from './video--variants.twig';

import cbVideoData from './video.yml';
import cbSizeData from './video--variants-layouts.yml';


// // ** Media with video

// // merge globally stored Media field (video variant) with the Media field of our block
// cbVideoData.fields.media = window.styleguide.components.atoms.media.video;

// // ** Merge Media variants data with the main block data

// var cbVideoVariantsSizeObject = {variants: {}};
// // loop the variants
// for (var key in cbSizeData.variants) {
//   if (typeof cbSizeData.variants[key] !== 'undefined') {
//     // merge the block data into the variant and add to a new object
//     cbVideoVariantsSizeObject.variants[key] = merge(cbVideoData, cbSizeData.variants[key]);
//   }
// }


// ** Media with video

// merge globally stored Media field (video variant) with the Media field of our block
cbVideoData.fields.media = window.styleguide.components.atoms.media.video;

// ** Merge Media variants data with the main block data

var cbVideoVariantsSizeObject = {variants: {}};
// loop the variants
for (var key in cbSizeData.variants) {
  if (typeof cbSizeData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbVideoVariantsSizeObject.variants[key] = merge(cbVideoData, cbSizeData.variants[key]);
  }
}

// save in a global var for reuse in other components
window.styleguide.components.molecules.media = window.styleguide.components.molecules.media || {};
window.styleguide.components.molecules.media.video = {
  base: JSON.parse(JSON.stringify(cbVideoData)),
  layouts: JSON.parse(JSON.stringify(cbVideoVariantsSizeObject))
};

// documentation
import mdx from './video.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/002b-Video',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbVideoTwig(cbVideoData) }} />
);

export const size = () => (
  <div dangerouslySetInnerHTML={{ __html: cbVideoVariantsTwig(cbVideoVariantsSizeObject) }} />
);
