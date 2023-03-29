import React from 'react';

const merge = require('deepmerge');

import cbFocusTwig from './focus.twig';

import cbFocusData from './focus.yml';

// save in a global var for reuse in other components
window.styleguide.components.molecules.focus = {
  base: JSON.parse(JSON.stringify(cbFocusData)),
};

// import libary files - TO DO: import from RS Core library files
import './focus.js'

// documentation
import mdx from './focus.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/006-Focus',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbFocusTwig(cbFocusData) }} />
);
