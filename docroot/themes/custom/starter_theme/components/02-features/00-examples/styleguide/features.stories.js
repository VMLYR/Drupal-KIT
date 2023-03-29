import React from 'react';

import compTwig from './component.twig';

// documentation
import mdx from './features.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Intro/Features',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

//
export const info = () => (
  <div dangerouslySetInnerHTML={{ __html: compTwig({}) }} />
);
