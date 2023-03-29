import React from 'react';

import colors from './colors.twig';

import colorsData from './colors.yml';

// documentation
import mdx from './colors.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Base/Colors',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const Palettes = () => (
  <div dangerouslySetInnerHTML={{ __html: colors(colorsData) }} />
);
