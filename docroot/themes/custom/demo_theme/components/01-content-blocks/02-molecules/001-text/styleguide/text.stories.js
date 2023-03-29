import React from 'react';

const merge = require('deepmerge');

import cbTextTwig from './text.twig';
import cbTextVariantsTwig from './text--variants.twig';

import cbTextData from './text.yml';
import cbTextAlignmentData from './text--variants-alignment.yml';
import cbTextLengthData from './text--variants-length.yml';


// ** Merge alignment variants data with the main block data

var cbTextVariantsAlignmentObject = {variants: {}};

// loop the variants
for (var key in cbTextAlignmentData.variants) {
  if (typeof cbTextAlignmentData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbTextVariantsAlignmentObject.variants[key] = merge(cbTextData, cbTextAlignmentData.variants[key]);
  }
}

// ** Merge text length variants data with the main block data

var cbTextVariantsLengthObject = {variants: {}};
// loop the variants
for (var key in cbTextLengthData.variants) {
  if (typeof cbTextLengthData.variants[key] !== 'undefined') {
    // merge the block data into the variant and add to a new object
    cbTextVariantsLengthObject.variants[key] = merge(cbTextData, cbTextLengthData.variants[key]);
  }
}

// save variant data separately as well, in case we want to render this component separately at some point
var cbTextCenteredObject = cbTextVariantsAlignmentObject.variants[1];

// save in a global var for reuse in other components
window.styleguide.components.molecules.text = {
  base: JSON.parse(JSON.stringify(cbTextData)),
  centered: JSON.parse(JSON.stringify(cbTextCenteredObject)),
  alignments: JSON.parse(JSON.stringify(cbTextVariantsAlignmentObject)),
  content_length: JSON.parse(JSON.stringify(cbTextVariantsLengthObject))
};

// documentation
import mdx from './text.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/001-Text',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTextTwig(cbTextData) }} />
);

export const centered = () => (
  <div dangerouslySetInnerHTML={{ __html: cbTextTwig(cbTextCenteredObject) }} />
);

// export const alignments = () => (
//   <div dangerouslySetInnerHTML={{ __html: cbTextVariantsTwig(cbTextVariantsAlignmentObject) }} />
// );
