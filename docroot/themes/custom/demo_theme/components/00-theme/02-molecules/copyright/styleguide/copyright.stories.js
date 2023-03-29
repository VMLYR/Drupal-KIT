import React from 'react';

import copyrightTwig from './copyright.twig';

import copyrightData from './copyright.yml';

// documentation
import mdx from './copyright.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Copyright',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const copyrightBy = () => (
  <div dangerouslySetInnerHTML={{ __html: copyrightTwig(copyrightData) }} />
);
