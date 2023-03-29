import React from 'react';

import exampleTwig from './example.twig';

// documentation
import mdx from './example.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/00-example',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const example = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleTwig({}) }} />
);
