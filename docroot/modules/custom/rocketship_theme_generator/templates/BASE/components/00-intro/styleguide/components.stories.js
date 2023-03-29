import React from 'react';

import compTwig from './component.twig';

// documentation
import mdx from './components.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Intro/Components',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

//
export const info = () => (
  <div dangerouslySetInnerHTML={{ __html: compTwig({}) }} />
);
