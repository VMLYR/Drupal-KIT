import React from 'react';

const merge = require('deepmerge');

import cbFormTwig from './form.twig';

import cbFormData from './form.yml';

// save in a global var for reuse in other components
window.styleguide.components.molecules.form = {
  base: JSON.parse(JSON.stringify(cbFormData)),
};

// import libary files
import '../../../../00-theme/01-atoms/05-forms/forms.js';

// documentation
import mdx from './form.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/008-Form',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbFormTwig(cbFormData) }} />
);
