import React from 'react';

import poweredTwig from './powered.twig';

import poweredData from './powered.yml';

// documentation
import mdx from './powered.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Powered by',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const poweredBy = () => (
  <div dangerouslySetInnerHTML={{ __html: poweredTwig(poweredData) }} />
);
