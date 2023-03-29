import React from 'react';

const merge = require('deepmerge');

import cbPhotoGalleryTwig from './photo-gallery.twig';
import cbPhotoGalleryVariantsLayoutsTwig from './photo-gallery--variants.twig';

import cbPhotoGalleryData from './photo-gallery.yml';
import cbGridData from './photo-gallery--variants-grid.yml';
import cbMasonryData from './photo-gallery--variants-masonry.yml';


// ** Media with multiple images

// merge globally stored Media field (image variant) with the Media field of our block

var cbPhotoGalleryGridData = JSON.parse(JSON.stringify(cbPhotoGalleryData));
var cbPhotoGalleryMasonryData = JSON.parse(JSON.stringify(cbPhotoGalleryData));

cbPhotoGalleryGridData.fields.media = JSON.parse(JSON.stringify(window.styleguide.components.atoms.media.images_multiple_same));
cbPhotoGalleryMasonryData.fields.media = JSON.parse(JSON.stringify(window.styleguide.components.atoms.media.images_multiple));

// ** Merge Media variants data with the main block data
//     make a few different groupings of variants

var count = 0;
var cbPhotoGalleryVariants = {
  variants: {}
};

var cbPhotoGalleryVariantsGridObject = {variants: {}};
// loop the variants
for (var key in cbGridData.variants) {
  if (typeof cbGridData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbPhotoGalleryVariantsGridObject.variants[key] = merge(cbPhotoGalleryGridData, cbGridData.variants[key]);
    // merge into a bigger object
    cbPhotoGalleryVariants.variants[count] = cbPhotoGalleryVariantsGridObject.variants[key];
    ++count;
  }
}

var cbPhotoGalleryVariantsMasonryObject = {variants: {}};
// loop the variants
for (var key in cbMasonryData.variants) {
  if (typeof cbMasonryData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbPhotoGalleryVariantsMasonryObject.variants[key] = merge(cbPhotoGalleryMasonryData, cbMasonryData.variants[key]);
    // merge into a bigger object
    cbPhotoGalleryVariants.variants[count] = cbPhotoGalleryVariantsMasonryObject.variants[key];
    ++count;
  }
}

// save in a global var for reuse in other components
window.styleguide.components.molecules.photo_gallery = {
  grid: JSON.parse(JSON.stringify(cbPhotoGalleryVariantsGridObject)),
  masonry: JSON.parse(JSON.stringify(cbPhotoGalleryVariantsMasonryObject)),
  all: cbPhotoGalleryVariants
};

// libraries
import '../../../../00-theme/01-atoms/04-images/styleguide/blazy.load.js';
import '../../../../00-theme/01-atoms/04-images/00-image/images.js';
import './photo-gallery.js';

// documentation
import mdx from './photo-gallery.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/007-PhotoGallery',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const grid = () => (
  <div dangerouslySetInnerHTML={{ __html: cbPhotoGalleryVariantsLayoutsTwig(cbPhotoGalleryVariantsGridObject) }} />
);

export const masonry = () => (
  <div dangerouslySetInnerHTML={{ __html: cbPhotoGalleryVariantsLayoutsTwig(cbPhotoGalleryVariantsMasonryObject) }} />
);
