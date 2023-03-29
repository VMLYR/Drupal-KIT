import React from 'react';

const merge = require('deepmerge');

import cbRelatedItemsTwig from './related-items.twig';

import cbRelatedItemsData from './related-items.yml';

// save in a global var for reuse in other components
window.styleguide.components.molecules.related_items = {
  base: JSON.parse(JSON.stringify(cbRelatedItemsData)),
};

// documentation
import mdx from './related-items.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks/010-RelatedItems',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: cbRelatedItemsTwig(cbRelatedItemsData) }} />
);
