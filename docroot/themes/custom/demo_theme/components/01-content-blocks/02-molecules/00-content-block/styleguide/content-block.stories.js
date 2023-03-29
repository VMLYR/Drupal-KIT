import React from 'react';

// documentation
import mdx from './content-block.mdx';

import infoTwig from './info.twig';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Content Blocks',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const info = () => (
  <div dangerouslySetInnerHTML={{ __html: infoTwig({}) }} />
);
