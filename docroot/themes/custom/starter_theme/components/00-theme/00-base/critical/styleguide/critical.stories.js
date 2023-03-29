import React from 'react';

import criticalTwig from './critical.twig';

// documentation
import mdx from './critical.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Base/Critical CSS',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

//
export const usage = () => (
  <div dangerouslySetInnerHTML={{ __html: criticalTwig({}) }} />
);
