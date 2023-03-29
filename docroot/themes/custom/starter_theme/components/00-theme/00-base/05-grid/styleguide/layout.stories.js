import React from 'react';

import layoutTwig from './layout.twig';

// documentation
import mdx from './layout.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Base/Layout',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

//
export const usage = () => (
  <div dangerouslySetInnerHTML={{ __html: layoutTwig({}) }} />
);
