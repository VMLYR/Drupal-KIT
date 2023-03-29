import React from 'react';

const merge = require('deepmerge');

import cbFAQTwig from './faq.twig';

import cbFAQData from './faq.yml';

// add tabbed item (global var) field to faq
cbFAQData.fields.tabbed_item = JSON.parse(JSON.stringify(window.styleguide.components.atoms.tabbed_item.base));

// save in a global var for reuse in other components
window.styleguide.components.molecules.faq = {
  base: JSON.parse(JSON.stringify(cbFAQData)),
};

// import libary files - TO DO: import from RS Core library files
import './faq.js'

// documentation
import mdx from './faq.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/003-FAQ',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbFAQTwig(cbFAQData) }} />
);
