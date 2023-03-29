import React from 'react';

const merge = require('deepmerge');

import cbRelatedTwig from './page-related.twig';

import cbRelatedData from './page-related.yml';

// save in a global var for reuse in other components
window.styleguide.components.organisms.page = window.styleguide.components.organisms.page || {};
window.styleguide.components.organisms.page.related = {
  base: JSON.parse(JSON.stringify(cbRelatedData)),
};

// documentation
import mdx from './page-related.mdx';

/**
 * Storybook Definition.
 */
// export default {
//   title: 'Organisms/Page/Related',
//   parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
// };

// export const base = () => (
//   <div dangerouslySetInnerHTML={{ __html: cbRelatedTwig(cbRelatedData) }} />
// );
