import React from 'react';

const merge = require('deepmerge');

// import section3ColTwig from './section--3-col.twig';
import sectionVariantsTwig from './section--3-col--variants.twig';

import sectionData from './section--3-col.yml';
import sectionVariantsData from './section--3-col--variants-vert-align.yml';

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
import mdx from './section--3-col.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Content Sections/3 columns/Vertical align',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** Section in 2 columns

// -- Vertical alignment

// Text + other
export const with001Text = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsTextObject, ...{'block_type': 'cb_text'}}) }} />
);

// USP
export const with005USP = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsUSPObject, ...{'block_type': 'cb_usp'}}) }} />
);
