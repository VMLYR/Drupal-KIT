import React from 'react';

import visualTwig from './visual.twig';

import visualData from './visual.yml';
import visualHomeData from './visual-home.yml';

// import libaries
import '../../../01-atoms/04-images/styleguide/blazy.load.js';
import '../../../01-atoms/04-images/00-image/images.js';
import '../../../01-atoms/04-images/00-image/images.js';

// documentation
import mdx from './visual.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Visual',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const VisualOnHomepage = () => (
  <div dangerouslySetInnerHTML={{ __html: visualTwig({...visualData, ...visualHomeData}) }} />
);
