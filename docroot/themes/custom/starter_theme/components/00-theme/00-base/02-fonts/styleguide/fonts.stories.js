import React from 'react';

import fontsTwig from './fonts.twig';

// documentation
import mdx from './fonts.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Base/Fonts',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

//
export const usage = () => (
  <div dangerouslySetInnerHTML={{ __html: fontsTwig({}) }} />
);
