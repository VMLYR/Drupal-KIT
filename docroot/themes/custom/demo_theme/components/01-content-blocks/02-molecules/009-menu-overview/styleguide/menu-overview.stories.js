import React from 'react';

const merge = require('deepmerge');

import cbMenuOverviewTwig from './menu-overview.twig';

import cbMenuOverviewData from './menu-overview.yml';



// merge clickthrough field data from Atoms with our Molecule's field
cbMenuOverviewData.fields.menu_clickthrough = merge(window.styleguide.components.atoms.menu_clickthrough.base , cbMenuOverviewData.fields.menu_clickthrough);

// save in a global var for reuse in other components
window.styleguide.components.molecules.menu_overview = {
  base: JSON.parse(JSON.stringify(cbMenuOverviewData)),
};

// documentation
import mdx from './menu-overview.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/009-Menu Overview',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbMenuOverviewTwig(cbMenuOverviewData) }} />
);
