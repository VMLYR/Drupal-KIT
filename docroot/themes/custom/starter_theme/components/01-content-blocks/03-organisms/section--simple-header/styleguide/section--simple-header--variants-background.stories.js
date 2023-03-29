import React from 'react';

const merge = require('deepmerge');

import sectionVariantsTwig from './section--simple-header--variants.twig';

import breadcrumbsData from './breadcrumbs.yml';
import sectionData from './section--simple-header.yml';
import sectionBackgroundImage from './background-image.yml';
import sectionVariantsData from './section--simple-header--variants-background.yml';

// ** Import image data into the correct background variants

for (var key in sectionVariantsData.variants) {
  if (typeof sectionVariantsData.variants[key] !== 'undefined') {
    if (typeof sectionVariantsData.variants[key].background_image !== 'undefined') {
      sectionVariantsData.variants[key].layout_background_image = sectionBackgroundImage;
    }
  }
}

// ** Merge single Title block data, as well as the simple-header sections variants

var cbTitleData = window.styleguide.components.molecules.title.base;
var sectionVariantsTitleObject = sectionVariants.get(cbTitleData, 'cb_title', sectionData, sectionVariantsData);

var cbTitleExtendedData = window.styleguide.components.molecules.title.extended;
var sectionVariantsTitleExtendedObject = sectionVariants.get(cbTitleExtendedData, 'cb_title', sectionData, sectionVariantsData);

// Libraries
import '../../../../00-theme/01-atoms/04-images/styleguide/blazy.load.js';
import '../../../../00-theme/01-atoms/04-images/00-image/images.js';

// Extra component styling
import '../../../../00-theme/05-pages/00-styleguide/pages.scss';

// documentation
import mdx from './section--simple-header.mdx';


/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Content Sections/Simple Header/Backgrounds',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** Section in Carousel

// -- Background color

// Simple header
export const withSimpleHeaderBasic = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({
    ...{'breadcrumbs': breadcrumbsData },
    ...sectionVariantsTitleObject})
  }} />
);

// Simple header: extended
export const withSimpleHeaderExtended = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({
    ...{'breadcrumbs': breadcrumbsData },
    ...sectionVariantsTitleExtendedObject})
  }} />
);
