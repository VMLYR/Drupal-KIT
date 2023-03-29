import React from 'react';

const merge = require('deepmerge');

import cbTitleTwig from './title.twig';
import cbTitleVariantsTwig from './title--variants.twig';

import cbTitleData from './title.yml';
import cbTitleVariantsData from './title-variants.yml';

// ** Merge Title variants data with the various block data sets

var cbTitleVariantsObject = {variants: {}};

// loop the variants
for (var key in cbTitleVariantsData.variants) {
  if (typeof cbTitleVariantsData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbTitleVariantsObject.variants[key] = merge(cbTitleData, cbTitleVariantsData.variants[key]);
  }
}

// save variant data separately as well, in case we want to render this component separately
var cbTitleExtendedObject = cbTitleVariantsObject.variants[1];

// save in a global var for reuse in other components
window.styleguide.components.molecules.title = {
  base: JSON.parse(JSON.stringify(cbTitleData)),
  extended: JSON.parse(JSON.stringify(cbTitleExtendedObject)),
  variants: JSON.parse(JSON.stringify(cbTitleVariantsObject))
};

// documentation
import mdx from './title.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/011-Title',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTitleTwig(cbTitleData) }} />
);

export const extended = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTitleTwig(cbTitleExtendedObject) }} />
);

// export const variants = () => (
//   <div dangerouslySetInnerHTML={{ __html: cbTitleVariantsTwig(cbTitleVariantsObject) }} />
// );
